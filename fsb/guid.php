<?
class fsb_guid	{
	function generate()	{
		for( $i = 0, $res = ''; $i<4; $i++ )	{
			$r = '0000'.dechex(rand());
			$res .= substr($r, strlen($r)-8);
		}
		return $res;
	}
};
