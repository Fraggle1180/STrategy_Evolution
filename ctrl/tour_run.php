<?
include_once('common/models/tour.php');
include_once('common/controllers/tour.php');

$ctrTour = new ctrTour();


#�������� ��������� � ctrTour 
$ctrTour->set('price1',  $_REQUEST['price1']);
$ctrTour->set('price2',  $_REQUEST['price2']);
$ctrTour->set('result1', $_REQUEST['result1']);
$ctrTour->set('result2', $_REQUEST['result2']);
$ctrTour->set('noise',   $_REQUEST['noise']);
$ctrTour->set('gamelen', $_REQUEST['gamelen']);


#���������, ��� �� ��������� �� �����
if (!$ctrTour->check_params_enough())	{
	$this->set_includeOption('template', 'tour_input_incorrect');
	return false;
}


#�������� ���
$ctrTour->run();


#����� ���������� �� ctrTour, ������� � data
$this->data['tour_result'] = $ctrTour->get_results();


#����� �������� �����������
$this->set_includeOption('template', 'tour_output');

return true;
