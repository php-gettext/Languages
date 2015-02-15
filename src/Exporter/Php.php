<?php
namespace Gettext\Languages\Exporter;

class Php extends Exporter
{
    /**
     * @see Exporter::toStringDo
     */
    protected static function toStringDo($languages)
    {
        $lines = array();
        $lines[] = '<?php';
        $lines[] = 'return array(';
        foreach ($languages as $lc) {
            $lines[] = '    \''.$lc->id.'\' => array(';
            $lines[] = '        \'name\' => \''.addslashes($lc->name).'\',';
            $lines[] = '        \'plurals\' => '.count($lc->categories).',';
            $lines[] = '        \'formula\' => \''.$lc->formula.'\',';
            $catNames = array();
            foreach ($lc->categories as $c) {
                $catNames[] = "'{$c->id}'";
            }
            $lines[] = '        \'cases\' => array('.implode(', ', $catNames).'),';
            $lines[] = '        \'examples\' => array(';
            foreach ($lc->categories as $c) {
                $lines[] = '            \''.$c->id.'\' => \''.$c->examples.'\',';
            }
            $lines[] = '        ),';
            if (isset($lc->supersededBy)) {
                $lines[] = '        \'supersededBy\' => \''.$lc->supersededBy.'\',';
            }
            $lines[] = '    ),';
        }
        $lines[] = ');';
        $lines[] = '';

        return implode("\n", $lines);
    }
}
