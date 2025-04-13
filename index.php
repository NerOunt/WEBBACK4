<?php
session_start();

// Получаем данные из сессии или кук
$errors = $_SESSION['errors'] ?? [];
$oldInput = $_SESSION['old_input'] ?? [];

// Очищаем данные сессии после использования
unset($_SESSION['errors']);
unset($_SESSION['old_input']);

// Если нет ошибок, пробуем взять данные из кук
if (empty($errors)) {
    $oldInput = [
        'full_name' => $_COOKIE['full_name'] ?? '',
        'phone' => $_COOKIE['phone'] ?? '',
        'email' => $_COOKIE['email'] ?? '',
        'birth_date' => $_COOKIE['birth_date'] ?? '',
        'gender' => $_COOKIE['gender'] ?? '',
        'languages' => isset($_COOKIE['languages']) ? explode(',', $_COOKIE['languages']) : [],
        'biography' => $_COOKIE['biography'] ?? '',
        'contract_agreed' => $_COOKIE['contract_agreed'] ?? false
    ];
}

// Функция для проверки выбранного языка
function isLanguageSelected($languages, $id) {
    return in_array($id, $languages ?? []);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Анкета</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .form-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select, textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        textarea {
            height: 100px;
        }
        select[multiple] {
            height: 120px;
        }
        .error {
            border-color: #ff0000 !important;
        }
        .error-message {
            color: #ff0000;
            font-size: 0.9em;
            margin-top: 5px;
        }
        .error-summary {
            background: #ffeeee;
            border-left: 4px solid #ff0000;
            padding: 10px;
            margin-bottom: 20px;
        }
        button {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }
        button:hover {
            background: #45a049;
        }
        .radio-group {
            display: flex;
            gap: 15px;
        }
        .radio-option {
            display: flex;
            align-items: center;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .success-message {
            color: #4CAF50;
            margin-bottom: 20px;
            padding: 10px;
            background: #eeffee;
            border-left: 4px solid #4CAF50;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Анкета</h1>
        
        <?php if (isset($_GET['success'])): ?>
        <div class="success-message">
            Данные успешно сохранены!
        </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
        <div class="error-summary">
            <h3>Ошибки в форме:</h3>
            <ul>
                <?php foreach ($errors as $field => $message): ?>
                <li><?= htmlspecialchars($message) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form method="POST" action="submit.php">
            <!-- Поле ФИО -->
            <div class="form-group <?= isset($errors['full_name']) ? 'error-field' : '' ?>">
                <label for="full_name">ФИО*</label>
                <input type="text" id="full_name" name="full_name" required
                       class="<?= isset($errors['full_name']) ? 'error' : '' ?>"
                       value="<?= htmlspecialchars($oldInput['full_name'] ?? '') ?>">
                <?php if (isset($errors['full_name'])): ?>
                <div class="error-message">Допустимы только буквы, пробелы и дефисы (2-150 символов)</div>
                <?php endif; ?>
            </div>

            <!-- Поле Телефон -->
            <div class="form-group <?= isset($errors['phone']) ? 'error-field' : '' ?>">
                <label for="phone">Телефон*</label>
                <input type="tel" id="phone" name="phone" required
                       class="<?= isset($errors['phone']) ? 'error' : '' ?>"
                       value="<?= htmlspecialchars($oldInput['phone'] ?? '') ?>">
                <?php if (isset($errors['phone'])): ?>
                <div class="error-message">Введите 10-15 цифр, можно с + в начале</div>
                <?php endif; ?>
            </div>

            <!-- Поле Email -->
            <div class="form-group <?= isset($errors['email']) ? 'error-field' : '' ?>">
                <label for="email">Email*</label>
                <input type="email" id="email" name="email" required
                       class="<?= isset($errors['email']) ? 'error' : '' ?>"
                       value="<?= htmlspecialchars($oldInput['email'] ?? '') ?>">
                <?php if (isset($errors['email'])): ?>
                <div class="error-message">Введите корректный email (например, user@example.com)</div>
                <?php endif; ?>
            </div>

            <!-- Поле Дата рождения -->
            <div class="form-group <?= isset($errors['birth_date']) ? 'error-field' : '' ?>">
                <label for="birth_date">Дата рождения*</label>
                <input type="date" id="birth_date" name="birth_date" required
                       class="<?= isset($errors['birth_date']) ? 'error' : '' ?>"
                       value="<?= htmlspecialchars($oldInput['birth_date'] ?? '') ?>">
                <?php if (isset($errors['birth_date'])): ?>
                <div class="error-message">Дата должна быть в прошлом</div>
                <?php endif; ?>
            </div>

            <!-- Поле Пол -->
            <div class="form-group <?= isset($errors['gender']) ? 'error-field' : '' ?>">
                <label>Пол*</label>
                <div class="radio-group">
                    <div class="radio-option">
                        <input type="radio" id="male" name="gender" value="male" required
                               <?= ($oldInput['gender'] ?? '') === 'male' ? 'checked' : '' ?>
                               class="<?= isset($errors['gender']) ? 'error' : '' ?>">
                        <label for="male">Мужской</label>
                    </div>
                    <div class="radio-option">
                        <input type="radio" id="female" name="gender" value="female"
                               <?= ($oldInput['gender'] ?? '') === 'female' ? 'checked' : '' ?>
                               class="<?= isset($errors['gender']) ? 'error' : '' ?>">
                        <label for="female">Женский</label>
                    </div>
                </div>
                <?php if (isset($errors['gender'])): ?>
                <div class="error-message"><?= htmlspecialchars($errors['gender']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Поле Языки программирования -->
            <div class="form-group <?= isset($errors['languages']) ? 'error-field' : '' ?>">
                <label for="languages">Любимые языки программирования*</label>
                <select id="languages" name="languages[]" multiple required
                        class="<?= isset($errors['languages']) ? 'error' : '' ?>">
                    <?php
                    $allLanguages = [
                        1 => 'Pascal',
                        2 => 'C',
                        3 => 'C++',
                        4 => 'JavaScript',
                        5 => 'PHP',
                        6 => 'Python',
                        7 => 'Java',
                        8 => 'Haskell',
                        9 => 'Clojure',
                        10 => 'Prolog',
                        11 => 'Scala',
                        12 => 'Go'
                    ];
                    foreach ($allLanguages as $id => $name): ?>
                        <option value="<?= $id ?>" 
                            <?= isLanguageSelected($oldInput['languages'], $id) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['languages'])): ?>
                <div class="error-message">Выберите хотя бы один язык</div>
                <?php endif; ?>
            </div>

            <!-- Поле Биография -->
            <div class="form-group <?= isset($errors['biography']) ? 'error-field' : '' ?>">
                <label for="biography">Биография</label>
                <textarea id="biography" name="biography" rows="5"
                          class="<?= isset($errors['biography']) ? 'error' : '' ?>"><?= htmlspecialchars($oldInput['biography'] ?? '') ?></textarea>
                <?php if (isset($errors['biography'])): ?>
                <div class="error-message"><?= htmlspecialchars($errors['biography']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Чекбокс Согласие -->
            <div class="form-group <?= isset($errors['contract_agreed']) ? 'error-field' : '' ?>">
    <div style="display: flex; align-items: center; gap: 10px;">
        <input type="checkbox" id="contract_agreed" name="contract_agreed" required
               style="width: auto; margin: 0;"
               <?= ($oldInput['contract_agreed'] ?? false) ? 'checked' : '' ?>
               class="<?= isset($errors['contract_agreed']) ? 'error' : '' ?>">
        <label for="contract_agreed" style="margin-bottom: 0;">С контрактом ознакомлен(а)*</label>
    </div>
    <?php if (isset($errors['contract_agreed'])): ?>
    <div class="error-message">Необходимо подтвердить ознакомление</div>
    <?php endif; ?>
</div>
            <!-- Кнопка отправки -->
            <button type="submit">Сохранить</button>
        </form>
    </div>
</body>
</html>
