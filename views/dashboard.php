<?php
session_start();

// Проверка авторизации
if (!isset($_SESSION['user_email'])) {
    header('Location: ../index.php');
    exit();
}

// Подключение к БД
try {
    $db = new PDO('mysql:host=localhost;dbname=report_db', 'root', '12345', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

// Обработка формы
$success = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $message = $_POST['message'] ?? '';
    $contactMethod = $_POST['contact_method'] ?? '';
    $interests = isset($_POST['interests']) ? implode(', ', $_POST['interests']) : '';
    $rating = $_POST['rating'] ?? '';

    // Валидация
    if (empty($name)) $errors[] = "Имя обязательно для заполнения";
    if (empty($message)) $errors[] = "Сообщение обязательно для заполнения";
    if (empty($contactMethod)) $errors[] = "Выберите способ связи";
    if (empty($rating)) $errors[] = "Укажите оценку";

    if (empty($errors)) {
        try {
            $stmt = $db->prepare(
                "INSERT INTO reports 
                (user_email, name, message, contact_method, interests, rating, created_at) 
                VALUES (:email, :name, :message, :contact_method, :interests, :rating, NOW())"
            );

            $stmt->execute([
                ':email' => $_SESSION['user_email'],
                ':name' => $name,
                ':message' => $message,
                ':contact_method' => $contactMethod,
                ':interests' => $interests,
                ':rating' => $rating
            ]);

            $success = "Ваш отзыв успешно отправлен!";
        } catch (PDOException $e) {
            $errors[] = "Ошибка при сохранении: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Форма обратной связи</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-container {
            max-width: 700px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .required:after {
            content: " *";
            color: red;
        }
    </style>
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="form-container">
        <h2 class="text-center mb-4">Форма обратной связи</h2>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <!-- Текстовое поле -->
            <div class="mb-3">
                <label for="name" class="form-label required">Ваше имя</label>
                <input type="text" class="form-control" id="name" name="name"
                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
            </div>

            <!-- Многострочное поле -->
            <div class="mb-3">
                <label for="message" class="form-label required">Ваше сообщение</label>
                <textarea class="form-control" id="message" name="message" rows="5" required><?=
                    htmlspecialchars($_POST['message'] ?? '') ?></textarea>
            </div>

            <!-- Радиокнопки -->
            <div class="mb-3">
                <label class="form-label required">Способ связи</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="contact_method" id="method_email"
                           value="email" <?= ($_POST['contact_method'] ?? '') === 'email' ? 'checked' : '' ?> required>
                    <label class="form-check-label" for="method_email">Email</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="contact_method" id="method_phone"
                           value="phone" <?= ($_POST['contact_method'] ?? '') === 'phone' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="method_phone">Телефон</label>
                </div>
            </div>

            <!-- Флажки -->
            <div class="mb-3">
                <label class="form-label">Ваши интересы</label>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="interests[]" id="interest_tech"
                           value="Технологии" <?= isset($_POST['interests']) && in_array('Технологии', $_POST['interests']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="interest_tech">Технологии</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="interests[]" id="interest_sport"
                           value="Спорт" <?= isset($_POST['interests']) && in_array('Спорт', $_POST['interests']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="interest_sport">Спорт</label>
                </div>
            </div>

            <!-- Выпадающий список -->
            <div class="mb-4">
                <label for="rating" class="form-label required">Оценка сервиса</label>
                <select class="form-select" id="rating" name="rating" required>
                    <option value="" disabled <?= empty($_POST['rating']) ? 'selected' : '' ?>>Выберите оценку</option>
                    <option value="5" <?= ($_POST['rating'] ?? '') === '5' ? 'selected' : '' ?>>5 (Отлично)</option>
                    <option value="4" <?= ($_POST['rating'] ?? '') === '4' ? 'selected' : '' ?>>4 (Хорошо)</option>
                    <option value="3" <?= ($_POST['rating'] ?? '') === '3' ? 'selected' : '' ?>>3 (Удовлетворительно)</option>
                    <option value="2" <?= ($_POST['rating'] ?? '') === '2' ? 'selected' : '' ?>>2 (Плохо)</option>
                    <option value="1" <?= ($_POST['rating'] ?? '') === '1' ? 'selected' : '' ?>>1 (Отвратительно)</option>
                </select>
            </div>

            <!-- Кнопки -->
            <div class="d-flex justify-content-between mt-4">
                <button type="reset" class="btn btn-secondary px-4">Сбросить</button>
                <button type="submit" class="btn btn-primary px-4">Отправить</button>
            </div>
        </form>

        <div class="mt-4 text-end">
            <a href="logout.php" class="btn btn-outline-danger">Выйти из системы</a>
        </div>
    </div>
</div>
</body>
</html>