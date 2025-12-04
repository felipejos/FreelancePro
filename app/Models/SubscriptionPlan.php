<?php

namespace App\Models;

use App\Core\Model;

/**
 * Model: SubscriptionPlan (subscription_plans)
 */
class SubscriptionPlan extends Model
{
    protected string $table = 'subscription_plans';
    
    protected array $fillable = [
        'name',
        'description',
        'price',
        'billing_cycle',
        'features',
        'max_employees',
        'max_playbooks',
        'max_courses',
        'is_active'
    ];

    /**
     * Buscar planos ativos
     */
    public function getActive(): array
    {
        return $this->where('is_active', 1);
    }

    /**
     * Decodificar features JSON
     */
    public function getWithDecodedFeatures(int $planId): ?array
    {
        $plan = $this->find($planId);
        
        if ($plan && isset($plan['features'])) {
            $plan['features'] = json_decode($plan['features'], true);
        }
        
        return $plan;
    }

    /**
     * Buscar todos os planos ativos com features decodificadas
     */
    public function getAllActiveWithFeatures(): array
    {
        $plans = $this->getActive();
        
        foreach ($plans as &$plan) {
            if (isset($plan['features'])) {
                $plan['features'] = json_decode($plan['features'], true);
            }
        }
        
        return $plans;
    }
}
