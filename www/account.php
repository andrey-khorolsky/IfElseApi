<?php


$method = $_SERVER["REQUEST_METHOD"];
$page = $_GET["q"];
$page = explode("/", $page);
require("database.php");
require("common_function.php");


if ($page[0] === "registration")
    registrationAccount($connect);
elseif ($page[1] === "search")
    getSearchAccount($connect);
elseif ($method === "DELETE")
    deleteAccountById($connect, $page[1]);
elseif ($method === "GET")
    getAccountById($connect, $page[1]);
elseif ($method === "PUT")
    updateAccount($connect, $page[1]);


    
//POST API 1.1: Регистрация нового аккаунта
function registrationAccount($connect){
    
    $data = file_get_contents("php://input");
    $data = json_decode($data, true);

    $firstName = $_POST["firstName"] ?? ($data["firstName"] ?? null);
    $lastName = $_POST["lastName"] ?? ($data["lastName"] ?? null);
    $email = $_POST["email"] ?? ($data["email"] ?? null);
    $password = $_POST["password"] ?? ($data["password"] ?? null);

    //валидация данных - 400
    if (notValidData($firstName, $lastName, $password) || notValidEmail($email)){
        giveError(400, "Invalid data");
        return;
    }

    //Запрос от авторизованного аккаунта - 403
    if (isset(getallheaders()["Authorization"])){
        giveError(403, "Authorization error");
        return;
    }

    //Аккаунт с таким email уже существует - 409
    if (mysqli_num_rows(mysqli_query($connect, "SELECT * FROM `accounts` WHERE `email` = '$email'")) !== 0){
        giveError(409, "This email is used");
        return;
    }

    $firstNameEsc = mysqli_real_escape_string($connect, $firstName);
    $lastNameEsc = mysqli_real_escape_string($connect, $lastName);

    mysqli_query($connect, "INSERT INTO `accounts` (`id`, `firstName`, `lastName`, `email`, `password`) VALUES (null, '$firstNameEsc', '$lastNameEsc', '$email', '$password')");

    http_response_code(201);
    echo json_encode([
        "id" => mysqli_insert_id($connect),
        "firstName" => $firstName,
        "lastName" => $lastName,
        "email" => $email
    ]);
}


//GET API 2.1: Получение информации об аккаунте пользователя
function getAccountById($connect, $id){
    //запрос в бд
    $accaount = mysqli_query($connect, "SELECT `id`, `firstName`, `lastName`, `email` FROM `accounts` WHERE `id` = '$id'");

    //неверный id - 400
    if ($id <= 0 || is_null($id)){
        giveError(400, "Incorrect id");
        return;
    }

    //Неверные авторизационные данные - 401
    if (notValidAuthorize($connect)){
        giveError(401, "Authorization error");
        return;
    }

    //аккаунт не найден - 404
    if (mysqli_num_rows($accaount) === 0){
        giveError(404, "Account not found");
        return;
    }

    $accaount = mysqli_fetch_assoc($accaount);
    
    $accaount["firstName"] = mysqli_real_escape_string($connect, $accaount["firstName"]);
    $accaount["lastName"] = mysqli_real_escape_string($connect, $accaount["lastName"]);
    $accaount["email"] = mysqli_real_escape_string($connect, $accaount["email"]);
    echo json_encode($accaount);
}

//GET API 2.2: Поиск аккаунтов пользователей по параметрам
function getSearchAccount($connect){

    //запрос и кол-во параметров
    $query = "SELECT `id`, `firstName`, `lastName`, `email` FROM `accounts`";
    $first = false;

    //переменные по дефолту
    $firstName = $_GET['firstName'] ?? null;
    $lastName = $_GET['lastName'] ?? null;
    $email = $_GET['email'] ?? null;
    $from = $_GET['from'] ?? 0;
    $size = $_GET['size'] ?? 10;

    //from < 0 || size <= 0 - 400
    if ($from < 0 || $size <= 0){
        giveError(400, "Bad request");
        return;
    }

    //Неверные авторизационные данные - 401
    if (notValidAuthorize($connect)){
        giveError(401, "Authorization error");
        return;
    }


    //создание запроса в зависимости от реквеста
    if (isset($firstName)){
        $query .= " WHERE `firstName` LIKE '%$firstName%' ";
        $first = true;
    }
    if (isset($lastName)){
        if ($first) $query .= " AND ";
        else {
            $query .= " WHERE ";
            $first = true;
        }
        $query .= "`lastName` LIKE '%$lastName%' ";
    }
    if (isset($email)){
        if ($first) $query .= " AND ";
        else {
            $query .= " WHERE ";
            $first = true;
        }
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

    foreach ($accountList as $acc){
        $acc["firstName"] = mysqli_real_escape_string($connect, $acc["firstName"]);
        $acc["lastName"] = mysqli_real_escape_string($connect, $acc["lastName"]);
        $acc["email"] = mysqli_real_escape_string($connect, $acc["email"]);
    }
    echo json_encode($accountList);
}


//PUT API 2.3: Обновление данных аккаунта пользователя
function updateAccount($connect, $id){

    $newData = file_get_contents("php://input");
    $newData = json_decode($newData, true);

    $firstName = $newData["firstName"];
    $lastName = $newData["lastName"];
    $email = $newData["email"];
    $password = $newData["password"];

    //accountId = null,
    // accountId <= 0 - 400
    if (is_null($id) || $id <=0 || notValidData($firstName, $lastName, $password) || notValidEmail($email)){
        giveError(400, "Invalid data");
        return;
    }


    //Запрос от неавторизованного аккаунта, Неверные авторизационные данные - 401
    if (notValidAuthorize($connect, true)){
        giveError(401, "Authorization error");
        return;
    }

    //Обновление не своего аккаунта, Аккаунт не найден - 403
    if (notYourAccount($connect, $id) || (mysqli_num_rows(mysqli_query($connect, "SELECT * FROM `accounts` WHERE `email` = (SELECT `email` FROM `accounts` WHERE `id` = '$id')")) === 0)){
        giveError(403, "Old account not found");
        return;
    }

    //Аккаунт с таким email уже существует - 409
    if (notYourAccount($connect, $id) && mysqli_num_rows(mysqli_query($connect, "SELECT * FROM `accounts` WHERE `email` = '$email'")) !== 1){
        giveError(409, "This email is used");
        return;
    }

    // $firstName = mysqli_real_escape_string($connect, $firstName);
    // $lastName = mysqli_real_escape_string($connect, $lastName);
    // $email = mysqli_real_escape_string($connect, $email);
    // $password = mysqli_real_escape_string($connect, $password);

    mysqli_query($connect, "UPDATE `accounts` SET `firstName` = '$firstName', `lastName` = '$lastName', `email` = '$email', `password` = '$password' WHERE `id` = '$id'");

    echo json_encode([
        "id" => $id,
        "firstName" => $firstName,
        "lastName" => $lastName,
        "email" => $email
    ]);
}


//DELETE API 2.4: Удаление аккаунта пользователя
function deleteAccountById($connect, $id){

    //accountId = null, accountId <= 0, Аккаунт связан с животным - 400
    if (is_null($id) || $id <=0 || mysqli_num_rows(mysqli_query($connect, "SELECT * FROM `animals` WHERE `chipperId` = '$id'")) !== 0){
        giveError(400, "Invalid id");
        return;
    }

    // Запрос от неавторизованного акк, Неверные авторизационные данные - 401
    if (notValidAuthorize($connect, true)){
        giveError(401, "Authorization error");
        return;
    }

    // Удаление не своего акк, Аккаунт с таким accountId не найден - 403
    if (notYourAccount($connect, $id) || (mysqli_num_rows(mysqli_query($connect, "SELECT * FROM `accounts` WHERE `id` = '$id'")) === 0)){
        giveError(403, "Account not found");
        return;
    }

    mysqli_query($connect, "DELETE FROM `accounts` WHERE `id` = '$id'");

}

