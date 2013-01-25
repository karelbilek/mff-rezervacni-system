<?php

function log_write($string, $title = "") {
	logWrite($string, $title);
}
function logWrite($string, $title = "")
	{
		$LogFile = '../../ples_log.txt';
				
			//this is built in PHP function
		$Addr = GetHostByAddr($_SERVER["REMOTE_ADDR"]);
		$head = "$Addr, " . Date("d.m.Y H:i:s");
		if ($title == "") $title = " >";

		if ($fp = FOpen($LogFile, "a")) 
		{
			fputs($fp, "\n$head - $title $string");
			FClose($fp);
		}
	}
	//------------------------------------------------------------------------------------------------------------------


	//------------------------------------------------------------------------------------------------------------------
	//				DieWithLog(dieString, logTitle, logString)
	//------------------------------------------------------------------------------------------------------------------
	// writes to log and dies 
	//
	function dieWithLog($dieString, $logString, $logTitle = "")
	{
		logWrite($logString, $logTitle);
		die($dieString);
	}

?>
