<?php
/**
 * Created by PhpStorm.
 * User: Pash
 * Date: 22.01.2016
 * Time: 18:06
 */

namespace MiniForm;


class Lang
{
    private static $translations;
    private static $lang = 'en';

    public static function setLanguage($value)
    {
        self::$lang = $value;
        self::$translations = null;
    }
    private static function loadTranslations()
    {
        if (self::$translations === null) {
            $path = __DIR__ . DIRECTORY_SEPARATOR . 'i18n';
            $file = $path . DIRECTORY_SEPARATOR . self::$lang;
            if (is_readable($file) && is_file($file)) {
                self::$translations = include($file);
            }
        }
    }

    public static function trans($text)
    {
        self::loadTranslations();
        $trans =  isset(self::$translations[$text]) ? self::$translations[$text] : $text;
        return sprintf($trans, func_get_args());
    }
}