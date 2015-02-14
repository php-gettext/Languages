<?php
namespace GettextLanguages\Exporter;

class Xml extends Exporter
{
    /**
     * @see Exporter::toStringDo
     */
    protected static function toStringDo($languages)
    {
        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->loadXML('<languages
            xmlns="https://github.com/mlocati/cldr-to-gettext-plural-rules"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="https://github.com/mlocati/cldr-to-gettext-plural-rules http://mlocati.github.io/cldr-to-gettext-plural-rules/GettextLanguages.xsd"
        />');
        $xLanguages = $xml->firstChild;
        foreach ($languages as $language) {
            $xLanguage = $xml->createElement('language');
            $xLanguage->setAttribute('id', $language->id);
            $xLanguage->setAttribute('name', $language->name);
            if (isset($language->supersededBy)) {
                $xLanguage->setAttribute('supersededBy', $language->supersededBy);
            }
            $xLanguage->setAttribute('formula', $language->formula);
            foreach ($language->categories as $category) {
                $xCategory = $xml->createElement('category');
                $xCategory->setAttribute('id', $category->id);
                $xCategory->setAttribute('examples', $category->examples);
                $xLanguage->appendChild($xCategory);
            }
            $xLanguages->appendChild($xLanguage);
        }
        $xml->formatOutput = true;

        return $xml->saveXML();
    }
}
