<h2>Результаты тура</h2>

<table border="1">

<tr>
<td><b>№</b></td>
<td><b>Стратегия</b></td>
<td><b>Результат</b></td>
</tr>
<?
foreach( $this->data['tour_result'] as $rank => $players )	{
?>
<tr><td colspan="3" align="center"><b>Место <?= $rank ?></b></td></tr>
<?
	foreach( $players as $player )	{
?>
<tr>
<td><?= $player['number'] ?></td>
<td><?= ctrStrategy::translateName($player['strategy'], 'ru') ?></td>
<td align="right"><?= $player['result'] ?></td>
</tr>
<?
	}
}
?>

</table>
