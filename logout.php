<?php
session_start(); // leggo una sessione esistente
require_once('classi.php');
use Site\User;

User::logout();