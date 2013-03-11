<?php
error_reporting(-1);

include "../dbs/inc.php";
include "../localSettings.php";
include "../reservations/delOldRes.php";

$id_spojeni = connectOrDie($dbAddress, $dbLogin, $dbPasswd, $dbName);
delOldRes($id_spojeni, $dbPrefix);

die("in pain");

$sql_query_bc = "UPDATE `rzrv_Seats` SET `status` = 'L' WHERE `seat_number` > 200 AND `seat_number` < 241 AND `hall_ID` = '12'";

mysql_query ( $sql_query_bc, $id_spojeni );


$sql_query_bc = "UPDATE `rzrv_Seats` SET `status` = 'A' WHERE `seat_number` > 240 AND `seat_number` < 261 AND `hall_ID` = '12'";

mysql_query ( $sql_query_bc, $id_spojeni );


?>
