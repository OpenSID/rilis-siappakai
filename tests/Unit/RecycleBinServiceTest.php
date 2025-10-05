<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\RecycleBinService;
use App\Services\FileService;
use Illuminate\Support\Facades\File;

class RecycleBinServiceTest extends TestCase
{
    protected $testDir;
    protected $recycleBinService;
    protected $fileService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test directory
        $this->testDir = sys_get_temp_dir() . '/recycle_bin_test_' . time();
        if (!file_exists($this->testDir)) {
            mkdir($this->testDir, 0755, true);
        }
        
        // Mock storage_path to use our test directory
        $this->recycleBinService = new class($this->testDir) extends RecycleBinService {
            private $testPath;
            
            public function __construct($testPath)
            {
                $this->testPath = $testPath;
                $this->recycleBinPath = $testPath . '/recycleBin';
                $this->ensureRecycleBinExists();
            }
        };
        
        $this->fileService = new FileService();
    }

    protected function tearDown(): void
    {
        // Clean up test directory
        if (file_exists($this->testDir)) {
            $this->removeDirectory($this->testDir);
        }
        
        parent::tearDown();
    }

    private function removeDirectory($path)
    {
        if (!is_dir($path)) {
            return;
        }
        
        $files = array_diff(scandir($path), array('.', '..'));
        foreach ($files as $file) {
            $filePath = $path . '/' . $file;
            if (is_dir($filePath)) {
                $this->removeDirectory($filePath);
            } else {
                unlink($filePath);
            }
        }
        rmdir($path);
    }

    public function test_recycle_bin_service_exists()
    {
        $this->assertInstanceOf(RecycleBinService::class, $this->recycleBinService);
    }

    public function test_file_service_has_recycle_bin_methods()
    {
        $this->assertTrue(method_exists($this->fileService, 'deleteToRecycleBin'));
        $this->assertTrue(method_exists($this->fileService, 'deletePermanent'));
    }

    public function test_recycle_bin_directory_is_created()
    {
        $recycleBinPath = $this->testDir . '/recycleBin';
        $this->assertTrue(file_exists($recycleBinPath));
        $this->assertTrue(is_dir($recycleBinPath));
    }

    public function test_can_move_file_to_recycle_bin()
    {
        // Create a test file
        $testFile = $this->testDir . '/test_file.txt';
        file_put_contents($testFile, 'Test content');
        
        $this->assertTrue(file_exists($testFile));
        
        // Move to recycle bin
        $recycleBinPath = $this->recycleBinService->moveToRecycleBin($testFile);
        
        // Original file should be deleted
        $this->assertFalse(file_exists($testFile));
        
        // File should exist in recycle bin
        $this->assertTrue(file_exists($recycleBinPath));
        
        // Content should be preserved
        $this->assertEquals('Test content', file_get_contents($recycleBinPath));
        
        // Metadata file should exist
        $this->assertTrue(file_exists($recycleBinPath . '.meta'));
    }

    public function test_can_move_directory_to_recycle_bin()
    {
        // Create test directory with content
        $testDir = $this->testDir . '/test_directory';
        mkdir($testDir, 0755, true);
        file_put_contents($testDir . '/file1.txt', 'Content 1');
        file_put_contents($testDir . '/file2.txt', 'Content 2');
        
        $this->assertTrue(file_exists($testDir));
        
        // Move to recycle bin
        $recycleBinPath = $this->recycleBinService->moveToRecycleBin($testDir);
        
        // Original directory should be deleted
        $this->assertFalse(file_exists($testDir));
        
        // Directory should exist in recycle bin
        $this->assertTrue(file_exists($recycleBinPath));
        $this->assertTrue(is_dir($recycleBinPath));
        
        // Content should be preserved
        $this->assertTrue(file_exists($recycleBinPath . '/file1.txt'));
        $this->assertTrue(file_exists($recycleBinPath . '/file2.txt'));
        $this->assertEquals('Content 1', file_get_contents($recycleBinPath . '/file1.txt'));
        $this->assertEquals('Content 2', file_get_contents($recycleBinPath . '/file2.txt'));
        
        // Metadata file should exist
        $this->assertTrue(file_exists($recycleBinPath . '.meta'));
    }

    public function test_recycle_bin_info_returns_correct_data()
    {
        // Create test file and move to recycle bin
        $testFile = $this->testDir . '/info_test.txt';
        file_put_contents($testFile, 'Test content for info');
        $this->recycleBinService->moveToRecycleBin($testFile);
        
        $info = $this->recycleBinService->getRecycleBinInfo();
        
        $this->assertIsArray($info);
        $this->assertArrayHasKey('total_items', $info);
        $this->assertArrayHasKey('total_size', $info);
        $this->assertArrayHasKey('items', $info);
        $this->assertGreaterThan(0, $info['total_items']);
        $this->assertGreaterThan(0, $info['total_size']);
    }

    public function test_exception_when_file_not_exists()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('File atau folder tidak ditemukan');
        
        $this->recycleBinService->moveToRecycleBin('/nonexistent/file.txt');
    }
}