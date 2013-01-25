<?php

function GetDeadline($reserv_days)
	{
		$SECONDS_PER_DAY = 24 * 60 * 60;
    
		$now_arr = getdate();
		$day = $now_arr["wday"];  // den v tydnu 0=nedele .. 6=sobota
	
		// pocet pracovnich dnu - resp. vsechny dny krome vikendu
		//   - je-li dnes streda az patek, pricitaji se navic dva dny
		//   - je-li dnes sobota, pricita se jeden den
		//

		$deadline = time() + $reserv_days * $SECONDS_PER_DAY;
		//if ($day == 6) $deadline += $SECONDS_PER_DAY;
		//else if ($day > 2 && $day < 6) $deadline += 2 * $SECONDS_PER_DAY;

		// posledni den rezervace plati az do 23. hod.
		//
		//$deadline +=
		//  $SECONDS_PER_DAY
		//  - (60 * (60 * ($now_arr["hours"] + 1) + $now_arr["minutes"]) + $now_arr["seconds"]);

		return $deadline;
	}

?>
