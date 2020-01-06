<?
class fsb_settings	{
	protected $data;

	function __construct()	{
		$this->data	= array();

		$this->read('ini.php');
		$this->read('local'.DIRECTORY_SEPARATOR.'ini.php');
	}

	function get($key)	{
		if (!isset($this->data[$key]))	return null;
		return $this->data[$key];
	}

	function set($key, $value)	{
		$this->data[$key] = $value;
	}

	function read($fname, $fpath='')	{
		if (!$fpath)	{
			$d = debug_backtrace();
			if (isset($d[0]['file']))
				$fpath = dirname($d[0]['file']);
		}

		if (!$fpath)	{
			print('Settings: unknown error');
			throw new Exception('Settings: unknown error');
		}

		$inifile = $fpath.DIRECTORY_SEPARATOR.$fname;
		if (!file_exists($inifile))	return false;

		include($inifile);

		if (isset($settings))	foreach( $settings as $key => $val )	$this->set($key, $val);
	}
};

global $fsb_settings;
$fsb_settings = new fsb_settings;

function fsb_getSettings()	{
	global $fsb_settings;
	$res = &$fsb_settings;
	return $res;
}
