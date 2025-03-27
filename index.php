<?php
declare(strict_types=1);

session_start();

// Конфигурация
const CORRECT_EMAIL = 'test@mail.ru';
const CORRECT_PASSWORD = '12345';

// Основная логика
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit();
}

if (isset($_SESSION['userEmail'])) {
    require __DIR__ . '/views/dashboard.html';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['email'] === CORRECT_EMAIL && $_POST['password'] === CORRECT_PASSWORD) {
        $_SESSION['userEmail'] = $_POST['email'];
        header('Location: index.php');
        exit();
    }
    $error = 'Ошибка: Неправильный email или пароль!';
}

require __DIR__ . '/views/login.html';
