<?php

$con = mysqli_connect("localhost", "dbo473066905", "wododou", "db473066905");

switch($_GET['action']) {
    case 'artikel_del' : {
        mysqli_query($con, "UPDATE adbgb_artikel SET visible = 0 WHERE ID = " . $_GET['id']);
        break;
    }

    default : {}
}

header('Location: ' . $_SERVER['HTTP_REFERER']);

?>
