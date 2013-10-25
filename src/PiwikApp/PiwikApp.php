<?php

namespace PiwikApp;

use Keboola\Csv\CsvFile,
    Buzz\Client\Curl,
    Buzz\Browser,
    PiwikClient\PiwikClient;

class PiwikApp
{
    protected $apiUrl;
    protected $apiToken;
    
    /**
     * @var PiwikClient\PiwikClient
     */
    protected $piwikClient;
    
    /**
     * Parameters set for SitesManager.addSite method. Order matters.
     * @var array An indexed array of parameter names
     */
    protected static $siteAddParameterKeys = array(
        'siteName',
        'urls',
        'ecommerce',
        'siteSearch',
        'searchKeywordParameters',
        'searchCategoryParameters',
        'excludedIps',
        'excludedQueryParameters',
        'timezone',
        'currency',
        'group',
        'startDate',
        'excludedUserAgents',
        'keepURLFragments',
    );

    /**
     * @return PiwikClient\PiwikClient
     */
    public function getPiwikClient()
    {
        if (!$this->piwikClient){
            $curl = new Curl();
            $curl->setTimeout(60);
            $this->piwikClient = new PiwikClient($this->apiUrl, $this->apiToken, new Browser($curl));
        }
        return $this->piwikClient;
    }

    /**
     * PiwikApp objects constructor
     * @param string $configFile
     * @param PiwikClient\PiwikClient $piwikClient
     * @throws \Exception
     */
    public function __construct($configFile = NULL, $piwikClient = NULL)
    {
        if (!$configFile)
            $configFile = ROOT . '/config.php';
        
        if (is_file($configFile)) {
            $config = require_once $configFile;
        } else {
            $config = require_once $configFile;
        }

        if(is_array($config)){
            $this->apiUrl = $config['apiUrl'];
            $this->apiToken = $config['apiToken'];
        } else {
            throw new \Exception("No valid config file was provided!");
        }

        if ($piwikClient !== NULL)
            $this->piwikClient = $piwikClient;
    }

    /**
     * Get file from post
     * @param POST filename $filename
     * @return string Posted temp filename
     * @throws \Exception
     */
    public function getFromPost($filename)
    {
        $file = $_FILES[$filename];
        if ($file["error"] != UPLOAD_ERR_OK) {
            throw new \Exception("An error occurred during operation!");
        }
        return $file["tmp_name"];
    }

    /**
     * Import and call SitesManager.addSite Piwik API method to add sites to the system
     * @param string $filename
     * @param string $delimiter
     * @param string $enclosure
     */
    public function importSitesFromCSV($filename, $delimiter = CsvFile::DEFAULT_DELIMITER, $enclosure = CsvFile::DEFAULT_ENCLOSURE)
    {
        $csvFile = new CsvFile($filename, $delimiter, $enclosure);

        //Request per site
        $ids = array();
        foreach ($csvFile as $key => $site) {
            if ($key === 0) continue;

            $params = array();
            foreach ($site as $k => $v) {
                if (empty($v)) continue;
                $params[self::$siteAddParameterKeys[$k]] = strpos($v, '|') !== FALSE ? explode('|', $v) : $v;
            }
            if (empty($params)) continue;

            //For API.getBulkRequest. Uncomment if using it
            //$sites[] = $params;

            $ids[] = $this->getPiwikClient()->call('SitesManager.addSite', $params);
        }
        return $ids;
        //Request via API.getBulkRequest. Piwik has some problems with url parsing. So if site has multiple URLs, piwik will set them incorrectly in case when using API.getBulkRequest.
        /*
        $params = array();
        foreach ($sites as $site)
            $params['urls[]'][] = $this->getPiwikClient()->buildRequestUrlQuery(array_replace(array('method' => 'SitesManager.addSite'), $site));

        return $this->getPiwikClient()->call('API.getBulkRequest', $params);
        */

        //Delete sites:
        /*
        $start = 2;
        $end = 3;
        for($i=$start;$i<=$end;$i++)
            $this->getPiwikClient()->call('SitesManager.deleteSite', array('idSite' => $i));
        */
    }
}

?>
