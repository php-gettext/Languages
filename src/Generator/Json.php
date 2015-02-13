<?php
namespace Cldr2Gettext\Generator;

class Json extends Generator
{
    /**
     * Return the options for json_encode
     * @return int
     */
    protected static function getEncodeOptions()
    {
        return 0;
    }
    /**
     * @see Generator::toString
     */
    public static function toString($languageConverters)
    {
        return json_encode(self::toArray($languageConverters), static::getEncodeOptions());
    }
}
