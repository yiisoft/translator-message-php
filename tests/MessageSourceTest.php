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
            [
                'app',
                'en-150',
                [
                    'test.id1' => [
                        'message' => 'app: Test 1 on the (en-150)',
                    ],
                    'test.id2' => [
                        'message' => 'app: Test 2 on the (en-150)',
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

    public function generateTranslationsDataWithQuotes(): array
    {
        return [
            [
                'app',
                'de',
                [
                    'test\'.id1' => [
                        'message' => 'app: \'Test 1\' on the (de)',
                        'comment' => 'Translate \'wisely!',
                    ],
                    'test.\'id2\'' => [
                        'message' => 'app: Test 1\' on the (de)',
                        'comment' => 'Translate \'wisely!',
                    ],
                    'test."id3' => [
                        'message' => 'app: "Test 2" on the (de)',
                    ],
                    'test."id4"' => [
                        'message' => 'app: Test 3" on the (de)',
                    ],
                ],
            ],
        ];
    }

    protected function tearDown(): void
    {
        $this->cleanFiles();
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
    }

    public function testReadWithEmptyTranslations(): void
    {
        $this->path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'translate_tests' . uniqid('', true);

        $messageSource = new MessageSource($this->path);
        $messageSource->write('category', 'language', []);

        $expectedContent = "<?php\nreturn []\n";
        $this->assertEquals($expectedContent, file_get_contents($this->path . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR . 'category.php'));
    }

    public function testReadWithoutFileSource(): void
    {
        $this->path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'translate_tests' . uniqid('', true);

        $messageSource = new MessageSource($this->path);

        $this->assertNull($messageSource->getMessage('test', 'category', 'locale'));
    }

    /**
     * @dataProvider invalidLocalesData
     */
    public function testReadWithIncorrectLocale(string $locale): void
    {
        $this->path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'translate_tests' . uniqid('', true);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Invalid locale code: "%s".', $locale));

        $messageSource = new MessageSource($this->path);

        $messageSource->getMessage('test', 'category', $locale);
    }

    public function invalidLocalesData(): array
    {
        return [
            ['$%&',],
            ['як9',],
            ['(9a)',],
        ];
    }

    public function testCannotCreateDirectory(): void
    {
        $locale = 'test_locale';
        $this->path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'translate_tests' . uniqid('', true);

        $this->disableErrorHandling(2, 'mkdir(): ');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Directory "' . $this->path . DIRECTORY_SEPARATOR . $locale . '" was not created');

        file_put_contents($this->path, '');

        $messageSource = new MessageSource($this->path);
        $messageSource->write('category', $locale, []);

        $this->enableErrorHandling();
    }

    public function testCannotWriteToFile(): void
    {
        $locale = 'test_locale';
        $this->path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'translate_tests' . uniqid('', true);
        $translationFile = $this->path . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . 'category.php';

        $this->disableErrorHandling(2, 'failed to open stream: Permission denied');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Can not write to ' . $translationFile);

        $messageSource = new MessageSource($this->path);
        $messageSource->write('category', $locale, []);

        chmod($translationFile, 0444);

        $messageSource = new MessageSource($this->path);
        $messageSource->write('category', $locale, []);

        $this->enableErrorHandling();
    }

    private function cleanFiles(): void
    {
        if (file_exists($this->path)) {
            self::rmdir_recursive($this->path);
        }
    }

    private static function rmdir_recursive(string $path): void
    {
        if (is_file($path)) {
            chmod($path, 0666);
            unlink($path);
            return;
        }

        $directoryIterator = new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directoryIterator, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $file) {
            self::rmdir_recursive($file->getPathname());
        }

        chmod($path, 0775);
        rmdir($path);
    }

    protected function disableErrorHandling($skippedErrno, $skippedErrstr)
    {
        set_error_handler(fn ($errno, $errstr, $errfile, $errline) => // skip not needed warning, notice or errors
(bool)($errno == $skippedErrno && stripos($errstr, (string) $skippedErrstr) !== false));
    }

    protected function enableErrorHandling()
    {
        restore_error_handler();
    }

    /**
     * @dataProvider generateTranslationsData
     */
    public function testReadMessages(string $category, string $locale, array $data): void
    {
        $this->path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'translate_tests' . uniqid();

        $messageSource = new MessageSource($this->path);
        $messageSource->write($category, $locale, $data);

        // Removing comments from reference messages.
        $referenceMessages = array_map(fn ($elem) => ['message' => $elem['message']], $data);

        $messages = $messageSource->getMessages($category, $locale);
        $this->assertEquals($messages, $referenceMessages);
    }

    /**
     * @dataProvider generateTranslationsDataWithQuotes
     */
    public function testWriteQuotedString(string $category, string $locale, array $data): void
    {
        $this->path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'translate_tests' . uniqid();

        $messageSource = new MessageSource($this->path);
        $messageSource->write($category, $locale, $data);
        foreach ($data as $id => $value) {
            $this->assertEquals($messageSource->getMessage($id, $category, $locale), $value['message']);
        }
    }
}
