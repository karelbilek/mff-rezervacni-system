<?php
$RESERV_TOO_SOON = 10;
$RESERV_TOO_LATE = 11;
$RESERV_OK=1;

function reservationTimeCheck($user_type, $reserv_from_dt, $reserv_to_dt) {

    global $RESERV_TOO_SOON, $RESERV_TOO_LATE,$RESERV_OK;

    // $allow_reserve_before_start = false;
    $now = Time();
    if ($user_type == 'S' || $user_type == 'A' || $user_type == 'P') {
                            return $RESERV_OK;
    }
    if ($now >= $reserv_to_dt) {

                    // konec rezervaci plati jen pro zakazniky
                    return $RESERV_TOO_LATE;
                } else if ($now < $reserv_from_dt) {
                    return $RESERV_TOO_SOON;
                } else {
                    
                    return $RESERV_OK;
                }       
}


?>

