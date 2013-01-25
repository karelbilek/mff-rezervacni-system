<?php

function logout($id_spojeni, $session_id, $dbPrefix) {

	  $sql_query = "DELETE FROM ".$dbPrefix."Session WHERE id = $session_id LIMIT 1; "; 
        
	  $result = mysql_query ( $sql_query, $id_spojeni );
	  if ($result == false)
	  {
		  $msg = "Chyba přístupu k databázi. Zkuste se prosím odhlásit znovu.".$sql_query;
		  $error = 1;
		  log_write("DB_ERROR [1] $sql_query", "logout");
		  $logged_in=true;
	  }
	  else if (mysql_affected_rows($id_spojeni) == 0)
	  {
		  $msg = "Odhlášení proběhlo úspěšně. Vlastně jste ani nebyl přihlášen :-)";
		  log_write("LOGOUT_FAILED [2] $sql_query", "logout");
		  $logged_in = false;
	  }
	  else 
	  {
		  $msg = "Odhlášení proběhlo úspěšně";
		  $logged_in = false;
	  }
	  return array($logged_in, $msg);

}

?>
