# Repository is archived
If you want to maintain it, you can make a fork.

# Mougrim logger port Extension for Yii 2

This extension provides using [mougrim/php-logger](https://github.com/mougrim/php-logger) (ex [Mitallast](https://github.com/mitallast/) Logger) in Yii 2 Framework.

This extension provides all logs expects profiling logs.

[![Latest Stable Version](https://poser.pugx.org/mougrim/yii2-mougrim-logger/version)](https://packagist.org/packages/mougrim/yii2-mougrim-logger)
[![Latest Unstable Version](https://poser.pugx.org/mougrim/yii2-mougrim-logger/v/unstable)](https://packagist.org/packages/mougrim/yii2-mougrim-logger)
[![License](https://poser.pugx.org/mougrim/yii2-mougrim-logger/license)](https://packagist.org/packages/mougrim/yii2-mougrim-logger)
[![Build Status](https://api.travis-ci.org/mougrim/yii2-mougrim-logger.png?branch=master)](https://travis-ci.org/mougrim/yii2-mougrim-logger)

## Benefits

[mougrim/php-logger](https://github.com/mougrim/php-logger) has next benefits:
- flexible configuration;
- interface like Apache log4php (with debug log level);
- higher than yii2 logger performance.

For more information and benchmark result see [benefits](BENEFITS.md).

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```bash
php composer.phar require --prefer-dist mougrim/yii2-mougrim-logger
```

or add

```json
"mougrim/yii2-mougrim-logger": "*"
```

to the `require` section of your composer.json, then run `php composer.phar update`

## Configuration

To use this extension, you have to configure Logger in your application configuration:

```php
<?php
use Mougrim\Logger\Logger as MougrimLogger;
use mougrim\yii2Logger\Logger;
use yii\log\Logger as YiiLogger;
...
// change standard logger class
\Yii::$container->set(
    YiiLogger::class,
    [
        'class' => Logger::class,
    ]
);
// configure Mougrim Logger
MougrimLogger::configure(__DIR__ . '/logger.php');
// your config
return [
    ....
];
```

For configuration Mougrim Logger see documentation [mougrim/php-logger](https://github.com/mougrim/php-logger).

If you want use Mougrim Logger and Yii Logger together, change your config:

```php
\Yii::$container->set(
    YiiLogger::class,
    [
        'class' => Logger::class,
        'alwaysYiiLoggerLog' => true,
    ]
);
```

May be you want use this way for correct working of debug panel.
