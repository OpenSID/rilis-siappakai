<?php

namespace App\Services;

use Symfony\Component\Process\Process;

final class ProcessService
{
    public static function runProcess(array $command, $folder, $message = null)
    {
        echo "$message";
        $process = new Process($command, $folder);
        $process->setTimeout(null);
        $process->run(function ($type, $buffer)  use ($command) {
            if (Process::ERR === $type) {
                echo "Kesalahan Proses: $buffer\n";
                echo "Command :";
                print_r($command);
                echo "\n";
            } else {
                echo "Output Proses: $buffer\n";
            }
        });
    }
}
