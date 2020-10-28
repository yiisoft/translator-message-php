<?php

declare(strict_types=1);

namespace Yiisoft\Translator\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Translator\Message\Php\MessageSource;
use function PHPUnit\Framework\assertEquals;

final class MessageSourceTest extends TestCase
{
    private string $path;

    public function generateTranslationsData(): array
    {
        return [
            [
                'app',
                'de',
                [
                    'test.id1' => 'app: Test 1 on the (de)',
                    'test.id2' => 'app: Test 2 on the (de)',
                    'test.id3' => 'app: Test 3 on the (de)',
                ]
            ],
            [
                'app',
                'de-DE',
                [
                    'test.id1' => 'app: Test 1 on the (de-DE)',
                    'test.id2' => 'app: Test 2 on the (de-DE)',
                ]
            ],
        ];
    }

    /**
     * @dataProvider generateTranslationsData
     */
    public function testWrite(string $category, string $language, array $data): void
    {
        $this->path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'translate_tests' . uniqid();

        $messageSource = new MessageSource($this->path);
        $messageSource->write($category, $language, $data);
        foreach ($data as $id => $value) {
            $this->assertEquals($messageSource->getMessage($id, $category, $language), $value);
        }

        // clean after test
        if (file_exists($this->path)) {
            static::rmdir_recursive($this->path);
        }
    }

    private static function rmdir_recursive(string $path): void
    {
        $directoryIterator = new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directoryIterator, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                static::rmdir_recursive($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }
        rmdir($path);
    }
}
