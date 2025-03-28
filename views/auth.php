<?php
session_start();

// Фиксированные данные для примера
const CORRECT_EMAIL = 'test@mail.ru';
const CORRECT_PASSWORD = '12345';

if ($_POST['email'] === CORRECT_EMAIL && $_POST['password'] === CORRECT_PASSWORD) {
    $_SESSION['user_email'] = CORRECT_EMAIL;
    header('Location: dashboard.php');
} else {
    header('Location: index.php?error=1');
}
exit();
?>