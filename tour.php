<?
include_once('fsb/common.php');

$content = new fsb_content();

$content->set_includeOption('controller', 'default', 'tour_default');

$content->act();
$content->show();
