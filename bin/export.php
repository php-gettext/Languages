<?php
use Gettext\Languages\Exporter\Exporter;
use Gettext\Languages\Language;

// Let's start by imposing that we don't accept any error or warning.
// This is a really life-saving approach.
error_reporting(E_ALL);
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    Enviro::echoErr("$errstr\nFile: $errfile\nLine: $errline\nCode: $errno\n");
    die(5);
});

require_once dirname(__DIR__).'/src/autoloader.php';

// Parse the command line options
Enviro::initialize();

try {
    $languages = Language::getAll();
    if (isset(Enviro::$outputFilename)) {
        echo call_user_func(array(Exporter::getExporterClassName(Enviro::$outputFormat), 'toFile'), $languages, Enviro::$outputFilename, array('us-ascii' => Enviro::$outputUSAscii));
    } else {
        echo call_user_func(array(Exporter::getExporterClassName(Enviro::$outputFormat), 'toString'), $languages, array('us-ascii' => Enviro::$outputUSAscii));
    }
} catch (Exception $x) {
    Enviro::echoErr($x->getMessage()."\n");
    Enviro::echoErr("Trace:\n");
    Enviro::echoErr($x->getTraceAsString()."\n");
    die(4);
}

die(0);

/**
 * Helper class to handle command line options.
 */
class Enviro
{
    /**
     * Shall the output contain only US-ASCII characters?
     * @var bool
     */
    public static $outputUSAscii;
    /**
     * The output format.
     * @var string
     */
    public static $outputFormat;
    /**
     * Output file name.
     * @var string
     */
    public static $outputFilename;
    /**
     * Parse the command line options.
     */
    public static function initialize()
    {
        global $argv;
        self::$outputUSAscii = false;
        self::$outputFormat = null;
        self::$outputFilename = null;
        $exporters = Exporter::getExporters();
        if (isset($argv) && is_array($argv)) {
            foreach ($argv as $argi => $arg) {
                if ($argi === 0) {
                    continue;
                }
                if (is_string($arg)) {
                    $argLC = trim(strtolower($arg));
                    switch ($argLC) {
                        case '--us-ascii':
                            self::$outputUSAscii = true;
                            break;
                        default:
                            if (preg_match('/^--output=.+$/', $argLC)) {
                                if (isset(self::$outputFilename)) {
                                    self::echoErr("The output file name has been specified more than once!\n");
                                    self::showSyntax();
                                    die(3);
                                }
                                list(, self::$outputFilename) = explode('=', $arg);
                                self::$outputFilename = trim(self::$outputFilename);
                            } elseif (isset($exporters[$argLC])) {
                                if (isset(self::$outputFormat)) {
                                    self::echoErr("The output format has been specified more than once!\n");
                                    self::showSyntax();
                                    die(3);
                                }
                                self::$outputFormat = $argLC;
                            } else {
                                self::echoErr("Unknown option: $arg\n");
                                self::showSyntax();
                                die(2);
                            }
                            break;
                    }
                }
            }
        }
        if (!isset(self::$outputFormat)) {
            self::showSyntax();
            die(1);
        }
    }

    /**
     * Write out the syntax.
     */
    public static function showSyntax()
    {
        self::echoErr("Syntax: php ".basename(__FILE__)." [--us-ascii] [--output=<file name>] <".implode('|', array_keys(Exporter::getExporters())).">\n");
    }
    /**
     * Print a string to stderr.
     * @param string $str The string to be printed out.
     */
    public static function echoErr($str)
    {
        $hStdErr = @fopen('php://stderr', 'a');
        if ($hStdErr === false) {
            echo $str;
        } else {
            fwrite($hStdErr, $str);
            fclose($hStdErr);
        }
    }
}
