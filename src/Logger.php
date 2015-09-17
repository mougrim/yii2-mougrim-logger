<?php
namespace mougrim\yii2Logger;

use Mougrim\Logger\Logger as MougrimLogger;
use yii\log\Logger as YiiLogger;

/**
 * @package mougrim\yii2Logger
 * @author Mougrim <rinat@mougrim.ru>
 */
class Logger extends YiiLogger
{
    private $alwaysYiiLoggerLog = false;

    /**
     * @return boolean
     */
    public function getAlwaysYiiLoggerLog()
    {
        return $this->alwaysYiiLoggerLog;
    }

    /**
     * @param boolean $alwaysYiiLoggerLog
     */
    public function setAlwaysYiiLoggerLog($alwaysYiiLoggerLog)
    {
        $this->alwaysYiiLoggerLog = (boolean)$alwaysYiiLoggerLog;
    }

    public function log($message, $level, $category = 'application')
    {
        $mougrimLoggerLevel = $this->getMougrimPhpLoggerLevel($level);
        if ($mougrimLoggerLevel !== null) {
            $this->getMougrimLogger($category)->log($mougrimLoggerLevel, $message);
            if (!$this->getAlwaysYiiLoggerLog()) {
                return;
            }
        }
        parent::log($message, $level, $category);
    }

    public function getMougrimPhpLoggerLevel($yiiLevel)
    {
        static $levels = [
            self::LEVEL_ERROR => MougrimLogger::ERROR,
            self::LEVEL_WARNING => MougrimLogger::WARN,
            self::LEVEL_INFO => MougrimLogger::INFO,
            self::LEVEL_TRACE => MougrimLogger::TRACE,
        ];

        return isset($levels[$yiiLevel]) ? $levels[$yiiLevel] : null;
    }

    protected function getMougrimLogger($category)
    {
        return MougrimLogger::getLogger($category);
    }
}
