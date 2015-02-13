<?php
namespace Cldr2Gettext;

use Exception;

/**
 * A helper class that handles a single category rules (eg 'zero', 'one', ...) and its formula and examples.
 */
class CategoryConverter
{
    /**
     * The category identifier (eg 'zero', 'one', ..., 'other').
     * @var string
     */
    public $id;
    /**
     * The gettext formula that identifies this category (null if and only if the category is 'other')
     * @var string|null
     */
    public $formula;
    /**
     * The CLDR representation of some exemplar numeric ranges that satisfy this category
     * @var string|null
     */
    public $examples;
    /**
     * Initialize the instance and parse the formula.
     * @param string $cldrCategoryId The CLDR category identifier (eg 'pluralRule-count-one').
     * @param string $cldrFormulaAndExamples The CLDR formula and examples (eg 'i = 1 and v = 0 @integer 1').
     * @throws Exception
     */
    public function __construct($cldrCategoryId, $cldrFormulaAndExamples)
    {
        $matches = array();
        if (!preg_match('/^pluralRule-count-(.+)$/', $cldrCategoryId, $matches)) {
            throw new Exception("Invalid CLDR category: '$cldrCategoryId'");
        }
        if (!in_array($matches[1], CldrData::$categories)) {
            throw new Exception("Invalid CLDR category: '$cldrCategoryId'");
        }
        $this->id = $matches[1];
        $cldrFormulaAndExamplesNormalized = trim(preg_replace('/\s+/', ' ', $cldrFormulaAndExamples));
        if (!preg_match('/^([^@]*)(?:@integer([^@]+))?(?:@decimal(?:[^@]+))?$/', $cldrFormulaAndExamplesNormalized, $matches)) {
            throw new Exception("Invalid CLDR category rule: $cldrFormulaAndExamples");
        }
        $cldrFormula = trim($matches[1]);
        $s = isset($matches[2]) ? trim($matches[2]) : '';
        $this->examples = ($s === '') ? null : $s;
        switch ($this->id) {
            case CldrData::OTHER_CATEGORY:
                if ($cldrFormula !== '') {
                    throw new Exception("The '".CldrData::OTHER_CATEGORY."' category should not have any formula, but it has '$cldrFormula'");
                }
                $this->formula = null;
                break;
            default:
                if ($cldrFormula === '') {
                    throw new Exception("The '{$this->id}' category does not have a formula");
                }
                $this->formula = FormulaConverter::convertFormula($cldrFormula);
                break;
        }
    }
}
