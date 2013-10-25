<?php

namespace PiwikAppTest;

require_once 'PHPUnit/Autoload.php';

use \PHPUnit_Framework_TestCase,
    PiwikApp\PiwikApp;

class PiwikAppTest extends PHPUnit_Framework_TestCase
{
    public function csvProvider()
    {
        return array(
            array(realpath(__DIR__ . '/../_data/csv1.csv'), ";", '"', 20),
        );
    }

    /**
     * @dataProvider csvProvider
     */
    public function testImportSitesFromCSV($filename, $delimiter, $enclosure, $count)
    {
        $PiwikApp = new PiwikApp(is_file(__DIR__ . '/config.php') ? __DIR__ . '/config.php' : __DIR__ . '/config.php.dist');
        $ids = $PiwikApp->importSitesFromCSV($filename, $delimiter, $enclosure);
        $this->assertTrue(is_array($ids));
        $this->assertEquals($count, count($ids));
    }

}

?>
