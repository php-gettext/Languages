<?php
namespace GettextLanguages\Generator;

use Exception;

/**
 * Base class for all the generators
 */
abstract class Generator
{
    /**
     * @var array
     */
    private static $generators;
    /**
     * Return the list of all the available generators. Keys are the generator handles, values are the generator class names
     * @return string[]
     */
    final public static function getGenerators()
    {
        if (!isset(self::$generators)) {
            $generators = array();
            $m = null;
            foreach (scandir(__DIR__) as $f) {
                if (preg_match('/^(\w+)\.php$/', $f, $m)) {
                    if ($f !== basename(__FILE__)) {
                        $generators[strtolower($m[1])] = $m[1];
                    }
                }
            }
            self::$generators = $generators;
        }

        return self::$generators;
    }
    /**
     * Returns the fully qualified class name of a generator given its handle.
     * @param string $generatorHandle The generator class handle
     * @return string
     */
    final public static function getGeneratorClassName($generatorHandle)
    {
        return __NAMESPACE__.'\\'.ucfirst(strtolower($generatorHandle));
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
