<h2>Моделирование эволюции</h2>

<form action="sequence.php" method="POST">
<input type="hidden" name="mode" value="seuqence_run">

<table width="360px" border="0">

<tr>

<td align="left">
<textarea cols="75" rows="30" name="condition">
</textarea>
</td>

<td valign="top">

<input type="button" value="Все со всеми" onClick="this.form.condition.value='[lasting]\nmintours=50\nmaxchange=5\n\n[strategies]\ngive x5\ntake x5\ncopycat x5\nunforgiving x5\ndetective x5\nrandom(30-70) x5\nsimpleton x5\ncopycat_forgiving(1)\ncopycat_rebalance(5)\ncopycat_trusted\n\n[variance]\ndropout=5'"><br><br>

<!--
<input type="button" value="Название прогона" onClick="this.form.condition.value='%условия%'"><br><br>
Формат условий:
[tour]
price=N
price1=N
price2=N
result=N
result1=N
result2=N
noisein=N
noiseout=N
moves=N

[lasting]
mintours=N
maxchange=N

[strategies]
strategy(params) xN  --  params и xN - опциональны; params могут быть в формате N-N

[variance]
dropout=N
clone=N
strategy(params) xN  -- dropout - clone - sum(xN) = должно быть 0

-->
</td>

</tr>

<tr>

<td align="center" colspan="2">
<input type="submit" value="Запустить!">
</td>

</tr>

</form>
