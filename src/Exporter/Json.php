<?php
namespace GettextLanguages\Exporter;

class Json extends Exporter
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
     * @see Exporter::toString
     */
    public static function toString($languageConverters)
    {
        return json_encode(self::toArray($languageConverters), static::getEncodeOptions());
    }
}
