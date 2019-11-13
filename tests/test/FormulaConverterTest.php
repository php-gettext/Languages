<?php

namespace Gettext\Languages\Test;

use Gettext\Languages\FormulaConverter;

class FormulaConverterTest extends TestCase
{
    public function testConvertFormulaWithInvalidFormula()
    {
        $this->isGoingToThrowException('\Exception');
        FormulaConverter::convertFormula('()');
    }

    public function testConvertAtomWithInvalidFormulaChunk()
    {
        $this->isGoingToThrowException('\Exception');
        FormulaConverter::convertFormula('f ==== empty');
    }
}
