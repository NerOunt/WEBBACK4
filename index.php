<?php
header('Content-Type: text/html; charset=UTF-8');

// Параметры подключения к БД
$db_host = 'localhost';
$db_name = 'u68895';
$db_user = 'u68895';
$db_pass = '1562324';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Инициализация сообщений
    $messages = array();
    
    // Сообщение об успешном сохранении
    if (!empty($_COOKIE['save'])) {
        setcookie('save', '', 100000);
        $messages[] = '<div class="success">Спасибо, результаты сохранены.</div>';
    }
    
    // Сообщения об ошибках
    $errors = array();
    $errors['full_name'] = !empty($_COOKIE['full_name_error']);
    $errors['phone'] = !empty($_COOKIE['phone_error']);
    $errors['email'] = !empty($_COOKIE['email_error']);
    $errors['birth_date'] = !empty($_COOKIE['birth_date_error']);
    $errors['gender'] = !empty($_COOKIE['gender_error']);
    $errors['languages'] = !empty($_COOKIE['languages_error']);
    $errors['biography'] = !empty($_COOKIE['biography_error']);
    $errors['contract_agreed'] = !empty($_COOKIE['contract_agreed_error']);
    
    // Вывод сообщений об ошибках
    if ($errors['full_name']) {
        setcookie('full_name_error', '', 100000);
        $messages[] = '<div class="error">Заполните имя правильно (только буквы, пробелы и дефисы, 2-150 символов).</div>';
    }
    if ($errors['phone']) {
        setcookie('phone_error', '', 100000);
        $messages[] = '<div class="error">Введите телефон правильно (10-15 цифр, можно с + в начале).</div>';
    }
    if ($errors['email']) {
        setcookie('email_error', '', 100000);
        $messages[] = '<div class="error">Введите корректный email.</div>';
    }
    if ($errors['birth_date']) {
        setcookie('birth_date_error', '', 100000);
        $messages[] = '<div class="error">Введите корректную дату рождения (из прошлого).</div>';
    }
    if ($errors['gender']) {
        setcookie('gender_error', '', 100000);
        $messages[] = '<div class="error">Укажите пол.</div>';
    }
    if ($errors['languages']) {
        setcookie('languages_error', '', 100000);
        $messages[] = '<div class="error">Выберите хотя бы один язык программирования.</div>';
    }
    if ($errors['contract_agreed']) {
        setcookie('contract_agreed_error', '', 100000);
        $messages[] = '<div class="error">Необходимо подтвердить ознакомление с контрактом.</div>';
    }
    
    // Сохраненные значения полей
    $values = array();
    $values['full_name'] = empty($_COOKIE['full_name_value']) ? '' : $_COOKIE['full_name_value'];
    $values['phone'] = empty($_COOKIE['phone_value']) ? '' : $_COOKIE['phone_value'];
    $values['email'] = empty($_COOKIE['email_value']) ? '' : $_COOKIE['email_value'];
    $values['birth_date'] = empty($_COOKIE['birth_date_value']) ? '' : $_COOKIE['birth_date_value'];
    $values['gender'] = empty($_COOKIE['gender_value']) ? '' : $_COOKIE['gender_value'];
    $values['languages'] = empty($_COOKIE['languages_value']) ? array() : explode(',', $_COOKIE['languages_value']);
    $values['biography'] = empty($_COOKIE['biography_value']) ? '' : $_COOKIE['biography_value'];
    $values['contract_agreed'] = !empty($_COOKIE['contract_agreed_value']);
    
    // Включаем форму
    include('form.php');
}
else {
    // Обработка POST данных
    $errors = FALSE;
    
    // Валидация полей
    if (empty($_POST['full_name']) || !preg_match('/^[а-яА-ЯёЁa-zA-Z\s\-]{2,150}$/u', $_POST['full_name'])) {
        setcookie('full_name_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    }
    setcookie('full_name_value', $_POST['full_name'], time() + 30 * 24 * 60 * 60);
    
    if (empty($_POST['phone']) || !preg_match('/^\+?\d{10,15}$/', $_POST['phone'])) {
        setcookie('phone_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    }
    setcookie('phone_value', $_POST['phone'], time() + 30 * 24 * 60 * 60);
    
    if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        setcookie('email_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    }
    setcookie('email_value', $_POST['email'], time() + 30 * 24 * 60 * 60);
    
    $today = new DateTime();
    $birthdate = DateTime::createFromFormat('Y-m-d', $_POST['birth_date']);
    if (empty($_POST['birth_date']) || !$birthdate || $birthdate > $today) {
        setcookie('birth_date_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    }
    setcookie('birth_date_value', $_POST['birth_date'], time() + 30 * 24 * 60 * 60);
    
    if (empty($_POST['gender']) || !in_array($_POST['gender'], ['male', 'female'])) {
        setcookie('gender_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    }
    setcookie('gender_value', $_POST['gender'], time() + 30 * 24 * 60 * 60);
    
    $allowedLanguages = range(1, 12);
    if (empty($_POST['languages'])) {
        setcookie('languages_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    } else {
        foreach ($_POST['languages'] as $langId) {
            if (!in_array($langId, $allowedLanguages)) {
                setcookie('languages_error', '1', time() + 24 * 60 * 60);
                $errors = TRUE;
                break;
            }
        }
    }
    setcookie('languages_value', implode(',', $_POST['languages']), time() + 30 * 24 * 60 * 60);
    
    if (empty($_POST['contract_agreed'])) {
        setcookie('contract_agreed_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    }
    setcookie('contract_agreed_value', isset($_POST['contract_agreed']) ? '1' : '', time() + 30 * 24 * 60 * 60);
    
    if ($errors) {
        header('Location: index.php');
        exit();
    }
    else {
        // Удаляем куки с ошибками
        setcookie('full_name_error', '', 100000);
        setcookie('phone_error', '', 100000);
        setcookie('email_error', '', 100000);
        setcookie('birth_date_error', '', 100000);
        setcookie('gender_error', '', 100000);
        setcookie('languages_error', '', 100000);
        setcookie('contract_agreed_error', '', 100000);
        
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