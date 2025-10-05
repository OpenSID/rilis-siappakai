<?php

namespace App\Services;

use App\Contracts\DomainValidationRule;

class DomainValidator
{
    protected array $rules = [];

    public function addRule(DomainValidationRule $rule): void
    {
        $this->rules[] = $rule;
    }

    public function validate(string $domain): bool
    {
        foreach ($this->rules as $rule) {
            if (!$rule->validate($domain)) {
                return false;
            }
        }
        return true;
    }
}
