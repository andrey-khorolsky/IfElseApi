<?php

require("functions.php");

header("Content-type: application/json");

$q = $_GET["q"];
$params = explode("/", $q);
$page = $params[0];
if (isset($params[0])) $id = $params[1];

if ($page === "accounts"){
    if ($id === "search")
        getSearchAccount($connect);
    getOneAccount($connect, $id);
}  
