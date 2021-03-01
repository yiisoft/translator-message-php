<?php

declare(strict_types=1);

namespace Yiisoft\Translator\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Translator\Message\Php\MessageSource;
use InvalidArgumentException;

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
                    'test.id1' => [
                        'message' => 'app: Test 1 on the (de)',
                        'comment' => 'Translate wisely!',
                    ],
                    'test.id2' => [
                        'message' => 'app: Test 2 on the (de)',
                    ],
                    'test.id3' => [
                        'message' => 'app: Test 3 on the (de)',
                    ],
                ],
            ],
            [
                'app',
                'de-DE',
                [
                    'test.id1' => [
                        'message' => 'app: Test 1 on the (de-DE)',
                    ],
                    'test.id2' => [
                        'message' => 'app: Test 2 on the (de-DE)',
                    ],
                ],
            ],
        ];
    }

    public function generateFailTranslationsData(): array
    {
        return [
            [
                'app',
                'de',
                [
                    'test.id1' => [
                    ],
                ],
            ],
            [
                'app',
                'de-DE',
                [
                    'test.id1' => [
                        'message' => 1,
                    ],
                ],
            ],
            [
                'app',
                'de-DE',
                [
                    'test.id1' => [
                        'message' => '',
                        'comment' => 1,
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider generateTranslationsData
     */
    public function testWrite(string $category, string $locale, array $data): void
    {
        $this->path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'translate_tests' . uniqid();

        $messageSource = new MessageSource($this->path);
        $messageSource->write($category, $locale, $data);
        foreach ($data as $id => $value) {
            $this->assertEquals($messageSource->getMessage($id, $category, $locale), $value['message']);
        }

        $this->cleanFiles();
    }

    /**
     * @dataProvider generateFailTranslationsData
     */
    public function testWriteWithFailData(string $category, string $locale, array $data): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'translate_tests' . uniqid();

        $messageSource = new MessageSource($this->path);
        $messageSource->write($category, $locale, $data);
    }

    public function testMultiWrite(): void
    {
        $allData = $this->generateTranslationsData();

        $this->path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'translate_tests' . uniqid('', true);
        $messageSource = new MessageSource($this->path);

        foreach ($allData as $fileData) {
            [$category, $locale, $data] = $fileData;
            $messageSource->write($category, $locale, $data);
        }

        foreach ($allData as $fileData) {
            [$category, $locale, $data] = $fileData;
            foreach ($data as $id => $value) {
                $this->assertEquals($messageSource->getMessage($id, $category, $locale), $value['message']);
            }
        }

        $this->cleanFiles();
    }

    public function testReadWithoutFiles(): void
    {
        $this->path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'translate_tests' . uniqid('', true);

        $messageSource = new MessageSource($this->path);
        $messageSource->write('category', 'language', []);

        $expectedContent = "<?php\nreturn []\n";
        $this->assertEquals($expectedContent, file_get_contents($this->path . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR . 'category.php'));

        $this->cleanFiles();
    }

    private function cleanFiles(): void
    {
        if (file_exists($this->path)) {
            self::rmdir_recursive($this->path);
        }
    }

    private static function rmdir_recursive(string $path): void
    {
        $directoryIterator = new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directoryIterator, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                self::rmdir_recursive($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }
        rmdir($path);
    }
}
