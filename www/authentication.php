<?php

//API 1.1: Регистрация нового аккаунта
function registrationAccount($connect){
    
    $data = file_get_contents("php://input");
    $data = json_decode($data, true);

    $firstName = $_POST["firstName"] ?? ($data["firstName"] ?? null);
    $lastName = $_POST["lastName"] ?? ($data["lastName"] ?? null);
    $email = $_POST["email"] ?? ($data["email"] ?? null);
    $password = $_POST["password"] ?? ($data["password"] ?? null);

    //валидация данных - 400
    if (validData($firstName, $lastName, $password) || validEmail($email)){
        giveError(400, "Invalid data");
        return;
    }

    //Запрос от авторизованного аккаунта - 403
    if (isset(getallheaders()["Authorization"])){
        giveError(401, "Authorization error");
        return;
    }

    //Аккаунт с таким email уже существует - 409
    if (mysqli_num_rows(mysqli_query($connect, "SELECT * FROM `accounts` WHERE `email` = '$email'")) !== 0){
        giveError(409, "This email is used");
        return;
    }

    $firstName = mysqli_real_escape_string($connect, $firstName);
    $lastName = mysqli_real_escape_string($connect, $lastName);
    $email = mysqli_real_escape_string($connect, $email);
    $password = mysqli_real_escape_string($connect, $password);

    mysqli_query($connect, "INSERT INTO `accounts` (`id`, `firstName`, `lastName`, `email`, `password`) VALUES (null, '$firstName', '$lastName', '$email', '$password')");

    http_response_code(201);
    echo json_encode([
        "id" => mysqli_insert_id($connect),
        "firstName" => $firstName,
        "lastName" => $lastName,
        "email" => $email
    ]);
}

