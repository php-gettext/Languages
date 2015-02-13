<?php
namespace GettextLanguages\Exporter;

class Json extends Exporter
{
    /**
     * Return the options for json_encode.
     * @return int
     */
    protected static function getEncodeOptions()
    {
        return 0;
    }
    /**
     * @see Exporter::toStringDo
     */
    protected static function toStringDo($languages)
    {
        return json_encode(self::toArray($languages), static::getEncodeOptions());
    }
}
