<?

class fsb_lock	{
	protected $key;
	protected $lockFactor;

	function __construct()	{
		$this->voidKey();
		$this->lockFactor = 1;
	}

	function Lock($lockTime, $extraParameter=false)	{
		$d = debug_backtrace();
		$this->key = 'Locker: '.$d[0]['file'].'::'.$d[0]['line'];
		if ($extraParameter)	$this->key .= ': '.$extraParameter;

		$time   = time();
		$cache  = new fsb_cache;
		$locked = $this->isLocked();

		if ($locked)	return false;

		$cache->set($this->key, ' ', $time + $lockTime * $this->lockFactor);
		return true;
	}

	function Unlock()	{
		if (!$this->key)	return false;

		$time  = time();
		$cache = new fsb_cache;

		$cache->set($this->key, ' ', $time - 1);
		$this->voidKey();

		return true;
	}

	function isLocked()	{
		if (!$this->key)	return null;

		$cache = new fsb_cache;
		if (!$cache->get($this->key))	return false;

		return true;
	}

	protected function voidKey()	{
		$this->key = '';
	}

	protected function setLockfactor($lockFactor)	{
		if (!is_numeric($lockFactor))	return false;
		if ($lockFactor <= 0)		return false;

		$this->lockFactor = $lockFactor;
	}
};
