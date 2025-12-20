<?php

/**
 * Validation class for form data and files
 * 
 * @package App\Includes\Services
 */
class Validator {
    private array $errors = [];
    private array $data = [];
    private array $files = [];

    /**
     * Constructor
     * 
     * @param array $data Form data to validate
     * @param array $files Uploaded files to validate
     */
    public function __construct(array $data = [], array $files = []) {
        $this->data = $data;
        $this->files = $files;
    }

    /**
     * Validate data against rules
     * 
     * @param array $rules Validation rules in format ['field' => [['rule'], ['rule', param1, param2]]]
     * @return array Validation errors in format ['field' => ['error1', 'error2']]
     */
    public function validate(array $rules): array {
        $this->errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $this->data[$field] ?? null;
            $file = $this->files[$field] ?? null;

            foreach ($fieldRules as $rule) {
                if (is_string($rule)) {
                    $this->applyRule($field, $value, $file, $rule);
                } elseif (is_array($rule)) {
                    $this->applyRule($field, $value, $file, $rule[0], array_slice($rule, 1));
                }
            }
        }

        return $this->errors;
    }

    /**
     * Apply a single validation rule to a field
     * 
     * @param string $field Field name
     * @param mixed $value Field value
     * @param array|null $file Uploaded file data or null
     * @param string $rule Rule name
     * @param array $params Rule parameters
     * @return void
     */
    private function applyRule(string $field, $value, $file, string $rule, array $params = []): void {
        switch ($rule) {
            case 'required':
                if ($file !== null) {
                    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
                        $this->addError($field, 'Toto pole je povinné.');
                    }
                } elseif (empty($value)) {
                    $this->addError($field, 'Toto pole je povinné.');
                }
                break;

            case 'string':
                if (!empty($value) && !is_string($value)) {
                    $this->addError($field, 'Hodnota musí být text.');
                }
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, 'Neplatný formát emailu.');
                }
                break;

            case 'min':
                $min = $params[0] ?? 0;
                if (!empty($value) && strlen($value) < $min) {
                    $this->addError($field, "Hodnota musí mít alespoň {$min} znaků.");
                }
                break;

            case 'max':
                $max = $params[0] ?? 0;
                if (!empty($value) && strlen($value) > $max) {
                    $this->addError($field, "Hodnota nesmí mít více než {$max} znaků.");
                }
                break;

            case 'username':
                if (!empty($value)) {
                    if (!preg_match('/^(?![._-])[A-Za-z0-9._-]+(?<![._-])$/', $value)) {
                        $this->addError($field, 'Uživatelské jméno nesmí začínat ani končit tečkou, pomlčkou nebo podtržítkem a nesmí obsahovat jiné specialní znaky.');
                    } elseif (strlen($value) < 3) {
                        $this->addError($field, 'Uživatelské jméno musí mít alespoň 3 znaky.');
                    }
                }
                break;

            case 'password':
                if (!empty($value)) {
                    if (strlen($value) < 8) {
                        $this->addError($field, 'Heslo musí mít alespoň 8 znaků.');
                    }
                    if (!preg_match('/[A-Z]/', $value)) {
                        $this->addError($field, 'Heslo musí obsahovat alespoň jedno velké písmeno.');
                    }
                    if (!preg_match('/\d/', $value)) {
                        $this->addError($field, 'Heslo musí obsahovat alespoň jedno číslo.');
                    }
                    if (!preg_match('/[^A-Za-z0-9]/', $value)) {
                        $this->addError($field, 'Heslo musí obsahovat alespoň jeden speciální znak.');
                    }
                }
                break;

            case 'confirmed':
                // For password_confirmation field, check against password field
                if ($field === 'password_confirmation') {
                    $originalValue = $this->data['password'] ?? null;
                    if (!empty($value) && !empty($originalValue) && $value !== $originalValue) {
                        $this->addError($field, 'Hesla se neshodují.');
                    }
                } else {
                    // For other fields, use the parameter or default to field_confirmation
                    $confirmField = $params[0] ?? $field . '_confirmation';
                    $confirmValue = $this->data[$confirmField] ?? null;
                    if (!empty($value) && $value !== $confirmValue) {
                        $this->addError($field, 'Hodnoty se neshodují.');
                    }
                }
                break;

            case 'image':
                // Only validate if file was uploaded (not required by default)
                if ($file !== null && isset($file['error']) && $file['error'] === UPLOAD_ERR_OK) {
                    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                    $mime = mime_content_type($file['tmp_name']);
                    if (!in_array($mime, $allowed)) {
                        $this->addError($field, 'Podporované formáty: JPG, PNG, WEBP, GIF.');
                    }
                } elseif ($file !== null && isset($file['error']) && $file['error'] !== UPLOAD_ERR_NO_FILE) {
                    // If file was attempted but failed, show error
                    $this->addError($field, 'Chyba při nahrávání souboru.');
                }
                break;

            case 'image_max_size':
                $maxSize = $params[0] ?? 5242880; // 5MB default
                if ($file !== null && isset($file['error']) && $file['error'] === UPLOAD_ERR_OK) {
                    if ($file['size'] > $maxSize) {
                        $maxSizeMB = round($maxSize / 1048576, 1);
                        $this->addError($field, "Obrázek nesmí být větší než {$maxSizeMB} MB.");
                    }
                }
                break;

            case 'email_part_min':
                $min = $params[0] ?? 4;
                if (!empty($value) && filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    [$emailPart] = explode('@', $value);
                    if (strlen($emailPart) < $min) {
                        $this->addError($field, "Část před @ musí mít alespoň {$min} znaky.");
                    }
                }
                break;

            case 'identifier':
                // For login - just check it's not empty, existence check is in model
                // No format validation needed - user can enter username or email
                if (empty($value)) {
                    $this->addError($field, 'Toto pole je povinné.');
                }
                break;

            case 'year':
                $currentYear = (int)date('Y');
                $minYear = $params[0] ?? 1980;
                $maxYear = $params[1] ?? $currentYear;
                if (!empty($value)) {
                    $year = (int)$value;
                    if ($year < $minYear || $year > $maxYear) {
                        $this->addError($field, "Rok musí být mezi {$minYear} a {$maxYear}.");
                    }
                }
                break;

            case 'array_not_empty':
                if (!is_array($value) || empty($value)) {
                    $this->addError($field, 'Vyberte alespoň jednu možnost.');
                }
                break;

            case 'rating':
                $min = $params[0] ?? 1;
                $max = $params[1] ?? 10;
                if (!empty($value)) {
                    $rating = (int)$value;
                    if ($rating < $min || $rating > $max) {
                        $this->addError($field, "Hodnocení musí být mezi {$min} a {$max}.");
                    }
                }
                break;
        }
    }

    /**
     * Add an error message to a field
     * 
     * @param string $field Field name
     * @param string $message Error message
     * @return void
     */
    private function addError(string $field, string $message): void {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

}

