<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>升级站点</title>
    <!-- Latest compiled and minified CSS -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <link rel="stylesheet" href="../../public/css/upgrade.css?_version=<?php echo $versionCode?>">
    <script type="text/javascript" src="../../../public/js/jquery.min.js"></script>
    <script src="../../public/js/template-web.js?_version=<?php echo $versionCode?>"></script>
    <script src="../../public/js/zalyjsHelper.js?_version=<?php echo $versionCode?>"></script>
    <script src="../../public/sdk/zalyjsNative.js?_version=<?php echo $versionCode?>"></script>
    <script src="../../public/js/jquery.i18n.properties.min.js?_version=<?php echo $versionCode?>"></script>
</head>
<body>

<div class="container">
    <div class="zaly_container">
        <div class="zaly_init zaly_upgrade">

        </div>
    </div>

    <div class="zaly_window">

    </div>
</div>

<input type="hidden" value='<?php echo $passwordFileName;?>' class="passwordFileName">
<input type="hidden" value='<?php echo $needUpgrade;?>' class="needUpgrade">
<?php include (dirname(__DIR__) . '/upgrade/template_upgrade.php');?>
<script src="../../public/js/upgrade/upgrade.js?_version=<?php echo $versionCode?>"></script>


</body>
</html>
