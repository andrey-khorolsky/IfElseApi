<?php

function getAnimalById($connect, $id){
    //запрос в бд
    $animal = mysqli_query($connect, "SELECT `id`, `weight`, `length`, `height`, `gender`, `lifeStatus`, `chippingDateTime`, `chipperId`, `chippingLocationId`, `deathDateTime` FROM `animals` WHERE `id` = '$id'");
    $animal_types = mysqli_query($connect, "SELECT `id_type` FROM `animal_types` WHERE `id_animal` = '$id'");

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
    if (mysqli_num_rows($animal) === 0){
        http_response_code(404);

        echo json_encode([
            "status" => false,
            "message" => "Account not found"
        ]);
        return;
    }

    $animal = mysqli_fetch_assoc($animal);
    $typesList = [];

    while ($type = mysqli_fetch_assoc($animal_types)){
        array_push($typesList, array_values($type));
    }

    $animal_types = [];
    for ($i = 0; $i < count($typesList); $i++){
        $animal_types[] = $typesList[$i];
    }

    $animal_types = ["animalTypes" => $typesList];
    $res = array_merge(array_slice($animal, 0, 1), $animal_types, array_slice($animal, 1));
    
    echo json_encode($res);
}

function getSearchAnimals($connect){

}
