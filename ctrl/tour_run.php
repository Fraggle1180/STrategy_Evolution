<?
include_once('common/models/tour.php');
include_once('common/controllers/tour.php');

$ctrTour = new ctrTour();


#передать параметры в ctrTour 
$ctrTour->set('price1',  $_REQUEST['price1']);
$ctrTour->set('price2',  $_REQUEST['price2']);
$ctrTour->set('result1', $_REQUEST['result1']);
$ctrTour->set('result2', $_REQUEST['result2']);
$ctrTour->set('noise',   $_REQUEST['noise']);
$ctrTour->set('gamelen', $_REQUEST['gamelen']);


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
