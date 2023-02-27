<?php

require("database.php");
require("accounts.php");
require("animals.php");
require("animal_type.php");

header("Content-type: application/json");

$q = $_GET["q"];
$params = explode("/", $q);
$page = [];
for ($i = 0; $i < count($params); $i++){
    $page[] = $params[$i];
}
// if (isset($params[count($params)]) && $params[count($params)] !== $page) $id = $params[count($params)];

if ($page[0] === "accounts"){
    if ($page[1] === "search")
        getSearchAccount($connect);
    else
        getOneAccount($connect, $page[1]);
} elseif ($page[0] === "animals") {
    if ($page[1] === "types")
        getAnimalTypeById($connect, $page[2]);
    elseif ($page[1] === "search")
        getSearchAnimals($connect);
    else
        getAnimalById($connect, $page[1]);
}
