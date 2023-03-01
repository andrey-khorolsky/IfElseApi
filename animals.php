<?php

//поиск типов животного и добавление информации в результат
function getAnimalsType($connect, $animal){
    //берется id животного
    $id = $animal["id"];
    $typesList = [];
    $locationsList = [];

    //запрос в бд о его типах
    $animal_types = mysqli_query($connect, "SELECT `id_type` FROM `animal_types` WHERE `id_animal` = '$id'");
    $animal_locations = mysqli_query($connect, "SELECT `id_location` FROM `animal_locations` WHERE `id_animal` = '$id'");
    
    //добавление типов в массив
    while ($type = mysqli_fetch_assoc($animal_types)){
        array_push($typesList, array_values($type));
    }
    $animal_types = ["animalTypes" => $typesList];

    //добавление локаций в массив
    while ($locations = mysqli_fetch_assoc($animal_locations)){
        array_push($locationsList, array_values($locations));
    }
    $animal_locations = ["visitedLocations" => $locationsList];

    return array_merge(array_slice($animal, 0, 1), $animal_types, array_slice($animal, 1, 8), $animal_locations, array_slice($animal, 8));
}


function getAnimalById($connect, $id){
    //запрос в бд
    $animal = mysqli_query($connect, "SELECT `id`, `weight`, `length`, `height`, `gender`, `lifeStatus`, `chippingDateTime`, `chipperId`, `chippingLocationId`, `deathDateTime` FROM `animals` WHERE `id` = '$id'");
    
    //неверный id - 400
    if ($id <= 0 || $id == null){
        http_response_code(400);

        echo json_encode([
            "status" => false,
            "message" => "Incorrect id"
        ]);
        return;
    }

    //Неверные авторизационные данные - 401  ???????

    //аккаунт не найден - 404
    if (mysqli_num_rows($animal) === 0){
        http_response_code(404);

        echo json_encode([
            "status" => false,
            "message" => "Animal not found"
        ]);
        return;
    }

    $animal = mysqli_fetch_assoc($animal);

    echo json_encode(getAnimalsType($connect, $animal));
}


function getSearchAnimals($connect){
    
    //запрос и кол-во параметров
    $query = "SELECT `id`, `weight`, `length`, `height`, `gender`, `lifeStatus`, `chippingDateTime`, `chipperId`, `chippingLocationId`, `deathDateTime` FROM `animals` WHERE ";
    $first = false;

    //переменные по дефолту
    $startDateTime = $_GET['startDateTime'] ?? null;
    $endDateTime = $_GET['endDateTime'] ?? null;
    $chipperId = $_GET['chipperId'] ?? null;
    $chippingLocationId = $_GET['chippingLocationId'] ?? null;
    $lifeStatus = $_GET['lifeStatus'] ?? null;
    $gender = $_GET['gender'] ?? null;
    $from = $_GET['from'] ?? 0;
    $size = $_GET['size'] ?? 10;

    //from < 0 || size <= 0 - 400
    if ($from < 0 || $size <= 0){
        http_response_code(400);
        echo json_encode([
            "status" => false,
            "message" => "Bad request"
        ]);
        return;
    }

    //Неверные авторизационные данные - 401  ???????


    //создание запроса в зависимости от реквеста
    if (isset($startDateTime)){
        $query .= "`chippingDateTime` >= '$startDateTime' ";
        $first = true;
    }
    if (isset($endDateTime)){
        if ($first) $query .= " AND ";
        else $first = true;
        $query .= "`chippingDateTime` <= '$endDateTime' ";
    }
    if (isset($chipperId)){
        if ($first) $query .= " AND ";
        else $first = true;
        $query .= "`chipperId` = '$chipperId' ";
    }
    if (isset($chippingLocationId)){
        if ($first) $query .= " AND ";
        else $first = true;
        $query .= "`chippingLocationId` = '$chippingLocationId' ";
    }
    if (isset($lifeStatus)){
        if ($first) $query .= " AND ";
        else $first = true;
        $query .= "`lifeStatus` = '$lifeStatus' ";
    }
    if (isset($gender)){
        if ($first) $query .= " AND ";
        else $first = true;
        $query .= "`gender` = '$gender' ";
    }

    $animalList = [];
    $searchAnimals = mysqli_query($connect, $query);

    //пропускаем указанное кол-во элементов
    for($i=0; $i++<$from; mysqli_fetch_assoc($searchAnimals));
    
    //вывод указаного кол-ва элементов
    while ($animal = mysqli_fetch_assoc($searchAnimals)){
        $animalList[] = getAnimalsType($connect, $animal);
        if (--$size === 0) break;
    }

    echo json_encode($animalList);
}
