<?

abstract class fsb_model	{
	protected $db;
	protected $dataFields;
	protected $dbFields;
	protected $setResult;

	function __construct()	{
		$this->db = new fsb_dbconnection;

		$this->Create();
	}

	function __destruct()	{
	}

	function Create()	{
		$this->FillDataFields();
	}

	abstract function FillDataFields();
	abstract function Load($key);
	abstract function Save();
	abstract function Find($cond);

	function get($field)	{
		return (array_key_exists($field, $this->dataFields)) ? $this->dataFields[$field] : null;
	}

	function set($field, $value)	{
		$this->setDirect($field, $value);
	}

	protected function setDirect($field, $value)	{
		if (!array_key_exists($field, $this->dataFields))	return false;
		$this->dataFields[$field] = $value;
		return true;
	}

	protected function setDBFields()	{
		$this->dbFields = $this->dataFields;

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
throw new Exception('We are here');
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
};
