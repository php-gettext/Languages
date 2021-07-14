<?php

namespace Gettext\Languages\Test;

class ExecutableFilesTest extends TestCase
{
    public function testExecutableFiles()
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            $this->markTestSkipped('Testing executable files requires a Posix environment');
        }
        $expected = array(
            'bin/export-plural-rules',
        );
        $actual = $this->listExecutableFiles();
        $this->assertSame($expected, $actual);
    }

    /**
     * @return string[]
     */
    private function listExecutableFiles()
    {
        $rc = -1;
        $output = array();
        exec('find ' . escapeshellarg(GETTEXT_LANGUAGES_TESTROOTDIR) . ' -type f -executable 2>&1', $output, $rc);
        if ($rc !== 0) {
            $this->markTestSkipped('Failed to retrieve the list of executable files (' . trim(implode("\n", $output)) . ')');
        }
        $result = array_map(
            function ($file) {
                return substr($file, strlen(GETTEXT_LANGUAGES_TESTROOTDIR) + 1);
            },
            $output
        );
        $result = array_filter(
            $result,
            function ($file) {
                if ($file === '') {
                    return false;
                }
                if (strpos($file, '/') === false) {
                    return true;
                }
                return strpos($file, 'bin/') === 0 || strpos($file, 'src/') === 0 || strpos($file, 'tests/') === 0;
            }
        );
        sort($result);

        return $result;
    }
}
