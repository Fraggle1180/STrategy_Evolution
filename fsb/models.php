<?

###############################################################################
# DataRecord: единичный экземпляр данных (одна запись), находящийся в памяти
# Функции:    get, set

abstract class fsb_model_datarecord	{
	protected $dataFields;

	function __construct()	{
		$this->Create();
	}

	function Create()	{
		$this->FillDataFields();
	}

	abstract function FillDataFields();

	function get($field)	{
		return (array_key_exists($field, $this->dataFields)) ? $this->dataFields[$field] : null;
	}

	function get_record()	{
		return $this->dataFields;
	}

	function set($field, $value)	{
		$this->setDirect($field, $value);
	}

	protected function setDirect($field, $value)	{
		if (!array_key_exists($field, $this->dataFields))	return false;
		$this->dataFields[$field] = $value;
		return true;
	}
}


###############################################################################
# DatabaseRecord: единичный экземпляр данных (одна запись), находящийся в базе данных
# Функции:    load, save, find
# Наследует от:   DataRecord

abstract class fsb_model_databaserecord	extends fsb_model_datarecord {
	protected $db;
	protected $dbFields;

	function __construct()	{
		$this->db = new fsb_dbconnection;
		parent::__construct();
	}

	abstract function load($key);
	abstract function save();
	abstract function find($cond);

	protected function setDBFields()	{
		$this->dbFields = $this->get_record();

		foreach( $this->dbFields as $field => $v )
			$this->escapeDBField($field);
	}

	private function escapeDBField($field)	{
		$link = '$this->dbFields';
		if (is_array($field))	{
			$link .= "['".implode("']['", $field)."']";

			$f = $field;
			$p = array_pop($f);
			$i = '$this->dbFields['."'".implode("']['", $f)."']";

			exec("\$exist = array_key_exists($f, $i)");
		}	else	{
			$link .= "['".$field."']";
			$exist = array_key_exists($field, $this->dbFields);
		}
		if (!$exist)	return null;

		eval('$lnk = &'.$link.';');
		if (is_array($lnk))	{
			foreach( $lnk as $fld => $val )
				if (is_array($lnk[$fld]))	{
					$f = is_array($field) ? $field : array($field);
					$f[] = $fld;
					$this->escapeDBField($f);
				}	else	{
					$lnk[$fld] = $this->escapeDBValue($val);
				}
		}	else	$lnk = $this->escapeDBValue($lnk);

		return true;
	}

	private function escapeDBValue($val)	{
		if (is_null($val))	return 'NULL';

		$v = $this->db->escape($val);
		if (!is_numeric($val))	$v = "'".$v."'";

		return $v;
	}
}


###############################################################################
# DataSet: набор данных (N записей), находящийся в памяти
# Функции:    get, set, add, count, key_exists

abstract class fsb_model_datarecord	{
	protected $dataFields;

	function __construct()	{
		$this->Create();
	}

	function Create()	{
		$this->dataFields = array();
	}

	abstract function newRecord();

	function get($num, $field)	{
#		return (array_key_exists($field, $this->dataFields)) ? $this->dataFields[$field] : null;
	}

	function get_record($num)	{
#		return (array_key_exists($field, $this->dataFields)) ? $this->dataFields[$field] : null;
	}

	function set($num, $field, $value, $allow_add = false)	{
		if (!$this->key_exists($num))	{
			if (!$allow_add)	return false;
			$this->add($num);
		}

		if (!array_key_exists($field, $this->dataFields[$num]))	return false;
		$this->dataFields[$num][$field] = $value;
		return true;
	}

	function add($num, $allow_overwrite = false)	{
		if ($this->key_exists($num) and !$allow_overwrite)	return false;

		$this->dataFields[$num] = $this->newRecord();
		return true;
	}

	function count()	{
		return count($this->dataFields);
	}

	function key_exists($key)	{
		return array_key_exists($key, $this->dataFields);
	}
}

