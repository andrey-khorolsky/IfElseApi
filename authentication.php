<?php

//API 1.1: Регистрация нового аккаунта
function registrationAccount(){
    //
    if (validData()) giveError(400, "Invalid data");

    //Запрос от авторизованного аккаунта - 403

    //Аккаунт с таким email уже существует - 409
}


//валидация данных. all right -> false
function validData(){
    if (trim($_POST["firstName"]) === "" || trim($_POST["lastName"]) === "" || trim($_POST["email"]) === "" || trim($_POST["password"]) === "") return true;

    $reg = "/^([a-zA-Z0-9]+[a-zA-Z0-9._-]+[a-zA-Z0-9]+@[a-zA-Z0-9._-]+\.[a-zA-Z]{2,})$/";
    if (preg_match($reg, $_POST["email"]) !== 1) return true;
    
    return false;
}
