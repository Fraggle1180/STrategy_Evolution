<?
class fsb_dbconnection	{
	protected $link;
	protected $res;
	protected $open;
	protected $error;
	protected $log;
	protected $writelog;
	protected $escape_cache;

	function __construct()	{
		$this->close(true);

		$settings = fsb_getSettings();
		$this->writelog = $settings->get('db::common::writelog');

		$this->log = fsb_getLog();

		$this->escape_cache = new fsb_cache_mem;
	}

	function __destruct()	{
		$this->close();
	}

	function execute($sql, $log=null)	{
		if (!$this->is_open() and !$this->open())	return false;

		$logIt = (is_null($log)) ? $this->writelog : $log;
		if ($logIt)	$this->log->write('dbconnection', $sql);

		$this->res = mysqli_query($this->link, $sql);
		return true;
	}

	function read_all()	{
		if (!$this->is_open() or !$this->res)	return false;
		return mysqli_fetch_array($this->res);
	}

	function read($type='assoc')	{
		if ($type=='assoc')	return $this->read_assoc();
		if ($type=='row')	return $this->read_row();

		throw new Exception("DB connection: unknown read type = '$type'");
	}

	function read_row()	{
		if (!$this->is_open() or !$this->res)	return false;
		return mysqli_fetch_row($this->res);
	}

	function read_assoc()	{
		if (!$this->is_open() or !$this->res)	return false;
		return mysqli_fetch_assoc($this->res);
	}

	function escape($txt)	{
		$cached = $this->escape_cache->get($txt);
		if ($cached)	return $cached;

		if (!$this->is_open() and !$this->open())	throw new Exception('DB connection: unable to connect');
		$escaped = mysqli_real_escape_string($this->link, $txt);

		$this->escape_cache->set($txt, $escaped, time() + 60);

		return $escaped;
	}

	function get_insert_id()	{
		if (!$this->is_open())	return null;
		return mysqli_insert_id($this->link);
	}

	function is_open()	{
		return $this->open;
	}

	function open()	{
		if ($this->is_open())	return false;

		$settings = fsb_getSettings();
		$host     = $settings->get('db::connection::host');
		$user     = $settings->get('db::connection::user');
		$password = $settings->get('db::connection::password');
		$database = $settings->get('db::connection::database');
		$allow_empty_password = $settings->get('db::connection::allow_empty_password');

		if ((!$host) or (!$user))
			throw new Exception('DB connection: read settings error');

		if (!$password and (is_null($allow_empty_password) or !$allow_empty_password))
			throw new Exception('DB connection: read settings error');

		$this->link = mysqli_connect($host, $user, $password, $database);
		if (!$this->link)	{
			$this->error = array( 'errno' => mysqli_connect_errno(), 'error' => mysqli_connect_error() );
			return false;
		}

		$this->open = true;

		if (!mysqli_set_charset($this->link, "utf8"))	{
			$this->error = array( 'error' => mysqli_error($this->link) );
			return false;
		}

		return true;
	}

	function close($force = false)	{
		if (!$force and !$this->is_open())	return false;

		if ($this->link)	mysqli_close($this->link);
		$this->link	= false;
		$this->open	= false;
		$this->error	= null;
		$this->res	= null;

		return true;
	}
};
