<?php

namespace Gettext\Languages\Test;

use Exception;
use Gettext\Languages\Category;

class RulesTest extends TestCase
{
    public function providerTestRules()
    {
        $testData = array();
        foreach (array('php', 'json') as $format) {
            foreach ($this->readData($format) as $locale => $info) {
                foreach ($info['examples'] as $rule => $numbers) {
                    $testData[] = array(
                        $format,
                        $locale,
                        $info['formula'],
                        $info['cases'],
                        $numbers,
                        $rule,
                    );
                }
            }
        }

        return $testData;
    }

    /**
     * @dataProvider providerTestRules
     */
    public function testRules($format, $locale, $formula, $allCases, $numbers, $expectedCase)
    {
        $expectedCaseIndex = in_array($expectedCase, $allCases);
        foreach (Category::expandExamples($numbers) as $number) {
            $numericFormula = preg_replace('/\bn\b/', (string) $number, $formula);
            $extraneousChars = preg_replace('/^[\d %!=<>&\|()?:]+$/', '', $numericFormula);
            $this->assertSame('', $extraneousChars, "The formula '{$numericFormula}' contains extraneous characters: '{$extraneousChars}' (format: {$format})");

            $caseIndex = @eval("return (({$numericFormula}) === true) ? 1 : ((({$numericFormula}) === false) ? 0 : ({$numericFormula}));");
            $caseIndexType = gettype($caseIndex);
            $this->assertSame('integer', $caseIndexType, "Error evaluating the numeric formula '{$numericFormula}' (format: {$format})");

            $this->assertArrayHasKey($caseIndex, $allCases, "The formula '{$formula}' evaluated for {$number} gave an out-of-range case index ({$caseIndex}) (format: {$format})");

            $case = $allCases[$caseIndex];
            $this->assertSame($expectedCase, $case, "The formula '{$formula}' evaluated for {$number} resulted in '{$case}' ({$caseIndex}) instead of '{$expectedCase}' ({$expectedCaseIndex}) (format: {$format})");
        }
    }

    public function providerTestExamplesExist()
    {
        $testData = array();
        foreach (array('php', 'json') as $format) {
            foreach ($this->readData($format) as $locale => $info) {
                foreach ($info['cases'] as $case) {
                    $testData[] = array(
                        $format,
                        $locale,
                        $case,
                        $info['examples'],
                    );
                }
            }
        }

        return $testData;
    }

    /**
     * @dataProvider providerTestExamplesExist
     */
    public function testExamplesExist($format, $locale, $case, $examples)
    {
        $this->assertArrayHasKey($case, $examples, "The language '{$locale}' does not have tests for the case '{$case}' (format: {$format})");
    }

    private function readData($format)
    {
        static $data = array();
        if (!isset($data[$format])) {
            $filename = GETTEXT_LANGUAGES_TESTDIR . '/data.' . $format;
            switch ($format) {
                case 'php':
                    $data[$format] = require $filename;
                    break;
                case 'json':
                    $data[$format] = json_decode(file_get_contents($filename), true);
                    break;
                default:
                    throw new Exception("Unhandled format: {$format}");
            }
        }

        return $data[$format];
    }
}
