<?php
/**
 * BabeStand - Classe Validator
 * Validação de dados de entrada
 */

namespace App;

class Validator
{
    private array $data;
    private array $errors = [];
    private array $rules;

    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
    }

    /**
     * Executa a validação
     */
    public function validate(): bool
    {
        foreach ($this->rules as $field => $rules) {
            $rulesList = is_string($rules) ? explode('|', $rules) : $rules;
            
            foreach ($rulesList as $rule) {
                $this->applyRule($field, $rule);
            }
        }

        return empty($this->errors);
    }

    /**
     * Aplica uma regra de validação
     */
    private function applyRule(string $field, string $rule): void
    {
        // Separar regra dos parâmetros
        $parts = explode(':', $rule, 2);
        $ruleName = $parts[0];
        $params = isset($parts[1]) ? explode(',', $parts[1]) : [];

        $value = $this->data[$field] ?? null;

        // Não validar campos vazios exceto 'required'
        if ($ruleName !== 'required' && ($value === null || $value === '')) {
            return;
        }

        $method = 'validate' . ucfirst($ruleName);
        
        if (method_exists($this, $method)) {
            $this->$method($field, $value, $params);
        }
    }

    /**
     * Campo obrigatório
     */
    private function validateRequired(string $field, mixed $value): void
    {
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            $this->addError($field, 'O campo %s é obrigatório.');
        }
    }

    /**
     * Email válido
     */
    private function validateEmail(string $field, mixed $value): void
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, 'O campo %s deve ser um email válido.');
        }
    }

    /**
     * Tamanho mínimo
     */
    private function validateMin(string $field, mixed $value, array $params): void
    {
        $min = (int) $params[0];
        if (strlen($value) < $min) {
            $this->addError($field, "O campo %s deve ter pelo menos {$min} caracteres.");
        }
    }

    /**
     * Tamanho máximo
     */
    private function validateMax(string $field, mixed $value, array $params): void
    {
        $max = (int) $params[0];
        if (strlen($value) > $max) {
            $this->addError($field, "O campo %s não pode ter mais de {$max} caracteres.");
        }
    }

    /**
     * Valor numérico
     */
    private function validateNumeric(string $field, mixed $value): void
    {
        if (!is_numeric($value)) {
            $this->addError($field, 'O campo %s deve ser numérico.');
        }
    }

    /**
     * Valor inteiro
     */
    private function validateInteger(string $field, mixed $value): void
    {
        if (!filter_var($value, FILTER_VALIDATE_INT)) {
            $this->addError($field, 'O campo %s deve ser um número inteiro.');
        }
    }

    /**
     * Confirmação de campo (ex: password_confirmation)
     */
    private function validateConfirmed(string $field, mixed $value): void
    {
        $confirmField = $field . '_confirmation';
        $confirmValue = $this->data[$confirmField] ?? null;
        
        if ($value !== $confirmValue) {
            $this->addError($field, 'A confirmação do campo %s não corresponde.');
        }
    }

    /**
     * Força da password
     */
    private function validatePassword(string $field, mixed $value): void
    {
        $errors = [];

        if (!preg_match('/[A-Z]/', $value)) {
            $errors[] = 'uma letra maiúscula';
        }
        if (!preg_match('/[a-z]/', $value)) {
            $errors[] = 'uma letra minúscula';
        }
        if (!preg_match('/[0-9]/', $value)) {
            $errors[] = 'um número';
        }
        if (!preg_match('/[^A-Za-z0-9]/', $value)) {
            $errors[] = 'um caractere especial';
        }

        if (!empty($errors)) {
            $this->addError($field, 'A password deve conter: ' . implode(', ', $errors) . '.');
        }
    }

    /**
     * Telefone português
     */
    private function validatePhone(string $field, mixed $value): void
    {
        // Remove espaços e outros caracteres
        $phone = preg_replace('/[\s\-\(\)]/', '', $value);
        
        // Verificar se tem 9 dígitos
        if (strlen($phone) !== 9) {
            $this->addError($field, 'O campo %s deve ter 9 dígitos.');
            return;
        }
        
        // Verificar se são apenas números
        if (!ctype_digit($phone)) {
            $this->addError($field, 'O campo %s deve conter apenas números.');
            return;
        }
        
        // Verificar prefixos portugueses válidos
        // Móveis: 91, 92, 93, 96
        // Fixos: 2xx
        $validPrefixes = ['91', '92', '93', '96', '21', '22', '23', '24', '25', '26', '27', '28', '29'];
        $prefix = substr($phone, 0, 2);
        
        if (!in_array($prefix, $validPrefixes)) {
            $this->addError($field, 'O campo %s deve ser um número português válido (começar por 91, 92, 93, 96 ou 2X).');
        }
    }

    /**
     * Telefone único na base de dados
     */
    private function validatePhoneUnique(string $field, mixed $value): void
    {
        $phone = preg_replace('/[\s\-\(\)]/', '', $value);
        
        $db = Database::getInstance();
        $result = $db->fetch(
            "SELECT COUNT(*) as count FROM users WHERE REPLACE(REPLACE(phone, ' ', ''), '-', '') = ?",
            [$phone]
        );
        
        if ($result['count'] > 0) {
            $this->addError($field, 'Este número de telefone já está registado.');
        }
    }

    /**
     * NIF português
     */
    private function validateNif(string $field, mixed $value): void
    {
        $nif = preg_replace('/\D/', '', $value);
        
        if (strlen($nif) !== 9) {
            $this->addError($field, 'O NIF deve ter 9 dígitos.');
            return;
        }

        // Validar primeiro dígito
        $firstDigit = (int) $nif[0];
        if (!in_array($firstDigit, [1, 2, 3, 5, 6, 7, 8, 9])) {
            $this->addError($field, 'O NIF é inválido.');
            return;
        }

        // Validar check digit
        $sum = 0;
        for ($i = 0; $i < 8; $i++) {
            $sum += (int) $nif[$i] * (9 - $i);
        }
        
        $checkDigit = 11 - ($sum % 11);
        if ($checkDigit >= 10) {
            $checkDigit = 0;
        }

        if ($checkDigit !== (int) $nif[8]) {
            $this->addError($field, 'O NIF é inválido.');
        }
    }

    /**
     * Ano válido
     */
    private function validateYear(string $field, mixed $value, array $params = []): void
    {
        $year = (int) $value;
        $currentYear = (int) date('Y');
        
        if ($year < 1900 || $year > $currentYear + 1) {
            $this->addError($field, 'O campo %s deve ser um ano válido.');
        }
    }

    /**
     * Data válida
     */
    private function validateDate(string $field, mixed $value): void
    {
        $date = \DateTime::createFromFormat('Y-m-d', $value);
        if (!$date || $date->format('Y-m-d') !== $value) {
            $this->addError($field, 'O campo %s deve ser uma data válida.');
        }
    }

    /**
     * Data futura
     */
    private function validateAfterToday(string $field, mixed $value): void
    {
        $date = \DateTime::createFromFormat('Y-m-d', $value);
        $today = new \DateTime('today');
        
        if (!$date || $date <= $today) {
            $this->addError($field, 'O campo %s deve ser uma data futura.');
        }
    }

    /**
     * Matrícula portuguesa
     */
    private function validateLicensePlate(string $field, mixed $value): void
    {
        $plate = strtoupper(str_replace(['-', ' '], '', $value));
        
        // Formatos: AA-00-00, 00-00-AA, 00-AA-00, AA-00-AA
        $patterns = [
            '/^[A-Z]{2}\d{4}$/',      // AA-00-00 (antigo)
            '/^\d{4}[A-Z]{2}$/',      // 00-00-AA (antigo)
            '/^\d{2}[A-Z]{2}\d{2}$/', // 00-AA-00 (antigo)
            '/^[A-Z]{2}\d{2}[A-Z]{2}$/' // AA-00-AA (novo)
        ];

        $valid = false;
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $plate)) {
                $valid = true;
                break;
            }
        }

        if (!$valid) {
            $this->addError($field, 'O campo %s deve ser uma matrícula válida.');
        }
    }

    /**
     * Tabelas permitidas para validacao unique/exists (prevenir SQL Injection)
     */
    private const ALLOWED_TABLES = [
        'users', 'vehicles', 'brands', 'fuel_types', 'vehicle_status',
        'test_drives', 'test_drive_status', 'reviews', 'favorites',
        'contact_messages', 'settings', 'roles'
    ];

    /**
     * Colunas permitidas para validacao unique/exists (prevenir SQL Injection)
     */
    private const ALLOWED_COLUMNS = [
        'id', 'email', 'phone', 'nif', 'name', 'slug', 'license_plate',
        'vin', 'model', 'brand_id', 'user_id', 'vehicle_id', 'status_id'
    ];

    /**
     * Valor único na base de dados
     */
    private function validateUnique(string $field, mixed $value, array $params): void
    {
        $table = $params[0];
        $column = $params[1] ?? $field;
        $exceptId = $params[2] ?? null;

        // Validar tabela contra whitelist (prevenir SQL Injection)
        if (!in_array($table, self::ALLOWED_TABLES, true)) {
            throw new \InvalidArgumentException("Tabela nao permitida para validacao: {$table}");
        }

        // Validar coluna contra whitelist (prevenir SQL Injection)
        if (!in_array($column, self::ALLOWED_COLUMNS, true)) {
            throw new \InvalidArgumentException("Coluna nao permitida para validacao: {$column}");
        }

        $db = Database::getInstance();

        // Usar backticks para identificadores (seguranca adicional)
        $sql = "SELECT COUNT(*) as count FROM `{$table}` WHERE `{$column}` = ?";
        $bindings = [$value];

        if ($exceptId) {
            $exceptId = (int) $exceptId; // Garantir que e inteiro
            $sql .= " AND `id` != ?";
            $bindings[] = $exceptId;
        }

        $result = $db->fetch($sql, $bindings);

        if ($result['count'] > 0) {
            $this->addError($field, 'Este %s já está em uso.');
        }
    }

    /**
     * Valor existe na base de dados
     */
    private function validateExists(string $field, mixed $value, array $params): void
    {
        $table = $params[0];
        $column = $params[1] ?? 'id';

        // Validar tabela contra whitelist (prevenir SQL Injection)
        if (!in_array($table, self::ALLOWED_TABLES, true)) {
            throw new \InvalidArgumentException("Tabela nao permitida para validacao: {$table}");
        }

        // Validar coluna contra whitelist (prevenir SQL Injection)
        if (!in_array($column, self::ALLOWED_COLUMNS, true)) {
            throw new \InvalidArgumentException("Coluna nao permitida para validacao: {$column}");
        }

        $db = Database::getInstance();
        $result = $db->fetch(
            "SELECT COUNT(*) as count FROM `{$table}` WHERE `{$column}` = ?",
            [$value]
        );

        if ($result['count'] === 0) {
            $this->addError($field, 'O valor selecionado para %s é inválido.');
        }
    }

    /**
     * Valor em lista permitida
     */
    private function validateIn(string $field, mixed $value, array $params): void
    {
        if (!in_array($value, $params)) {
            $this->addError($field, 'O valor selecionado para %s é inválido.');
        }
    }

    /**
     * Adiciona um erro
     */
    private function addError(string $field, string $message): void
    {
        $label = $this->getFieldLabel($field);
        $this->errors[$field][] = sprintf($message, $label);
    }

    /**
     * Obtém label do campo
     */
    private function getFieldLabel(string $field): string
    {
        $labels = [
            'name' => 'nome',
            'email' => 'email',
            'password' => 'password',
            'phone' => 'telefone',
            'address' => 'morada',
            'nif' => 'NIF',
            'brand_id' => 'marca',
            'model' => 'modelo',
            'year' => 'ano',
            'price' => 'preço',
            'mileage' => 'quilómetros',
            'color' => 'cor',
            'scheduled_date' => 'data',
            'scheduled_time' => 'hora',
            'subject' => 'assunto',
            'message' => 'mensagem'
        ];

        return $labels[$field] ?? $field;
    }

    /**
     * Verifica se tem erros
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Obtém todos os erros
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Obtém erros de um campo
     */
    public function getFieldErrors(string $field): array
    {
        return $this->errors[$field] ?? [];
    }

    /**
     * Obtém primeiro erro de um campo
     */
    public function getFirstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Obtém primeiro erro global
     */
    public function getFirstErrorMessage(): ?string
    {
        foreach ($this->errors as $fieldErrors) {
            return $fieldErrors[0] ?? null;
        }
        return null;
    }

    /**
     * Obtém dados validados
     */
    public function getValidData(): array
    {
        return array_intersect_key($this->data, $this->rules);
    }
}
