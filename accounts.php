<?php

function getOneAccount($connect, $id){
    //запрос в бд
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

    //запрос и кол-во параметров
    $query = "SELECT `id`, `firstName`, `lastName`, `email` FROM `accounts` WHERE ";
    $first = false;

    //переменные по дефолту
    $firstName = $_GET['firstName'] ?? null;
    $lastName = $_GET['lastName'] ?? null;
    $email = $_GET['email'] ?? null;
    $from = $_GET['from'] ?? 0;
    $size = $_GET['size'] ?? 10;

    //from < 0 || size <= 0 - 400
    if ($from < 0 || $size <= 0){
        http_response_code(400);
        echo json_encode([
            "status" => false,
            "message" => "Bad request"
        ]);
        return;
    }

    //Неверные авторизационные данные - 401  ???????


    //создание запроса в зависимости от реквеста
    if (isset($firstName)){
        $query .= "`firstName` LIKE '%$firstName%' ";
        $first = true;
    }
    if (isset($lastName)){
        if ($first) $query .= " AND ";
        else $first = true;
        $query .= "`lastName` LIKE '%$lastName%' ";
    }
    if (isset($email)){
        if ($first) $query .= " AND ";
        else $first = true;
        $query .= "`email` LIKE '%$email%' ";
    }

    $accountList = [];
    $searchAccounts = mysqli_query($connect, $query);

    //пропускаем указанное кол-во элементов
    for($i=0; $i++<$from; mysqli_fetch_assoc($searchAccounts));
    
    //вывод указаного кол-ва элементов
    while ($account = mysqli_fetch_assoc($searchAccounts)){
        $accountList[] = $account;
        if (--$size === 0) break;
    }

    echo json_encode($accountList);
}
