<?php

function getLocationById($connect, $id){
    //запрос в бд
    $location = mysqli_query($connect, "SELECT `id`, `latitude`, `longitude` FROM `locations` WHERE `id` = '$id'");

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
    if (mysqli_num_rows($location) === 0){
        http_response_code(404);

        echo json_encode([
            "status" => false,
            "message" => "Location not found"
        ]);
        return;
    }

    $location = mysqli_fetch_assoc($location);
    echo json_encode($location);
}
