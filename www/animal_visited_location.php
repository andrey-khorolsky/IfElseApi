<?php

//GET API 6.1: Просмотр точек локации, посещенных животным
function getVisitedLocations($connect, $id){
    
    $query = "SELECT `id`, DATE_FORMAT(`dateTimeOfVisitLocationPoint`, '%Y-%m-%dT%T+03:00') as dateTimeOfVisitLocationPoint, `id_location` as locationPointId FROM `animal_locations` WHERE `id_animal` = '$id'";
    $locationsList = [];

    $startDateTime = $_GET["startDateTime"] ?? null;
    $endDateTime = $_GET["endDateTime"] ?? null;
    $from = $_GET['from'] ?? 0;
    $size = $_GET['size'] ?? 10;

    // animalId = null,
    // animalId <= 0,
    // from < 0,
    // size <= 0,
    // startDateTime - не в формате ISO-8601,
    // endDateTime - не в формате ISO-8601
    // 400
    if ($id == null || $id <= 0 || $from < 0 || $size <= 0 || dateTimeIso($startDateTime) || dateTimeIso($endDateTime)){
        giveError(400, "Bad request");
        return;
    }

    //Неверные авторизационные данные - 401
    if (validAuthorize($connect)){
        giveError(401, "Authorization error");
        return;
    }
    
    //аккаунт не найден - 404
    if (mysqli_num_rows(mysqli_query($connect, "SELECT `id` FROM `animals` WHERE `id` = '$id'")) === 0){
        giveError(404, "Animal not found");
        return;
    }

    if (isset($startDateTime)){
        $query .= " AND `dateTimeOfVisitLocationPoint` >= '$startDateTime' ";
    }
    if (isset($endDateTime)){
        $query .= " AND `dateTimeOfVisitLocationPoint` <= '$endDateTime' ";
    }
    $animal_locations = mysqli_query($connect, $query);

    //пропускаем указанное кол-во элементов
    for($i=0; $i++<$from; mysqli_fetch_assoc($animal_locations));
    
    //вывод указаного кол-ва элементов
    while ($location = mysqli_fetch_assoc($animal_locations)){
        $locationsList[] = $location;
        if (--$size === 0) break;
    }

    echo json_encode($locationsList);
}


//POST API 6.2: Добавление точки локации, посещенной животным
function addVisitedLocationToAnimal($connect, $animalId, $locationId){
    
    //animalId = null, animalId <= 0,
    // pointId= null, pointId <= 0,
    // У животного lifeStatus = "DEAD"
    // Животное находится в точке чипирования и никуда не перемещалось, попытка добавить точку локации, равную точке чипирования.
    // Попытка добавить точку локации, в которой уже находится животное
    if (validDidgitData($animalId, $locationId)){
        giveError(400, "Invalid id");
        return;
    }

    //Животное с animalId не найдено Точка локации с pointId не найдена - 404
    if ((mysqli_num_rows(mysqli_query($connect, "SELECT `id` FROM `animals` WHERE `id` = '$animalId'")) !== 1)
    || (mysqli_num_rows(mysqli_query($connect, "SELECT `id` FROM `locations` WHERE `id` = '$locationId'")) !== 1)){
        giveError(404, "Animal or location not found");
        return;
    }

    if ((mysqli_fetch_assoc(mysqli_query($connect, "SELECT `lifeStatus` FROM `animals` WHERE `id` = '$animalId'"))["lifeStatus"] === "DEAD")){
        giveError(400, "Animal is dead");
        return;
    }
    // || mysqli_num_rows(mysqli_query($connect, "SELECT * FROM `animal_locations` WHERE `id_animal` = '$animalId' AND `id_location` = '$locationId'")) !== 0
    if (mysqli_fetch_assoc(mysqli_query($connect, "SELECT `chippingLocationId` FROM `animals` WHERE `id` = '$animalId'"))["chippingLocationId"] == $locationId){
        giveError(400, "Animal in chipping location");
        return;
    }

    //Запрос от неавторизованного аккаунта Неверные авторизационные данные - 401
    if (validAuthorize($connect, true)){
        giveError(401, "Authorization error");
        return;
    }


    mysqli_query($connect, "INSERT INTO `animal_locations` (`id`, `id_animal`, `id_location`, `dateTimeOfVisitLocationPoint`) VALUES (null, '$animalId', '$locationId',  DATE_FORMAT(CURRENT_TIMESTAMP, '%Y-%m-%dT%T+03:00'))");
    http_response_code(201);
    $id = mysqli_insert_id($connect);
    echo json_encode([
        "id" => $id,
        "dateTimeOfVisitLocationPoint" => mysqli_fetch_assoc(mysqli_query($connect, "SELECT DATE_FORMAT(`dateTimeOfVisitLocationPoint`, '%Y-%m-%dT%T+03:00') as dateTimeOfVisitLocationPoint FROM `animal_locations` WHERE `id` = '$id'"))["dateTimeOfVisitLocationPoint"],
        "locationPointId" => $locationId
    ]);
}


//PUT API 6.3: Изменение точки локации, посещенной животным
function changeAnimalVisitedLocation($connect, $animalId){
    $locations = file_get_contents("php://input");
    $locations = json_decode($locations, true);

    $visitedLocationPointId = $locations["visitedLocationPointId"];
    $locationPointId = $locations["locationPointId"];

    //animalId = null,
    // animalId <= 0,
    // visitedLocationPointId = null,
    // visitedLocationPointId <= 0,
    // locationPointId = null,
    // locationPointId <= 0
    // Обновление первой посещенной точки на точку чипирования
    // Обновление точки на такую же точку
    // Обновление точки локации на точку, совпадающую со следующей и/или с предыдущей точками - 400
    if (validDidgitData($animalId, $visitedLocationPointId, $locationPointId)){
        giveError(400, "Invalid data");
        return;
    }

    //Запрос от неавторизованного аккаунта Неверные авторизационные данные - 401
    if (validAuthorize($connect, true)){
        giveError(401, "Authorization error");
        return;
    }

    // Животное с animalId не найдено
    // Объект с информацией о посещенной точке локации с visitedLocationPointId не найден.
    // У животного нет объекта с информацией о посещенной точке локации с visitedLocationPointId.
    // Точка локации с locationPointId не найденa - 404
    if (mysqli_num_rows(mysqli_query($connect, "SELECT `id` FROM `animals` WHERE `id` = '$animalId'")) !== 1
    || mysqli_num_rows(mysqli_query($connect, "SELECT `id` FROM `animal_locations` WHERE `id` = '$visitedLocationPointId' AND `id_animal` = '$animalId'")) !== 1
    || mysqli_num_rows(mysqli_query($connect, "SELECT `id` FROM `locations` WHERE `id` = '$locationPointId'")) !== 1){
        giveError(404, "Ainmal or Location not found. Or this animal wasnt in this location");
        return;
    }

    mysqli_query($connect, "UPDATE `animal_locations` SET `id_location` = '$locationPointId'");
    echo json_encode([
        "id" => $visitedLocationPointId,
        "dateTimeOfVisitLocationPoint" => mysqli_fetch_assoc(mysqli_query($connect, "SELECT DATE_FORMAT(`dateTimeOfVisitLocationPoint`, '%Y-%m-%dT%T+03:00') as dateTimeOfVisitLocationPoint FROM `animal_locations` WHERE `id` = '$visitedLocationPointId'"))["dateTimeOfVisitLocationPoint"],
        "locationPointId" => $locationPointId
    ]);

}


//DELETE API 6.4: Удаление точки локации, посещенной животным
function deleteVisitedLocation($connect, $animalId, $visitedPointId){

    //animalId = null, animalId <= 0
    // visitedPointId = null, visitedPointId <= 0 - 400
    if (validDidgitData($animalId, $visitedPointId)){
        giveError(400, "Invalid data");
        return;
    }

    //Запрос от неавторизованного аккаунта Неверные авторизационные данные - 401
    if(validAuthorize($connect, true)){
        giveError(401, "Authorization error");
        return;
    }

    //Животное с animalId не найдено
    // Объект с информацией о посещенной точке локации с visitedPointId не найден.
    // У животного нет объекта с информацией о посещенной точке локации с visitedPointId - 404
    if (mysqli_num_rows(mysqli_query($connect, "SELECT `id` FROM `animals` WHERE `id` = '$animalId'")) !== 1
    || mysqli_num_rows(mysqli_query($connect, "SELECT `id` FROM `animal_locations` WHERE `id` = '$visitedPointId' AND `id_animal` = '$animalId'")) === 0){
        giveError(404, "Ainmal or Location not found. Or this animal wasnt in this location");
        return;
    }

    //удаление посещенной точки локации
    mysqli_query($connect, "DELETE FROM `animal_locations` WHERE `id` = '$visitedPointId'");

    //получение информации о первой посещенной точке
    $firstVisitedLocation = mysqli_fetch_assoc(mysqli_query($connect, "SELECT `id`, `id_location` FROM `animal_locations` WHERE `id_animal` = '$animalId'"));
    $firstVisitedLocationId = $firstVisitedLocation["id_location"];
    $chipperedLocationId = mysqli_fetch_assoc(mysqli_query($connect, "SELECT `chippingLocationId` FROM `animals` WHERE `id` = '$animalId"))["chippingLocationId"];

    //если id первой посещенной точки = id чипирования, то ее тоже удаляем
    if ($firstVisitedLocationId === $chipperedLocationId){
        $firstLocationId = $firstVisitedLocation["id"];
        mysqli_query($connect, "DELETE FROM `animal_locations` WHERE `id` ='$firstLocationId'");
    }

}
