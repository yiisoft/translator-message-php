<?php
namespace Yiisoft\I18n\Resource;

use Yiisoft\I18n\MessageReader;
use Yiisoft\I18n\MessageWriter;

class PhpFile implements MessageReader, MessageWriter
{
    private $path;
    private $messages;
    private $fileMtime;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function all($context = null): array
    {
        if (!is_file($this->path)) {
            throw new \RuntimeException('Invalid path provided: ' . $this->path);
        }

        $mtime = filemtime($this->path);
        if ($this->fileMtime !== $mtime) {
            $messages = include $this->path;

            if (!\is_array($messages)) {
                throw new \RuntimeException('Invalid file format: ' . $this->path);
            }

            $this->messages = $messages;
            $this->fileMtime = $mtime;
        }

        return $this->messages;
    }

    public function one(string $id, $context = null): ?string
    {
        $messages = $this->all($context);
        return $messages[$id] ?? null;
    }

    public function plural(string $id, int $count, $context = null): ?string
    {
        return $this->one($id, $context);
    }

    public function write(array $messages): void
    {
        $content = "<?php\nreturn ";
        if (empty($messages)) {
            $content .= '[]';
        } else {
            $content .= $this->arrayToCode($messages);
            $content .= ';';
        }
        $content .= "\n";

        if (file_put_contents($this->path, $content, LOCK_EX) === false) {
            throw new \RuntimeException('Can not write to ' . $this->path);
        }
    }

    private function arrayToCode($array, $level = 0)
    {
        $code = '[';

        $keys = array_keys($array);
        $outputKeys = ($keys !== range(0, count($array) - 1));
        $spaces = str_repeat(' ', $level * 4);
        foreach ($keys as $key) {
            $code .= "\n" . $spaces . '    ';
            if ($outputKeys) {
                $code .= $this->arrayToCode($key, 0);
                $code .=  ' => ';
            }
            $code .=  $this->arrayToCode($array[$key], $level + 1);
            $code .= ',';
        }
        $code .= "\n" . $spaces . ']';
        return $code;
    }
}
