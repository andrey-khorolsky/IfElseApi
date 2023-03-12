<?php

//вывод ошибки с сообщением
function giveError($err, $msg){
    http_response_code($err);

    echo json_encode([
        "status" => false,
        "message" => $msg
    ]);
}


//проверка формата даты. all right -> false
function dateTimeIso($datetime){
    if ($datetime == null) return false;
    try{
        $date = date_create($datetime);
        date_format($date, 'Y-m-d H:i:s');
    } catch(Throwable){
        return true;
    }
    return  false;
}


//валидация численных значений. all right -> flase
function validDidgitData(...$data){
    foreach ($data as $d){
        if (is_null($d) || $d <= 0) return true;
    }
    return false;
}


//валидация данных. all right -> false
function validData(...$data){
    foreach ($data as $d){
        if (is_null($d) || trim($d) === "") return true;
    }
    return false;
}


//валидация почты. all right -> false
function validEmail($email){
    if (is_null($email)) return true;
    $reg = "/^([a-zA-Z0-9]+[a-zA-Z0-9._-]+[a-zA-Z0-9]+@[a-zA-Z0-9._-]+\.[a-zA-Z]{2,})$/";
        if (preg_match($reg, $email) === 1) return false;
    return true;
}


//валидация координат. all right -> false
function validCoordinates($latitude, $longitude){
    //latitude = null, latitude < -90, latitude > 90,
    // longitude = null, longitude < -180, longitude > 180

    if (is_null($latitude) || ($latitude < (-90)) || ($latitude > 90) || is_null($longitude) || ($longitude < (-180)) || ($longitude > 180))
        return true;
    return false;
}

//проверка авторизационных данных. all right -> false
function validAuthorize($connect){
    
    if (is_null($authorization = getallheaders()["Authorization"] ?? null)) return false;

    $authorization = substr($authorization, stripos($authorization, " ")+1); //login:pass (in base64)
    $authorization = base64_decode($authorization);  //login:pass (in unicode)
    
    $login = substr($authorization, 0, stripos($authorization, ":"));  //login
    $pass = substr($authorization, stripos($authorization, ":")+1);  //password

    if (mysqli_num_rows(mysqli_query($connect, "SELECT `id` FROM `accounts` WHERE `email` = '$login' AND `password` = '$pass'")) !== 1){
        return true;
    }
    return false;
}

//проверка авторизации. all right -> false
function notAuthorize(){
    // if (isset(getallheaders()["Authorization"])) return false;
    // return true;
}

//проверка на доступ к чужему аккаунту. all right -> false
function notYourAccount($connect, $id){
    $acc = mysqli_query($connect, "SELECT `email`, `password` FROM `accounts` WHERE `id` = '$id'");
    if (mysqli_num_rows($acc) !== 1) return true;
    $acc = mysqli_fetch_assoc($acc);

    $authorization = getallheaders()["Authorization"];
    $authorization = substr($authorization, stripos($authorization, " ")+1); //login:pass (in base64)
    $authorization = base64_decode($authorization);  //login:pass (in unicode)
    $login = substr($authorization, 0, stripos($authorization, ":"));  //login
    $pass = substr($authorization, stripos($authorization, ":")+1);  //password

    if (($acc["email"] !== $login) || ($acc["password"] !== $pass)){
        return true;
    }
    return false;
}
