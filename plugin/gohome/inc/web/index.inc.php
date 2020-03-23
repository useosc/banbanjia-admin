<?php 
defined("IN_IA") or exit( "Access Denied" );
global $_W;
global $_GPC;
header("location:" . iurl("article/information/list"));
exit;

?>
