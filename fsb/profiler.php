<?

class fsb_profiler	{
	protected $data;
	protected $mode;

	function __construct()	{
		$this->data = array();
		$this->set_mode(1);
	}

	function __destruct()	{
		$db = new fsb_dbconnection;

		foreach($this->data as $section => $data)	foreach($data['data'] as $tick)	{
			$sql = "insert into sys_Profiler (Section, Source, Line, Created, Duration) values ('".$db->escape($section)."', '".$db->escape($tick['s'])."', '".$db->escape($tick['l'])."', ".intval($tick['c']).", ".$tick['d'].")";
			$db->execute($sql, false);
		}
	}

	function set_mode($mode)	{
		$this->mode = $mode;
	}

	function Tick($section='common', $linesuffix='', $useDuration=true)	{
		if (!$this->mode)	return;

		$sc = (is_array($section)) ? md5(print_r($section, 1)) : $section;

		if (isset($this->data[$sc]))	{
			$b = debug_backtrace();
			if (!isset($b[0]))	throw new Exception('Profiler: debug_backtrace contains no zero-item');

			$s = $b[0]['file'];
			$l = $b[0]['line'];	if ($linesuffix)	$l .= ': '.$linesuffix;

			$c = microtime(true);
			$d = $c - $this->data[$sc]['last'];

			$this->data[$sc]['data'][] = array('s'=>$s, 'l'=>$l, 'c'=>$c, 'd'=>($useDuration ? $d : 0));
			$this->data[$sc]['last'] = $c;
		}	else	{
			$this->data[$sc]['last'] = microtime(true);
			$this->data[$sc]['data'] = array();
		}
	}
};
