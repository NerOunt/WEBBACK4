<?php
// Подключение к базе данных
$host = 'localhost';
$dbname = 'u68895'; 
$username = 'u68895'; 
$password = '1562324'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

// Функция для сохранения ошибок в cookies
function saveErrorsToCookies($errors, $formData) {
    setcookie('form_errors', json_encode($errors), 0, '/');
    setcookie('form_data', json_encode($formData), 0, '/');
}

// Функция для очистки cookies с ошибками
function clearErrorCookies() {
    setcookie('form_errors', '', time() - 3600, '/');
    setcookie('form_data', '', time() - 3600, '/');
}

// Функция для сохранения успешных данных в cookies на год
function saveSuccessDataToCookies($formData) {
    foreach ($formData as $key => $value) {
        if (is_array($value)) {
            setcookie($key, json_encode($value), time() + 365 * 24 * 3600, '/');
        } else {
            setcookie($key, $value, time() + 365 * 24 * 3600, '/');
        }
    }
}

// Валидация данных
$errors = [];
$formData = $_POST;

// Валидация ФИО
if (empty($_POST['full_name'])) {
    $errors['full_name'] = "ФИО обязательно для заполнения.";
} elseif (!preg_match('/^[а-яА-ЯёЁa-zA-Z\s\-]+$/u', $_POST['full_name'])) {
    $errors['full_name'] = "ФИО должно содержать только буквы, пробелы и дефисы.";
}

// Валидация телефона
if (empty($_POST['phone'])) {
    $errors['phone'] = "Телефон обязателен для заполнения.";
} elseif (!preg_match('/^[\d\s\-\+\(\)]+$/', $_POST['phone'])) {
    $errors['phone'] = "Телефон должен содержать только цифры, пробелы, +, -, ( и ).";
}

// Валидация email
if (empty($_POST['email'])) {
    $errors['email'] = "Email обязателен для заполнения.";
} elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "Введите корректный email.";
}

// Валидация даты рождения
if (empty($_POST['birth_date'])) {
    $errors['birth_date'] = "Дата рождения обязательна.";
} elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['birth_date'])) {
    $errors['birth_date'] = "Дата должна быть в формате ГГГГ-ММ-ДД.";
}

// Валидация пола
if (empty($_POST['gender'])) {
    $errors['gender'] = "Укажите пол.";
}

// Валидация языков программирования
if (empty($_POST['languages'])) {
    $errors['languages'] = "Выберите хотя бы один язык программирования.";
}

// Валидация согласия с контрактом
if (empty($_POST['contract_agreed'])) {
    $errors['contract_agreed'] = "Необходимо подтвердить ознакомление с контрактом.";
}

// Если есть ошибки, сохраняем их в cookies и перенаправляем обратно
if (!empty($errors)) {
    saveErrorsToCookies($errors, $formData);
    header('Location: form.php');
    exit;
}

// Если ошибок нет, сохраняем данные в БД
try {
    // Вставка основной информации
    $stmt = $pdo->prepare("
        INSERT INTO applications 
        (full_name, phone, email, birth_date, gender, biography, contract_agreed) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $_POST['full_name'],
        $_POST['phone'],
        $_POST['email'],
        $_POST['birth_date'],
        $_POST['gender'],
        $_POST['biography'] ?? '',
        (int)$_POST['contract_agreed']
    ]);

    $applicationId = $pdo->lastInsertId();

    // Вставка выбранных языков программирования
    $stmt = $pdo->prepare("
        INSERT INTO application_languages (application_id, language_id) 
        VALUES (?, ?)
    ");

    foreach ($_POST['languages'] as $languageId) {
        $stmt->execute([$applicationId, $languageId]);
    }

    // Сохраняем успешные данные в cookies
    saveSuccessDataToCookies($formData);
    clearErrorCookies();
    
    header('Location: form.php?success=1');
    exit;
} catch (PDOException $e) {
    die("Ошибка при сохранении данных: " . $e->getMessage());
}
?>
