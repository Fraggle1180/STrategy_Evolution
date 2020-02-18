<?
$conditions = array( 	'tour' => array( 'price'=>1, 'price1'=>null, 'price2'=>null, 'result'=>3, 'result1'=>null, 'result2'=>null, 'noisein'=>0, 'noiseout'=>0, 'moves'=>30 ),
			'lasting'  => array( 'mintours'=>10, 'maxchange'=>25 ),
			'variance' => array( 'dropout'=>5, 'clone'=>null, 'strategy'=>array() ),
			'strategies' => array() );


$cond_req = (isset($_REQUEST['condition'])) ? $_REQUEST['condition'] : '';
$cond_ar  = explode("\n", $cond_req);


# получить параметры из запроса
$m = array();
$cur_section = '';

foreach( $cond_ar as $cond_line )	{
	# пустые строки не обрабатываются
	if (!trim($cond_line))	continue;


	# проверить: название секции
	$pm = preg_match('|\s*\[(.+)\]\s*|', $cond_line, $m);
	if ($pm)	{
		$cur_section = (isset($conditions[$m[1]])) ? $m[1] : '';

		continue;
	}


	# ниже этой строки обрабатываются только параметры
	if (!$cur_section)	continue;


	# проверить: параметр "равенство"
	$pm = preg_match('|\s*(.+)\s*=\s*(.+)\s*|', $cond_line, $m);
	if ($pm and isset($m[1]) and isset($m[2]))	{
		if (array_key_exists($m[1], $conditions[$cur_section]))
			$conditions[$cur_section][$m[1]] = $m[2];

		continue;
	}


	# проверить: параметр "стратегия"
	$pm = preg_match('|\s*([_a-z]+)\s*(\((.+)\))?\s*(x.+)?\s*?|', $cond_line, $m);
	if ($pm)	{
		$str = $m[1];
		$prm = isset($m[3]) ? $m[3] : '';
		$mlt = isset($m[4]) ? substr($m[4], 1) : 1;

		$elm = array( 'strategy' => $str, 'param' => $prm );


		$arr = null;
		$add = false;

		if ($cur_section == 'strategies')	{	$add = true;	$arr = & $conditions['strategies'];		}
		if ($cur_section == 'variance')		{	$add = true;	$arr = & $conditions['variance']['strategy'];	}


		if ($add)
			for( $i = 1; $i <= $mlt; $i++ )
				$arr[] = $elm;
		unset($arr);

		continue;
	}
}


# дозаполнить параметры
if (is_null($conditions['tour']['price1']))	$conditions['tour']['price1'] = $conditions['tour']['price'];
if (is_null($conditions['tour']['price2']))	$conditions['tour']['price2'] = $conditions['tour']['price'];

if (is_null($conditions['tour']['result1']))	$conditions['tour']['result1'] = $conditions['tour']['result'];
if (is_null($conditions['tour']['result2']))	$conditions['tour']['result2'] = $conditions['tour']['result'];

if (is_null($conditions['variance']['dropout'])  and is_null($conditions['variance']['clone']))
	$conditions['variance']['dropout'] = 0;
if (is_null($conditions['variance']['dropout'])  and !is_null($conditions['variance']['clone']))	{
	$val = $conditions['variance']['clone'] + count($conditions['variance']['strategy']);
	$conditions['variance']['dropout'] = max(0, $val);
}
if (!is_null($conditions['variance']['dropout']) and is_null($conditions['variance']['clone'])) 	{
	$val = $conditions['variance']['dropout'] - count($conditions['variance']['strategy']);
	$conditions['variance']['clone'] = max(0, $val);
}


# проверить параметры
$check  = true;
$errmsg = array();


#   variance: dropout = clone + count(strategy)
if ($conditions['variance']['dropout'] <> $conditions['variance']['clone'] + count($conditions['variance']['strategy']))	{
	$check  = false;
	$errmsg[] = "Раздел variance: не совпадают количества dropout, clone и strategy (проверка: dropout = clone + strategy)";
}


#   strategies: есть стратегий больше нуля
if (count($conditions['strategies']) <= 0)	{
	$check  = false;
	$errmsg[] = "Раздел strategies: должна быть хотя бы одна стратегия";
}


$conditions['check']['result'] = $check;

if (!$check)
	$conditions['check']['error'] = implode("\n", $errmsg);



$this->set_includeOption('template', 'sequence_run');
