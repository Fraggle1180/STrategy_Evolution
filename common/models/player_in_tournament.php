<?
include_once('fsb/models.php');

class modPlayerInTournament extends fsb_model_databaserecord {

	function FillDataFields()	{
		$this->dataFields = array( 'id' => null, 'id_tournament' => null, 'player_number' => null, 'player_strategy' => null, 'player_result' => null );
	}

	function getTableName()	{
		return 'd_player_in_tournament';
	}

	function getPrimaryKey()	{
		return 'id';
	}

	static function getRating($tour_id)	{
		$db  = new fsb_dbconnection;
		$sql = "select p.* from d_player_in_tournament p where p.id_tournament=$tour_id order by player_result desc, player_number";
		if (!$db->execute($sql))	return false;

		$res  = array();
		$rank = null;
		$last_result = null;

		for( $row_num = 1; $row = $db->read(); $row_num++ )	{
			$p_num = $row['player_number'];
			$p_res = $row['player_result'];
			$p_str = $row['player_strategy'];


			if (($row_num == 1) or ($last_result <> $p_res))	{
				$rank = $row_num;
				$last_result = $p_res;
			}

			$player_res = array( 'number' => $p_num, 'strategy' => $p_str, 'result' => $p_res );
			$res[$rank][] = $player_res;
		}

		return $res;
	}
}
