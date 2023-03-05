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
