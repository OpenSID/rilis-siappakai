<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\Helpers\EnvController;
use App\Http\Controllers\Helpers\ConfigController;
use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use Illuminate\Filesystem\Filesystem;

class CustomDatabasePortTest extends TestCase
{
    private $files;
    private $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->files = new Filesystem();
        $this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'siappakai_test_' . uniqid();
        $this->files->makeDirectory($this->tempDir);
    }

    protected function tearDown(): void
    {
        if ($this->files->exists($this->tempDir)) {
            $this->files->deleteDirectory($this->tempDir);
        }
        parent::tearDown();
    }

    public function test_env_controller_includes_custom_database_port_in_pbb_template()
    {
        // Arrange
        $envController = new EnvController();
        $templateFile = $this->tempDir . DIRECTORY_SEPARATOR . '.env.example';
        $outputFile = $this->tempDir . DIRECTORY_SEPARATOR . '.env';
        
        // Create a template file with {$db_port} placeholder
        $templateContent = "DB_HOST={$db_host}\nDB_PORT={$db_port}\nDB_DATABASE=db_{$kodedesa}_pbb";
        $this->files->put($templateFile, $templateContent);
        
        // Set custom port in environment
        putenv('DB_PORT=3307');
        
        // Act
        $envController->envPbb(
            'localhost',
            dirname($templateFile),
            'https://server.test',
            dirname($outputFile),
            '12345',
            '12345',
            'https://asset.test',
            'test_token'
        );
        
        // Assert
        $this->assertTrue($this->files->exists($outputFile));
        $content = $this->files->get($outputFile);
        $this->assertStringContains('DB_PORT=3307', $content);
        $this->assertStringNotContains('{$db_port}', $content);
        
        // Cleanup
        putenv('DB_PORT');
    }

    public function test_env_controller_includes_custom_database_port_in_api_template()
    {
        // Arrange
        $envController = new EnvController();
        $templateFile = $this->tempDir . DIRECTORY_SEPARATOR . '.env.example';
        $outputFile = $this->tempDir . DIRECTORY_SEPARATOR . '.env';
        
        // Create a template file with {$db_port} placeholder
        $templateContent = "DB_HOST={$db_host}\nDB_PORT={$db_port}\nDB_DATABASE=db_{$kodedesa}";
        $this->files->put($templateFile, $templateContent);
        
        // Set custom port in environment
        putenv('DB_PORT=3308');
        
        // Act
        $envController->envApi(
            'localhost',
            dirname($templateFile),
            'https://server.test',
            dirname($outputFile),
            '12345',
            '12345',
            'test_token',
            'mail.test',
            'user@test.com',
            'password',
            'from@test.com',
            'ftp.test',
            'ftpuser',
            'ftppass'
        );
        
        // Assert
        $this->assertTrue($this->files->exists($outputFile));
        $content = $this->files->get($outputFile);
        $this->assertStringContains('DB_PORT=3308', $content);
        $this->assertStringNotContains('{$db_port}', $content);
        
        // Cleanup
        putenv('DB_PORT');
    }

    public function test_config_controller_includes_custom_database_port_in_opensid_config()
    {
        // Arrange
        $configController = new ConfigController();
        $templateFile = $this->tempDir . DIRECTORY_SEPARATOR . 'database.php';
        $outputFile = $this->tempDir . DIRECTORY_SEPARATOR . 'output_database.php';
        
        // Create a template file with {$db_port} placeholder
        $templateContent = "\$db['default']['hostname'] = '{$db_host}';\n\$db['default']['port'] = '{$db_port}';\n\$db['default']['database'] = 'db_{$database}';";
        $this->files->put($templateFile, $templateContent);
        
        // Set custom port in environment
        putenv('DB_PORT=3309');
        
        // Act
        $result = $configController->configDatabaseBaru(
            'testdesa',
            'testdatabase',
            'localhost',
            $outputFile,
            $templateFile
        );
        
        // Assert
        $this->assertTrue($this->files->exists($outputFile));
        $content = $this->files->get($outputFile);
        $this->assertStringContains("'port'] = '3309'", $content);
        $this->assertStringNotContains('{$db_port}', $content);
        
        // Cleanup
        putenv('DB_PORT');
    }

    public function test_attribute_controller_respects_db_port_environment_variable()
    {
        // Arrange
        putenv('DB_PORT=3310');
        $attributeController = new AttributeSiapPakaiController();
        
        // Act
        $port = $attributeController->getPort();
        
        // Assert
        $this->assertEquals('3310', $port);
        
        // Cleanup
        putenv('DB_PORT');
    }

    public function test_attribute_controller_uses_default_port_when_env_not_set()
    {
        // Arrange
        putenv('DB_PORT'); // Clear the environment variable
        $attributeController = new AttributeSiapPakaiController();
        
        // Act
        $port = $attributeController->getPort();
        
        // Assert
        $this->assertNull($port); // Should be null when env is not set
    }
}