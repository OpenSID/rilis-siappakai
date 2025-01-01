<?php 
namespace App\Validations;

use App\Contracts\DomainValidationRule;

class FormatValidationRule implements DomainValidationRule
{
    public function validate(string $domain): bool
    {
        return preg_match('/^(?!:\/\/)([a-zA-Z0-9-_]+\.)?[a-zA-Z0-9-_]+\.[a-zA-Z]{2,}$/', $domain);
    }
}

class ResolvableValidationRule implements DomainValidationRule
{
    public function validate(string $domain): bool
    {
        return checkdnsrr($domain, 'A') || checkdnsrr($domain, 'CNAME');
    }
}

