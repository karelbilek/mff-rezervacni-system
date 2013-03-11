<?php
error_reporting(-1);

include "mbank.php";
include "send_mails.php";
include "pdf_gen/pdf.php";
include "../dbs/inc.php";
include "../localSettings.php";

//hack, nutno nastavit na online usera
$onlineuser = 51;

include "../reservations/delOldRes.php";

$id_spojeni = connectOrDie($dbAddress, $dbLogin, $dbPasswd, $dbName);
delOldRes($id_spojeni, $dbPrefix);


$array = get_logged_mbank();

echo "=================";

function get_random_string() {
    $length=50;
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

function get_free_barcode($id_spojeni , $dbPrefix) {
    $sql_query_bc = "SELECT  `barcode` 
		        FROM  `${dbPrefix}Barcodes` 
		        WHERE sold  = 'N'
		        ORDER BY  `Barcode` ASC 
		        LIMIT 1";
	            $result_bc = mysql_query ( $sql_query_bc, $id_spojeni );
	            if ( $result_bc == FALSE) {
		            die( "Neco s databazi pri query $sql_query_bc<br>");
		            return;
	            }
	            $arr_bc = mysql_fetch_array($result_bc);
	            $barcode = $arr_bc["barcode"];
	            $sql_query = "
		            UPDATE ${dbPrefix}Barcodes SET Sold = 'Y'
		            WHERE Barcode = $barcode
		        LIMIT 1";
	            $result_bc = mysql_query ( $sql_query, $id_spojeni );
	            if ( $result_bc == FALSE) {
	                die ("Neco s databazi pri query $sql_query<br>");
		            return;
	            }
	            return $barcode;
}

foreach ($array as $customer_id=>$castka) {
    
    #chyby
    #a jejich ad-hoc doresovani
    if ($customer_id==218) {
    
        $castka=1200;
    }
    $sql_user = "SELECT U.login, U.firstname, U.lastname, U.address FROM ".
                "${dbPrefix}User U WHERE U.id = $customer_id;";
    $vysledek_user = mysql_query ($sql_user, $id_spojeni );
	if ( $vysledek_user == false ) {
		die("CHYBA $sql_user");
	}  
	if (mysql_num_rows($vysledek_user)!=1) {
	    echo "CHYBA V USERU ".$customer_id."<br>";
	}
	
	$user = mysql_fetch_array ($vysledek_user);
	$celeJmenoUseru = $user['firstname']." ".$user['lastname'];
	$files = array();
    $sql_query = 
				"SELECT 
				  SEAT.seat_number,   
				  HALL.id AS hall_ID, HALL.name AS hall_name, HALLTYPE.price, TAABLE.label as table_label,
          unix_timestamp(SEAT.reserv_to_dt) AS reserv_to ,
          SEAT.reserv_to_dt, SEAT.reserv_dt
				FROM ${dbPrefix}Seats SEAT  
				LEFT JOIN ${dbPrefix}Hall HALL ON SEAT.hall_ID = HALL.id
				LEFT JOIN ${dbPrefix}Hall_type HALLTYPE ON HALLTYPE.id = HALL.hall_type_ID
				LEFT JOIN ${dbPrefix}Tables TAABLE ON TAABLE.table_number = SEAT.table_number AND SEAT.hall_ID=TAABLE.hall_ID
				WHERE SEAT.customer_ID = $customer_id AND status='S'
				ORDER BY HALLTYPE.`order`, HALL.`order`, SEAT.seat_number";

        $vysledek = mysql_query ($sql_query, $id_spojeni );
		if ( $vysledek == false ) {
			    die("CHYBA! $sql_query");
		}
		$res = "";
		$all_price = 0;
		while ($arr = mysql_fetch_array ($vysledek )) {
				$u_price  = $arr ['price'];
				$all_price += $u_price;
		}
		if ($all_price==0) {
		    echo "Uzivatel ".$user['login']." ( ".$user['firstname']." ".$user['lastname'].") nema koupene nic <br>";
		    continue;
		}
		

		if ($all_price!=$castka) {
		    echo "CHYBA - Uzivatel ".$user['login']." ( ".$user['firstname']." ".$user['lastname'].") neposlal presne, mel : $all_price , poslal $castka <br>";
		    
		    continue;
		}
		
		 echo "OK - Uzivatel ".$user['login']." ( ".$user['firstname']." ".$user['lastname'].") poslal presne, mel : $all_price , poslal $castka <br>";
	    
	    
	    
}
echo "ALL OK";

?>
