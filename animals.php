<?php

function getAnimalById($connect, $id){
    //запрос в бд
    $accaount = mysqli_query($connect, "SELECT `id`, `weight`, `length`, `height`, `gender`, `lifeStatus`, `chippingDateTime`, `chipperId`, `chippingLocationId`, `deathDateTime` FROM `animals` WHERE `id` = '$id'");

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
    if (mysqli_num_rows($accaount) === 0){
        http_response_code(404);

        echo json_encode([
            "status" => false,
            "message" => "Account not found"
        ]);
        return;
    }

    $accaount = mysqli_fetch_assoc($accaount);
    echo json_encode($accaount);
}

function getSearchAnimals($connect){

}
