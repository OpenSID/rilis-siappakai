<?php 
namespace App\Contracts;

interface FileExtractorInterface
{
    public function extract(string $filePath, string $destination): void;
}