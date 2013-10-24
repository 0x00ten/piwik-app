<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!-->
<html> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <title>Piwik CSV sites import</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->
    </head>
    <body>
        <?php

            if (isset($_FILES['csv'])){
                require 'vendor/autoload.php';

                $uploads_dir = __DIR__ . '/uploads';
                if (!realpath($uploads_dir)){
                    echo "Path \"$uploads_dir\" doesn't exist!";
                    return;
                }
                
                if ($_FILES["csv"]["error"] == UPLOAD_ERR_OK) {
                    $tmp_name = $_FILES["csv"]["tmp_name"];
                    $name = $_FILES["csv"]["name"];
                    move_uploaded_file($tmp_name, "$uploads_dir/$name");
                }

                $csvFile = new Keboola\Csv\CsvFile("$uploads_dir/$name", ";");

                $curl = new Buzz\Client\Curl();
                $curl->setTimeout(60);
                $piwikClient = new \PiwikClient\PiwikClient('http://piwik.local', '27a40a0bd97cc5279c0f80de088767b6', new \Buzz\Browser($curl));
                
                //Request per site
                foreach($csvFile as $key => $site) {
                    if ($key === 0) continue;
                    
                    //order matters
                    $parameterKeys = array(
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

                    $params = array();
                    foreach ($site as $k => $v){
                        if (empty($v)) continue;
                        $params[$parameterKeys[$k]] = strpos($v, '|') !== FALSE ? explode('|', $v) : $v;
                    }
                    if (empty($params)) continue;

                    //For API.getBulkRequest. Uncomment if using it
                    //$sites[] = $params;

                    echo "Site $params[siteName] id: " . $piwikClient->call('SitesManager.addSite', $params) . "<br>\n";
                }

                
                //Request via API.getBulkRequest. Piwik has some problems with url parsing. So if site has multiple URLs, piwik will set them incorrectly in case when using API.getBulkRequest.
                /*
                $params = array();
                foreach ($sites as $site)
                    $params['urls[]'][] = $piwikClient->buildRequestUrlQuery(array_replace(array('method' => 'SitesManager.addSite'), $site));

                $result = $piwikClient->call('API.getBulkRequest', $params);
                foreach ($result as $key => $idSite)
                    echo "Site " . $sites[$key]['siteName'] . " id: $idSite<br>\n";
                 */

                //Delete sites:
                /*
                $start = 2;
                $end = 3;
                for($i=$start;$i<=$end;$i++)
                    $piwikClient->call('SitesManager.deleteSite', array('idSite' => $i));
                 */

            } else {?>
                Choose a CSV file and press the Send button.
                <form action="" enctype="multipart/form-data" method="post">
                    <input type="file" name="csv"/>
                    <input type="submit" name="send" value="Send"/>
                </form>
        <?php } ?>

    </body>
</html>