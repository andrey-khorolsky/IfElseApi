<?php

require("database.php");
require("accounts.php");

header("Content-type: application/json");

$q = $_GET["q"];
$params = explode("/", $q);
$page = $params[0];
if (isset($params[0])) $id = $params[1];

if ($page === "accounts"){
    if ($id === "search")
        getSearchAccount($connect);
    else
    getOneAccount($connect, $id);
}  
