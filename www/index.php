<?php


header("Content-type: application/json");

$page = $_GET["q"];
$page = explode("/", $page);


if ($page[0] === "accounts" || $page[0] === "registration"){
    require("account.php");
} elseif ($page[0] === "animals") {
    if (isset($page[2]) && $page[2] === "locations")
        require("animal_visited_location.php");
    elseif (isset($page[1]) && $page[1] === "types")
        require("animal_type.php");
    else
        require("animal.php");
} elseif ($page[0] === "locations"){
    require("location.php");
}
