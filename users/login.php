<?php

function login($login, $password, $id_spojeni, $dbPrefix) {
$error = 0;

$msg="";
    if ($login == "")
    {
        $msg = "Chyba při přihlášení – chybí login";
        $error = 1;
        $log = "LOGIN_FAILED[11]";
    }
    else
    {
    $session_key="";
      // povol prihlaseni jen aktivnim uzivatelum (Deleted ani necti, 
      //   Banned odfiltruj nize - aby zakazany vedel, ze ma sice spravne heslo, ale 
      //   my ho nechceme)
      // prihlasit se mohou uzivatele jakehokoliv typu (Admin, Seller, Customer),
      //   opravneni se overi az v konkretnich aplikacich 
      //
		
		//KB: Mirne prepisuji
		//Nejdriv se najde uzivatel.
 	    $sql_query = "SELECT id, hashed_password FROM ".$dbPrefix."User "
          ."WHERE status <> 'D' " //D - deleted uzivatel
          ."AND login = '$login'; ";

		 $result = mysql_query ( $sql_query, $id_spojeni );
		  if ($result == false)
		  {
			  $msg = "Chyba přístupu k databázi";
			  $error = 1;
			  $log = "DB_ERROR [8] $sql_query";
		  }
		  else if (mysql_num_rows($result) == 0)
		  {
			  $msg = 
          "Neexistující uživatel $login. "
          ."Zkuste to prosím <a href=\"".$_SERVER['SCRIPT_NAME']."\">znovu</a>.";
      logWrite($sql_query);
            $error = 1;
			  $log = "LOGIN_FAILED [9] $sql_query";
		  }
		
		//KB: Ted vezme jeho heslo
		if ($error == 0) {
			$arr = mysql_fetch_array($result);
		    $DB_hashed_password = $arr['hashed_password'];

			$post_hashed_password = crypt($password, $DB_hashed_password);

			if ($post_hashed_password != $DB_hashed_password) {
				$msg = 
	          "Špatné heslo pro uživatele $login. "
	          ."Zkuste to prosím <a href=\"".$_SERVER['SCRIPT_NAME']."\">znovu</a>.";
	      logWrite($sql_query);
	      $error = 1;
				  $log = "LOGIN_FAILED [9] $sql_query";
			}
		}
		

		//KB: ted uz se spravne kontroluji nejake statusy nebo co
		//NEKONTROLUJI HESLO, to uz je vys
		
		if ($error ==0) {
		
 		    $sql_query = "SELECT id, status FROM ".$dbPrefix."User "
	          ."WHERE status <> 'D' "
	          ."AND login = '$login'; ";
	      
			  $result = mysql_query ( $sql_query, $id_spojeni );
			  if ($result == false)
			  {
				  $msg = "Chyba přístupu k databázi";
				  $error = 1;
				  $log = "DB_ERROR [8] $sql_query";
			  }
			  else if (mysql_num_rows($result) == 0)
			  {
				  $msg = 
	            "Neexistující uživatel nebo špatné heslo. "
	            ."Zkuste to prosím <a href=\"".$_SERVER['SCRIPT_NAME']."\">znovu</a>.";
	        logWrite($sql_query);
	        $error = 1;
				  $log = "LOGIN_FAILED [9] $sql_query";
			  }
			  else
			  {
			      $arr = mysql_fetch_array($result);
			      $user_id = $arr['id'];
			      $status = $arr['status'];
			      
			      if ($status != 'A'&&$status != 'C'&&$status != 'S'&&$status != 'P')  // přihlásit se mohou jen aktivní uživatelé
			      {
			          $msg = "Přístup odepřen";
			          $error = 1;
			          $log = "ACCESS_DENIED [10]";
	          	}
	          	else
	    		  {
	    			  logWrite("LOGIN successfull");
	    		  }
	    	}
		}

			//KB: netusim, proc autor dela session pres PHP a MySQL, ale 
			//nemam naladu to prepisovat
			
		  // je-li vse v poradku, zaloz session
		  //	
		  if ($error == 0)
		  {

			  // zalozeni session
			  //
			  $time = Time();
			  $time_sql = date("YmdHis", $time);

			  $sql_query = 
            "INSERT INTO ".$dbPrefix."Session(`user_ID`, `timestamp`) "
            ."VALUES('$user_id', '$time_sql'); ";
            
			  $result = mysql_query ( $sql_query, $id_spojeni );
			  if ($result == false)
			  {
				  $msg = "A Chyba přístupu k databázi. Zkuste se prosím přihlásit znovu.". $sql_query;
				  $error = 1;
				  $log = "DB_ERROR [1] $sql_query";
			  }
			  else if (mysql_affected_rows($id_spojeni) == 0)
			  {
				  $msg = "B Chyba přístupu k databázi. Zkuste se prosím přihlásit znovu.";
				  $error = 1;
				  $log = "LOGIN_FAILED [2] $sql_query";
			  }
			  else 
			  {
				  $sid = mysql_insert_id($id_spojeni);
			  }

			 if (!$error) {

			  $session_key = md5($sid.$time).$sid;

			  $sql_query = "UPDATE ".$dbPrefix."Session SET session_key = '$session_key' WHERE id = $sid; ";
			  $result = mysql_query ( $sql_query, $id_spojeni );

			  if ($result == FALSE)
			  {
				  $msg = "C Chyba přístupu k databázi. Zkuste se prosím přihlásit znovu.";
				  $error = 1;
				  $log = "DB_ERROR [3] $sql_query";
			  }
			  else if (mysql_affected_rows($id_spojeni) != 1)
			  {
				  $msg = "Chyba přístupu k databázi. Zkuste se prosím přihlásit znovu.";
				  $error = 1;
				  $log = "LOGIN_FAILED [4] $sql_query";
			  }
			  else 
			  {
				  logWrite("session created, sid = $sid, session_key = $session_key");
			  }
         }
      }
    }

    if ($error == 0)
    {
        $logged_in = true;
        $msg = "Přihlášení proběhlo úspěšně.";
    }
    else
    {
        logWrite($log);
        $logged_in = false;
    }
   return array($logged_in, $msg, $session_key, $error);
}

?>
