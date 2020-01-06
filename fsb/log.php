<?

class fsb_log	{
	protected $logDir;

	function __construct()	{
		$this->init();
	}

	function init($logDir='')	{
		$this->logDir = ($logDir) ? $logDir : $this->getCurDir();
	}

	protected function getCurDir()	{
		$d = debug_backtrace();
		if (isset($d[0]['file']))
			return dirname($d[0]['file']).DIRECTORY_SEPARATOR.'log';

		throw new Exception('Log: unknown error');
	}

	function write($group, $text, $options=false)	{
		$default = array('subgroup'=>'default', 'datetime'=>true, 'blankbefore'=>false, 'blankafter'=>true);

		if (!$options or !is_array($options))	$options = $default;
		foreach( $default as $key => $val )	if (!isset($options[$key]))	$options[$key] = $default[$key];


		$ln  = ($options['blankbefore']) ? "\n" : "";
		if ($options['datetime'])	$ln .= date('Y-m-d H:i:s:	', time());
		$ln .= $text;
		if ($options['blankafter'])	$ln .= "\n";

		$logDir = $this->logDir.DIRECTORY_SEPARATOR.$group;
		if (!file_exists($logDir))	mkdir($logDir);

		$fp = fopen($logDir.DIRECTORY_SEPARATOR.$options['subgroup'].'.log', 'a');
		fputs($fp, $ln);
		fclose($fp);
	}
};

global $fsb_log;
$fsb_log = new fsb_log;

function fsb_getLog()	{
	global $fsb_log;
	$res = &$fsb_log;
	return $res;
}
