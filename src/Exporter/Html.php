<?php
namespace GettextLanguages\Exporter;

class Html extends Exporter
{
    /**
     * @see Exporter::toString
     */
    public static function toString($languageConverters)
    {
        return self::buildTable($languageConverters, false);
    }
    protected static function h($str)
    {
        return htmlspecialchars($str, ENT_COMPAT, 'UTF-8');
    }
    protected static function buildTable($languageConverters, $forDocs)
    {
        $prefix = $forDocs ? '            ' : '';
        $lines = array();
        $lines[] = $prefix.'<table'.($forDocs ? ' class="table table-bordered table-condensed table-striped"' : '').'>';
        $lines[] = $prefix.'    <thead>';
        $lines[] = $prefix.'        <tr>';
        $lines[] = $prefix.'            <th>Language code</th>';
        $lines[] = $prefix.'            <th>Language name</th>';
        $lines[] = $prefix.'            <th># plurals</th>';
        $lines[] = $prefix.'            <th>Formula</th>';
        $lines[] = $prefix.'            <th>Plurals</th>';
        $lines[] = $prefix.'        </tr>';
        $lines[] = $prefix.'    </thead>';
        $lines[] = $prefix.'    <tbody>';
        foreach ($languageConverters as $lc) {
            $lines[] = $prefix.'        <tr>';
            $lines[] = $prefix.'            <td>'.$lc->languageId.'</td>';
            $name = self::h($lc->name);
            if (isset($lc->supersededBy)) {
                $name .= '<br /><small><span>Superseded by</span> '.$lc->supersededBy.'</small>';
            }
            $lines[] = $prefix.'            <td>'.$name.'</td>';
            $lines[] = $prefix.'            <td>'.count($lc->categories).'</td>';
            $lines[] = $prefix.'            <td>'.self::h($lc->formula).'</td>';
            $cases = array();
            foreach ($lc->categories as $c) {
                $cases[] = '<li><span>'.$c->id.'</span><code>'.self::h($c->examples).'</code></li>';
            }
            $lines[] = $prefix.'            <td><ol'.($forDocs ? ' class="cases"' : '').' start="0">'.implode('', $cases).'</ol></td>';
            $lines[] = $prefix.'        </tr>';
        }
        $lines[] = $prefix.'    </tbody>';
        $lines[] = $prefix.'</table>';

        return implode("\n", $lines);
    }
}
