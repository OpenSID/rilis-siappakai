<?php

namespace App\Contracts;

interface ProcessServiceInterface
{
    public function run(string $command): array;
}