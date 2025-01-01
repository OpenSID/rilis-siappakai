<?php
namespace App\Contracts;

interface DomainValidationRule
{
    public function validate(string $domain): bool;
}
