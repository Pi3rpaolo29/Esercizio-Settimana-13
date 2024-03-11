<?php
session_start();
require_once('classi.php');
use Site\Database;
use Site\User;

$config = require_once('config.php');

// Test site Database
$Database = Database::getInstance($config);
/* var_dump($conn); */

$email = $_POST['email'];
$password = $_POST['password'];
$check = $_POST['check'];

if (isset($email) && isset($password)) {
    $userData['email'] = $email;
    $userData['password'] = $password;
    $userData['check'] = $check;
    $isLogged = User::login($userData, $Database);
    if ($isLogged === 0) {
        header('Location: http://localhost/Esercizio-Settimana-13/login.php');
    } else {
        header('Location: http://localhost/Esercizio-Settimana-13/');
    }
}
