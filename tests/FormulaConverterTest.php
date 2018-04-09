<?php
use PHPUnit\Framework\TestCase;

class FormulaConverterTest extends TestCase
{
    public function testConvertFormulaWithInvalidFormula()
    {
        $this->setExpectedException('\Exception');
        \Gettext\Languages\FormulaConverter::convertFormula('()');
    }

    public function testConvertAtomWithInvalidFormulaChunk()
    {
        $this->setExpectedException('\Exception');
        \Gettext\Languages\FormulaConverter::convertFormula('f ==== empty');
    }
}
