<?php

$timeout = 1 * 60;

if (!isset($_SESSION['last_activity']) || (time() - $_SESSION['last_activity'] > $timeout)) {
    header('Location: ' . BASE_URL . '/timeout');
    exit;
}

$_SESSION['last_activity'] = time();