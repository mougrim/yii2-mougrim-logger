# Benchmark

For test performance, I created project with next composer.json:

```json
{
    "type": "project",
    "minimum-stability": "stable",
    "require": {
        "php": ">=5.5.0",
        "yiisoft/yii2": "2.*",
        "mougrim/yii2-mougrim-logger": "*"
    }
}
```

And install:

```bash
composer.phar update
```

## Yii Logger

### Test script

Yii Logger test script `yii-logger.php`:

```php
#!/usr/bin/env php
<?php
use yii\web\Application;
use yii\web\IdentityInterface;

defined('YII_DEBUG') or define('YII_DEBUG', false);
defined('YII_ENV') or define('YII_ENV', 'prod');

require(__DIR__ . '/vendor/autoload.php');
require(__DIR__ . '/vendor/yiisoft/yii2/Yii.php');
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

class IdentityClass implements IdentityInterface
{
    public function getId()
    {
        return 1;
    }

    public static function findIdentity($id) {}
    public static function findIdentityByAccessToken($token, $type = null) {}
    public function getAuthKey() {}
    public function validateAuthKey($authKey) {}
}

/** @noinspection PhpIncludeInspection */
$config = [
    'id' => 'test-log',
    'basePath' => __DIR__,
    'bootstrap' => ['log'],
    'components' => [
        'log' => require $argv[1],
        'user' => [
            'identityClass' => IdentityClass::class,
        ],
    ],
];

$application = new Application($config);
$application->getUser()->setIdentity(new IdentityClass());
$application->getSession()->open();
$messagesQty = count(\Yii::getLogger()->messages);
\Yii::getLogger()->flush();

$start = microtime(true);

for ($i = 0; $i < 10000; $i++) {
    \Yii::info("very very very very very very very very long test message {$i}");
}
\Yii::getLogger()->flush();

echo (microtime(true) - $start) . "\n";
```

### Default config

Default Yii Logger config `yii-logger-default.php`:

```php
<?php
use yii\log\FileTarget;

return [
    'traceLevel' => 0,
    'targets' => [
        [
            'class'  => FileTarget::class,
            'logFile' => __DIR__ . '/yii-logger-default.log',
            'levels' => ['error', 'warning', 'info'],
        ],
    ],
];
```

Log line example:

```text
2015-09-17 19:12:16 [127.0.0.1][1][4fkkubnplohgbd21g4erhsjcm2][info][application] very very very very very very very very long test message 0
```

### Force flush config

Force flush Yii Logger config `yii-logger-force-flush.php`:

```php
<?php
use yii\log\FileTarget;

return [
    'traceLevel' => 0,
    'flushInterval' => 1,
    'targets' => [
        [
            'class'  => FileTarget::class,
            'logFile' => __DIR__ . '/yii-logger-force-flush.log',
            'levels' => ['error', 'warning', 'info'],
            'exportInterval' => 1,
            'logVars' => [],
        ],
    ],
];
```

Log line example:

```text
2015-09-17 19:12:43 [127.0.0.1][1][m31421ttoiqemm12tf15ooi673][info][application] very very very very very very very very long test message 0
```


## Mougrim Logger

### Test script

Mougrim Logger test script `mougrim-logger.php`:

```php
#!/usr/bin/env php
<?php
use Mougrim\Logger\Logger as MougrimLogger;
use Mougrim\Logger\LoggerMDC;
use mougrim\yii2Logger\Logger;
use yii\console\Application;
use yii\log\Logger as YiiLogger;

defined('YII_DEBUG') or define('YII_DEBUG', false);
defined('YII_ENV') or define('YII_ENV', 'prod');

require(__DIR__ . '/vendor/autoload.php');
require(__DIR__ . '/vendor/yiisoft/yii2/Yii.php');
\Yii::$container->set(
    YiiLogger::class,
    [
        'class' => Logger::class,
    ]
);
/** @noinspection PhpIncludeInspection */
MougrimLogger::configure(require $argv[1]);
LoggerMDC::put('ip', '127.0.0.1');
LoggerMDC::put('userId', 1);
LoggerMDC::put('sessionId', '69u5oevbjp0c25d38rg4ahl114');
$config = [
    'id' => 'test-log',
    'basePath' => __DIR__,
    'bootstrap' => ['log'],
];
$application = new Application($config);
\Yii::getLogger()->flush();

$start = microtime(true);

for ($i = 0; $i < 10000; $i++) {
    \Yii::info("very very very very very very very very long test message {$i}");
}

echo (microtime(true) - $start) . "\n";
```

### Like Yii format

Like Yii format Mougrim Logger config `mougrim-logger-like-yii.php`:

```php
<?php
use Mougrim\Logger\Logger as MougrimLogger;
use Mougrim\Logger\Appender\AppenderStream;
use mougrim\yii2Logger\Layout;

$logPath = __DIR__ . '/mougrim-logger-like-yii.log';
return [
    'policy'    => [
        'ioError'            => 'trigger_warn',
        'configurationError' => 'trigger_warn'
    ],
    'renderer'  => [
        'nullMessage' => '-',
    ],
    'layouts'   => [
        'yii-like' => [
            'class'   => Layout::class,
            'logUserInfo' => true,
        ],
    ],
    'appenders' => [
        'root_console_log' => [
            'class'    => AppenderStream::class,
            'layout'   => 'yii-like',
            'stream'   => $logPath,
            'minLevel' => MougrimLogger::getLevelByName('info'),
        ],
    ],
    'root'      => ['appenders' => ['root_console_log']],
];
```

Log line example:

```text
2015-09-17 19:13:21 [127.0.0.1][1][69u5oevbjp0c25d38rg4ahl114][INFO][application][][] very very very very very very very very long test message 0
```

### Like Yii format without user info

Like Yii format without user info Mougrim Logger config `mougrim-logger-like-yii-without-user-info.php`:

```php
<?php
use Mougrim\Logger\Logger as MougrimLogger;
use Mougrim\Logger\Appender\AppenderStream;
use mougrim\yii2Logger\Layout;

$logPath = __DIR__ . 'mougrim-logger-like-yii-without-user-info.log';
return [
    'policy'    => [
        'ioError'            => 'trigger_warn',
        'configurationError' => 'trigger_warn'
    ],
    'renderer'  => [
        'nullMessage' => '-',
    ],
    'layouts'   => [
        'yii-like' => [
            'class'   => Layout::class,
            'logUserInfo' => false,
        ],
    ],
    'appenders' => [
        'root_console_log' => [
            'class'    => AppenderStream::class,
            'layout'   => 'yii-like',
            'stream'   => $logPath,
            'minLevel' => MougrimLogger::getLevelByName('info'),
        ],
    ],
    'root'      => ['appenders' => ['root_console_log']],
];
```

Log line example:

```text
2015-09-17 19:13:37 [INFO][application][][ip=127.0.0.1 userId=1 sessionId=69u5oevbjp0c25d38rg4ahl114] very very very very very very very very long test message 0
```

### Simple format

Simple format Mougrim Logger config `mougrim-logger-simple.php`:

```php
<?php
use Mougrim\Logger\Layout\LayoutSimple;
use Mougrim\Logger\Logger as MougrimLogger;
use Mougrim\Logger\Appender\AppenderStream;

$logPath = __DIR__ . '/mougrim-logger-simple.log';
return [
    'policy'    => [
        'ioError'            => 'trigger_warn',
        'configurationError' => 'trigger_warn'
    ],
    'renderer'  => [
        'nullMessage' => '-',
    ],
    'layouts'   => [
        'simple' => [
            'class'   => LayoutSimple::class,
        ],
    ],
    'appenders' => [
        'root_console_log' => [
            'class'    => AppenderStream::class,
            'layout'   => 'simple',
            'stream'   => $logPath,
            'minLevel' => MougrimLogger::getLevelByName('info'),
        ],
    ],
    'root'      => ['appenders' => ['root_console_log']],
];
```

Log line example:

```text
application [INFO] ip=127.0.0.1 userId=1 sessionId=69u5oevbjp0c25d38rg4ahl114 - very very very very very very very very long test message 0
```

### Without format

Without format Mougrim Logger config `mougrim-logger-without-format.php`:

```php
<?php
use Mougrim\Logger\Logger as MougrimLogger;
use Mougrim\Logger\Appender\AppenderStream;

$logPath = __DIR__ . '/mougrim-logger-without-format.log';
return [
    'policy'    => [
        'ioError'            => 'trigger_warn',
        'configurationError' => 'trigger_warn'
    ],
    'renderer'  => [
        'nullMessage' => '-',
    ],
    'appenders' => [
        'root_console_log' => [
            'class'    => AppenderStream::class,
            'stream'   => $logPath,
            'minLevel' => MougrimLogger::getLevelByName('info'),
        ],
    ],
    'root'      => ['appenders' => ['root_console_log']],
];
```

Log line example (without line separate):

```text
very very very very very very very very long test message 0
```

### Flexible format

Flexible format Mougrim Logger config `mougrim-logger-format.php`:

```php
<?php
use Mougrim\Logger\Layout\LayoutPattern;
use Mougrim\Logger\Logger as MougrimLogger;
use Mougrim\Logger\Appender\AppenderStream;

$logPath = __DIR__ . '/mougrim-logger-format.log';
return [
    'policy'    => [
        'ioError'            => 'trigger_warn',
        'configurationError' => 'trigger_warn'
    ],
    'renderer'  => [
        'nullMessage' => '-',
    ],
    'layouts'   => [
        'simple' => [
            'class'   => LayoutPattern::class,
            'pattern' => '[{date:Y-m-d H:i:s}] {logger}.{level} {message} {ex}',
        ],
    ],
    'appenders' => [
        'root_console_log' => [
            'class'    => AppenderStream::class,
            'layout'   => 'simple',
            'stream'   => $logPath,
            'minLevel' => MougrimLogger::getLevelByName('info'),
        ],
    ],
    'root'      => ['appenders' => ['root_console_log']],
];
```

Log line example (but you can configure this format):

```text
[2015-09-17 19:13:02] application.INFO very very very very very very very very long test message 0
```

## Test script

For benchmark used next bash script `test.sh`:

```bash
#!/usr/bin/env bash
rm ./*.log;
for test in yii-logger mougrim-logger
do
    for config in ./${test}*
    do
        testFile="./${test}.php";
        if [ ${config} ==  ${testFile} ]
        then
            continue
        fi
        echo "Run: ${testFile} ${config}";
        for i in {1..10}
        do
            php ${testFile} ${config};
            sleep 1;
        done
    done
done
```

Run it:

```sh
./test.sh > result.log
```

`result.log`:

```text
Run: ./yii-logger.php ./yii-logger-default.php
0.33005094528198
0.31700611114502
0.31252789497375
0.30738091468811
0.31800222396851
0.31497287750244
0.30120396614075
0.31021404266357
0.3043360710144
0.34637403488159
Run: ./yii-logger.php ./yii-logger-force-flush.php
0.77033400535583
0.75161790847778
0.76752209663391
0.74885582923889
0.78592300415039
0.76373910903931
0.77648997306824
0.79255294799805
0.73552012443542
0.74397683143616
Run: ./mougrim-logger.php ./mougrim-logger-format.php
0.90701007843018
0.85872006416321
0.88718509674072
0.89909982681274
0.83546686172485
0.87992095947266
0.88694286346436
0.87246084213257
0.86820316314697
0.88084316253662
Run: ./mougrim-logger.php ./mougrim-logger-like-yii.php
0.60193610191345
0.51603007316589
0.50766611099243
0.4729311466217
0.49772596359253
0.48878312110901
0.49942111968994
0.49234986305237
0.49913692474365
0.50595712661743
Run: ./mougrim-logger.php ./mougrim-logger-like-yii-without-user-info.php
0.40585589408875
0.39485597610474
0.39401602745056
0.38017392158508
0.38001990318298
0.39332008361816
0.3880181312561
0.41817212104797
0.37904906272888
0.38041591644287
Run: ./mougrim-logger.php ./mougrim-logger-simple.php
0.34937214851379
0.32780694961548
0.34946513175964
0.31183004379272
0.32258296012878
0.32765197753906
0.31947994232178
0.32056593894958
0.35075807571411
0.35902500152588
Run: ./mougrim-logger.php ./mougrim-logger-without-format.php
0.2192530632019
0.26069593429565
0.32294011116028
0.20833611488342
0.21739196777344
0.20019006729126
0.21921586990356
0.20600509643555
0.20366716384888
0.20428609848022
```

For remove values outside the statistical accuracy and calculate average used next php-script `process-result.php` (fast written and not optimal):

```php
<?php
$file = new \SplFileObject($argv[1]);
$data = [];
$test = null;
while (!$file->eof()) {
    $row = trim($file->fgets());
    if (!$row) {
        continue;
    }
    if (!is_numeric($row)) {
        $test = $row;
        continue;
    } elseif ($test === null) {
        throw new \RuntimeException("Can't process result file '{$argv[1]}': test not passed");
    }
    $value = (float)$row;

    $data[$test][] = $value;
}

$averageResults = [];
foreach ($data as $test => $results) {
    sort($results);
    $previousResult = null;
    $resultsQty = count($results);
    for ($i = 0; $i < $resultsQty; $i++) {
        foreach ($results as $resultNumber => $result) {
            if ($previousResult === null) {
                $previousResult       = $result;
                $previousResultNumber = $resultNumber;
                continue;
            }
            if ($previousResult / $result < .95) {
                if ($resultNumber / count($results) < .5) {
                    unset($results[$previousResultNumber]);
                    $previousResultNumber = $resultNumber;
                    $previousResult       = $result;
                } else {
                    unset($results[$resultNumber]);
                }
            } else {
                $previousResultNumber = $resultNumber;
                $previousResult       = $result;
            }
        }
    }
    $averageResults[$test] = array_sum($results) / count($results);
    echo "Average result for {$test}:\t{$averageResults[$test]}\n";
}
```

Result:

```text
> php process-result.php result.log
Average result for Run: ./yii-logger.php ./yii-logger-default.php:                            0.31620690822601
Average result for Run: ./yii-logger.php ./yii-logger-force-flush.php:                        0.7636531829834
Average result for Run: ./mougrim-logger.php ./mougrim-logger-format.php:                     0.87758529186249
Average result for Run: ./mougrim-logger.php ./mougrim-logger-like-yii.php:                   0.49777793884277
Average result for Run: ./mougrim-logger.php ./mougrim-logger-like-yii-without-user-info.php: 0.39138970375061
Average result for Run: ./mougrim-logger.php ./mougrim-logger-simple.php:                     0.32165296872457
Average result for Run: ./mougrim-logger.php ./mougrim-logger-without-format.php:             0.20979318022728
```

Histogram:

![Benchmark results](https://github.com/mougrim/yii2-mougrim-logger-performance/blob/master/benchmark.png)

[Benchmark source code](https://github.com/mougrim/yii2-mougrim-logger-performance).

## Conclusions

Attention! Mougrim Logger don't collect log messages, all log messages have chronological order and without memory leaks. 

- `yii-logger-default.php`: this is default Yii2 Logger config with collect log messages in memory, this is fast variant, but isn't chronological and can get memory leaks;
- `yii-logger-force-flush.php`: this is Yii2 Logger config with force flush log messages, this is chronological and without memory leaks and isn't fast;
- `mougrim-logger-format.php`: this is flexible Mougrim Logger config, but isn't fast;
- `mougrim-logger-like-yii.php`: this is like Yii2 Logger format Mougrim Logger config, faster than `yii-logger-force-flush.php`;
- `mougrim-logger-like-yii-without-user-info.php`: this is like `mougrim-logger-like-yii.php` by log data, but has another format and faster than `mougrim-logger-like-yii.php`;
- `mougrim-logger-simple.php`: this is simple format Mougrim Logger config without datetime info, this config has same performance with `yii-logger-default.php`;
- `mougrim-logger-without-format.php`: this is without format Mougrim Logger config, without line brake and another info, this config is faster than `yii-logger-default.php`.

I think, performance difference isn't principled (isn't very big), but Mougrim Logger has flexible configuration.
