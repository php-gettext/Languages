<?php
namespace Gettext\Languages\Exporter;

use Exception;
use Gettext\Languages\Language;

/**
 * Base class for all the exporters.
 */
abstract class Exporter
{
    /**
     * @var array
     */
    private static $exporters;
    /**
     * Return the list of all the available exporters. Keys are the exporter handles, values are the exporter class names.
     * @return string[]
     */
    final public static function getExporters()
    {
        if (!isset(self::$exporters)) {
            $exporters = array();
            $m = null;
            foreach (scandir(__DIR__) as $f) {
                if (preg_match('/^(\w+)\.php$/', $f, $m)) {
                    if ($f !== basename(__FILE__)) {
                        $exporters[strtolower($m[1])] = $m[1];
                    }
                }
            }
            self::$exporters = $exporters;
        }

        return self::$exporters;
    }
    /**
     * Returns the fully qualified class name of a exporter given its handle.
     * @param string $exporterHandle The exporter class handle.
     * @return string
     */
    final public static function getExporterClassName($exporterHandle)
    {
        return __NAMESPACE__.'\\'.ucfirst(strtolower($exporterHandle));
    }
    /**
     * Convert a list of Language instances to string.
     * @param Language[] $languages The Language instances to convert.
     * @return string
     */
    protected static function toStringDo($languages)
    {
        throw new Exception(get_class().' does not implement the method '.__FUNCTION__);
    }
    /**
     * Convert a list of Language instances to string.
     * @param Language[] $languages The Language instances to convert.
     * @return string
     */
    final public static function toString($languages, $options = null)
    {
        if (isset($options) && is_array($options)) {
            if (isset($options['us-ascii']) && $options['us-ascii']) {
                $asciiList = array();
                foreach ($languages as $language) {
                    $asciiList[] = $language->getUSAsciiClone();
                }
                $languages = $asciiList;
            }
        }

        return static::toStringDo($languages);
    }
    /**
     * Save the Language instances to a file.
     * @param Language[] $languages The Language instances to convert.
     * @throws Exception
     */
    final public static function toFile($languages, $filename, $options = null)
    {
        $data = self::toString($languages, $options);
        if (@file_put_contents($filename, $data) === false) {
            throw new Exception("Error writing data to '$filename'");
        }
    }
    /**
     * Convert a list of Language instances to a standard php array.
     * @param Language[] $languages
     * @return array
     */
    final protected static function toArray($languages)
    {
        $result = array();
        foreach ($languages as $language) {
            $array = array(
                'name' => $language->name,
                'plurals' => count($language->categories),
                'formula' => $language->formula,
                'cases' => array(),
                'examples' => array(),
            );
            foreach ($language->categories as $category) {
                $array['cases'][] = $category->id;
                $array['examples'][$category->id] = $category->examples;
            }
            if (isset($language->supersededBy)) {
                $array['supersededBy'] = $language->supersededBy;
            }
            $result[$language->id] = $array;
        }

        return $result;
    }
}
