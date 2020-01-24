<?
include_once('fsb/models.php');

class modTour extends fsb_model_databaserecord {

	function FillDataFields()	{
		$this->dataFields = array( 'id' => null, 'game_length' => null, 'price1' => null, 'price2' => null, 'result1' => null, 'result2' => null, 'noise_in' => null, 'noise_out' => null, 'p_players' => null, 'p_time' => null, 'p_games' => null, 'p_moves' => null, 'p_game_speed' => null, 'p_move_speed' => null );
	}

	function getTableName()	{
		return 'd_tournament';
	}

	function getPrimaryKey()	{
		return 'id';
	}
}
