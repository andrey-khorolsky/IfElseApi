<?php

//API 3.1: Получение информации о точке локации животных
function getLocationById($connect, $id){
    //запрос в бд
    $location = mysqli_query($connect, "SELECT `id`, `latitude`, `longitude` FROM `locations` WHERE `id` = '$id'");

    //неверный id - 400
    if ($id <= 0 || $id == null){
        giveError(400, "Incorrect id");
        return;
    }

    //Неверные авторизационные данные - 401
    if (validAuthorize($connect)){
        giveError(401, "Authorization error");
        return;
    }

    //аккаунт не найден - 404
    if (mysqli_num_rows($location) === 0){
        giveError(404, "Location not found");
        return;
    }

    $location = mysqli_fetch_assoc($location);
    echo json_encode($location);
}


//API 3.2: Добавление точки локации животных
function addLocation($connect){
    
    $data = file_get_contents("php://input");
    $data = json_decode($data, true);

    $latitude = $_POST["latitude"] ?? ($data["latitude"] ?? null);
    $longitude = $_POST["longitude"] ?? ($data["latitude"] ?? null);

    //невалидные координаты 
    if (validCoordinates($latitude, $longitude)){
        giveError(400, "Invalid coord");
        return;
    }

    // Запрос от неавторизованного акк, Неверные авторизационные данные - 401
    if (notAuthorize() || validAuthorize($connect)){
        giveError(401, "Authorization error");
        return;
    }

    //Точка локации с такими latitude и longitude уже существует - 409
    if (mysqli_num_rows(mysqli_query($connect, "SELECT * FROM `locations` WHERE `latitude` = '$latitude' AND `longitude` = '$longitude'")) !== 0){
        giveError(409, "Location is existed");
        return;
    }

    mysqli_query($connect, "INSERT INTO `locations` (`id`, `latitude`, `longitude`) VALUES (NULL, '$latitude', '$longitude')");

    http_response_code(201);
    echo json_encode([
        "id" => mysqli_insert_id($connect),
        "latitude" => $latitude,
        "longitude" => $longitude
    ]);
}


//API 3.4: Удаление точки локации животных
function deleteLocationById($connect, $id){
    
    //pointId = null, pointId <= 0, Точка локации связана с животным - 400
    if (is_null($id) || $id <=0 || mysqli_num_rows(mysqli_query($connect, "SELECT * FROM `animals` WHERE `chippingLocationId` = '$id'")) !== 0){
        giveError(400, "Invalid id");
        return;
    }

    // Запрос от неавторизованного акк, Неверные авторизационные данные - 401
    if (notAuthorize() || validAuthorize($connect)){
        giveError(401, "Authorization error");
        return;
    }

    // Точка локации с таким pointId не найдена - 404
    if ((mysqli_num_rows(mysqli_query($connect, "SELECT * FROM `locations` WHERE `id` = '$id'")) === 0)){
        giveError(404, "Location not found");
        return;
    }

    mysqli_query($connect, "DELETE FROM `locations` WHERE `id` = '$id'");

}

//API 3.3: Изменение точки локации животных
function changeLocation($connect, $id){
    $newData = file_get_contents("php://input");
    $newData = json_decode($newData, true);

    $latitude = $newData["latitude"];
    $longitude = $newData["longitude"];

    //невалидный id или координаты 
    if (is_null($id) || $id <= 0 || validCoordinates($latitude, $longitude)){
        giveError(400, "Invalid data");
        return;
    }

    // Запрос от неавторизованного акк, Неверные авторизационные данные - 401
    if (notAuthorize() || validAuthorize($connect)){
        giveError(401, "Authorization error");
        return;
    }

    // Точка локации с таким pointId не найдена - 404
    if ((mysqli_num_rows(mysqli_query($connect, "SELECT * FROM `locations` WHERE `id` = '$id'")) === 0)){
        giveError(404, "Location not found");
        return;
    }

    //Точка локации с такими latitude и longitude уже существует - 409
    if (mysqli_num_rows(mysqli_query($connect, "SELECT * FROM `locations` WHERE `latitude` = '$latitude' AND `longitude` = '$longitude'")) !== 0){
        giveError(409, "Location is existed");
        return;
    }

    mysqli_query($connect, "UPDATE `accounts` SET `latitude` = '$latitude', `longitude` = '$longitude' WHERE `id` = '$id'");


    echo json_encode([
        "id" => mysqli_insert_id($connect),
        "latitude" => $latitude,
        "longitude" => $longitude
    ]);
}
