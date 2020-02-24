<h2>Эволюция стратегий</h2>

<?
$conditions = $this->data['conditions'];

# проверить на ошибки во входных данных
if (!$conditions['check']['result'])	{
?>
Ошибка во входных данных:<br>
<pre><?= $conditions['check']['error'] ?></pre>
<?

	return;
}


# выполнить моделирование
/* /
foreach( $conditions as $k => $v )
	foreach( $v as $k2 => $v2 )	{
?>
<hr>
$conditions[<?= $k ?>][<?= $k2 ?>] = <? var_dump($v2); ?>
<?
	}
/* */

include_once('common/controllers/evolution.php');

$evo = new ctrEvolution;


# установить параметры эволюции
  # tour
$cond_tour = & $conditions['tour'];
$evo->set_param('tour', 'price1',  $cond_tour['price1']);
$evo->set_param('tour', 'price2',  $cond_tour['price2']);
$evo->set_param('tour', 'result1', $cond_tour['result1']);
$evo->set_param('tour', 'result2', $cond_tour['result2']);
$evo->set_param('tour', 'noise_in',  $cond_tour['noisein']);
$evo->set_param('tour', 'noise_out', $cond_tour['noiseout']);
$evo->set_param('tour', 'gamelen', $cond_tour['moves']);
$evo->set_param('tour', 'gm_save', 0);


  # lasting
foreach( $conditions['lasting'] as $key => $val )
	$evo->set_param('lasting', $key, $val);


  # variance
foreach( $conditions['variance'] as $key => $val )
	$evo->set_param('variance', $key, $val);


  # strategies
foreach( $conditions['strategies'] as $key => $val )
	$evo->set_param('strategies', $key, $val);
#foreach( $conditions['strategies'] as $key => $val )
#	print("<br><br>\nevo->set_param('strategies', $key, ".print_r($val, 1).");");


# выполнить
?>
<table cellpadding="0" cellspacing="0">
<tr><td><b>Стратегии</b></td></tr>

<?
$picsize = 4;

ob_implicit_flush(1);

$evo->init();

for( ; $evo->step(); )	{
	$sl = $evo->get_slice();

	$output = "<tr>\n<td nowrap>";
	foreach( $sl as $str )
		$output .= '<img src="./img/img.php?rgb='.substr($str, -6).'" width="'.$picsize.'" height="'.$picsize.'">';
	$output .= "</td>\n</tr>\n";

	print($output);
}

ob_implicit_flush(0);
?>

</table>
