<?
include_once('fsb/model.php');

class modGame extends fsb_model {

	function FillDataFields()	{
		$this->dataFields = array( 'id' => null, 'id_tournament' => null, 'start_at' => null, 'player1_number' => null, 'player2_number' => null, 'player1_strategy' => null, 'player2_strategy' => null, 'player1_result' => null, 'player2_result' => null );
	}

	function Load($key)	{
		$this->FillDataFields();

		$db_id = $this->db->escape($key);

		$sql = "select * from d_game where id=$db_id";
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
		$db_id_tournament	= $this->dbFields['id_tournament'];
		$db_start_at		= $this->dbFields['start_at'];
		$db_player1_number	= $this->dbFields['player1_number'];
		$db_player2_number	= $this->dbFields['player2_number'];
		$db_player1_strategy	= $this->dbFields['player1_strategy'];
		$db_player2_strategy	= $this->dbFields['player2_strategy'];
		$db_player1_result	= $this->dbFields['player1_result'];
		$db_player2_result	= $this->dbFields['player2_result'];

		if ($id<>'NULL')	{
			$IDs = $this->Find(array('id'=>$id));
			if (!$IDs)	$id = null;
		}
		$bNewRecord = ($id=='NULL');

		if ($bNewRecord)	{
			//	new record (even if Id exists, but wasn't fount - anyway it is new record)
			$sql = "insert into d_game (id_tournament, start_at, player1_number, player2_number, player1_strategy, player2_strategy, player1_result, player2_result) values ($db_id_tournament, $db_start_at, $db_player1_number, $db_player2_number, $db_player1_strategy, $db_player2_strategy, $db_player1_result, $db_player2_result)";
			if (!$this->db->execute($sql))	return false;
			$this->set('id', $id = $this->db->get_insert_id());
		}	else	{
			//	Id is defined and record exists
			$sql = "update d_game set id_tournament=$db_id_tournament, start_at=$db_start_at, player1_number=$db_player1_number, player2_number=$db_player2_number, player1_strategy=$db_player1_strategy, player2_strategy=$db_player2_strategy, player1_result=$db_player1_result, player2_result=$db_player2_result where id=$id";
			if (!$this->db->execute($sql))	return false;
		}

		$db_id = $this->db->escape($id);

		return true;
	}

	function Find($cond)	{
		if (!is_array($cond))	return null;

		$sql = "select distinct g.ID from d_game g";

		$swc = array();
		foreach( $cond as $key => $val )	{
			if (!strcasecmp($key, 'id'))		$swc[] = "g.id=".$this->db->escape($val);
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
