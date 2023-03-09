<?php

//API 1.1: Регистрация нового аккаунта
function registrationAccount($connect){

    $firstName = $_POST["firstName"] ?? null;
    $lastName = $_POST["lastName"] ?? null;
    $email = $_POST["email"] ?? null;
    $password = $_POST["password"] ?? null;
    
    //валидация данных - 400
    if (validData($firstName, $lastName, $email, $password)){
        giveError(400, "Invalid data");
        return;
    }

    //Запрос от авторизованного аккаунта - 403

    //Аккаунт с таким email уже существует - 409
    if (mysqli_num_rows(mysqli_query($connect, "SELECT * FROM `accounts` WHERE `email` = '$email'")) !== 0){
        giveError(409, "This email is used");
        return;
    }


    mysqli_query($connect, "INSERT INTO `accounts` (`id`, `firstName`, `lastName`, `email`, `password`) VALUES (null, '$firstName', '$lastName', '$email', '$password')");

    http_response_code(201);
    echo json_encode([
        "id" => mysqli_insert_id($connect),
        "firstName" => $firstName,
        "lastName" => $lastName,
        "email" => $email
    ]);
}

