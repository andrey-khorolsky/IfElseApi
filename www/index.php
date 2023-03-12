<?php

require("database.php");
require("authentication.php");
require("accounts.php");
require("animals.php");
require("animal_type.php");
require("location.php");
require("animal_visited_location.php");
require("common_function.php");

header("Content-type: application/json");

$method = $_SERVER["REQUEST_METHOD"];
$q = $_GET["q"];
$page = explode("/", $q);
// $page = [];
// for ($i = 0; $i < count($params); $i++){
//     $page[] = $params[$i];
// }


if ($page[0] === "accounts"){
    if ($page[1] === "search")
        getSearchAccount($connect);
    else
        if ($method === "DELETE")
            deleteAccountById($connect, $page[1]);
        elseif ($method === "GET")
            getAccountById($connect, $page[1]);
        elseif ($method === "PUT")
            updateAccount($connect, $page[1]);
} elseif ($page[0] === "animals") {
    if (isset($page[2]) && $page[2] === "locations")
        getVisitedLocations($connect, $page[1]);
    elseif (isset($page[1]) && $page[1] === "types"){
        if ($method === "GET")
            getAnimalTypeById($connect, $page[2]);
        elseif ($method === "POST")
            addAnimalType($connect);
        }
    elseif (isset($page[1]) && $page[1] === "search")
        getSearchAnimals($connect);
    else
        if ($method === "GET")
            getAnimalById($connect, $page[1]);
        elseif ($method === "POST")
            addAnimal($connect);
} elseif ($page[0] === "locations"){
    if ($method === "DELETE")
        deleteLocationById($connect, $page[1]);
    elseif ($method === "GET")
        getLocationById($connect, $page[1]);
    elseif ($method === "POST")
        addLocation($connect);
    elseif ($method === "PUT")
        changeLocation($connect, $page[1]);
} elseif ($page[0] === "registration") {
    registrationAccount($connect);
}

// switch ($page[0]) {
//     case 'registration':
//         require_once("authentication.php");
//         break;
    
//     default:
//         # code...
//         break;
// }
