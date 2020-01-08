<?
include_once('fsb/model.php');

class modMove extends fsb_model {

	function FillDataFields()	{
		$this->dataFields = array( 'id' => null, 'id_game' => null, 'number_move' => null, 'player1_decision' => null, 'player2_decision' => null, 'player1_action' => null, 'player2_action' => null, 'player1_perception' => null, 'player2_perception' => null );
	}

	function Load($key)	{
		$this->FillDataFields();

		$db_id = $this->db->escape($key);

		$sql = "select * from d_move where id=$db_id";
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
		$db_id_game	= $this->dbFields['id_game'];
		$db_number_move	= $this->dbFields['number_move'];
		$db_player1_decision	= $this->dbFields['player1_decision'];
		$db_player2_decision	= $this->dbFields['player2_decision'];
		$db_player1_action	= $this->dbFields['player1_action'];
		$db_player2_action	= $this->dbFields['player2_action'];
		$db_player1_perception	= $this->dbFields['player1_perception'];
		$db_player2_perception	= $this->dbFields['player2_perception'];

		if ($id<>'NULL')	{
			$IDs = $this->Find(array('id'=>$id));
			if (!$IDs)	$id = null;
		}
		$bNewRecord = ($id=='NULL');

		if ($bNewRecord)	{
			//	new record (even if Id exists, but wasn't fount - anyway it is new record)
			$sql = "insert into d_move (id_game, number_move, player1_decision, player2_decision, player1_action, player2_action, player1_perception, player2_perception) values ($db_id_game, $db_number_move, $db_player1_decision, $db_player2_decision, $db_player1_action, $db_player2_action, $db_player1_perception, $db_player2_perception)";
			if (!$this->db->execute($sql))	return false;
			$this->set('id', $id = $this->db->get_insert_id());
		}	else	{
			//	Id is defined and record exists
			$sql = "update d_move set id_game=$db_id_game, number_move=$db_number_move, player1_decision=$db_player1_decision, player2_decision=$db_player2_decision, player1_action=$db_player1_action, player2_action=$db_player2_action, player1_perception=$db_player1_perception, player2_perception=$db_player2_perception where id=$id";
			if (!$this->db->execute($sql))	return false;
		}

		$db_id = $this->db->escape($id);

		return true;
	}

	function Find($cond)	{
		if (!is_array($cond))	return null;

		$sql = "select distinct m.ID from d_move m";

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
