<?php

//API 1.1: Регистрация нового аккаунта
function registrationAccount($connect){

    //валидация данных - 400
    if (validData()){
        giveError(400, "Invalid data");
        return;
    }

    //Запрос от авторизованного аккаунта - 403

    //Аккаунт с таким email уже существует - 409
    $email = $_POST["email"] ?? null;
    if (mysqli_num_rows(mysqli_query($connect, "SELECT * FROM `accounts` WHERE `email` = '$email'")) !== 0){
        giveError(409, "This email is used");
        return;
    }

    $firstName = $_POST["firstName"] ?? null;
    $lastName = $_POST["lastName"] ?? null;
    $password = $_POST["password"] ?? null;

    mysqli_query($connect, "INSERT INTO `accounts` (`id`, `firstName`, `lastName`, `email`, `password`) VALUES (null, '$firstName', '$lastName', '$email', '$password')");

    http_response_code(201);
    echo json_encode([
        "id" => mysqli_insert_id($connect),
        "firstName" => $firstName,
        "lastName" => $lastName,
        "email" => $email
    ]);
}


//валидация данных. all right -> false
function validData(){
    if (!isset($_POST["firstName"], $_POST["lastName"], $_POST["email"], $_POST["password"])) return true;
    if (trim($_POST["firstName"]) === "" || trim($_POST["lastName"]) === "" || trim($_POST["email"]) === "" || trim($_POST["password"]) === "") return true;

    $reg = "/^([a-zA-Z0-9]+[a-zA-Z0-9._-]+[a-zA-Z0-9]+@[a-zA-Z0-9._-]+\.[a-zA-Z]{2,})$/";
    if (preg_match($reg, $_POST["email"]) !== 1) return true;
    
    return false;
}
