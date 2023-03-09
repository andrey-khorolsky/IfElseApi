<?php

//API 6.1: Просмотр точек локации, посещенных животным
function getVisitedLocations($connect, $id){
    
    $query = "SELECT `id`, `dateTimeOfVisitLocationPoint`, `id_location` FROM `animal_locations` WHERE `id_animal` = '$id'";
    $locationsList = [];

    $startDateTime = $_GET["startDateTime"] ?? null;
    $endDateTime = $_GET["endDateTime"] ?? null;
    $from = $_GET['from'] ?? 0;
    $size = $_GET['size'] ?? 10;

    // animalId = null,
    // animalId <= 0,
    // from < 0,
    // size <= 0,
    // startDateTime - не в формате ISO-8601,
    // endDateTime - не в формате ISO-8601
    // 400
    if ($id == null || $id <= 0 || $from < 0 || $size <= 0 || dateTimeIso($startDateTime) || dateTimeIso($endDateTime)){
        giveError(400, "Bad request");
        return;
    }

    //Неверные авторизационные данные - 401
    if (validAuthorize($connect)){
        giveError(401, "Authorization error");
        return;
    }
    
    //аккаунт не найден - 404
    if (mysqli_num_rows(mysqli_query($connect, "SELECT `id` FROM `animals` WHERE `id` = '$id'")) === 0){
        giveError(404, "Animal not found");
        return;
    }

    if (isset($startDateTime)){
        $query .= " AND `dateTimeOfVisitLocationPoint` >= '$startDateTime' ";
    }
    if (isset($endDateTime)){
        $query .= " AND `dateTimeOfVisitLocationPoint` <= '$endDateTime' ";
    }
    $animal_locations = mysqli_query($connect, $query);

    //пропускаем указанное кол-во элементов
    for($i=0; $i++<$from; mysqli_fetch_assoc($animal_locations));
    
    //вывод указаного кол-ва элементов
    while ($location = mysqli_fetch_assoc($animal_locations)){
        $locationsList[] = $location;
        if (--$size === 0) break;
    }

    echo json_encode($locationsList);
}
