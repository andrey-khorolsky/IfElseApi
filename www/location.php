<?php


$method = $_SERVER["REQUEST_METHOD"];
$page = $_GET["q"];
$page = explode("/", $page);
require("database.php");
require("common_function.php");

if ($method === "DELETE")
    deleteLocationById($connect, $page[1]);
elseif ($method === "GET")
    getLocationById($connect, $page[1]);
elseif ($method === "POST")
    addLocation($connect);
elseif ($method === "PUT")
    changeLocation($connect, $page[1]);

//GET API 3.1: Получение информации о точке локации животных
function getLocationById($connect, $id){
    //запрос в бд
    $location = mysqli_query($connect, "SELECT `id`, `latitude`, `longitude` FROM `locations` WHERE `id` = '$id'");
    // var_dump($location);

    //неверный id - 400
    if ($id <= 0 || $id == null){
        giveError(400, "Incorrect id");
        return;
    }

    //Неверные авторизационные данные - 401
    if (notValidAuthorize($connect)){
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


//POST API 3.2: Добавление точки локации животных
function addLocation($connect){
    
    $data1 = file_get_contents("php://input");
    $data = json_decode($data1, true);

    $latitude = $_POST["latitude"] ?? ($data["latitude"] ?? null);
    $longitude = $_POST["longitude"] ?? ($data["longitude"] ?? null);

    //невалидные координаты 
    if (!validCoordinates($latitude, $longitude)){
        giveError(400, "Invalid coord");
        return;
    }

    // Запрос от неавторизованного акк, Неверные авторизационные данные - 401
    if (notValidAuthorize($connect, true)){
        giveError(401, "Authorization error");
        return;
    }

    //Точка локации с такими latitude и longitude уже существует - 409
    if (mysqli_num_rows(mysqli_execute_query($connect, "SELECT * FROM `locations` WHERE `latitude` = ? AND `longitude` = ?", [$latitude, $longitude])) !== 0){
        giveError(409, "Location is existed");
        return;
    }

    // подготовка запроса
    $stmt = mysqli_stmt_init($connect);
    mysqli_stmt_prepare($stmt, "INSERT INTO `locations` (`id`, `latitude`, `longitude`) VALUES (NULL, ?, ?)");
    mysqli_stmt_bind_param($stmt, "dd", $latitude, $longitude);

    // вставка
    mysqli_stmt_execute($stmt);

    http_response_code(201);
    echo json_encode([
        "id" => mysqli_insert_id($connect),
        "latitude" => $latitude,
        "longitude" => $longitude
    ]);
}


//PUT API 3.3: Изменение точки локации животных
function changeLocation($connect, $id){
    $newData = file_get_contents("php://input");
    $newData = json_decode($newData, true);

    $latitude = $newData["latitude"];
    $longitude = $newData["longitude"];

    //невалидный id или координаты 
    if (is_null($id) || $id <= 0 || !validCoordinates($latitude, $longitude)){
        giveError(400, "Invalid data");
        return;
    }

    // Запрос от неавторизованного акк, Неверные авторизационные данные - 401
    if (notValidAuthorize($connect, true)){
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

    mysqli_query($connect, "UPDATE `locations` SET `latitude` = '$latitude', `longitude` = '$longitude' WHERE `id` = '$id'");


    echo json_encode([
        "id" => $id,
        "latitude" => $latitude,
        "longitude" => $longitude
    ]);
}


//DELETE API 3.4: Удаление точки локации животных
function deleteLocationById($connect, $id){
    
    //pointId = null, pointId <= 0, Точка локации связана с животным - 400
    if (is_null($id) || $id <=0 || mysqli_num_rows(mysqli_query($connect, "SELECT * FROM `animals` WHERE `chippingLocationId` = '$id'")) !== 0){
        giveError(400, "Invalid id");
        return;
    }

    // Запрос от неавторизованного акк, Неверные авторизационные данные - 401
    if (notValidAuthorize($connect, true)){
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


//валидация координат
function validCoordinates($latitude, $longitude){
    //latitude = null, latitude < -90, latitude > 90,
    // longitude = null, longitude < -180, longitude > 180

    if (is_null($latitude) || ($latitude < (-90)) || ($latitude > 90) || is_null($longitude) || ($longitude < (-180)) || ($longitude > 180))
        return false;
    return true;
}
