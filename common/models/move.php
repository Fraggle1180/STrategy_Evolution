<?
include_once('fsb/models.php');

class modMove extends fsb_model_databaserecord {

	function FillDataFields()	{
		$this->dataFields = array( 'id' => null, 'id_game' => null, 'number_move' => null, 'player1_decision' => null, 'player2_decision' => null, 'player1_action' => null, 'player2_action' => null, 'player1_perception' => null, 'player2_perception' => null );
	}

	function getTableName()	{
		return 'd_move';
	}

	function getPrimaryKey()	{
		return 'id';
	}
}
