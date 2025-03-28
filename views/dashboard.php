<?php

declare(strict_types=1);

session_start();

// Проверка авторизации
if (!isset($_SESSION['user_email'])) {
    header('Location: index.php');
    exit();
}

// Конфигурация БД (в реальном проекте вынесите в отдельный файл)
const DB_HOST = 'localhost';
const DB_NAME = 'report_db';
const DB_USER = 'root';
const DB_PASS = '';

/**
 * Подключается к базе данных
 *
 * @return PDO
 * @throws PDOException
 */
function connectToDatabase(): PDO
{
    return new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
}

// Обработка отправки формы
$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = connectToDatabase();

        // Получение и валидация данных
        $name = $_POST['name'] ?? '';
        $message = $_POST['message'] ?? '';
        $contactMethod = $_POST['contact_method'] ?? '';
        $interests = isset($_POST['interests']) ? implode(', ', $_POST['interests']) : '';
        $rating = $_POST['rating'] ?? '';
        $userEmail = $_SESSION['user_email'];

        // Валидация
        if (empty($name)) {
            $errors[] = "Имя обязательно для заполнения";
        }

        if (empty($message)) {
            $errors[] = "Сообщение обязательно для заполнения";
        }

        if (empty($rating)) {
            $errors[] = "Пожалуйста, оцените наш сервис";
        }

        // Если нет ошибок - сохраняем
        if (empty($errors)) {
            $stmt = $db->prepare(
                "INSERT INTO reports 
                (user_email, name, message, contact_method, interests, rating, created_at) 
                VALUES (:email, :name, :message, :contact_method, :interests, :rating, NOW())"
            );

            $stmt->execute([
                ':email' => $userEmail,
                ':name' => $name,
                ':message' => $message,
                ':contact_method' => $contactMethod,
                ':interests' => $interests,
                ':rating' => $rating,
            ]);

            $successMessage = "Ваше сообщение успешно отправлено!";
        }
    } catch (PDOException $e) {
        $errors[] = "Ошибка базы данных: " . $e->getMessage();
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
        body {
            background-color: #f8f9fa;
            padding-top: 20px;
        }
        .feedback-form {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        .form-title {
            color: #0d6efd;
            margin-bottom: 25px;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="feedback-form">
                <h2 class="form-title">Форма обратной связи</h2>

                <?php if (!empty($errors)) : ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error) : ?>
                            <p><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($successMessage)) : ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($successMessage) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="dashboard.php">
                    <div class="mb-3">
                        <label for="name" class="form-label">Ваше имя</label>
                        <input
                            type="text"
                            class="form-control"
                            id="name"
                            name="name"
                            value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label for="message" class="form-label">Ваше сообщение</label>
                        <textarea
                            class="form-control"
                            id="message"
                            name="message"
                            rows="5"
                            required
                        ><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Предпочтительный способ связи</label>
                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="radio"
                                name="contact_method"
                                id="contact_email"
                                value="email"
                                <?= ($_POST['contact_method'] ?? 'email') === 'email' ? 'checked' : '' ?>
                            >
                            <label class="form-check-label" for="contact_email">Email</label>
                        </div>
                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="radio"
                                name="contact_method"
                                id="contact_phone"
                                value="phone"
                                <?= ($_POST['contact_method'] ?? '') === 'phone' ? 'checked' : '' ?>
                            >
                            <label class="form-check-label" for="contact_phone">Телефон</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ваши интересы</label>
                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="interests[]"
                                id="interest_tech"
                                value="technology"
                                <?= isset($_POST['interests']) && in_array('technology', $_POST['interests']) ? 'checked' : '' ?>
                            >
                            <label class="form-check-label" for="interest_tech">Технологии</label>
                        </div>
                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="interests[]"
                                id="interest_sport"
                                value="sport"
                                <?= isset($_POST['interests']) && in_array('sport', $_POST['interests']) ? 'checked' : '' ?>
                            >
                            <label class="form-check-label" for="interest_sport">Спорт</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="rating" class="form-label">Оцените наш сервис</label>
                        <select class="form-select" id="rating" name="rating" required>
                            <option
                                value=""
                                disabled
                                <?= empty($_POST['rating']) ? 'selected' : '' ?>
                            >
                                Выберите оценку
                            </option>
                            <option
                                value="5"
                                <?= ($_POST['rating'] ?? '') === '5' ? 'selected' : '' ?>
                            >
                                Отлично
                            </option>
                            <option
                                value="4"
                                <?= ($_POST['rating'] ?? '') === '4' ? 'selected' : '' ?>
                            >
                                Хорошо
                            </option>
                            <option
                                value="3"
                                <?= ($_POST['rating'] ?? '') === '3' ? 'selected' : '' ?>
                            >
                                Удовлетворительно
                            </option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="reset" class="btn btn-secondary">Сбросить</button>
                        <button type="submit" class="btn btn-primary">Отправить</button>
                    </div>
                </form>
            </div>

            <div class="text-center mt-3">
                <a href="logout.php" class="btn btn-outline-danger">Выйти из системы</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
