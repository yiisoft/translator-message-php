<?php

declare(strict_types=1);

namespace Yiisoft\Translator\Message\Php;

use Yiisoft\Translator\MessageReaderInterface;
use Yiisoft\Translator\MessageWriterInterface;

final class MessageSource implements MessageReaderInterface, MessageWriterInterface
{
    private $path;
    private $messages;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function getMessage(string $id, string $category, string $locale, array $parameters = []): ?string
    {
        if (!isset($this->messages[$category][$locale])) {
            $this->read($category, $locale);
        }

        return $this->messages[$category][$locale][$id] ?? null;
    }

    private function getFilePath(string $category, string $locale, bool $withCreateDir = false): ?string
    {
        $filePath = $this->path . DIRECTORY_SEPARATOR . $locale;
        if ($withCreateDir && !file_exists($filePath)) {
            mkdir($filePath, 0775, true);
        }
        $filePath .= DIRECTORY_SEPARATOR . $category . '.php';

        return $filePath;
    }

    private function read(string $category, string $locale): void
    {
        $path = $this->getFilePath($category, $locale);

        if (is_file($path)) {
            $messages = include $path;

            if (!\is_array($messages)) {
                throw new \RuntimeException('Invalid file format: ' . $path);
            }
        } else {
            $messages = [];
        }

        $this->messages[$category][$locale] = $messages;
    }

    public function write(string $category, string $locale, array $messages): void
    {
        $content = $this->generateMessageString($messages);

        $path = $this->getFilePath($category, $locale, true);

        if (file_put_contents($path, $content, LOCK_EX) === false) {
            throw new \RuntimeException('Can not write to ' . $path);
        }
    }

    private function generateMessageString(array $messages): string
    {
        $content = "<?php\nreturn ";
        if (empty($messages)) {
            $content .= '[]';
        } else {
            $content .= $this->arrayToCode($messages);
            $content .= ';';
        }
        $content .= "\n";

        return $content;
    }

    private function arrayToCode($array, $level = 0)
    {
        $code = '[';
        foreach ($array as $key => $value) {
            $code .= "\n" . '    ';
            $code .= "'{$key}'";
            $code .= ' => ';
            if (!is_string($value)) {
                throw new \RuntimeException('Value is not a string');
            }
            $code .= "'{$value}'";
            $code .= ',';
        }
        $code .= "\n" . ']';
        return $code;
    }
}
