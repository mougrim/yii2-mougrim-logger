<?php
namespace mougrim\yii2Logger;

use Mougrim\Logger\Logger as MougrimLogger;

/**
 * @package mougrim\yii2Logger
 * @author  Mougrim <rinat@mougrim.ru>
 */
class LoggerTest extends \PHPUnit_Framework_TestCase
{
    public function dataProviderLog()
    {
        return [
            'Error' => [
                'yiiLevel' => Logger::LEVEL_ERROR,
                'mougrimLoggerLevel' => MougrimLogger::ERROR,
                'alwaysYiiLoggerLog' => false,
                'resultMessagesInLogger' => [],
            ],
            'Warning' => [
                'yiiLevel' => Logger::LEVEL_WARNING,
                'mougrimLoggerLevel' => MougrimLogger::WARN,
                'alwaysYiiLoggerLog' => false,
                'resultMessagesInLogger' => [],
            ],
            'Info' => [
                'yiiLevel' => Logger::LEVEL_INFO,
                'mougrimLoggerLevel' => MougrimLogger::INFO,
                'alwaysYiiLoggerLog' => false,
                'resultMessagesInLogger' => [],
            ],
            'Trace' => [
                'yiiLevel' => Logger::LEVEL_TRACE,
                'mougrimLoggerLevel' => MougrimLogger::TRACE,
                'alwaysYiiLoggerLog' => false,
                'resultMessagesInLogger' => [],
            ],
            'Unknown' => [
                'yiiLevel' => Logger::LEVEL_PROFILE,
                'mougrimLoggerLevel' => null,
                'alwaysYiiLoggerLog' => false,
                'resultMessagesInLogger' => [
                    [
                        'message',
                        Logger::LEVEL_PROFILE,
                        'category',
                    ],
                ],
            ],
            'Always Yii Logger log' => [
                'yiiLevel' => Logger::LEVEL_INFO,
                'mougrimLoggerLevel' => MougrimLogger::INFO,
                'alwaysYiiLoggerLog' => true,
                'resultMessagesInLogger' => [
                    [
                        'message',
                        Logger::LEVEL_INFO,
                        'category',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderLog
     *
     * @param integer $yiiLevel
     * @param integer $mougrimLoggerLevel
     * @param boolean $alwaysYiiLoggerLog
     * @param array $resultMessagesInLogger
     */
    public function testLog($yiiLevel, $mougrimLoggerLevel, $alwaysYiiLoggerLog, array $resultMessagesInLogger)
    {
        $mougrimLogger = $this->getMockBuilder(MougrimLogger::class)
            ->disableOriginalConstructor()
            ->getMock();
        if ($mougrimLoggerLevel !== null) {
            $mougrimLogger->expects(static::once())
                ->method('log')
                ->with($mougrimLoggerLevel, 'message');
        } else {
            $mougrimLogger->expects(static::never())
                ->method('log');
        }
        /** @var \PHPUnit_Framework_MockObject_MockObject|Logger $logger */
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMougrimLogger'])
            ->getMock();
        $logger->expects(static::any())
            ->method('getMougrimLogger')
            ->with('category')
            ->will(static::returnValue($mougrimLogger));
        $logger->setAlwaysYiiLoggerLog($alwaysYiiLoggerLog);
        $logger->flushInterval = 1000;
        $logger->log('message', $yiiLevel, 'category');
        $messagesInLogger = $logger->messages;
        // remove dynamic lines
        foreach ($messagesInLogger as &$messageInLogger) {
            unset($messageInLogger[3], $messageInLogger[4]);
        }
        unset($messageInLogger);
        static::assertEquals(
            $resultMessagesInLogger,
            $messagesInLogger
        );
    }
}
