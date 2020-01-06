<?
include_once('fsb/common.php');

$content = new fsb_content();

$content->set_includeOption('controller', 'index');

$content->act();
$content->show();
