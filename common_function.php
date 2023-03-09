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


//валидация данных. all right -> false
function validData(...$data){
    foreach ($data as $d)
        if (trim($d) === "" || is_null($d)) return true;
    return false;
}


//валидация почты. all right -> false
function validEmail($email){
    $reg = "/^([a-zA-Z0-9]+[a-zA-Z0-9._-]+[a-zA-Z0-9]+@[a-zA-Z0-9._-]+\.[a-zA-Z]{2,})$/";
    if (preg_match($reg, $email) !== 1) return true;
    return false;
}


//валидация координат. all right -> false
function validCoordinates($latitude, $longitude){
    //latitude = null, latitude < -90, latitude > 90,
    // longitude = null, longitude < -180, longitude > 180

    if (is_null($latitude) || ($latitude < -90) || ($latitude > 90) || is_null($longitude) || ($longitude < -180) || ($longitude > 180))
        return true;
    return false;
}
