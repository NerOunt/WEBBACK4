<?php
header('Content-Type: text/html; charset=UTF-8');

// Параметры подключения к БД
$db_host = 'localhost';
$db_name = 'u68895';
$db_user = 'u68895';
$db_pass = '1562324';

// Инициализация переменных
$messages = array();
$errors = array();
$values = array();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Проверяем наличие сообщения об успешном сохранении
    if (!empty($_COOKIE['save'])) {
        setcookie('save', '', time() - 3600);
        $messages[] = '<div class="success">Результаты сохранены.</div>';
    }

    // Проверяем ошибки в куках
    $error_fields = array(
        'full_name', 'phone', 'email', 'birth_date', 
        'gender', 'languages', 'contract_agreed'
    );
    
    foreach ($error_fields as $field) {
        $errors[$field] = !empty($_COOKIE[$field.'_error']);
        if ($errors[$field]) {
            setcookie($field.'_error', '', time() - 3600);
        }
    }

    // Загружаем сохраненные значения (хранятся 1 год)
    $value_fields = array(
        'full_name', 'phone', 'email', 'birth_date', 
        'gender', 'biography', 'contract_agreed'
    );
    
    foreach ($value_fields as $field) {
        $values[$field] = empty($_COOKIE[$field.'_value']) ? '' : $_COOKIE[$field.'_value'];
    }

    // Особые случаи
    $values['languages'] = empty($_COOKIE['languages_value']) ? array() : explode(',', $_COOKIE['languages_value']);
    $values['contract_agreed'] = !empty($_COOKIE['contract_agreed_value']);

    // Включаем форму
    include('form.php');
}
else {
    // Обработка POST данных
    $validation_failed = false;

    // Валидация полей
    if (empty($_POST['full_name']) || !preg_match('/^[а-яА-ЯёЁa-zA-Z\s\-]{2,150}$/u', $_POST['full_name'])) {
        setcookie('full_name_error', '1', 0); // До конца сессии
        $validation_failed = true;
    }
    setcookie('full_name_value', $_POST['full_name'], time() + 365 * 24 * 60 * 60); // На 1 год

    if (empty($_POST['phone']) || !preg_match('/^\+?\d{10,15}$/', $_POST['phone'])) {
        setcookie('phone_error', '1', 0);
        $validation_failed = true;
    }
    setcookie('phone_value', $_POST['phone'], time() + 365 * 24 * 60 * 60);

    if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        setcookie('email_error', '1', 0);
        $validation_failed = true;
    }
    setcookie('email_value', $_POST['email'], time() + 365 * 24 * 60 * 60);

    $today = new DateTime();
    $birthdate = DateTime::createFromFormat('Y-m-d', $_POST['birth_date']);
    if (empty($_POST['birth_date']) || !$birthdate || $birthdate > $today) {
        setcookie('birth_date_error', '1', 0);
        $validation_failed = true;
    }
    setcookie('birth_date_value', $_POST['birth_date'], time() + 365 * 24 * 60 * 60);

    if (empty($_POST['gender']) || !in_array($_POST['gender'], ['male', 'female'])) {
        setcookie('gender_error', '1', 0);
        $validation_failed = true;
    }
    setcookie('gender_value', $_POST['gender'], time() + 365 * 24 * 60 * 60);

    $allowedLanguages = range(1, 12);
    if (empty($_POST['languages'])) {
        setcookie('languages_error', '1', 0);
        $validation_failed = true;
    } else {
        foreach ($_POST['languages'] as $langId) {
            if (!in_array($langId, $allowedLanguages)) {
                setcookie('languages_error', '1', 0);
                $validation_failed = true;
                break;
            }
        }
    }
    setcookie('languages_value', implode(',', $_POST['languages']), time() + 365 * 24 * 60 * 60);

    if (empty($_POST['contract_agreed'])) {
        setcookie('contract_agreed_error', '1', 0);
        $validation_failed = true;
    }
    setcookie('contract_agreed_value', isset($_POST['contract_agreed']) ? '1' : '', time() + 365 * 24 * 60 * 60);

    if ($validation_failed) {
        header('Location: index.php');
        exit();
    }
    else {
        // Удаляем куки с ошибками
        $error_cookies = array(
            'full_name_error', 'phone_error', 'email_error', 
            'birth_date_error', 'gender_error', 'languages_error', 
            'contract_agreed_error'
        );
        
        foreach ($error_cookies as $cookie) {
            setcookie($cookie, '', time() - 3600);
        }

        // Сохранение в БД
        try {
            $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Сохраняем основную информацию
            $stmt = $pdo->prepare("INSERT INTO applications (full_name, phone, email, birth_date, gender, biography, contract_agreed) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['full_name'],
                $_POST['phone'],
                $_POST['email'],
                $_POST['birth_date'],
                $_POST['gender'],
                $_POST['biography'],
                isset($_POST['contract_agreed']) ? 1 : 0
            ]);
            
            $appId = $pdo->lastInsertId();
            
            // Сохраняем языки программирования
            $stmt = $pdo->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)");
            foreach ($_POST['languages'] as $langId) {
                $stmt->execute([$appId, $langId]);
            }
            
            setcookie('save', '1', time() + 24 * 60 * 60);
            header('Location: index.php');
        } catch (PDOException $e) {
            setcookie('database_error', '1', time() + 24 * 60 * 60);
            header('Location: index.php');
            exit();
        }
    }
}
?>
