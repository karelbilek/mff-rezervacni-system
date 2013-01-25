<?php
 
function connectOrDie($dbAddress, $dbLogin, $dbPasswd, $dbName) {

    $client_flags = 2;
    $new_link = true;

		$id_spojeni=mysql_connect ($dbAddress, $dbLogin, $dbPasswd, $new_link, $client_flags);
		if ($id_spojeni)
		{
			//jsme pripojeni k databazi
			$database = mysql_select_db ($dbName,$id_spojeni);
			if (!$database)
			{
				DieWithLog("Doslo k chybe... nemohu zvolit databazi\n".$page_footer, "DB_ERROR - SELECT_DB");
			}
		}
		else
		{
			DieWithLog("Doslo k chybe... nemohu se pripojit k databazi\n".$page_footer, "DB_ERROR - CONNECTION");
		}

        mysql_set_charset("utf8", $id_spojeni);
		return $id_spojeni;
}

?>
