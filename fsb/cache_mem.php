<?
class fsb_cache_mem	{
	protected $data;

	function __construct()	{
		$this->data = array();
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
		} else	{
			$type = 'array';
			$val  = print_r($key, true);
		}

		return md5("($type): $val");
	}

	function get($key, $allow_expired = false)	{
		$key_s = $this->getKeySignature($key);
		if (!array_key_exists($key_s, $this->data))	return NULL;

		$cache = $this->data[$key_s];
		$time  = time();

		if (($cache['expires'] < $time) and !$allow_expired)	return NULL;

		return $cache['value'];
	}

	function set($key, $value, $expires)	{
		$key_s = $this->getKeySignature($key);
		$cache = array( 'key' => $key, 'value' => $value, 'expires' => $expires );

		$this->data[$key_s] = $cache;
		return true;
	}

	function setExpiration($key, $expires)	{
		$key_s = $this->getKeySignature($key);
		if (!array_key_exists($key_s, $this->data))	return false;

		$this->data[$key_s]['expires'] = $expires;
		return true;
	}
};
