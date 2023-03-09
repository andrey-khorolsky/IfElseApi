<?php

//API 4.1: Получение информации о типе животного
function getAnimalTypeById($connect, $id){
    //запрос в бд
    $animal_types = mysqli_query($connect, "SELECT `id`, `type` FROM `types` WHERE `id` = '$id'");

    //$id <= 0 || $id == null  - 400
    if ($id <= 0 || is_null($id)){
        giveError(400, "Incorrect id");
        return;
    }

    //Неверные авторизационные данные - 401
    if (validAuthorize($connect)){
        giveError(401, "Authorization error");
        return;
    }

    //аккаунт не найден - 404
    if (mysqli_num_rows($animal_types) === 0){
        giveError(404, "Animal type not found");
        return;
    }

    $animal_types = mysqli_fetch_assoc($animal_types);
    echo json_encode($animal_types);
}
