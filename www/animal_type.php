<?php

//GET API 4.1: Получение информации о типе животного
function getAnimalTypeById($connect, $id){
    //запрос в бд
    $animal_types = mysqli_query($connect, "SELECT `id`, `type` FROM `types` WHERE `id` = '$id'");

    //$id <= 0 || $id == null  - 400
    if ($id <= 0 || is_null($id)){
        giveError(400, "Incorrect id");
        return;
    }

    //Неверные авторизационные данные - 401
    if (validAuthorize($connect)){
        giveError(401, "Authorization error");
        return;
    }

    //аккаунт не найден - 404
    if (mysqli_num_rows($animal_types) === 0){
        giveError(404, "Animal type not found");
        return;
    }

    $animal_types = mysqli_fetch_assoc($animal_types);
    echo json_encode($animal_types);
}


//POST API 4.2: Добавление типа животного
function addAnimalType($connect){
    $data = file_get_contents("php://input");
    $data = json_decode($data, true);

    $type = $_POST["type"] ?? ($data["type"] ?? null);

    //type = null, type = "" или состоит из пробелов - 400
    if (validData($type)){
        giveError(400, "Invalid data");
        return;
    }
    
    //Запрос от неавторизованного аккаунта Неверные авторизационные данные - 401
    if (validAuthorize($connect, true)){
        giveError(401, "Authorization error");
        return;
    }

    //Тип животного с таким type уже существует - 409
    if (mysqli_num_rows(mysqli_query($connect, "SELECT * FROM `types` WHERE `type` = '$type'")) !== 0){
        giveError(409, "This type already exists");
        return;
    }

    mysqli_query($connect, "INSERT INTO `types` (`id`, `type`) VALUES (null, '$type')");
    http_response_code(201);
    echo json_encode([
        "id" => mysqli_insert_id($connect),
        "type" => $type
    ]);
}
