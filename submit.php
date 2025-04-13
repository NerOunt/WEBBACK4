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

// Валидация ФИО (остается без изменений)
if (empty($input['full_name'])) {
    $errors['full_name'] = "ФИО обязательно для заполнения";
} elseif (!preg_match('/^[а-яА-ЯёЁa-zA-Z\s\-]{2,150}$/u', $input['full_name'])) {
    $errors['full_name'] = "ФИО должно содержать только буквы, пробелы и дефисы (2-150 символов)";
}

// Валидация пола с учетом нового значения 'other'
if (empty($input['gender']) || !in_array($input['gender'], ['male', 'female', 'other'])) {
    $errors['gender'] = "Укажите пол (male, female или other)";
}

// Получаем список допустимых языков из БД
$allowedLanguages = [];
try {
    $stmt = $pdo->query("SELECT id FROM programming_languages");
    $allowedLanguages = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $_SESSION['errors']['database'] = "Ошибка при получении списка языков: " . $e->getMessage();
    header('Location: index.php');
    exit;
}

// Валидация языков программирования
if (empty($input['languages'])) {
    $errors['languages'] = "Выберите хотя бы один язык программирования";
} else {
    foreach ($input['languages'] as $langId) {
        if (!in_array($langId, $allowedLanguages)) {
            $errors['languages'] = "Выбран недопустимый язык программирования";
            break;
        }
    }
}

// Остальная валидация остается без изменений...

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