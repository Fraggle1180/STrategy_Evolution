<?
class fsb_cache	{
	function get($key, $allow_expired = false)	{
		$db  = new fsb_dbconnection;
		$sql = "select * from sys_Cache where `Key`='".$db->escape($key)."'";
		if (!$allow_expired)	$sql .= " and Expires>=now()";

		if (!$db->execute($sql))	return NULL;
		if (is_null($row=$db->read()))	return false;

		return $row['Value'];
	}

	function set($key, $value, $expires)	{
		$db  = new fsb_dbconnection;

		$exp = date('Y-m-d H:i:s', $expires);
		$val = $db->escape($value);

		$sql = "INSERT INTO sys_Cache (`Key`, Expires, Value) VALUES ('".$db->escape($key)."', '$exp', '$val') ON DUPLICATE KEY UPDATE Expires='$exp', Value='$val'";
		return $db->execute($sql);
	}

	function setExpiration($key, $expires)	{
		$db  = new fsb_dbconnection;

		$exp = date('Y-m-d H:i:s', $expires);
		$val = $db->escape($value);

		$sql = "update sys_Cache set Expires='$exp' where `Key`='".$db->escape($key)."'";
		return $db->execute($sql);
	}
};
