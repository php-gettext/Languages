<?php
namespace GettextLanguages\Exporter;

use Exception;

/**
 * Base class for all the exporters
 */
abstract class Exporter
{
    /**
     * @var array
     */
    private static $exporters;
    /**
     * Return the list of all the available exporters. Keys are the exporter handles, values are the exporter class names
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
     * @param string $exporterHandle The exporter class handle
     * @return string
     */
    final public static function getExporterClassName($exporterHandle)
    {
        return __NAMESPACE__.'\\'.ucfirst(strtolower($exporterHandle));
    }
    /**
     * Convert a list of LanguageConverter instances to string.
     * @param LanguageConverter[] $languageConverters The LanguageConverter instances to convert
     * @return string
     */
    public static function toString($languageConverters)
    {
        throw new Exception(get_class().' does not implement the method '.__FUNCTION__);
    }
    /**
     * Save the LanguageConverter instances to a file.
     * @param LanguageConverter[] $languageConverters The LanguageConverter instances to convert
     * @throws Exception
     */
    final public static function toFile($languageConverters, $filename)
    {
        $data = static::toString($languageConverters);
        if (@file_put_contents($filename, $data) === false) {
            throw new Exception("Error writing data to '$filename'");
        }
    }
    /**
     * Convert a list of LanguageConverter instances to a standard php array
     * @param LanguageConverter[] $languageConverters
     * @return array
     */
    final protected static function toArray($languageConverters)
    {
        $result = array();
        foreach ($languageConverters as $languageConverter) {
            $array = array(
                'name' => $languageConverter->name,
                'plurals' => count($languageConverter->categories),
                'formula' => $languageConverter->formula,
                'cases' => array(),
                'examples' => array(),
            );
            foreach ($languageConverter->categories as $category) {
                $array['cases'][] = $category->id;
                $array['examples'][$category->id] = $category->examples;
            }
            if (isset($languageConverter->supersededBy)) {
                $array['supersededBy'] = $languageConverter->supersededBy;
            }
            $result[$languageConverter->languageId] = $array;
        }

        return $result;
    }
}
