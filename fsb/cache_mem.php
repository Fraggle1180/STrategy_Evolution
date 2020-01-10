<?
class fsb_cache_mem	{
	protected static $data;
	protected $log;
	protected $matches;

	function __construct()	{
		if (is_null(fsb_cache_mem::$data))	fsb_cache_mem::$data = array();
		$this->matches = array( 'match' => 0, 'miss' => 0 );
		$this->log = fsb_getLog();
	}

	function __destruct()	{
		$ma = $this->matches['match'];
		$mi = $this->matches['miss'];
		$mt = $ma + $mi;
		$mp = ($mt <> 0) ? round(100.0 * $ma / $mt, 2) : '-';

		$this->log->write('cache_mem', "Matches: $ma of $mt ($mp%)");
	}

	protected function getKeySignature($key)	{
		$type = '';
		$val  = '';

		if (is_null($key))	{
			$type = 'NULL';
			$val  = 'NULL';
		} elseif (!is_array($key))	{
			$type = gettype($key);
			$val  = ''.$key.'';

			if ($type == 'string')	return "string: $key";
		} else	{
			$type = 'array';
			$val  = print_r($key, true);
		}

		return md5("($type): $val");
	}

	function get($key, $allow_expired = false)	{
		$key_s = $this->getKeySignature($key);
		if (!array_key_exists($key_s, fsb_cache_mem::$data))	{	$this->matches['miss']++;	return NULL;	}
#		if (!array_key_exists($key_s, fsb_cache_mem::$data))	return NULL;

		$cache = fsb_cache_mem::$data[$key_s];
		$time  = time();

		if (($cache['expires'] < $time) and !$allow_expired)	{	$this->matches['miss']++;	return NULL;	}
#		if (($cache['expires'] < $time) and !$allow_expired)	return NULL;

		$this->matches['match']++;
		return $cache['value'];
	}

	function set($key, $value, $expires)	{
		$key_s = $this->getKeySignature($key);
		$cache = array( 'key' => $key, 'value' => $value, 'expires' => $expires );

		fsb_cache_mem::$data[$key_s] = $cache;
		return true;
	}

	function setExpiration($key, $expires)	{
		$key_s = $this->getKeySignature($key);
		if (!array_key_exists($key_s, fsb_cache_mem::$data))	return false;

		fsb_cache_mem::$data[$key_s]['expires'] = $expires;
		return true;
	}
};
