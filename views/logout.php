<?php

declare(strict_types=1);

session_start();

// Уничтожаем все данные сессии
$_SESSION = [];
session_destroy();

// Перенаправляем на страницу входа
header('Location: index.php');
exit();