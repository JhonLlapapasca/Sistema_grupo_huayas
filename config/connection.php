<?php

require_once 'index.php';

function connection()
{
    $host = constant('HOST');
    $user = constant('USER');
    $password = constant('PASSWORD');

    $bd = constant('DB');

    $connection = mysqli_connect($host, $user, $password) or die("Connection Error");

    mysqli_select_db($connection, $bd);

    return $connection;
}