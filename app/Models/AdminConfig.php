<?php

namespace App\Models;

use App\Core\Model;

/**
 * Model: AdminConfig (admin_configs)
 */
class AdminConfig extends Model
{
    protected string $table = 'admin_configs';
    
    protected array $fillable = [
        'config_key',
        'config_value',
        'config_type',
        'description',
        'is_sensitive'
    ];

    /**
     * Buscar configuração por chave
     */
    public function get(string $key, $default = null)
    {
        $sql = "SELECT * FROM {$this->table} WHERE config_key = :key LIMIT 1";
        $result = $this->query($sql, ['key' => $key]);
        
        if (empty($result)) {
            return $default;
        }
        
        $config = $result[0];
        return $this->castValue($config['config_value'], $config['config_type']);
    }

    /**
     * Definir configuração
     */
    public function set(string $key, $value, string $type = 'string', string $description = null): bool
    {
        $existing = $this->firstWhere('config_key', $key);
        
        if ($existing) {
            return $this->update($existing['id'], [
                'config_value' => $value,
                'config_type' => $type
            ]);
        }
        
        return $this->create([
            'config_key' => $key,
            'config_value' => $value,
            'config_type' => $type,
            'description' => $description
        ]) !== null;
    }

    /**
     * Buscar todas as configurações
     */
    public function getAll(bool $hideSensitive = true): array
    {
        $configs = $this->all();
        
        $result = [];
        foreach ($configs as $config) {
            if ($hideSensitive && $config['is_sensitive']) {
                $config['config_value'] = '********';
            } else {
                $config['config_value'] = $this->castValue($config['config_value'], $config['config_type']);
            }
            $result[$config['config_key']] = $config;
        }
        
        return $result;
    }

    /**
     * Converter valor para o tipo correto
     */
    protected function castValue($value, string $type)
    {
        switch ($type) {
            case 'number':
                return is_numeric($value) ? (float) $value : 0;
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    /**
     * Buscar configurações de API
     */
    public function getApiKeys(): array
    {
        return [
            'openai_api_key' => $this->get('openai_api_key'),
            'assas_api_key' => $this->get('assas_api_key'),
            'assas_environment' => $this->get('assas_environment', 'sandbox'),
            'recaptcha_site_key' => $this->get('recaptcha_site_key'),
            'recaptcha_secret_key' => $this->get('recaptcha_secret_key'),
        ];
    }

    /**
     * Buscar configurações de pagamento
     */
    public function getPaymentConfig(): array
    {
        return [
            'registration_fee' => $this->get('registration_fee', 29.90),
            'monthly_fee' => $this->get('monthly_fee', 29.90),
            'playbook_fee' => $this->get('playbook_fee', 19.90),
            'freelancer_fee' => $this->get('freelancer_fee', 0.07),
        ];
    }
}
