<?php
namespace App\Contracts;

interface FileDownloaderInterface
{
    public function download(string $url, string $destination, $filename): void;
}