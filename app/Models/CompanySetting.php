<?php

namespace App\Models;

use App\Core\Model;

class CompanySetting extends Model
{
    protected string $table = 'company_settings';
    protected array $fillable = ['company_id','setting_key','setting_value'];

    public function get(int $companyId, string $key, $default = null)
    {
        $sql = "SELECT setting_value FROM {$this->table} WHERE company_id = :cid AND setting_key = :k LIMIT 1";
        $row = $this->query($sql, ['cid' => $companyId, 'k' => $key]);
        if (!empty($row[0]['setting_value'])) {
            return $row[0]['setting_value'];
        }
        return $default;
    }

    public function set(int $companyId, string $key, $value): bool
    {
        $exists = $this->query("SELECT id FROM {$this->table} WHERE company_id = :cid AND setting_key = :k LIMIT 1", ['cid' => $companyId, 'k' => $key]);
        if ($exists) {
            return $this->execute("UPDATE {$this->table} SET setting_value = :v WHERE id = :id", ['v' => (string)$value, 'id' => $exists[0]['id']]);
        }
        return (bool) $this->create([
            'company_id' => $companyId,
            'setting_key' => $key,
            'setting_value' => (string)$value,
        ]);
    }
}
