<?
include_once('fsb/models.php');

class modGame extends fsb_model_databaserecord {

	function FillDataFields()	{
		$this->dataFields = array( 'id' => null, 'id_tournament' => null, 'start_at' => null, 'player1_number' => null, 'player2_number' => null, 'player1_strategy' => null, 'player2_strategy' => null, 'player1_result' => null, 'player2_result' => null );
	}

	function getTableName()	{
		return 'd_game';
	}

	function getPrimaryKey()	{
		return 'id';
	}
}
