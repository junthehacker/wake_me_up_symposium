<?php
    define("VIRTUALMODE", true);
    include "api/index.php";
?>
<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://fonts.googleapis.com/css?family=Arimo|Oswald" rel="stylesheet">
        <link href="libs/bootstrap-3.3.7-dist/css/bootstrap.min.css" type="text/css" rel="stylesheet" />
        <link href="less/main.less" type="text/less" rel="stylesheet" />
        <script src="libs/jquery-3.2.0.min.js" type="text/javascript"></script>
        <script src="libs/bootstrap-3.3.7-dist/js/bootstrap.min.js" type="text/javascript"></script>
        <script src="libs/less.min.js" type="text/javascript"></script>
        <script src="libs/jquery-ui.min.js"></script>
        <title>Wake Me Up</title>
    </head>
    <body>
        <?php
            if(!isset($_GET["p"])){
                $page = "home";
            } else {
                $page = $_GET["p"];
            }
            include "pages/$page.php";
        ?>
    </body>
</html>