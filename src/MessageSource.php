<?php

declare(strict_types=1);

namespace Yiisoft\Translator\Message\Php;

use InvalidArgumentException;
use RuntimeException;
use Yiisoft\Translator\MessageReaderInterface;
use Yiisoft\Translator\MessageWriterInterface;

use function array_key_exists;
use function is_array;
use function is_string;

final class MessageSource implements MessageReaderInterface, MessageWriterInterface
{
    private string $path;
    private array $messages = [];

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

    public function getMessages(string $category, string $locale): array
    {
        if (!isset($this->messages[$category][$locale])) {
            $this->read($category, $locale);
        }

        $messages = $this->messages[$category][$locale] ?? [];
        foreach ($messages as &$message) {
            $message = ['message' => $message];
        }

        return $messages;
    }

    public function write(string $category, string $locale, array $messages): void
    {
        $content = $this->generateMessagesFileContent($messages);

        $path = $this->getFilePath($category, $locale, true);

        if (file_put_contents($path, $content, LOCK_EX) === false) {
            throw new RuntimeException('Can not write to ' . $path);
        }
    }

    private function getFilePath(string $category, string $locale, bool $withCreateDir = false): string
    {
        $filePath = $this->path . DIRECTORY_SEPARATOR . $locale;
        if ($withCreateDir && !file_exists($filePath) && !mkdir($filePath, 0775, true) && !is_dir($filePath)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $filePath));
        }
        $filePath .= DIRECTORY_SEPARATOR . $category . '.php';

        return $filePath;
    }

    private function read(string $category, string $locale): void
    {
        $path = $this->getFilePath($category, $locale);

        if (is_file($path)) {
            $messages = include $path;

            if (!is_array($messages)) {
                throw new RuntimeException('Invalid file format: ' . $path);
            }
        } else {
            $messages = [];
        }

        $this->messages[$category][$locale] = $messages;
    }

    private function generateMessagesFileContent(array $messages): string
    {
        $content = "<?php\nreturn ";
        if (empty($messages)) {
            $content .= '[]';
        } else {
            $content .= $this->messagesToCode($messages);
            $content .= ';';
        }
        $content .= "\n";

        return $content;
    }

    private function messagesToCode(array $messages): string
    {
        $code = '[';
        foreach ($messages as $messageId => $messageData) {
            if (!array_key_exists('message', $messageData)) {
                throw new InvalidArgumentException("Message is not valid for ID \"$messageId\". \"message\" key is missing.");
            }

            if (!is_string($messageData['message'])) {
                throw new InvalidArgumentException("Message is not a string for ID \"$messageId\".");
            }

            if (array_key_exists('comment', $messageData)) {
                if (!is_string($messageData['comment'])) {
                    throw new InvalidArgumentException("Message comment is not a string for ID \"$messageId\".");
                }

                $code .= "\n" . '    /* ' . $messageData['comment'] . ' */';
            }

            $code .= "\n" . '    ';
            $code .= "'{$messageId}'";
            $code .= ' => ';
            $code .= "'{$messageData['message']}'";
            $code .= ',';
        }
        $code .= "\n" . ']';
        return $code;
    }
}
