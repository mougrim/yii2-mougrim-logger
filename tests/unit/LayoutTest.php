<?php
namespace mougrim\yii2Logger;

use Mougrim\Logger\Logger as MougrimLogger;
use Mougrim\Logger\LoggerMDC;
use Mougrim\Logger\LoggerNDC;

/**
 * @package mougrim\yii2Logger
 * @author  Mougrim <rinat@mougrim.ru>
 */
class LayoutTest extends \PHPUnit_Framework_TestCase
{
    public function dataProviderFormatMessage()
    {
        return [
            'Log user info, empty mdc and ndc' => [
                'isLogUserInfo' => true,
                'mdc' => [],
                'ndc' => [],
                'result' => "datetime [null][null][null][INFO][name][][] message mougrim\\yii2Logger\\LayoutTestException" . PHP_EOL,
            ],
            "Don't log user info, empty mdc and ndc" => [
                'isLogUserInfo' => false,
                'mdc' => [],
                'ndc' => [],
                'result' => "datetime [INFO][name][][] message mougrim\\yii2Logger\\LayoutTestException" . PHP_EOL,
            ],
            'Log user info, user info exists' => [
                'isLogUserInfo' => true,
                'mdc' => [
                    'ip' => '127.0.0.1',
                    'userId' => 1,
                    'sessionId' => 'sessionId',
                ],
                'ndc' => [],
                'result' => "datetime [127.0.0.1][1][sessionId][INFO][name][][] message mougrim\\yii2Logger\\LayoutTestException" . PHP_EOL,
            ],
            "Don't log user info, user info exists" => [
                'isLogUserInfo' => false,
                'mdc' => [
                    'ip' => '127.0.0.1',
                    'userId' => 1,
                    'sessionId' => 'sessionId',
                ],
                'ndc' => [],
                'result' => "datetime [INFO][name][][ip=127.0.0.1 userId=1 sessionId=sessionId] message mougrim\\yii2Logger\\LayoutTestException" . PHP_EOL,
            ],
            'Log user info, additional mdc context and ndc context' => [
                'isLogUserInfo' => true,
                'mdc' => [
                    'ip' => '127.0.0.1',
                    'userId' => 1,
                    'sessionId' => 'sessionId',
                    'someVariable' => 'someValue',
                ],
                'ndc' => [
                    'ndcContext',
                ],
                'result' => "datetime [127.0.0.1][1][sessionId][INFO][name][ndcContext][someVariable=someValue] message mougrim\\yii2Logger\\LayoutTestException" . PHP_EOL,
            ],
            "Don't log user info, additional mdc context and ndc context" => [
                'isLogUserInfo' => false,
                'mdc' => [
                    'ip' => '127.0.0.1',
                    'userId' => 1,
                    'sessionId' => 'sessionId',
                    'someVariable' => 'someValue',
                ],
                'ndc' => [
                    'ndcContext',
                ],
                'result' => "datetime [INFO][name][ndcContext][ip=127.0.0.1 userId=1 sessionId=sessionId someVariable=someValue] message mougrim\\yii2Logger\\LayoutTestException" . PHP_EOL,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderFormatMessage
     *
     * @param boolean $isLogUserInfo
     * @param array $mdc
     * @param array $ndc
     * @param string $result
     */
    public function testFormatMessage($isLogUserInfo, array $mdc, array $ndc, $result)
    {
        LoggerMDC::clear();
        LoggerNDC::clear();
        foreach ($mdc as $key => $context) {
            LoggerMDC::put($key, $context);
        }
        foreach ($ndc as $context) {
            LoggerNDC::push($context);
        }
        /** @var \PHPUnit_Framework_MockObject_MockObject|MougrimLogger $logger */
        $logger = $this->getMockBuilder(MougrimLogger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $logger->expects(static::any())
            ->method('getName')
            ->will(static::returnValue('name'));
        $layout = new Layout();
        $layout->setLogUserInfo($isLogUserInfo);
        $message = $layout->formatMessage($logger, MougrimLogger::INFO, 'message', new LayoutTestException());
        $datePrefixPattern = '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} /';
        static::assertRegExp($datePrefixPattern, $message);
        $message = preg_replace($datePrefixPattern, 'datetime ', $message);
        static::assertSame(
            $result,
            $message
        );
    }
}
