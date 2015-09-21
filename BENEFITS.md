# Benefits

## Flexible configuration.

In Yii2 for format log message you need redeclare method [\yii\log\Target::formatMessage()](http://www.yiiframework.com/doc-2.0/yii-log-target.html#formatMessage()-detail) or use [\yii\log\Target::$prefix](http://www.yiiframework.com/doc-2.0/yii-log-target.html#$prefix-detail) in your target.

Also in Yii2 you can't use different levels filter for different categories for one target. If you use two log targets and want to see the messages in chronological order, you should set [\yii\log\Target::$exportInterval](http://www.yiiframework.com/doc-2.0/yii-log-target.html#$exportInterval-detail) for targets to 1. It isn't good for performance. Also you should set [\yii\log\Target::$logVars](http://www.yiiframework.com/doc-2.0/yii-log-target.html#$logVars-detail) to empty array.

If you use [fork](http://php.net/pcntl_fork) then you should [\yii\log\Logger::flash()](http://www.yiiframework.com/doc-2.0/yii-log-logger.html#flush()-detail) Yii2 Logger. It's normally, but if you want to see the messages in chronological order you should configure logger same above (when two log targets) and set [\yii\log\Logger::$flushInterval](http://www.yiiframework.com/doc-2.0/yii-log-logger.html#$flushInterval-detail) to 1.

Also if you use big values of [\yii\log\Logger::$flushInterval](http://www.yiiframework.com/doc-2.0/yii-log-logger.html#$flushInterval-detail) and [\yii\log\Target::$exportInterval](http://www.yiiframework.com/doc-2.0/yii-log-target.html#$exportInterval-detail) (by default 1000) and you log big messages, you can get memory leaks.

Also in Yii Logger trace log not work in production environment and it's not configurable.

Mougrim Logger has flexible config:

```php
use Mougrim\Logger\Appender\AppenderStream;
use Mougrim\Logger\Layout\LayoutPattern;
use Mougrim\Logger\Layout\LayoutSimple;
use Mougrim\Logger\Logger;
use mougrim\yii2Logger\Layout as LikeYiiLayout;

Logger::configure([
    'policy' => [
        'ioError' => 'trigger_error', // ignore, trigger_warn, trigger_error, exception or exit
        'configurationError' => 'exception'
    ],
    'layouts' => [
        // specify format types
        'simple' => [
            'class' => LayoutSimple::class, // simple fast logging format
        ],
        'likeYii' => [
            'class' => LikeYiiLayout::class, // format like yii
        ],
        'pattern' => [
            'class' => LayoutPattern::class, // customizable format
            'pattern' => '{date:Y/m/d} [{level}] {logger} {location:file:line, class.function} {mdc:key} {mdc} {ndc}: {message} {ex}',
        ],
    ],
    'appenders' => [
        // appenders types
        'stream' => [
            'class' => AppenderStream::class,
            'stream' => 'php://stdout', // you can choose file here
            'useLock' => true,
            'useLockShortMessage' => false, // if message less than 4Kb, do not use locks (see PIPE_BUF in linux)
            // you can specify log level for appender
            'minLevel' => Logger::DEBUG,
            'maxLevel' => Logger::FATAL,
            // you can use layout if you want format message
            'layout' => 'simple',
        ],
    ],
    'loggers' => [
        'myLogger' => [ // logger is similar Yii Logger category
            'appenders' => ['stream'],
            'addictive' => false,
            // you can specify log level for logger
            'minLevel' => Logger::TRACE,
            'maxLevel' => Logger::FATAL,
        ],
    ],
    'root' => [ // if logger not found in loggers list, then use root logger
        'appenders' => ['stream'],
    ]
]);
```

## Good interface

You can use yii-style logging:

```php
\Yii::info("message", "myLogger"); // use myLogger logger
\Yii::info("message", "anotherLogger"); // use root logger
```

But in yii debug log level not found.

In Mougrim Logger interface like Apache log4php and has debug level:
```php
use Mougrim\Logger\Logger;
// only message:
Logger::getLogger("myLogger")->debug("message"); // use myLogger logger
Logger::getLogger("anotherLogger")->info("message"); // use root logger
// message with exception:
$exception = new \Exception();
Logger::getLogger("myLogger")->error("message", $exception);
```

## Performance

Benchmark results:

[![Benchmark results](https://github.com/mougrim/yii2-mougrim-logger-performance/blob/master/benchmark.png)](BENCHMARK.md)

Attention! Mougrim Logger don't collect log messages, all log messages have chronological order and without memory leaks. 

- `yii-logger-default.php`: this is default Yii2 Logger config with collect log messages in memory, this is fast variant, but isn't chronological and can get memory leaks (see above);
- `yii-logger-force-flush.php`: this is Yii2 Logger config with force flush log messages, this is chronological and without memory leaks and isn't fast;
- `mougrim-logger-format.php`: this is flexible Mougrim Logger config, but isn't fast;
- `mougrim-logger-like-yii.php`: this is like Yii2 Logger format Mougrim Logger config, faster than `yii-logger-force-flush.php`;
- `mougrim-logger-like-yii-without-user-info.php`: this is like `mougrim-logger-like-yii.php` by log data, but has another format and faster than `mougrim-logger-like-yii.php`;
- `mougrim-logger-simple.php`: this is simple format Mougrim Logger config without datetime info, this config has same performance with `yii-logger-default.php`;
- `mougrim-logger-without-format.php`: this is without format Mougrim Logger config, without line brake and another info, this config is faster than `yii-logger-default.php`.

I think, performance difference isn't principled (isn't very big), but Mougrim Logger has flexible configuration.

For log messages format and more information see [benchmark](BENCHMARK.md).
