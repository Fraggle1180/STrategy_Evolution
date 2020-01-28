<?

###############################################################################
# DataRecord: единичный экземпляр данных (одна запись), находящийся в памяти
# Функции:    get, set, get_change_flag, set_change_flag

abstract class fsb_model_datarecord	{
	protected $dataFields;
	protected $dataFields_changes;

	function __construct()	{
		$this->Create();
	}

	function Create()	{
		$this->FillDataFields();

		foreach( $this->dataFields as $key => $val)
			$this->dataFields_changes[$key] = false;
	}

	abstract function FillDataFields();

	function get($field)	{
		return ($this->key_exists($field)) ? $this->dataFields[$field] : null;
	}

	function get_record()	{
		return $this->dataFields;
	}

	function set($field, $value)	{
		return $this->setDirect($field, $value);
	}

	function get_change_flag($field)	{
		return ($this->key_change_exists($field)) ? $this->dataFields_changes[$field] : null;
	}

	function set_change_flag($field, $value)	{
		$this->dataFields_changes[$field] = $value;
		return true;
	}

	function set_bulk($fields)	{
		$return = true;

		foreach( $fields as $field => $value )
			if (!$this->set($field, $value))
				$return = false;

		return $return;
	}

	protected function setDirect($field, $value)	{
		if (!$this->key_exists($field))	return false;
		$this->dataFields[$field] = $value;
		$this->set_change_flag($field, true);
		return true;
	}

	function key_exists($field)	{
		if (isset($this->dataFields[$field]))	return true;
		return array_key_exists($field, $this->dataFields);
	}

	function key_change_exists($field)	{
		if (isset($this->dataFields_change[$field]))	return true;
		return array_key_exists($field, $this->dataFields_changes);
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
			$this->set_change_flag($field, false);
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

			foreach( $this->dataFields as $key => $val)
				$this->set_change_flag($key, false);
		}	else	{
			# запись существует в базе
			$s_update = array();
			foreach( $this->dbFields as $field => $value )
				if ($this->get_change_flag($field))
					$s_update[] = "$field = $value";

			if ($s_update)	{
				$sql = "update $table set ".implode(', ', $s_update)." where $p_key=$get_id";
				if (!$this->db->execute($sql))	return false;

				foreach( $this->dataFields as $key => $val)
					$this->set_change_flag($key, false);
			}
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
		foreach( $this->get_record() as $field => $value )
			if ($this->get_change_flag($field))
				$this->dbFields[$field] = $this->escapeDBValue($value);
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
# Функции:    get, set, get_change_flag, set_change_flag, add, count, rec_exists

abstract class fsb_model_dataset	{
	protected $dataFields;
	protected $dataFields_changes;

	function __construct()	{
		$this->profiler = array( 'set_bulk' => new fsb_profiler, 'set_direct' => new fsb_profiler, 'add' => new fsb_profiler, 'append' => new fsb_profiler );
		foreach( $this->profiler as &$prof )	$prof->Tick('ctrTour::game_run');
		$this->reset();
	}

	abstract function newRecord();

	function reset()	{
		$this->dataFields		= array();
		$this->dataFields_changes	= array();
	}

	function get($num, $field)	{
		if (isset($this->dataFields[$num][$field]) or $this->rec_exists($num) or $this->key_exists($num, $field))
			return $this->dataFields[$num][$field];
		return null;
	}

	function get_record($num)	{
		if (isset($this->dataFields[$num]) or $this->rec_exists($num))
			return $this->dataFields[$num];
		return null;
	}

	function get_all_records()	{
		return $this->dataFields;
	}

	function get_change_flag($num, $field)	{
		if (isset($this->dataFields_changes[$num][$field]) or $this->rec_exists($num) or $this->key_exists($num, $field))
			return $this->dataFields_changes[$num][$field];
		return null;
	}

	function set($num, $field, $value, $allow_add = false)	{
		if (!$this->rec_exists($num))	{
			if (!$allow_add)	return false;
			$this->add($num);
		}

		return $this->setDirect($num, $field, $value);
	}

	function set_bulk($num, $fields, $allow_add = false)	{
#$this->profiler['set_bulk']->Tick('ctrTour::game_run', '', 0);
		if (!$this->rec_exists($num))	{
			if (!$allow_add)	return false;
			$this->add($num);
		}
#$this->profiler['set_bulk']->Tick('ctrTour::game_run');

		$return = true;

#$this->profiler['set_bulk']->Tick('ctrTour::game_run');
		foreach( $fields as $field => $value )
			if (!$this->setDirect($num, $field, $value))
				$return = false;

#$this->profiler['set_bulk']->Tick('ctrTour::game_run');
		return $return;
	}

	protected function setDirect($num, $field, $value)	{
#$this->profiler['set_direct']->Tick('ctrTour::game_run', '', 0);
		if (!$this->key_exists($num, $field))	return false;
#$this->profiler['set_direct']->Tick('ctrTour::game_run');
		$this->dataFields[$num][$field] = $value;
#$this->profiler['set_direct']->Tick('ctrTour::game_run');
		$this->set_change_flag($num, $field, true);
#$this->profiler['set_direct']->Tick('ctrTour::game_run');
		return true;
	}

	function set_change_flag($num, $field, $value)	{
		if (is_null($field))	{
			$return = true;
			foreach( $this->get_record($num) as $key => $v )
				if (!$this->set_change_flag($num, $key, $value))
					$return = false;
			return $return;
		}

		$this->dataFields_changes[$num][$field] = $value;
		return true;
	}

	function add($num = null, $allow_overwrite = false)	{
#$this->profiler['add']->Tick('ctrTour::game_run');
		if (is_null($num))	{
#$this->profiler['add']->Tick('ctrTour::game_run');
			$this->dataFields[] = $this->newRecord();
#$this->profiler['add']->Tick('ctrTour::game_run');
			$newNum = key(array_slice($this->dataFields, -1, 1, true));	//  array_key_last($this->dataFields);
#$this->profiler['add']->Tick('ctrTour::game_run');

			foreach( $this->dataFields[$newNum] as $key => $val )
				$this->set_change_flag($newNum, $key, true);
#$this->profiler['add']->Tick('ctrTour::game_run');

			return $newNum;
		}


#$this->profiler['add']->Tick('ctrTour::game_run');
		if ($this->rec_exists($num) and !$allow_overwrite)	return false;
#$this->profiler['add']->Tick('ctrTour::game_run');

		$this->dataFields[$num] = $this->newRecord();
#$this->profiler['add']->Tick('ctrTour::game_run');
		foreach( $this->dataFields[$num] as $key => $val )
			$this->set_change_flag($num, $key, true);

#$this->profiler['add']->Tick('ctrTour::game_run');
		return true;
	}

	function append($record)	{
		$rec = $this->newRecord();
		foreach( $rec as $key => $val )
			if (isset($record[$key]))
				$rec[$key] = $record[$key];

		$rec_c = $rec;
		foreach( $rec_c as $val )
			$val = true;

		$this->dataFields[] = $rec;
		$newNum = key(array_slice($this->dataFields, -1, 1, true));	//  array_key_last($this->dataFields);
		$this->dataFields_changes[$newNum] = $rec_c;

		return true;
/*
		$new = $this->add();
		return $this->set_bulk($new, $record);
*/
	}

	function tail($dataset)	{
		if (!is_subclass_of($dataset, 'fsb_model_dataset'))	return false;

		foreach( $dataset->dataFields as $key => $rec )	{
			$this->dataFields[] = $dataset->dataFields[$key];
			$this->dataFields_changes[] = $dataset->dataFields_changes[$key];
		}
	}

	function count()	{
		return count($this->dataFields);
	}

	function rec_exists($key)	{
		if (isset($this->dataFields[$key]))	return true;
		return array_key_exists($key, $this->dataFields);
	}

	function key_exists($num, $field)	{
		if (isset($this->dataFields[$num][$field]))	return true;
		if (!isset($this->dataFields[$num]))		return false;
		return array_key_exists($field, $this->dataFields[$num]);
	}

	function key_change_exists($num, $field)	{
		if (isset($this->dataFields_change[$num][$field]))	return true;
		if (!isset($this->dataFields_change[$num]))		return false;
		return array_key_exists($field, $this->dataFields_changes[$num]);
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
		$this->dbFields = array();
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
				$this->set_change_flag($new_ind, $field, false);
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
		$changes    = array();

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

				$changes[$num] = 'insert';
			}	else	{
				# запись существует в базе
				$s_update = array();
				foreach( $this->dbFields[$num] as $field => $value )
					if ($this->get_change_flag($num, $field))
						$s_update[] = "$field = $value";

				if ($s_update)	{
					$sql = "update $table set ".implode(', ', $s_update)." where $p_key=$get_id";
					$sql_update[] = $sql;

					$changes[$num] = 'update';
				}
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


		# сбросить флаги изменений
		foreach( $changes as $num => $mode )
			$this->set_change_flag($num, null, false);


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
		foreach( $this->get_all_records() as $num => $rec )
			foreach( $rec as $field => $value )
				if ($this->get_change_flag($num, $field))
					$this->dbFields[$num][$field] = $this->escapeDBValue($value);
	}

	private function escapeDBValue($val)	{
		if (is_null($val))	return 'NULL';

		$v = $this->db->escape($val);
		if (!is_numeric($val))	$v = "'".$v."'";

		return $v;
	}
}
