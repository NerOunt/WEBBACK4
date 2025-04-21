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
            border-color: red;
        }
        .error-message {
            color: red;
            font-size: 0.8em;
            margin-top: 5px;
        }
        .success {
            color: green;
            margin-bottom: 15px;
            padding: 10px;
            background: #f0fff0;
            border: 1px solid green;
            border-radius: 4px;
        }
        .radio-group {
            display: flex;
            gap: 15px;
        }
        .radio-option {
            display: flex;
            align-items: center;
        }
    
        .checkbox-container {
    display: flex;
    align-items: center;
    gap: 10px;
}
    </style>
</head>
<body>
    <div class="form-container">
        
        <?php if (!empty($messages)): ?>
            <?php foreach ($messages as $message): ?>
                <?= $message ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="full_name">ФИО*</label>
                <input type="text" id="full_name" name="full_name" 
                       class="<?= $errors['full_name'] ? 'error' : '' ?>" 
                       value="<?= htmlspecialchars($values['full_name']) ?>">
                <?php if ($errors['full_name']): ?>
                <div class="error-message">Допустимы только буквы, пробелы и дефисы (2-150 символов)</div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="phone">Телефон*</label>
                <input type="tel" id="phone" name="phone" 
                       class="<?= $errors['phone'] ? 'error' : '' ?>" 
                       value="<?= htmlspecialchars($values['phone']) ?>">
                <?php if ($errors['phone']): ?>
                <div class="error-message">Введите 10-15 цифр, можно с + в начале</div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="email">Email*</label>
                <input type="email" id="email" name="email" 
                       class="<?= $errors['email'] ? 'error' : '' ?>" 
                       value="<?= htmlspecialchars($values['email']) ?>">
                <?php if ($errors['email']): ?>
                <div class="error-message">Введите корректный email</div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="birth_date">Дата рождения*</label>
                <input type="date" id="birth_date" name="birth_date" 
                       class="<?= $errors['birth_date'] ? 'error' : '' ?>" 
                       value="<?= htmlspecialchars($values['birth_date']) ?>">
                <?php if ($errors['birth_date']): ?>
                <div class="error-message">Дата должна быть в прошлом</div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Пол*</label>
                <div class="radio-group">
                    <div class="radio-option">
                        <input type="radio" id="male" name="gender" value="male" 
                               <?= $values['gender'] === 'male' ? 'checked' : '' ?>
                               class="<?= $errors['gender'] ? 'error' : '' ?>">
                        <label for="male">Мужской</label>
                    </div>
                    <div class="radio-option">
                        <input type="radio" id="female" name="gender" value="female" 
                               <?= $values['gender'] === 'female' ? 'checked' : '' ?>
                               class="<?= $errors['gender'] ? 'error' : '' ?>">
                        <label for="female">Женский</label>
                    </div>
                </div>
                <?php if ($errors['gender']): ?>
                <div class="error-message">Укажите пол</div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="languages">Любимые языки программирования*</label>
                <select id="languages" name="languages[]" multiple 
                        class="<?= $errors['languages'] ? 'error' : '' ?>">
                    <?php
                    $allLanguages = [
                        1 => 'Pascal', 2 => 'C', 3 => 'C++', 4 => 'JavaScript',
                        5 => 'PHP', 6 => 'Python', 7 => 'Java', 8 => 'Haskell',
                        9 => 'Clojure', 10 => 'Prolog', 11 => 'Scala', 12 => 'Go'
                    ];
                    foreach ($allLanguages as $id => $name): ?>
                        <option value="<?= $id ?>" 
                            <?= in_array($id, $values['languages']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($errors['languages']): ?>
                <div class="error-message">Выберите хотя бы один язык</div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="biography">Биография</label>
                <textarea id="biography" name="biography"><?= htmlspecialchars($values['biography']) ?></textarea>
            </div>

           <div class="form-group">
    <div class="checkbox-container">
        <input type="checkbox" id="contract_agreed" name="contract_agreed" value="1"
               <?= $values['contract_agreed'] ? 'checked' : '' ?>
               class="<?= $errors['contract_agreed'] ? 'error' : '' ?>">
        <label for="contract_agreed">С контрактом ознакомлен(а)*</label>
    </div>
    <?php if ($errors['contract_agreed']): ?>
    <div class="error-message">Необходимо подтвердить ознакомление</div>
    <?php endif; ?>
    </div>
            
            <div class="form-group">
                <button type="submit">Отправить</button>
            </div>
        </form>
    </div>
</body>
</html>
