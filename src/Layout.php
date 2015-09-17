<?php
namespace mougrim\yii2Logger;

use Mougrim\Logger\Layout\LayoutInterface;
use Mougrim\Logger\Logger as MougrimLogger;
use Mougrim\Logger\LoggerMDC;
use Mougrim\Logger\LoggerNDC;
use Mougrim\Logger\LoggerRender;

/**
 * @package mougrim\yii2Logger
 * @author  Mougrim <rinat@mougrim.ru>
 * Yii logger default format layout.
 * Format like as "datetime [ip][userId][sessionId][INFO][category][ndc_message][mdc=context] message exception"
 * or like as "datetime [INFO][category][ndc_message][mdc=context] message exception"
 * it depends on "logUserInfo" config property
 */
class Layout implements LayoutInterface
{
    private $logUserInfo = true;

    public function setLogUserInfo($isLogUserInfo)
    {
        $this->logUserInfo = (boolean)$isLogUserInfo;
    }

    public function formatMessage(MougrimLogger $logger, $level, $message, \Exception $throwable = null)
    {
        $prefix = '';
        $mdc = LoggerMDC::getMap();
        if ($this->logUserInfo) {
            foreach (['ip', 'userId', 'sessionId'] as $key) {
                $context = isset($mdc[$key]) ? $mdc[$key] : null;
                $context = LoggerRender::render($context);
                $prefix .= '[' . $context . ']';
                unset($mdc[$key]);
            }
        }

        $level = MougrimLogger::getLevelName($level);
        $formatted = date('Y-m-d H:i:s') . ' ' . $prefix . '[' . $level . '][' . $logger->getName() . ']';

        $ndcString = '';
        if ($ndcStack = LoggerNDC::getStack()) {
            $ndcString = implode(' ', $ndcStack);
        }
        $formatted .= '[' . $ndcString . ']';

        $mdcString = '';
        foreach ($mdc as $key => $context) {
            $mdcString .= $key . '=' . $context . ' ';
        }
        $formatted .= '[' . trim($mdcString) . ']';

        $formatted .= ' ' . LoggerRender::render($message);
        if ($throwable) {
            $formatted .= ' ' . LoggerRender::render($throwable);
        }
        return $formatted . PHP_EOL;
    }
}
