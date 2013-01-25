<?php
	
	//mozne pouziti funkci:
	//	IsSQLSafe ( IsNumeric ( $numeric_variable ))
	//	IsSQLSafe ( $string_variable )
	
	
	
	
	
	
	/*		---------------------------------------------------------------------------------------------------
	*											I s N u m e r i c    (   )
	*		---------------------------------------------------------------------------------------------------
	*	-	funkce zkontroluje, zda retezec promenne neobsahuje jine, nez numericke symboly
	*	-	pokud promenna obsahuje nenumericke symboly, skript se ukonci chybou
	*	-	funkce v pripade uspesne kontroly vraci svuj vstupni parametr, aby funkce nemusela byt volana na promennou
	*	samostatne pred jejim pouzitim
	*/
	function IsNumeric ( $ret )
	{
		$len = strlen ( $ret );
		for ($i=0;$i<$len;$i++)
		{
			if ( $ret[$i]<'0' || $ret[$i]>'9' )
				//retezec obsahuje nenumericky symbol
				die ("Validation failed. Numeric constant $ret contains inumerical character.");
		
		}
		
		//vraci vstupni parametr
		return $ret;
	
	}









	function isSQLSafe ( $ret, $connection_id )
	{
		return mysql_real_escape_string($ret, $connection_id);
		
	}	//end of function IsSQLSave



?>
