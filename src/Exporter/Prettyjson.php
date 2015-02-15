<?php
namespace Gettext\Languages\Exporter;

class Prettyjson extends Json
{
    /**
     * @see Json::getEncodeOptions
     */
    protected static function getEncodeOptions()
    {
        return JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
    }
}
