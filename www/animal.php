<?php


$method = $_SERVER["REQUEST_METHOD"];
$page = $_GET["q"];
$page = explode("/", $page);
require("database.php");
require("common_function.php");

if (isset($page[1]) && $page[1] === "search")
    getSearchAnimals($connect);
elseif (isset($page[3])){
    if ($method === "POST")
        addTypeToAnimal($connect, $page[1], $page[3]);
    if ($method === "DELETE")
        deleteTypeFromAnimal($connect, $page[1], $page[3]);
}
elseif(isset($page[2]) && $page[2] === "types")
    updateTypeOfAnimal($connect, $page[1]);
elseif ($method === "GET")
    getAnimalById($connect, $page[1]);
elseif ($method === "POST")
    addAnimal($connect);
elseif ($method === "PUT")
    updateAnimal($connect, $page[1]);
elseif ($method === "DELETE")
    deleteAnimal($connect, $page[1]);

return;

//GET API 5.1: Получение информации о животном
function getAnimalById($connect, $id){
    //запрос в бд
    $animal = mysqli_query($connect, "SELECT `id`, `weight`, `length`, `height`, `gender`, `lifeStatus`, DATE_FORMAT(`chippingDateTime`, '%Y-%m-%dT%T+03:00') as chippingDateTime, `chipperId`, `chippingLocationId`, DATE_FORMAT(`deathDateTime`, '%Y-%m-%dT%T+03:00') as deathDateTime FROM `animals` WHERE `id` = '$id'");
    
    //неверный id - 400
    if ($id <= 0 || $id == null){
        giveError(400, "Incorrect id");
        return;
    }

    //Неверные авторизационные данные - 401
    if (notValidAuthorize($connect)){
        giveError(401, "Authorization error");
        return;
    }

    //аккаунт не найден - 404
    if (mysqli_num_rows($animal) === 0){
        giveError(404, "Animal not found");
        return;
    }

    $animal = mysqli_fetch_assoc($animal);

    echo json_encode(getAnimalsType($connect, $animal));
}


//GET API 5.2: Поиск животных по параметрам
function getSearchAnimals($connect){
    
    //запрос и кол-во параметров
    $query = "SELECT `id`, `weight`, `length`, `height`, `gender`, `lifeStatus`, DATE_FORMAT(`chippingDateTime`, '%Y-%m-%dT%T+03:00') as chippingDateTime, `chipperId`, `chippingLocationId`, DATE_FORMAT(`deathDateTime`, '%Y-%m-%dT%T+03:00') as deathDateTime FROM `animals`";
    $first = false;

    //переменные по дефолту
    $startDateTime = $_GET['startDateTime'] ?? null;
    $endDateTime = $_GET['endDateTime'] ?? null;
    $chipperId = $_GET['chipperId'] ?? null;
    $chippingLocationId = $_GET['chippingLocationId'] ?? null;
    $lifeStatus = $_GET['lifeStatus'] ?? null;
    $gender = $_GET['gender'] ?? null;
    $from = $_GET['from'] ?? 0;
    $size = $_GET['size'] ?? 10;

    // from < 0,
    // size <= 0,
    // startDateTime - не в формате ISO-8601,
    // endDateTime - не в формате ISO-8601,
    // chipperId <= 0,
    // chippingLocationId <= 0,
    // lifeStatus != “ALIVE”, “DEAD”,
    // gender != “MALE”, “FEMALE”, “OTHER”
    // 400
    if ($from < 0 || $size <= 0 || (isset($chipperId) && $chipperId <= 0) || (isset($chippingLocationId) && $chippingLocationId <= 0) || !validLifestatus($lifeStatus)
        || (isset($gender) && !validGender($gender)) || !dateTimeIso($startDateTime) || !dateTimeIso($endDateTime)){
        giveError(400, "Bad request");
        return;
    }

    //Неверные авторизационные данные - 401
    if (notValidAuthorize($connect)){
        giveError(401, "Authorization error");
        return;
    }


    //создание запроса в зависимости от реквеста
    if (isset($startDateTime)){
        $query .= " WHERE `chippingDateTime` >= '$startDateTime' ";
        $first = true;
    }
    if (isset($endDateTime)){
        if ($first) $query .= " AND ";
        else{
            $first = true;
            $query .= " WHERE ";
        }
        $query .= "`chippingDateTime` <= '$endDateTime' ";
    }
    if (isset($chipperId)){
        if ($first) $query .= " AND ";
        else{
            $first = true;
            $query .= " WHERE ";
        }
        $query .= "`chipperId` = '$chipperId' ";
    }
    if (isset($chippingLocationId)){
        if ($first) $query .= " AND ";
        else{
            $first = true;
            $query .= " WHERE ";
        }
        $query .= "`chippingLocationId` = '$chippingLocationId' ";
    }
    if (isset($lifeStatus)){
        if ($first) $query .= " AND ";
        else{
            $first = true;
            $query .= " WHERE ";
        }
        $query .= "`lifeStatus` = '$lifeStatus' ";
    }
    if (isset($gender)){
        if ($first) $query .= " AND ";
        else{
            $first = true;
            $query .= " WHERE ";
        }
        $query .= "`gender` = '$gender' ";
    }

    $animalList = [];
    $searchAnimals = mysqli_query($connect, $query);

    //пропускаем указанное кол-во элементов
    for($i=0; $i++<$from; mysqli_fetch_assoc($searchAnimals));
    
    //вывод указаного кол-ва элементов
    while ($animal = mysqli_fetch_assoc($searchAnimals)){
        $animalList[] = getAnimalsType($connect, $animal);
        if (--$size === 0) break;
    }

    echo json_encode($animalList);
}


//POST API 5.3: Добавление нового животного
function addAnimal($connect){
    
    $animalData = file_get_contents("php://input");
    $animalData = json_decode($animalData, true);

    //"animalTypes": "[long]", // Массив идентификаторов типов животного
    // "weight": "float", // Масса животного, кг
    // "length": "float", // Длина животного, м
    // "height": "float", // Высота животного, м
    // "gender": "string", // Гендерный признак животного, доступные значения “MALE”, “FEMALE”, “OTHER”
    // "chipperId": "int", // Идентификатор аккаунта пользователя, чипировавшего животное
    // "chippingLocationId": "long" // Идентификатор точки локации животных

    $animalTypes = $_POST["animalTypes"] ?? ($animalData["animalTypes"] ?? null);
    $weight = $_POST["weight"] ?? ($animalData["weight"] ?? null);
    $length = $_POST["length"] ?? ($animalData["length"] ?? null);
    $height = $_POST["height"] ?? ($animalData["height"] ?? null);
    $gender = $_POST["gender"] ?? ($animalData["gender"] ?? null);
    $chipperId = $_POST["chipperId"] ?? ($animalData["chipperId"] ?? null);
    $chippingLocationId = $_POST["chippingLocationId"] ?? ($animalData["chippingLocationId"] ?? null);

    if (is_null($animalTypes) || count($animalTypes) <= 0 || notValidDidgitData($weight, $length, $height, $chipperId, $chippingLocationId)
    || !validGender($gender) || !validArr($animalTypes)){
        giveError(400, "Invalid data");
        return;
    }

    //Запрос от неавторизованного аккаунта Неверные авторизационные данные - 401
    if (notValidAuthorize($connect, true)){
        giveError(401, "Authorization error");
        return;
    }

    //Тип животного не найден - 404
    foreach($animalTypes as $id)
        if (mysqli_num_rows(mysqli_query($connect, "SELECT * FROM `types` WHERE `id` = '$id'")) == 0){
            giveError(404, "Animals type not found");
            return;
        }
    
    //Аккаунт с chipperId не найден - 404
    if (mysqli_num_rows(mysqli_query($connect, "SELECT * FROM `accounts` WHERE `id` = '$chipperId'")) == 0){
        giveError(404, "Account not found");
        return;
    }

    //Точка локации с chippingLocationId не найдена - 404
    if (mysqli_num_rows(mysqli_query($connect, "SELECT * FROM `locations` WHERE `id` = '$chippingLocationId'")) == 0){
        giveError(404, "Location not found");
        return;
    }

    //Массив animalTypes содержит дубликаты - 409
    if (count(array_diff(array_unique($animalTypes), $animalTypes)) > 0){
        giveError(409, "Some animals types are same");
        return;
    }

    //добавление животного в бд
    mysqli_query($connect, "INSERT INTO `animals` (`id`, `weight`, `length`, `height`, `gender`, `lifeStatus`, `chippingDateTime`, `chipperId`, `chippingLocationId`, `deathDateTime`) VALUES (NULL, '$weight', '$length', '$height', '$gender', 'ALIVE', DATE_FORMAT(CURRENT_TIMESTAMP, '%Y-%m-%dT%T+03:00'), '$chipperId', '$chippingLocationId', NULL)");
    //добавление типов к животному в бд
    $animalId = mysqli_insert_id($connect);
    foreach (array_values($animalTypes) as $type)
        mysqli_query($connect, "INSERT INTO `animal_types` (`id_animal`, `id_type`) VALUES ('$animalId', '$type')");

    http_response_code(201);
    $animal = mysqli_fetch_assoc(mysqli_query($connect, "SELECT `id`, `weight`, `length`, `height`, `gender`, `lifeStatus`, DATE_FORMAT(`chippingDateTime`, '%Y-%m-%dT%T+03:00') as chippingDateTime, `chipperId`, `chippingLocationId`, DATE_FORMAT(`deathDateTime`, '%Y-%m-%dT%T+03:00') as deathDateTime FROM `animals` WHERE `id` = '$animalId'"));
    echo json_encode(getAnimalsType($connect, $animal));

}


//PUT API 5.4: Обновление информации о животном
function updateAnimal($connect, $id){
    
    $newAnimalData = file_get_contents("php://input");
    $newAnimalData = json_decode($newAnimalData, true);

    $weight = $_POST["weight"] ?? ($newAnimalData["weight"] ?? null);
    $length = $_POST["length"] ?? ($newAnimalData["length"] ?? null);
    $height = $_POST["height"] ?? ($newAnimalData["height"] ?? null);
    $gender = $_POST["gender"] ?? ($newAnimalData["gender"] ?? null);
    $lifeStatus = $_POST["lifeStatus"] ?? ($newAnimalData["lifeStatus"] ?? null);
    $chipperId = $_POST["chipperId"] ?? ($newAnimalData["chipperId"] ?? null);
    $chippingLocationId = $_POST["chippingLocationId"] ?? ($newAnimalData["chippingLocationId"] ?? null);

    //animalId = null, animalId <=0,
    // weight = null, weight <=0,
    // length = null, length <=0,
    // height = null height <=0,
    // chipperId = null, chipperId <=0,
    // chippingLocationId = null, chippingLocationId <=0
    // gender != “MALE”, “FEMALE”, “OTHER”,
    // lifeStatus != “ALIVE”, “DEAD”,
    // - 400
    if (notValidDidgitData($id, $weight, $length, $height, $chipperId, $chippingLocationId) || !validGender($gender) || !validLifestatus($lifeStatus)){
        giveError(400, "Invalid data");
        return;
    }

    //Животное с animalId не найдено. Аккаунт с chipperId не найден. Точка локации с chippingLocationId не найдена - 404
    if ((mysqli_num_rows(mysqli_query($connect, "SELECT `id` FROM `animals` WHERE `id` = '$id'")) !== 1)
    || (mysqli_num_rows(mysqli_query($connect, "SELECT `id` FROM `accounts` WHERE `id` = '$chipperId'")) !== 1)
    || (mysqli_num_rows(mysqli_query($connect, "SELECT `id` FROM `locations` WHERE `id` = '$chippingLocationId'")) !== 1)){
        giveError(404, "Animal or account or location not found");
        return;
    }

    
    // Установка lifeStatus = “ALIVE”, если у животного lifeStatus = “DEAD”
    // Новая точка чипирования совпадает с первой посещенной точкой локации - 400
    $currentLifeStatus = mysqli_fetch_assoc(mysqli_query($connect, "SELECT `lifeStatus` FROM `animals` WHERE `id` = '$id'"))["lifeStatus"];
    $firstLocation = mysqli_fetch_assoc(mysqli_query($connect, "SELECT `id_location` FROM `animal_locations` WHERE `id` = '$id'")) ?? -1;
    
    if (($currentLifeStatus === "DEAD" && $lifeStatus === "ALIVE") || $firstLocation === $chippingLocationId){
        giveError(400, "Invalid data");
        return;
    }

    //Запрос от неавторизованного аккаунта Неверные авторизационные данные - 401
    if (notValidAuthorize($connect, true)){
        giveError(401, "Authorization error");
        return;
    }

    // подготовка запроса
    $stmt = mysqli_stmt_init($connect);

    if (mysqli_fetch_assoc(mysqli_query($connect, "SELECT `lifeStatus` FROM `animals` WHERE `id` = $id"))["lifeStatus"] === "ALIVE" && $lifeStatus === "DEAD"){
        mysqli_stmt_prepare($stmt, "UPDATE `animals` SET `weight` = ?, `length` = ?, `height` = ?, `gender` = ?, `lifeStatus` = ?, `chipperId` = ?, `chippingLocationId` = ?,  `deathDateTime` = DATE_FORMAT(CURRENT_TIMESTAMP, '%Y-%m-%dT%T+03:00') WHERE `id` = '$id'");
        mysqli_stmt_bind_param($stmt, "dddssid", $weight, $length, $height, $gender, $lifeStatus, $chipperId, $chippingLocationId);
    } else{
        mysqli_stmt_prepare($stmt, "UPDATE `animals` SET `weight` = ?, `length` = ?, `height` = ?, `gender` = ?, `lifeStatus` = ?, `chipperId` = ?, `chippingLocationId` = ? WHERE `id` = '$id'");
        mysqli_stmt_bind_param($stmt, "dddssid", $weight, $length, $height, $gender, $lifeStatus, $chipperId, $chippingLocationId);
    }

    // вставка
    mysqli_stmt_execute($stmt);

    // mysqli_query($connect, "UPDATE `animals` SET `weight` = '$weight', `length` = '$length', `height` = '$height', `gender` = '$gender', `lifeStatus` = '$lifeStatus', `chipperId` = '$chipperId', `chippingLocationId` = '$chippingLocationId' WHERE `id` = '$id'");
    $animal = mysqli_fetch_assoc(mysqli_query($connect, "SELECT `id`, `weight`, `length`, `height`, `gender`, `lifeStatus`, DATE_FORMAT(`chippingDateTime`, '%Y-%m-%dT%T+03:00') as chippingDateTime, `chipperId`, `chippingLocationId`, DATE_FORMAT(`deathDateTime`, '%Y-%m-%dT%T+03:00') as deathDateTime FROM `animals` WHERE `id` = '$id'"));
    echo json_encode(getAnimalsType($connect, $animal));
}


//DELETE API 5.5: Удаление животного
function deleteAnimal($connect, $id){

    //animalId = null, animalId <=0. Животное покинуло локацию чипирования, при этом есть другие посещенные точки - 400
    if (is_null($id) || $id <= 0 || mysqli_num_rows(mysqli_query($connect, "SELECT * FROM `animal_locations` WHERE `id_animal` = '$id'")) !== 0){
        giveError(400, "Invalid data");
        return;
    }

    //Запрос от неавторизованного аккаунта Неверные авторизационные данные - 401
    if (notValidAuthorize($connect, true)){
        giveError(401, "Authorziation error");
        return;
    }

    //Животное с animalId не найдено - 404
    if (mysqli_num_rows(mysqli_query($connect, "SELECT `id` FROM `animals` WHERE `id` = '$id'")) !== 1){
        giveError(404, "Animal not found");
        return;
    }

    mysqli_query($connect, "DELETE FROM `animals` WHERE `id` = '$id'");

}


//POST API 5.6: Добавление типа животного к животному
function addTypeToAnimal($connect, $animalId, $typeId){

    //animalId = null, animalId <= 0,
    // typeId = null, typeId < = 0 - 400
    if (notValidDidgitData($animalId, $typeId)){
        giveError(400 ,"Invalid data");
        return;
    }

    //Запрос от неавторизованного аккаунта. Неверные авторизационные данные - 401
    if (notValidAuthorize($connect, true)){
        giveError(401, "Authorization error");
        return;
    }

    // Животное с animalId не найдено
    // Тип животного с typeId не найден - 404
    if (mysqli_num_rows(mysqli_query($connect, "SELECT `id` FROM `animals` WHERE `id` = $animalId")) !== 1
    || mysqli_num_rows(mysqli_query($connect, "SELECT `id` FROM `types` WHERE `id` = $typeId")) !== 1){
        giveError(404, "Account or type not found");
        return;
    }

    //Тип животного с typeId уже есть у животного с animalId - 409
    if (mysqli_num_rows(mysqli_query($connect, "SELECT * FROM `animal_types` WHERE `id_animal` = $animalId AND `id_type` = $typeId")) !== 0){
        giveError(409, "Animal is has this type");
        return;
    }

    mysqli_query($connect, "INSERT INTO `animal_types` (`id_animal`, `id_type`) VALUES ($animalId, $typeId)");
    $animal = mysqli_fetch_assoc(mysqli_query($connect, "SELECT `id`, `weight`, `length`, `height`, `gender`, `lifeStatus`, DATE_FORMAT(`chippingDateTime`, '%Y-%m-%dT%T+03:00') as chippingDateTime, `chipperId`, `chippingLocationId`, DATE_FORMAT(`deathDateTime`, '%Y-%m-%dT%T+03:00') as deathDateTime FROM `animals` WHERE `id` = '$animalId'"));
    http_response_code(201);
    echo json_encode(getAnimalsType($connect, $animal));
}


//PUT API 5.7: Изменение типа животного у животного
function updateTypeOfAnimal($connect, $animalId){

    $newType = file_get_contents("php://input");
    $newType = json_decode($newType, true);

    $oldTypeId = $_POST["oldTypeId"] ?? ($newType["oldTypeId"] ?? null);
    $newTypeId = $_POST["newTypeId"] ?? ($newType["newTypeId"] ?? null);

    //animalId = null,
    // animalId <= 0,
    // oldTypeId = null,
    // oldTypeId <= 0,
    // newTypeId = null,
    // newTypeId <= 0
    if (notValidDidgitData($animalId, $oldTypeId, $newTypeId)){
        giveError(400, "Invalid data");
        return;
    }

    //Запрос от неавторизованного аккаунта Неверные авторизационные данные - 401
    if (notValidAuthorize($connect, true)){
        giveError(401, "Authorization error");
        return;
    }

    //  Животное с animalId не найдено
    // Тип животного с oldTypeId не найден
    // Тип животного с newTypeId не найден
    // Типа животного с oldTypeId нет у животного с animalId - 404
    if (mysqli_num_rows(mysqli_query($connect, "SELECT `id` FROM `animals` WHERE `id` = '$animalId'")) !== 1
    || mysqli_num_rows(mysqli_query($connect, "SELECT `id` FROM `types` WHERE `id` = '$oldTypeId'")) !== 1
    || mysqli_num_rows(mysqli_query($connect, "SELECT `id` FROM `types` WHERE `id` = '$newTypeId'")) !== 1
    || mysqli_num_rows(mysqli_query($connect, "SELECT * FROM `animal_types` WHERE `id_animal` = '$animalId' AND `id_type` = '$oldTypeId'")) === 0){
        giveError(404, "Type not found or this animal hasnt this type");
        return;
    }

    //Тип животного с newTypeId уже есть у животного с animalId. Животное с animalId уже имеет типы с oldTypeId и newTypeId - 409
    if (mysqli_num_rows(mysqli_query($connect, "SELECT `id_animal` FROM `animal_types` WHERE `id_animal` = '$animalId' AND `id_type` = '$newTypeId'")) !== 0
    || (mysqli_num_rows(mysqli_query($connect, "SELECT `id_animal` FROM `animal_types` WHERE `id_animal` = '$animalId' AND `id_type` = '$newTypeId'")) !== 0
    && mysqli_num_rows(mysqli_query($connect, "SELECT `id_animal` FROM `animal_types` WHERE `id_animal` = '$animalId' AND `id_type` = '$oldTypeId'")) !== 0)){
        giveError(409, "Animal is has this type");
        return;
    }

    mysqli_query($connect, "UPDATE `animal_types` SET `id_type` = '$newTypeId' WHERE `id_animal` = '$animalId'");
    $animal = mysqli_fetch_assoc(mysqli_query($connect, "SELECT `id`, `weight`, `length`, `height`, `gender`, `lifeStatus`, DATE_FORMAT(`chippingDateTime`, '%Y-%m-%dT%T+03:00') as chippingDateTime, `chipperId`, `chippingLocationId`, DATE_FORMAT(`deathDateTime`, '%Y-%m-%dT%T+03:00') as deathDateTime FROM `animals` WHERE `id` = '$animalId'"));
    echo json_encode(getAnimalsType($connect, $animal));

}


//DELETE API 5.8: Удаление типа животного у животного
function deleteTypeFromAnimal($connect, $animalId, $typeId){
    
    //animalId = null, animalId <= 0,
    // typeId = null, typeId <= 0
    // У животного только один тип и это тип с typeId - 400
    if (notValidDidgitData($animalId, $typeId) || mysqli_num_rows(mysqli_query($connect, "SELECT * FROM `animal_types` WHERE `id` = $animalId")) === 1){
        giveError(400, "Invalid data");
        return;
    }

    //Запрос от неавторизованного аккаунта. Неверные авторизационные данные - 401
    if (notValidAuthorize($connect, true)){
        giveError(401, "Authorization error");
        return;
    }

    //Животное с animalId не найдено
    // Тип животного с typeId не найден
    // У животного с animalId нет типа с typeId - 404
    if (mysqli_num_rows(mysqli_query($connect, "SELECT `id` FROM `animals` WHERE `id` = $animalId")) !== 1
    || mysqli_num_rows(mysqli_query($connect, "SELECT * FROM `animal_types` WHERE `id_animal` = $animalId AND `id_type` = $typeId")) === 0){
        giveError(404, "Animal or type not found");
        return;
    }

    $animal = mysqli_fetch_assoc(mysqli_query($connect, "SELECT `id`, `weight`, `length`, `height`, `gender`, `lifeStatus`, DATE_FORMAT(`chippingDateTime`, '%Y-%m-%dT%T+03:00') as chippingDateTime, `chipperId`, `chippingLocationId`, DATE_FORMAT(`deathDateTime`, '%Y-%m-%dT%T+03:00') as deathDateTime FROM `animals` WHERE `id` = '$animalId'"));
    echo json_encode(getAnimalsType($connect, $animal));
}


//поиск типов и посещенных локаций животного и добавление информации в результат
function getAnimalsType($connect, $animal){
    //берется id животного
    $id = $animal["id"];
    $typesList = [];
    $locationsList = [];

    //запрос в бд о его типах
    $animal_types = mysqli_query($connect, "SELECT `id_type` FROM `animal_types` WHERE `id_animal` = '$id'");
    $animal_locations = mysqli_query($connect, "SELECT `id_location` FROM `animal_locations` WHERE `id_animal` = '$id'");
    
    //добавление типов в массив
    while ($type = mysqli_fetch_assoc($animal_types)){
        $typesList[] = $type["id_type"];
    }

    //добавление локаций в массив
    while ($locations = mysqli_fetch_assoc($animal_locations)){
        $locationsList[] = $locations["id_location"];
    }
    
    return array_merge(array_slice($animal, 0, 1), ["animalTypes" => array_values($typesList)], array_slice($animal, 1, 8), ["visitedLocations" => ($locationsList)], array_slice($animal, 8));
}

//проверка массива
function validArr($arr){
    foreach($arr as $a)
        if ($a <= 0 || is_null($a)) return false;
    return true;
}


//проверка поля gender
function validGender($gender){
    if (is_null($gender)) return false;
    if (strcmp($gender, "MALE") == 0 || strcmp($gender, "FEMALE") == 0 || strcmp($gender, "OTHER") == 0) return true;
    return false;
}


//проверка поля lifestatus
function validLifestatus($lifestatus){
    if (is_null($lifestatus)) return true;
    if (strcmp($lifestatus, "ALIVE") == 0 || strcmp($lifestatus, "DEAD") == 0) return true;
    return false;
}
