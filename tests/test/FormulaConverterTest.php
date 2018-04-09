<?php

namespace Gettext\Languages\Test;

use Gettext\Languages\FormulaConverter;

class FormulaConverterTest extends TestCase
{
    public function testConvertFormulaWithInvalidFormula()
    {
        $this->setExpectedException('\Exception');
        FormulaConverter::convertFormula('()');
    }

    public function testConvertAtomWithInvalidFormulaChunk()
    {
        $this->setExpectedException('\Exception');
        FormulaConverter::convertFormula('f ==== empty');
    }
}
