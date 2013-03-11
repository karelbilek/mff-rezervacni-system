<?php


function sendConfirmation($conn, $customer_id, $subject, $body, $dbPrefix)
	{
		global $OK, $DB_ERROR, $CUST_DOESNT_EXIST, $MAIL_ERROR, $MAIL_NO_ADDRESS;

		$sql_query = "SELECT address FROM ${dbPrefix}User WHERE id = $customer_id; ";
		$result = mysql_query($sql_query, $conn);
		

		if ($result == FALSE) $retval = $DB_ERROR;
		else if (mysql_num_rows($result) == 0) $retval = $CUST_DOESNT_EXIST;
		else
		{
			$arr = mysql_fetch_array($result);

			$to = $arr['address'];
	
			if ($to == "")
			{
				return $MAIL_NO_ADDRESS;
			}

			$from = 'ples@matfyzak.cz';
			$date = date('r');
			$headers = "From: $from\r\n"
				."Date: $date\r\n"
				."Content-Type: text/plain; charset=iso-8859-2\r\n"
				."Content-Transfer-Encoding: 8bit\r\n";
			
			// konvertuji do standardniho kodovani
			//   - pozor, iconv neumi konvertovat dlouhou pomlcku 0x96, musim rucne
			//
			$body = str_replace(chr(0x96), '-', $body);
			$body = iconv('CP1250', 'ISO-8859-2', $body);

			if (!$body) 
			{ 
				$retval = $MAIL_ERROR;
			}
			else if (mail($to, $subject, $body, "From: $from\r\nDate: $date\r\n"))
			{
				$retval = $OK;
			}
			else
			{
				$retval = $MAIL_ERROR;
			}
		}
		
		return $retval;
	}
?>
