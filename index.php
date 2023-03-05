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

$q = $_GET["q"];
$params = explode("/", $q);
$page = [];
for ($i = 0; $i < count($params); $i++){
    $page[] = $params[$i];
}

if ($page[0] === "accounts"){
    if ($page[1] === "search")
        getSearchAccount($connect);
    else
        getAccountById($connect, $page[1]);
} elseif ($page[0] === "animals") {
    if (isset($page[2]) && $page[2] === "locations")
        getVisitedLocations($connect, $page[1]);
    elseif ($page[1] === "types")
        getAnimalTypeById($connect, $page[2]);
    elseif ($page[1] === "search")
        getSearchAnimals($connect);
    else
        getAnimalById($connect, $page[1]);
} elseif ($page[0] === "locations"){
    getLocationById($connect, $page[1]);
} elseif ($page[0] === "registration") {
    registrationAccount();
}
