<?php

$connect = mysqli_connect("localhost", "root", "", "animal_chipization_db");

function getOneAccount($connect, $id){
    $accaount = mysqli_query($connect, "SELECT `id`, `firstName`, `lastName`, `email` FROM `accounts` WHERE `id` = '$id'");

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

function getSearchAccount($connect){
    if (isset($_GET["firstName"])) $firstName = $_GET["firstName"];
    // $firstName = isset($_GET["firstName"]) ? $_GET["firstName"] : null;
    die(var_dump($firstName));
}
