<?php
namespace GettextLanguages;

use Exception;

/**
 * Holds the CLDR data.
 */
class CldrData
{
    /**
     * Super-special plural category: this should always be present for any language
     * @var string
     */
    const OTHER_CATEGORY = 'other';
    /**
     * The list of the plural categories, sorted from 'zero' to 'other'.
     * @var string[]
     */
    public static $categories = array('zero', 'one', 'two', 'few', 'many', self::OTHER_CATEGORY);
    /**
     * @var string[]
     */
    private static $languageNames;
    /**
     * Returns a dictionary containing the language names.
     * The keys are the language identifiers.
     * The values are the language names in US English.
     * @return string[]
     */
    public static function getLanguageNames()
    {
        if (!isset(self::$languageNames)) {
            $json = json_decode(file_get_contents(__DIR__.'/cldr-data/main/en-US/languages.json'), true);
            self::$languageNames = $json['main']['en-US']['localeDisplayNames']['languages'];
        }

        return self::$languageNames;
    }
    /**
     * @var string[]
     */
    private static $territoryNames;
    /**
     * Return a dictionary containing the territory names (in US English).
     * The keys are the territory identifiers.
     * The values are the territory names in US English.
     * @return string[]
     */
    public static function getTerritoryNames()
    {
        if (!isset(self::$territoryNames)) {
            $json = json_decode(file_get_contents(__DIR__.'/cldr-data/main/en-US/territories.json'), true);
            self::$territoryNames = $json['main']['en-US']['localeDisplayNames']['territories'];
        }

        return self::$territoryNames;
    }
    /**
     * @var array
     */
    private static $plurals;
    /**
     * A dictionary containing the plural rules.
     * The keys are the language identifiers.
     * The values are arrays whose keys are the CLDR category names and the values are the CLDR category definition.
     * @example The English key-value pair is somethink like this:
     * <code><pre>
     * "en": {
     *     "pluralRule-count-one": "i = 1 and v = 0 @integer 1",
     *     "pluralRule-count-other": " @integer 0, 2~16, 100, 1000, 10000, 100000, 1000000, … @decimal 0.0~1.5, 10.0, 100.0, 1000.0, 10000.0, 100000.0, 1000000.0, …"
     * }
     * </pre></code>
     * @var array
     */
    public static function getPlurals()
    {
        if (!isset(self::$plurals)) {
            $json = json_decode(file_get_contents(__DIR__.'/cldr-data/supplemental/plurals.json'), true);
            self::$plurals = $json['supplemental']['plurals-type-cardinal'];
        }

        return self::$plurals;
    }
    /**
     * Retrieve the name of a language, as well as if a language code is deprecated in favor of another language code.
     * @param string $fullId The CLDR language identifier.
     * @throws Exception Throws an Exception if $fullId is not valid.
     * @return array Returns an array with the keys 'name' and 'supersededBy'.
     */
    public static function getLanguageInfo($fullId)
    {
        $result = array(
            'name' => null,
            'supersededBy' => null,
        );
        $matches = array();
        if (!preg_match('/^([a-z]{2,3})(?:-([A-Z][a-z]{3}))?(?:-([A-Z]{2}|[0-9]{3}))?(?:$|-)/', $fullId, $matches)) {
            throw new Exception("Unknown CLDR language code: $fullId");
        }
        $languageId = $matches[1];
        // $matches[2] is the script id, we don't use it
        $territoryId = isset($matches[3]) ? $matches[3] : null;
        $normalizedFullId = isset($territoryId) ? "{$languageId}-{$territoryId}" : $languageId;
        $languageNames = self::getLanguageNames();
        if (isset($languageNames[$normalizedFullId])) {
            $result['name'] = $languageNames[$normalizedFullId];
        } elseif (isset($languageNames[$languageId])) {
            $result['name'] = $languageNames[$languageId];
            if (isset($territoryId)) {
                $territoryNames = self::getTerritoryNames();
                if (!isset($territoryNames[$territoryId])) {
                    throw new Exception("Unknown territory code '$territoryId' in language '$fullId'");
                }
                $result['name'] .= ' ('.$territoryNames[$territoryId].')';
            }
        } else {
            // The CLDR plural rules contains some language that's not defined in the language names dictionary...
            $formerCodes = array(
                'in' => 'id', // former Indonesian
                'iw' => 'he', // former Hebrew
                'ji' => 'yi', // former Yiddish
                'jw' => 'jv', // former Javanese
                'mo' => 'ro-MD', // former Moldavian
            );
            if (isset($formerCodes[$normalizedFullId]) && isset($languageNames[$formerCodes[$normalizedFullId]])) {
                $result['supersededBy'] = str_replace('-', '_', $formerCodes[$normalizedFullId]);
                $result['name'] = $languageNames[$formerCodes[$normalizedFullId]];
            } else {
                switch ($normalizedFullId) {
                    case 'bh':
                        $result['name'] = 'Bihari';
                        break;
                    case 'guw':
                        $result['name'] = 'Gun';
                        break;
                    case 'nah':
                        $result['name'] = 'Nahuatl';
                        break;
                    case 'smi':
                        $result['name'] = 'Sami';
                        break;
                    default:
                        throw new Exception("Unknown CLDR language code: $fullId");
                }
            }
        }

        return $result;
    }
}
