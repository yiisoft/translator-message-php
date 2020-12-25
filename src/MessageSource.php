<?php

declare(strict_types=1);

namespace Yiisoft\Translator\Message\Php;

use InvalidArgumentException;
use Yiisoft\Translator\MessageInterface;
use Yiisoft\Translator\MessageReaderInterface;
use Yiisoft\Translator\MessageWriterInterface;
use function array_key_exists;
use function is_string;

final class MessageSource implements MessageReaderInterface, MessageWriterInterface
{
    private string $path;
    private array $messages;

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
        if ($withCreateDir && !file_exists($filePath) && !mkdir($filePath, 0775, true) && !is_dir($filePath)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $filePath));
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
        $this->validateMessages($messages);
        $content = $this->generateMessagesFileContent($messages);

        $path = $this->getFilePath($category, $locale, true);

        if (file_put_contents($path, $content, LOCK_EX) === false) {
            throw new \RuntimeException('Can not write to ' . $path);
        }
    }

    private function validateMessages(array $messages): void
    {
        foreach ($messages as $key => $message) {
            if (!$message instanceof MessageInterface) {
                $realType = gettype($message);
                throw new InvalidArgumentException("Messages should contain \"\Yiisoft\Translator\MessageInterface\" instances only. \"$realType\" given for \"$key\".");
            }
        }
    }

    /**
     * @param MessageInterface[] $messages
     *
     * @return string
     */
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

    /**
     * @param MessageInterface[] $messages
     *
     * @return string
     */
    private function messagesToCode(array $messages): string
    {
        $code = '[';
        foreach ($messages as $messageId => $message) {
            if (array_key_exists('comment', $message->meta())) {
                if (!is_string($message->meta()['comment'])) {
                    throw new InvalidArgumentException("Message comment is not a string for ID \"$messageId\".");
                }

                $code .= "\n" . '    /* ' . $message->meta()['comment'] . ' */';
            }

            $code .= "\n" . '    ';
            $code .= "'{$messageId}'";
            $code .= ' => ';
            $code .= "'{$message->translation()}'";
            $code .= ',';
        }
        $code .= "\n" . ']';
        return $code;
    }
}
