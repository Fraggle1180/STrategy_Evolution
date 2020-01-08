<?
$total_players = 100;
?>

<h2>Моделирование тура</h2>

<form action="tour.php" method="POST">
<input type="hidden" name="mode" value="tour_run">
<input type="hidden" name="total_players" value="<?= $total_players ?>">

<table width="360px" border="1">
<tr><td>

<table>
<tr>
<td colspan="4"><b>Ввод параметров:</b></td>
</tr>

<tr>
<td align="right"><b>Цена 1</b>&nbsp;</td><td><input type="text" name="price1" size="3" value="1"></td>
<td align="right"><b>Цена 2</b>&nbsp;</td><td><input type="text" name="price2" size="3" value="1"></td>
</tr>

<tr>
<td align="right"><b>Результат 1</b>&nbsp;</td><td><input type="text" name="result1" size="3" value="3"></td>
<td align="right"><b>Результат 2</b>&nbsp;</td><td><input type="text" name="result2" size="3" value="3"></td>
</tr>

<tr>
<td><b>Шум (вход)</b></td><td><input type="text" name="noise_in" size="3" value="0"></td>
<td><b>Шум (выход)</b></td><td><input type="text" name="noise_out" size="3" value="0"></td>
</tr>

<tr>
<td><b>Ходов в игре</b></td><td><input type="text" name="gamelen" size="3" value="30"></td>
<td><b></b></td><td></td>
</tr>
</table>

</td>
<td align="center">

<input type="submit" value="Запустить!">

</td></tr>

<tr><td colspan="2">

<table>
<tr>
<td colspan="4"><b>Выбор стратегий:</b></td>
</tr>

<tr>
<td><b>Номер&nbsp;игрока</b></td>
<td><b>Стратегия</b></td>
<td><b>Параметры</b></td>
</tr>

<?
for( $n=1; $n<=$total_players; $n++ )	{
?>
<tr>
<td><b>Игрок&nbsp;<?= $n ?></b></td>
<td><select name="player<?= $n ?>_strategy">
 <option value="none">Не участвует</option>
 <option value="give">Отдающий</option>
 <option value="take">Забирающий</option>
 <option value="copycat">Копирующий</option>
 <option value="unforgiving">Непрощающий</option>
 <option value="detective">Детектив</option>
 <option value="random">Случайный</option>
 <option value="simpleton">Простак</option>
 <option value="copycat_forgiving">Копирующий с прощением</option>
 <option value="copycat_rebalance">Копирующий с ребалансировкой</option>
 <option value="copycat_trusted">Копирующий с доверием</option>
</select></td>
<td><input type="text" name="player<?= $n ?>_params" size="20" value=""></td>
</tr>
<?
}
?>
</table>

</td></tr>


</table>

</form>
