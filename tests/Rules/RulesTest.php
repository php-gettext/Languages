<?php
class RulesTest extends PHPUnit_Framework_TestCase
{
    private function readData()
    {
        static $data;
        if (!isset($data)) {
            $data = require dirname(dirname(__FILE__)).'/data.php';
        }

        return $data;
    }

    private static function expandNumbers($numbers)
    {
        $result = array();
        if (substr($numbers, -strlen(', …')) === ', …') {
            $numbers = substr($numbers, 0, strlen($numbers) -strlen(', …'));
        }
        foreach (explode(',', str_replace(' ', '', $numbers)) as $range) {
            if (preg_match('/^\d+$/', $range)) {
                $result[] = intval($range);
            } elseif (preg_match('/^(\d+)~(\d+)$/', $range, $m)) {
                $from = intval($m[1]);
                $to = intval($m[2]);
                $delta = $to - $from;
                $step = (int) max(1, $delta / 100);
                for ($i = $from; $i < $to; $i += $step) {
                    $result[] = $i;
                }
                $result[] = $to;
            } else {
                throw new Exception("Unhandled test range '$range' in '$numbers'");
            }
        }
        if (empty($result)) {
            throw new Exception("No test numbers from '$numbers'");
        }

        return $result;
    }

    public function providerTestRules()
    {
        $testData = array();
        foreach ($this->readData() as $locale => $info) {
            foreach ($info['examples'] as $rule => $numbers) {
                $testData[] = array(
                    $locale,
                    $info['formula'],
                    $info['cases'],
                    $numbers,
                    $rule,
                );
            }
        }

        return $testData;
    }
    /**
     * @dataProvider providerTestRules
     */
    public function testRules($locale, $formula, $allCases, $numbers, $expectedCase)
    {
        $expectedCaseIndex = in_array($expectedCase, $allCases);
        foreach (self::expandNumbers($numbers) as $number) {
            $numericFormula = preg_replace('/\bn\b/', strval($number), $formula);
            $extraneousChars = preg_replace('/^[\d %!=<>&\|()?:]+$/', '', $numericFormula);
            $this->assertSame('', $extraneousChars, "The formula '$numericFormula' contains extraneous characters: '$extraneousChars'");

            $caseIndex = @eval("return (($numericFormula) === true) ? 1 : ((($numericFormula) === false) ? 0 : ($numericFormula));");
            $this->assertInternalType('integer', $caseIndex, "Error evaluating the numeric formula '$numericFormula'");

            $this->assertArrayHasKey($caseIndex, $allCases, "The formula '$formula' evaluated for $number gave an out-of-range case index ($caseIndex)");

            $case = $allCases[$caseIndex];
            $this->assertSame($expectedCase, $case, "The formula '$formula' evaluated for $number resulted in '$case' ($caseIndex) instead of '$expectedCase' ($expectedCaseIndex)");
        }
    }

    public function providerTestExamplesExist()
    {
        $testData = array();
        foreach ($this->readData() as $locale => $info) {
            foreach ($info['cases'] as $case) {
                $testData[] = array(
                    $locale,
                    $case,
                    $info['examples'],
                );
            }
        }

        return $testData;
    }
    /**
     * @dataProvider providerTestExamplesExist
     */
    public function testExamplesExist($locale, $case, $examples)
    {
        $this->assertArrayHasKey($case, $examples, "The language '$locale' does not have tests for the case '$case'");
    }
}
