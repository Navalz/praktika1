<?php
session_start();

// Если уже авторизован - перенаправляем в ЛК
if (isset($_SESSION['user_email'])) {
    header('Location: views/dashboard.php');
    exit();
}

// Отображение ошибки
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход в систему</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5" style="max-width: 400px;">
    <h2 class="mb-4">Вход в систему</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger">Неверный email или пароль!</div>
    <?php endif; ?>

    <form action="views/auth.php" method="POST">
        <div class="mb-3">
            <input type="email" name="email" class="form-control" placeholder="test@mail.ru" required>
        </div>
        <div class="mb-3">
            <input type="password" name="password" class="form-control" placeholder="12345" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Войти</button>
    </form>
</div>
</body>
</html>