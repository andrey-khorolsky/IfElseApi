<?php

function getAnimalTypeById($connect, $id){
    //запрос в бд
    $animal_types = mysqli_query($connect, "SELECT `id`, `type` FROM `types` WHERE `id` = '$id'");

    //$id <= 0 || $id == null  - 400
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
    if (mysqli_num_rows($animal_types) === 0){
        http_response_code(404);

        echo json_encode([
            "status" => false,
            "message" => "Animal type not found"
        ]);
        return;
    }

    $animal_types = mysqli_fetch_assoc($animal_types);
    echo json_encode($animal_types);
}
