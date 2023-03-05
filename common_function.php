<?php

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
