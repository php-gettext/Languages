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
    /**
     * @see Exporter::getDescription
     */
    public static function getDescription()
    {
        return 'Build an uncompressed JSON-encoded file';
    }
}
