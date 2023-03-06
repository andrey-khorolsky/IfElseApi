<?php

//API 3.1: Получение информации о точке локации животных
function getLocationById($connect, $id){
    //запрос в бд
    $location = mysqli_query($connect, "SELECT `id`, `latitude`, `longitude` FROM `locations` WHERE `id` = '$id'");

    //неверный id - 400
    if ($id <= 0 || $id == null){
        giveError(400, "Incorrect id");
        return;
    }

    //Неверные авторизационные данные - 401  ???????

    //аккаунт не найден - 404
    if (mysqli_num_rows($location) === 0){
        giveError(404, "Location not found");
        return;
    }

    $location = mysqli_fetch_assoc($location);
    echo json_encode($location);
}

//API 3.4: Удаление точки локации животных
function deleteLocationById($connect, $id){
    
    //pointId = null, pointId <= 0, Точка локации связана с животным - 400
    if ($id == null || $id <=0 || mysqli_num_rows(mysqli_query($connect, "SELECT * FROM `animals` WHERE `chippingLocationId` = '$id'")) !== 0){
        giveError(400, "Invalid id");
        return;
    }

    // Запрос от неавторизованного акк, Неверные авторизационные данные - 401

    // Удаление не своего акк, Аккаунт с таким accountId не найден - 403
    if (mysqli_num_rows(mysqli_query($connect, "SELECT * FROM `locations` WHERE `id` = '$id'")) === 0){
        giveError(403, "Location not found");
        return;
    }

    mysqli_query($connect, "DELETE FROM `locations` WHERE `id` = '$id'");

}
