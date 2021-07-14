<?php

namespace Gettext\Languages\Test;

use Gettext\Languages\Exporter\Php;
use Gettext\Languages\Language;

class USAsciiTest extends TestCase
{
    public function testExportUSAscii()
    {
        $array = $this->getExportedPhpArray();
        foreach ($array as $localeID => $localeData) {
            $this->assertUSAscii($localeID, $localeData);
        }
    }

    /**
     * @param string $key
     */
    private function assertUSAscii($key, $value)
    {
        switch (gettype($value)) {
            case 'string':
                $this->assertSame(1, preg_match('/^[\x20-\x7F\n]*$/s', $value), "The string at {$key} does not contain only US-ASCII characters: {$value}");
                break;
            case 'array':
                foreach ($value as $valueKey => $valueValue) {
                    $this->assertUSAscii("{$key}.{$valueKey}", $valueValue);
                }
                break;
        }
    }

    /**
     * @return array
     */
    private function getExportedPhpArray()
    {
        $phpCode = Php::toString(Language::getAll(), array('us-ascii' => true));

        return eval(preg_replace('/^<\?php\n/', '', $phpCode));
    }
}
