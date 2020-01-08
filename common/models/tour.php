<?
include_once('fsb/model.php');

class modTour extends fsb_model {

	function FillDataFields()	{
		$this->dataFields = array( 'id' => null, 'game_length' => null, 'price1' => null, 'price2' => null, 'result1' => null, 'result2' => null, 'noise_in' => null, 'noise_out' => null );
	}

	function Load($key)	{
		$this->FillDataFields();

		$db_id = $this->db->escape($key);

		$sql = "select * from d_tournament where id=$db_id";
		if (!$this->db->execute($sql))		return false;
		if (!($row = $this->db->read()))	return false;

		foreach( $this->dataFields as $field => $v )	{
			$this->set($field, $row[$field]);
		}

		return true;
	}

	function Save()	{
		$this->setDBFields();

		$get_id		= $this->get('id');
		$id		= $this->dbFields['id'];
		$db_game_length	= $this->dbFields['game_length'];
		$db_price1	= $this->dbFields['price1'];
		$db_price2	= $this->dbFields['price2'];
		$db_result1	= $this->dbFields['result1'];
		$db_result2	= $this->dbFields['result2'];
		$db_noise_in	= $this->dbFields['noise_in'];
		$db_noise_out	= $this->dbFields['noise_out'];

		if ($id<>'NULL')	{
			$IDs = $this->Find(array('id'=>$id));
			if (!$IDs)	$id = null;
		}
		$bNewRecord = ($id=='NULL');

		if ($bNewRecord)	{
			//	new record (even if Id exists, but wasn't fount - anyway it is new record)
			$sql = "insert into d_tournament (game_length, price1, price2, result1, result2, noise_in, noise_out) values ($db_game_length, $db_price1, $db_price2, $db_result1, $db_result2, $db_noise_in, $db_noise_out)";
			if (!$this->db->execute($sql))	return false;
			$this->set('id', $id = $this->db->get_insert_id());
		}	else	{
			//	Id is defined and record exists
			$sql = "update d_tournament set game_length=$db_game_length, price1=$db_price1, price2=$db_price2, result1=$db_result1, result2=$db_result2, noise_in=$db_noise_in, noise_out=$db_noise_out where id=$id";
			if (!$this->db->execute($sql))	return false;
		}

		$db_id = $this->db->escape($id);

		return true;
	}

	function Find($cond)	{
		if (!is_array($cond))	return null;

		$sql = "select distinct t.ID from d_tournament d";

		$swc = array();
		foreach( $cond as $key => $val )	{
			if (!strcasecmp($key, 'id'))		$swc[] = "t.id=".$this->db->escape($val);
		}

		if ($swc)
			$sql = $sql . ' where ' . implode(' and ', $swc);

		if (!$this->db->execute($sql))		return null;

		for( $found = array(); $res = $this->db->read(); )	{
			$found[] = $res['ID'];
		}

		return $found;
	}
}
