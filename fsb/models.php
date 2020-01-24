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
		return $this->setDirect($field, $value);
	}

	function set_bulk($fields)	{
		$return = true;

		foreach( $fields as $field => $value )
			if (!$this->set($field, $value))
				$return = false;

		return $return;
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
			$IDs = $this->find(array($p_key=>$get_id));
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
		if ($addr <> '')	$addr = "['$addr']";

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
		$this->reset();
	}

	abstract function newRecord();

	function reset()	{
		$this->dataFields = array();
	}

	function get($num, $field)	{
		if (!$this->key_exists($num))	return null;
		if (!array_key_exists($field, $this->dataFields[$num]))	return null;
		return $this->dataFields[$num][$field];
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
		return $this->setDirect($num, $field, $value);
	}

	function set_bulk($num, $fields, $allow_add = false)	{
		if (!$this->key_exists($num))	{
			if (!$allow_add)	return false;
			$this->add($num);
		}

		$return = true;

		foreach( $fields as $field => $value )
			if (!$this->set($num, $field, $value, $allow_add))
				$return = false;

		return $return;
	}

	protected function setDirect($num, $field, $value)	{
		if (!array_key_exists($field, $this->dataFields[$num]))	return false;
		$this->dataFields[$num][$field] = $value;
		return true;
	}

	function add($num = null, $allow_overwrite = false)	{
		if (is_null($num))	{
			$this->dataFields[] = $this->newRecord();
			return key(array_slice($this->dataFields, -1, 1, true));	//  array_key_last($this->dataFields);
		}


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
# DatabaseRecord: набор данных (N записей), находящийся в базе данных
# Функции:    load, save, find
# Наследует от:   DataSet

abstract class fsb_model_databaseset	extends fsb_model_dataset {
	protected $db;
	protected $dbFields;

	function __construct()	{
		$this->db = new fsb_dbconnection;
		parent::__construct();
	}

	abstract function getTableName();
	abstract function getPrimaryKey();

	function load($keys)	{
		$this->empty();

		$table = $this->getTableName();
		$p_key = $this->getPrimaryKey();

		foreach( $keys as $key )	{
			$db_id = $this->db->escape($key);

			$sql = "select * from $table where $p_key=$db_id";
			if (!$this->db->execute($sql))		return false;
			if (!($row = $this->db->read()))	return false;

			$new_ind = $this->add();

			foreach( $this->dataFields[$new_ind] as $field => $v )	{
				$this->set($new_ind, $field, $row[$field]);
			}
		}

		return true;
	}

	function save()	{
		$this->setDBFields();

		$table = $this->getTableName();
		$p_key = $this->getPrimaryKey();
		$return = true;


		# подготовить запросы insert и update
		$sql_insert = array();
		$sql_update = array();

		foreach( $this->dbFields as $num => $r )	{
			$get_id	= $this->get($num, $p_key);

			if (!is_null($get_id) and ($get_id<>'NULL'))	{
				$IDs = $this->find(array($p_key=>$get_id));
				if (!$IDs)	$get_id = 'NULL';
			}
			$bNewRecord = (is_null($get_id) or ($get_id == 'NULL'));


			if ($bNewRecord)	{
				# новая запись (даже если p_key определен, но не найден в базе - это новая запись)
				$s_fields = array();
				$s_values = array();
				foreach( $this->dbFields[$num] as $field => $value )
					if ($field <> $p_key)	{
						$s_fields[] = $field;
						$s_values[] = $value;
					}

				if (!count($sql_insert))	$sql_insert[] = "(".implode(', ', $s_fields).")";
				$sql_insert[] = "(".implode(', ', $s_values).")";
				$sql_insert[] = $num;
			}	else	{
				# запись существует в базе
				$s_update = array();
				foreach( $this->dbFields[$num] as $field => $value )
					$s_update[] = "$field = $value";

				$sql = "update $table set ".implode(', ', $s_update)." where $p_key=$get_id";
				$sql_update[] = $sql;
			}
		}


		# исполнить запросы insert и update

		# вставки новых записей
		$ins_num = count($sql_insert);
		if ($ins_num>0)	{
			$max_packet = 10485760;  # TODO: get it from   SHOW VARIABLES LIKE 'max_allowed_packet';
			$sql_i1 = "insert into $table ".$sql_insert[0]." values ";

			$sql_statements  = new fsb_list_limited($sql_i1, ', ');
			$recnum_list     = array(array());
			$recnum_list_ind = 0;


			for( $n = 1;  $n < $ins_num;  $n += 2 )	{
				$values = $sql_insert[$n];
				$recnum = $sql_insert[$n+1];

				$add = $sql_statements->add($values);
				switch ($add)	{
					case -1: $recnum_list[$recnum_list_ind++][] = $recnum; break; # добавлен - сдвиг
					case -2: $recnum_list[++$recnum_list_ind][] = $recnum; break; # сдвиг - добавлен
					default: $recnum_list[$recnum_list_ind][] = $recnum;   break; # добавлен
				}
			}


			$recnum_list_ind = 0;
			foreach( $sql_statements as $sql )	{
				if ($this->db->execute($sql))	{
					$get_id = $this->db->get_insert_id();
					$cur_id = $get_id;

					foreach( $recnum_list[$recnum_list_ind] as $recnum )	{
						$this->set($recnum, $p_key, $cur_id);
						$cur_id++;
					}
				}	else	$return = false;

				$recnum_list_ind++;
			}
		}

		# обновление существующих записей
		foreach( $sql_update as $sql )	$this->db->execute($sql);


		return $return;
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
		$this->dbFields = $this->get_all_records();

		$this->escapeDBField();
	}

	private function escapeDBField($field = null)	{
		if (is_null($field))	$field = array();
		$addr = implode("']['", $field);
		if ($addr <> '')	$addr = "['$addr']";

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
