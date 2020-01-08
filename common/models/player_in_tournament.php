<?
include_once('fsb/model.php');

class modPlayerInTournament extends fsb_model {

	function FillDataFields()	{
		$this->dataFields = array( 'id' => null, 'id_tournament' => null, 'player_number' => null, 'player_strategy' => null, 'player_result' => null );
	}

	function Load($key)	{
		$this->FillDataFields();

		$db_id = $this->db->escape($key);

		$sql = "select * from d_player_in_tournament where id=$db_id";
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
		$db_player_number	= $this->dbFields['player_number'];
		$db_player_strategy	= $this->dbFields['player_strategy'];
		$db_player_result	= $this->dbFields['player_result'];

		if ($id<>'NULL')	{
			$IDs = $this->Find(array('id'=>$id));
			if (!$IDs)	$id = null;
		}
		$bNewRecord = ($id=='NULL');

		if ($bNewRecord)	{
			//	new record (even if Id exists, but wasn't fount - anyway it is new record)
			$sql = "insert into d_player_in_tournament (id_tournament, player_number, player_strategy, player_result) values ($db_id_tournament, $db_player_number, $db_player_strategy, $db_player_result)";
			if (!$this->db->execute($sql))	return false;
			$this->set('id', $id = $this->db->get_insert_id());
		}	else	{
			//	Id is defined and record exists
			$sql = "update d_player_in_tournament set id_tournament=$db_id_tournament, player_number=$db_player_number, player_strategy=$db_player_strategy, player_result=$db_player_result where id=$id";
			if (!$this->db->execute($sql))	return false;
		}

		$db_id = $this->db->escape($id);

		return true;
	}

	function Find($cond)	{
		if (!is_array($cond))	return null;

		$sql = "select distinct p.ID from d_player_in_tournament p";

		$swc = array();
		foreach( $cond as $key => $val )	{
			if (!strcasecmp($key, 'id'))		$swc[] = "p.id=".$this->db->escape($val);
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
