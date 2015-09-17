<?php
namespace mougrim\yii2Logger;

/**
 * @package mougrim\yii2Logger
 * @author  Mougrim <rinat@mougrim.ru>
 */
class LayoutTestException extends \Exception
{
    public function __toString()
    {
        /** @noinspection MagicMethodsValidityInspection */
        return static::class;
    }
}
