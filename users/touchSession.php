<?php

//I have no idea what happens here
function touchSession($conn, $session_key,  $dbPrefix)
	{
		global $OK, $SESS_DOESNT_EXIST, $DB_ERROR;	

		$now = Time();
		$now_sql = date("YmdHis", $now);

		$sql_query = 
       "SELECT S.id, S.user_ID, U.login, U.status "
      ."FROM ".$dbPrefix."Session S INNER JOIN ".$dbPrefix."User U ON S.user_ID = U.id "
      ."WHERE S.session_key = '$session_key'; ";
		$result = mysql_query($sql_query, $conn);

		if ($result == FALSE)
		
		{
		echo "$sql_query";
			$retval = $DB_ERROR;
		}
		else if (mysql_num_rows($result) != 1)
		{
			$retval = $SESS_DOESNT_EXIST;
		}
		else
		{
			$arr = mysql_fetch_array($result);
			$session_id = $arr["id"]; 
			$user_id = $arr["user_ID"];
            $login = $arr["login"];

			$user_type = $arr["status"];
			$retval = $OK;
		}

		if ($retval != $OK) return array($retval,  0, 0, 0, 0, 0);

		$sql_query = "UPDATE ".$dbPrefix."Session SET timestamp = $now_sql WHERE id = $session_id; ";
		$result = mysql_query($sql_query, $conn);
		if ($result == FALSE)
		{
			$retval = $DB_ERROR;
		}
		// test affected_rows predpoklada connect flag CLIENT_FOUND_ROWS
		//
		else if (mysql_affected_rows($conn) == 0) 
		{
			$retval = $SESS_DOESNT_EXIST;
		}
		else
		{
			$retval = $OK;
		}

		return array($retval, $session_id, $user_id, $user_type, $login);
	}


?>
