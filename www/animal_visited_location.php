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

    //Запрос от неавторизованного аккаунта Неверные авторизационные данные - 404
    if ((mysqli_num_rows(mysqli_query($connect, "SELECT `id` FROM `animals` WHERE `id` = '$animalId'")) !== 1)
    || (mysqli_num_rows(mysqli_query($connect, "SELECT `id` FROM `locations` WHERE `id` = '$locationId'")) !== 1)){
        giveError(404, "Animal or location not found");
        return;
    }

    mysqli_query($connect, "INSERT INTO `animal_locations` (`id`, `id_animal`, `id_location`, `dateTimeOfVisitLocationPoint`) VALUES (null, '$animalId', '$locationId',  DATE_FORMAT(CURRENT_TIMESTAMP, '%Y-%m-%dT%T+03:00'))");
    http_response_code(201);
    $id = mysqli_insert_id($connect);
    echo json_encode([
        "id" => $id,
        "dateTimeOfVisitLocationPoint" => mysqli_fetch_assoc(mysqli_query($connect, "SELECT `dateTimeOfVisitLocationPoint` FROM `animal_locations` WHERE `id` = '$id'"))["dateTimeOfVisitLocationPoint"],
        "locationPointId" => $locationId
    ]);
}
