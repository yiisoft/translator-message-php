<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://yiisoft.github.io/docs/images/yii_logo.svg" height="100px" alt="Yii">
    </a>
    <h1 align="center">Yii Translator PHP Message Storage</h1>
    <br>
</p>

[![Latest Stable Version](https://poser.pugx.org/yiisoft/translator-message-php/v)](https://packagist.org/packages/yiisoft/translator-message-php)
[![Total Downloads](https://poser.pugx.org/yiisoft/translator-message-php/downloads)](https://packagist.org/packages/yiisoft/translator-message-php)
[![Build status](https://github.com/yiisoft/translator-message-php/workflows/build/badge.svg)](https://github.com/yiisoft/translator-message-php/actions?query=workflow%3Abuild)
[![Code Coverage](https://codecov.io/gh/yiisoft/translator-message-php/branch/master/graph/badge.svg)](https://codecov.io/gh/yiisoft/translator-message-php)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fyiisoft%2Ftranslator-message-php%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/yiisoft/translator-message-php/master)
[![static analysis](https://github.com/yiisoft/translator-message-php/workflows/static%20analysis/badge.svg)](https://github.com/yiisoft/translator-message-php/actions?query=workflow%3A%22static+analysis%22)
[![type-coverage](https://shepherd.dev/github/yiisoft/translator-message-php/coverage.svg)](https://shepherd.dev/github/yiisoft/translator-message-php)

The package provides message storage backend based on PHP arrays to be used with [`yiisoft/translator`](https://github.com/yiisoft/translator) package.

## Requirements

- PHP 8.0 or higher.

## Installation

The package could be installed with [Composer](https://getcomposer.org):

```shell
composer require yiisoft/translator-message-php
```

## General usage

The package is meant to be used with [`yiisoft/translator`](https://github.com/yiisoft/translator):

```php
use Yiisoft\Translator\CategorySource;
use Yiisoft\Translator\IntlMessageFormatter;
use Yiisoft\Translator\Message\Php\MessageSource;

$categorySource = new CategorySource(
    'my-category',
    new MessageSource('/path/to/messages'),
    new IntlMessageFormatter(),
);
```

The examples below are about using it separately.

### Create an instance of message source

```php
/** @var string $path - full path to your translations */
$messageSource = new \Yiisoft\Translator\Message\Php\MessageSource($path);
```

### Read a message without `yiisoft/translator` package

```php
/** 
 * @var \Yiisoft\Translator\Message\Php\MessageSource $messageSource
 * @var ?string $translatedString
 */
$id = 'messageIdentifier';
$category = 'messageCategory';
$language = 'de-DE';

$translatedString = $messageSource->getMessage($id, $category, $language);
```

### Write an array of messages to storage

```php
/** 
 * @var \Yiisoft\Translator\Message\Php\MessageSource $messageSource
 */
$category = 'messageCategory';
$language = 'de-DE';
$data = [
    'test.id1' => [
        'message' => 'Nachricht 1', // translated string
        'comment' => 'Comment for message 1', // is optional parameter for save extra metadata
    ],
    'test.id2' => [
        'message' => 'Nachricht 2',
    ],
    'test.id3' => [
        'message' => 'Nachricht 3',
    ],
];

$messageSource->write($category, $language, $data);
```

The following structure will be created after writing:

```
📁 path_to_your_storage
  📁 de-DE
     🗎 messageCategory.php
```

## Documentation

- [Internals](docs/internals.md)

If you need help or have a question, the [Yii Forum](https://forum.yiiframework.com/c/yii-3-0/63) is a good place for that.
You may also check out other [Yii Community Resources](https://www.yiiframework.com/community).

## License

The Yii Translator PHP Message Storage is free software. It is released under the terms of the BSD License.
Please see [`LICENSE`](./LICENSE.md) for more information.

Maintained by [Yii Software](https://www.yiiframework.com/).

## Support the project

[![Open Collective](https://img.shields.io/badge/Open%20Collective-sponsor-7eadf1?logo=open%20collective&logoColor=7eadf1&labelColor=555555)](https://opencollective.com/yiisoft)

## Follow updates

[![Official website](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](https://www.yiiframework.com/)
[![Twitter](https://img.shields.io/badge/twitter-follow-1DA1F2?logo=twitter&logoColor=1DA1F2&labelColor=555555?style=flat)](https://twitter.com/yiiframework)
[![Telegram](https://img.shields.io/badge/telegram-join-1DA1F2?style=flat&logo=telegram)](https://t.me/yii3en)
[![Facebook](https://img.shields.io/badge/facebook-join-1DA1F2?style=flat&logo=facebook&logoColor=ffffff)](https://www.facebook.com/groups/yiitalk)
[![Slack](https://img.shields.io/badge/slack-join-1DA1F2?style=flat&logo=slack)](https://yiiframework.com/go/slack)
