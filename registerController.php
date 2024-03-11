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
    if (isset($check)) {
        $user = $Database->getUser($email);
        if (!isset($user)) {
            User::register($userData, $Database);
            $_SESSION['error'] = '';
            header('Location: http://localhost/Esercizio-Settimana-13/');
        } else {
            $_SESSION['error'] = 'Email già usata';
            header('Location: http://localhost/Esercizio-Settimana-13/register.php');
        }
    } else {
        $_SESSION['error'] = 'Devi accettare le condizioni della privacy';
        header('Location: http://localhost/Esercizio-Settimana-13/register.php');
    }
}


