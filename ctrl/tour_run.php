<?
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
$ctrTour->set_param('gm_save',	($_REQUEST['gm_save'] == 'yes') ? 1 : 0);

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
