<?php
namespace App\Contracts;

interface VersionCheckerInterface
{
    public function getCurrentVersion(string $moduleName): string;
    public function getModuleVersion(): array;
}