<?
include_once('common/controllers/evolution.php');

$evo = new ctrEvolution;
?>
<h2>Эволюция стратегий</h2>

<table cellpadding="0" cellspacing="0">
<tr><td><b>Стратегии</b></td></tr>

<?
for( $tr = 1; $tr <= 300; $tr++ )	{
	$evo->run();
?>
<tr>
<td nowrap><?
for( $td = 1; $td <= 80; $td ++)	{
?><img src="./img/img.php?EE6633" width="3" height="3"><?
}
?></td>
</tr>
<?
}
?>

</table>
