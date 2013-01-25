<?php
	$OK = 0;
	$DB_ERROR = 1;
	$DB_ERROR_UNIQUE = 15;
	$MAIL_ERROR = 2;
	$CUST_DOESNT_EXIST = 3;
	$CUST_WRONG_PWD = 4;
	$SESS_DOESNT_EXIST = 5;
	$MAIL_NO_ADDRESS = 6;
	
	$MODE_VIEW = 10;
	$MODE_CHANGE = 11;
	$MODE_SELL = 12;
	$MODE_LOGIN = 13;
	$MODE_NEW = 14;

	
	                                                                                        
function CreateCustomer($conn, $dbPrefix, $login, $firstname, $lastname, $email, $password, &$custId, &$err)
	{
		global $OK, $DB_ERROR, $DB_ERROR_UNIQUE, $CUST_DOESNT_EXIST, $CUST_WRONG_PWD;

		$hashed_password = crypt($password);

		$sql_query = "
        INSERT INTO ${dbPrefix}User( 
            status, login, firstname, lastname,  
            address, hashed_password) 
        VALUES (
            'C', '$login', '$firstname', '$lastname', 
            '$email',  '$hashed_password')
		    ";
		    
		$result = mysql_query($sql_query, $conn);
		if ($result == false)
		{
      $errno = mysql_errno($conn);
      if ($errno == 1062)
      {
          $retval = $DB_ERROR_UNIQUE;
          $err="AAA";
      }
      else
      {
    			$retval = $DB_ERROR;
    			$err = "DB_ERROR - $sql_query";
    			log_write("DB_ERROR - $sql_query", "CreateCustomer");
    	}
		}
		else
		{
			$custId = mysql_insert_id($conn);
			$retval = $OK;
		}
		return $retval;
	}

?>
