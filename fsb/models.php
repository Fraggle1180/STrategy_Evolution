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

	abstract function getTableName();
	abstract function getPrimaryKey();

	function load($key)	{
		$this->FillDataFields();

		$db_id = $this->db->escape($key);
		$table = $this->getTableName();
		$p_key = $this->getPrimaryKey();

		$sql = "select * from $table where $p_key=$db_id";
		if (!$this->db->execute($sql))		return false;
		if (!($row = $this->db->read()))	return false;

		foreach( $this->dataFields as $field => $v )	{
			$this->set($field, $row[$field]);
		}

		return true;
	}

	function save()	{
		$this->setDBFields();

		$table = $this->getTableName();
		$p_key = $this->getPrimaryKey();

		$get_id	= $this->get($p_key);

		if ($get_id<>'NULL')	{
			$IDs = $this->Find(array($p_key=>$get_id));
			if (!$IDs)	$get_id = 'NULL';
		}
		$bNewRecord = ($get_id=='NULL');


		if ($bNewRecord)	{
			# новая запись (даже если p_key определен, но не найден в базе - это новая запись)
			$s_fields = array();
			$s_values = array();
			foreach( $this->dbFields as $field => $value )
				if ($field <> $p_key)	{
					$s_fields[] = $field;
					$s_values[] = $value;
				}

			$sql = "insert into $table (".implode(', ', $s_fields).") values (".implode(', ', $s_values).")";
			if (!$this->db->execute($sql))	return false;
			$this->set($p_key, $get_id = $this->db->get_insert_id());
		}	else	{
			# запись существует в базе
			$s_update = array();
			foreach( $this->dbFields as $field => $value )
				$s_update[] = "$field = $value";

			$sql = "update $table set ".implode(', ', $s_update)." where $p_key=$get_id";
			if (!$this->db->execute($sql))	return false;
		}


		return true;
	}

	function find($cond)	{
		if (!is_array($cond))	return null;

		$table = $this->getTableName();
		$p_key = $this->getPrimaryKey();

		$swc = array();
		foreach( $cond as $key => $val )
			$swc[] = $this->find_ConvertCondition($key, $val);

		$sql = "select distinct t.$p_key from $table t";
		if ($swc)	$sql = $sql . ' where ' . implode(' and ', $swc);


		if (!$this->db->execute($sql))		return null;

		for( $found = array(); $res = $this->db->read(); )	{
			$found[] = $res[$p_key];
		}

		return $found;
	}

	protected function find_ConvertCondition($key, $val)	{
		return "t.$key=".$this->db->escape($val);
	}

	protected function setDBFields()	{
		$this->dbFields = $this->get_record();

		$this->escapeDBField();
	}

	private function escapeDBField($field = null)	{
		if (is_null($field))	$field = array();

		$addr = implode("']['", $field);
		if ($addr)	$addr = "['$addr']";

		$l_db = "\$this->dbFields$addr";
		$eval = "\$link = $l_db;";

		eval($eval);


		if (is_array($link))	{
			foreach( $link as $key => $val )	{
				$arg = $field;
				$arg[] = $key;

				$this->escapeDBField($arg);
			}
		}	else	{
			$eval = "$l_db = \$this->escapeDBValue($l_db);";
			eval($eval);
		}

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

abstract class fsb_model_dataset	{
	protected $dataFields;

	function __construct()	{
		$this->Create();
	}

	function Create()	{
		$this->dataFields = array();
	}

	abstract function newRecord();

	function get($num, $field)	{
		if (!$this->key_exists($num))	return null;
		if (!array_key_exists($field, $this->dataFields[$num]))	return null;
		return $this->dataFields[$num];
	}

	function get_record($num)	{
		if (!$this->key_exists($num))	return null;
		return $this->dataFields[$num];
	}

	function get_all_records()	{
		return $this->dataFields;
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


###############################################################################
# DatabaseRecord: единичный экземпляр данных (одна запись), находящийся в базе данных
# Функции:    load, save, find
# Наследует от:   DataSet

abstract class fsb_model_databaseset	extends fsb_model_dataset {
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
		$this->dbFields = $this->get_all_records();

		$this->escapeDBField($this->dbFields);
	}

	private function escapeDBField($field = null)	{
		if (is_null($field))	$field = array();

		$addr = implode("']['", $field);
		if ($addr)	$addr = "['$addr']";

		$l_db = "\$this->dbFields$addr";
		$eval = "\$link = $l_db;";

		eval($eval);


		if (is_array($link))	{
			foreach( $link as $key => $val )	{
				$arg = $field;
				$arg[] = $key;

				$this->escapeDBField($arg);
			}
		}	else	{
			$eval = "$l_db = \$this->escapeDBValue($l_db);";
			eval($eval);
		}

		return true;
	}

	private function escapeDBValue($val)	{
		if (is_null($val))	return 'NULL';

		$v = $this->db->escape($val);
		if (!is_numeric($val))	$v = "'".$v."'";

		return $v;
	}
}
