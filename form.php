<?php
// Получаем данные из cookies
$formData = [];
$errors = [];

if (isset($_COOKIE['form_errors']) {
    $errors = json_decode($_COOKIE['form_errors'], true);
}

if (isset($_COOKIE['form_data'])) {
    $formData = json_decode($_COOKIE['form_data'], true);
} else {
    // Проверяем сохраненные успешные данные
    foreach ($_COOKIE as $key => $value) {
        if ($key === 'languages') {
            $formData[$key] = json_decode($value, true);
        } elseif (!in_array($key, ['PHPSESSID', 'form_errors', 'form_data'])) {
            $formData[$key] = $value;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Форма анкеты</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="tel"],
        input[type="email"],
        input[type="date"],
        textarea,
        select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ccc;
        }
        .radio-group {
            display: flex;
            gap: 15px;
        }
        .error {
            color: red;
            font-size: 14px;
            margin-top: 5px;
        }
        .error-field {
            border-color: red !important;
        }
        .success {
            color: green;
            font-size: 16px;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f0fff0;
            border: 1px solid green;
        }
        button {
            background: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background: #45a049;
        }
    </style>
</head>
<body>
    <?php if (isset($_GET['success'])): ?>
        <div class="success">Данные успешно сохранены!</div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div style="color: red; margin-bottom: 20px;">
            <strong>Ошибки при заполнении формы:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="submit.php" method="post">
        <div class="form-group">
            <label for="full_name">ФИО*</label>
            <input type="text" id="full_name" name="full_name" required maxlength="150"
                   value="<?= htmlspecialchars($formData['full_name'] ?? '') ?>"
                   class="<?= isset($errors['full_name']) ? 'error-field' : '' ?>">
            <?php if (isset($errors['full_name'])): ?>
                <div class="error"><?= htmlspecialchars($errors['full_name']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="phone">Телефон*</label>
            <input type="tel" id="phone" name="phone" required
                   value="<?= htmlspecialchars($formData['phone'] ?? '') ?>"
                   class="<?= isset($errors['phone']) ? 'error-field' : '' ?>">
            <?php if (isset($errors['phone'])): ?>
                <div class="error"><?= htmlspecialchars($errors['phone']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="email">E-mail*</label>
            <input type="email" id="email" name="email" required
                   value="<?= htmlspecialchars($formData['email'] ?? '') ?>"
                   class="<?= isset($errors['email']) ? 'error-field' : '' ?>">
            <?php if (isset($errors['email'])): ?>
                <div class="error"><?= htmlspecialchars($errors['email']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="birth_date">Дата рождения*</label>
            <input type="date" id="birth_date" name="birth_date" required
                   value="<?= htmlspecialchars($formData['birth_date'] ?? '') ?>"
                   class="<?= isset($errors['birth_date']) ? 'error-field' : '' ?>">
            <?php if (isset($errors['birth_date'])): ?>
                <div class="error"><?= htmlspecialchars($errors['birth_date']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label>Пол*</label>
            <div class="radio-group">
                <label><input type="radio" name="gender" value="male" required
                    <?= isset($formData['gender']) && $formData['gender'] === 'male' ? 'checked' : '' ?>> Мужской</label>
                <label><input type="radio" name="gender" value="female"
                    <?= isset($formData['gender']) && $formData['gender'] === 'female' ? 'checked' : '' ?>> Женский</label>
            </div>
            <?php if (isset($errors['gender'])): ?>
                <div class="error"><?= htmlspecialchars($errors['gender']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="languages">Любимые языки программирования*</label>
            <select id="languages" name="languages[]" multiple required
                    class="<?= isset($errors['languages']) ? 'error-field' : '' ?>">
                <?php
                $languages = [
                    1 => 'Pascal', 2 => 'C', 3 => 'C++', 4 => 'JavaScript',
                    5 => 'PHP', 6 => 'Python', 7 => 'Java', 8 => 'Haskell',
                    9 => 'Clojure', 10 => 'Prolog', 11 => 'Scala', 12 => 'Go'
                ];
                
                foreach ($languages as $id => $name) {
                    $selected = isset($formData['languages']) && in_array($id, $formData['languages']) ? 'selected' : '';
                    echo "<option value=\"$id\" $selected>$name</option>";
                }
                ?>
            </select>
            <?php if (isset($errors['languages'])): ?>
                <div class="error"><?= htmlspecialchars($errors['languages']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="biography">Биография</label>
            <textarea id="biography" name="biography" rows="5"><?= htmlspecialchars($formData['biography'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="contract_agreed" required
                    <?= isset($formData['contract_agreed']) ? 'checked' : '' ?>>
                С контрактом ознакомлен(а)*
            </label>
            <?php if (isset($errors['contract_agreed'])): ?>
                <div class="error"><?= htmlspecialchars($errors['contract_agreed']) ?></div>
            <?php endif; ?>
        </div>

        <button type="submit">Сохранить</button>
    </form>
</body>
</html>