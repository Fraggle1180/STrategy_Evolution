<h2>Результаты тура</h2>

<table border="1">

<tr>
<td><b>№</b></td>
<td><b>Стратегия</b></td>
<td><b>Результат</b></td>
</tr>
<?
$min_width  = 2;
$max_width  = 398;
$max_result = $this->data['tour_result']['rating'][1][0]['result'];
if ($max_result <= 0)	$max_result = 1;

foreach( $this->data['tour_result']['rating'] as $rank => $players )	{
	$col = (isset($players[0]['color'])) ? substr($players[0]['color'], -6) : '506c78';
?>
<tr><td colspan="3" align="center">
<table width="100%">
<tr><td align="center"><b>Место <?= $rank ?></b><br></td></tr>
<tr><td align="left"><img src="./img/img.php?rgb=<?= $col ?>" width="<?= $min_width + round($max_width * $players[0]['result'] / $max_result) ?>" height="8"></td></tr>
</table>
</td></tr>
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

<hr>

<?
$stats = $this->data['tour_result']['stats'];

$dur_s = $stats['time'];
$dur_m = floor($dur_s / 60);	$dur_s = $dur_s % 60;
$dur_h = floor($dur_m / 60);	$dur_m = $dur_m % 60;
$dur_d = floor($dur_h / 24);	$dur_h = $dur_h % 24;

$duration = (($dur_d) ? "$dur_d дн. " : '') . (($dur_h) ? "$dur_h ч. " : '') . (($dur_m) ? "$dur_m мин. " : '') . ("$dur_s сек. ");

?>

<table>

<tr>
<td colspan="4" align="center"><b>Статистика тура:</b></td>
</tr>

<tr>
<td><b>Игроков:</b></td>
<td align="right"><?= $stats['players'] ?></td>
<td align="right" width="30px"></td>
<td align="right"></td>
</tr>

<tr>
<td><b>Время:</b></td>
<td align="right"><?= round($stats['time'], 3) ?>&nbsp;сек</td>
<td></td>
<td align="right"><nobr><?= $duration ?></nobr></td>
</tr>

<tr>
<td><b>Игр:</b></td>
<td align="right"><?= $stats['games'] ?></td>
<td></td>
<td align="right"><?= ($stats['time']) ? round($stats['games'] / $stats['time'], 2) . ' игр/сек' : '' ?></td>
</tr>

<tr>
<td><b>Ходов:</b></td>
<td align="right"><?= $stats['moves'] ?></td>
<td></td>
<td align="right"><?= ($stats['time']) ? round($stats['moves'] / $stats['time'], 2) . ' ход/сек' : '' ?></td>
</tr>

</table>
