<?php
    require 'vendor/autoload.php';
    defined('ROOT')  || define('ROOT', __DIR__);
?>

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
                
                $PiwikApp = new PiwikApp\PiwikApp();
                $ids = $PiwikApp->importSitesFromCSV($PiwikApp->getFromPost("csv"), ";");
                if (is_array($ids))
                    echo "Imported sites ids: " . implode (', ', $ids) . ".";

            } else {?>
                Choose a CSV file and press the Send button.
                <form action="" enctype="multipart/form-data" method="post">
                    <input type="file" name="csv"/>
                    <input type="submit" name="send" value="Send"/>
                </form>
        <?php } ?>

    </body>
</html>