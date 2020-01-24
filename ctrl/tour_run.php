<?
/* /
$db = new fsb_dbconnection;
$db->open();

$val1 = "(1, 1, 'step-by-step', 1)";
$val2 = "(1, 1, 'bunch', 1)";
$sql  = "insert into d_player_in_tournament (id_tournament, player_number, player_strategy, player_result) values ";
$tot  = 45;

$list = new fsb_list_limited($sql,   ', ');
$list->set_limit(10485760);


$t1 = microtime(true);


?><hr><b>Step-by=step</b><br><br><?
for( $n = $tot; $n--; )	{
	$db->execute($sql.$val1);
	$id = $db->get_insert_id();
	print("id: $id<br>\n");
}


$t2 = microtime(true);


?><hr><b>Bunch</b><br><br><?
for( $n = $tot, $bunch = $sql; $n--; )
	$list->add($val2);

foreach( $list as $key => $statement )	{
	$db->execute($statement);
	print("bunch #$key<br>\n");
	$id = $db->get_insert_id();
	print("id: $id<br>\n");
}


$t3 = microtime(true);


print("t1 = $t1, t2 = $t2, t3 = $t3<br>\n");
print("step-by-step time: ".($t2-$t1)." sec<br>\n");
print("bunch time: ".($t3-$t2)." sec<br>\n");


return;
####################
/* */

include_once('common/models/tour.php');
include_once('common/controllers/tour.php');

$ctrTour = new ctrTour();


#передать параметры в ctrTour 
$ctrTour->set_param('price1',	$_REQUEST['price1']);
$ctrTour->set_param('price2',	$_REQUEST['price2']);
$ctrTour->set_param('result1',	$_REQUEST['result1']);
$ctrTour->set_param('result2',	$_REQUEST['result2']);
$ctrTour->set_param('noise_in',	$_REQUEST['noise_in']);
$ctrTour->set_param('noise_out',$_REQUEST['noise_out']);
$ctrTour->set_param('gamelen',	$_REQUEST['gamelen']);

$totalPlayers = $_REQUEST['total_players'];
for ( $n = 1; $n <= $totalPlayers; $n++ )	{
	$strat = $_REQUEST['player'.$n.'_strategy'];
	$param = $_REQUEST['player'.$n.'_params'];

	if ($strat == 'none')	continue;

	$ctrTour->set_player($n, $strat, $param);
}


#проверить, все ли параметры на месте
if (!$ctrTour->check_params_enough())	{
	$this->set_includeOption('template', 'tour_input_incorrect');
	return false;
}


#провести тур
$ctrTour->run();


#взять результаты из ctrTour, занести в data
$this->data['tour_result'] = $ctrTour->get_results();


#взять темплейт результатов
$this->set_includeOption('template', 'tour_output');

return true;
