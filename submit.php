<?php
session_start();

// Подключение к базе данных
$host = 'localhost';
$dbname = 'u68895'; 
$username = 'u68895'; 
$password = '1562324'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $_SESSION['errors']['database'] = "Ошибка подключения к базе данных: " . $e->getMessage();
    header('Location: index.php');
    exit;
}

// Валидация данных
$errors = [];
$input = [
    'full_name' => trim($_POST['full_name'] ?? ''),
    'phone' => trim($_POST['phone'] ?? ''),
    'email' => trim($_POST['email'] ?? ''),
    'birth_date' => $_POST['birth_date'] ?? '',
    'gender' => $_POST['gender'] ?? '',
    'languages' => $_POST['languages'] ?? [],
    'biography' => trim($_POST['biography'] ?? ''),
    'contract_agreed' => isset($_POST['contract_agreed']) ? 1 : 0
];

// Валидация ФИО
if (empty($input['full_name'])) {
    $errors['full_name'] = "ФИО обязательно для заполнения";
} elseif (!preg_match('/^[а-яА-ЯёЁa-zA-Z\s\-]{2,150}$/u', $input['full_name'])) {
    $errors['full_name'] = "ФИО должно содержать только буквы, пробелы и дефисы (2-150 символов)";
}

// Валидация телефона
if (empty($input['phone'])) {
    $errors['phone'] = "Телефон обязателен для заполнения";
} elseif (!preg_match('/^\+?\d{10,15}$/', $input['phone'])) {
    $errors['phone'] = "Телефон должен содержать 10-15 цифр, может начинаться с +";
}

// Валидация email
if (empty($input['email'])) {
    $errors['email'] = "Email обязателен для заполнения";
} elseif (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "Введите корректный email (например, user@example.com)";
}

// Валидация даты рождения
if (empty($input['birth_date'])) {
    $errors['birth_date'] = "Дата рождения обязательна";
} else {
    $today = new DateTime();
    $birthdate = DateTime::createFromFormat('Y-m-d', $input['birth_date']);
    if (!$birthdate || $birthdate > $today) {
        $errors['birth_date'] = "Введите корректную дату рождения (не из будущего)";
    }
}

// Валидация пола
if (empty($input['gender']) || !in_array($input['gender'], ['male', 'female'])) {
    $errors['gender'] = "Укажите пол";
}

// Валидация языков программирования
if (empty($input['languages'])) {
    $errors['languages'] = "Выберите хотя бы один язык программирования";
} else {
    $allowedLanguages = range(1, 12);
    foreach ($input['languages'] as $langId) {
        if (!in_array($langId, $allowedLanguages)) {
            $errors['languages'] = "Выбран недопустимый язык программирования";
            break;
        }
    }
}

// Валидация согласия с контрактом
if (!$input['contract_agreed']) {
    $errors['contract_agreed'] = "Необходимо подтвердить ознакомление с контрактом";
}

// Если есть ошибки
if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old_input'] = $input;
    header('Location: index.php');
    exit;
}

// Сохранение в базу данных
try {
    $pdo->beginTransaction();

    // 1. Сохраняем основную информацию
    $stmt = $pdo->prepare("
        INSERT INTO applications (
            full_name, 
            phone, 
            email, 
            birth_date, 
            gender, 
            biography, 
            contract_agreed
        ) VALUES (
            :full_name, 
            :phone, 
            :email, 
            :birth_date, 
            :gender, 
            :biography, 
            :contract_agreed
        )
    ");
    
    $stmt->execute([
        ':full_name' => $input['full_name'],
        ':phone' => $input['phone'],
        ':email' => $input['email'],
        ':birth_date' => $input['birth_date'],
        ':gender' => $input['gender'],
        ':biography' => $input['biography'],
        ':contract_agreed' => $input['contract_agreed']
    ]);
    
    $applicationId = $pdo->lastInsertId();

    // 2. Сохраняем выбранные языки программирования
    $stmt = $pdo->prepare("
        INSERT INTO application_languages (
            application_id, 
            language_id
        ) VALUES (
            :application_id, 
            :language_id
        )
    ");
    
    foreach ($input['languages'] as $langId) {
        $stmt->execute([
            ':application_id' => $applicationId,
            ':language_id' => $langId
        ]);
    }
    
    $pdo->commit();

    // Сохраняем данные в куки на год
    foreach ($input as $key => $value) {
        $cookieValue = is_array($value) ? implode(',', $value) : $value;
        setcookie($key, $cookieValue, time() + 60*60*24*365, '/');
    }
    
    header('Location: index.php?success=1');
    exit;

} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['errors']['database'] = "Ошибка при сохранении данных: " . $e->getMessage();
    $_SESSION['old_input'] = $input;
    header('Location: index.php');
    exit;
}
