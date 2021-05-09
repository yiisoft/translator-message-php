<?php

declare(strict_types=1);

use Yiisoft\Translator\MessageReaderInterface;
use Yiisoft\Translator\Message\Php\MessageSource;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Factory\Definition\DynamicReference;

return [
    MessageReaderInterface::class => [
        'class' => MessageSource::class,
        '__construct()' => [
            DynamicReference::to(fn (Aliases $aliases) => $aliases->get('@message')),
        ],
    ],
];
