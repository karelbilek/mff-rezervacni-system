<?php


function text_uplaceni($tickets_count, $tickets_price, $customer_id) {
    $res = "<div class='well'><table><tr><td style='border-right:1px solid black; '><table style='font-weight:bold; width:190px'>\n";
    $res .= "<tr><td>Počet vstupenek:</td><td>$tickets_count</td></tr>\n";
    $res .= "<tr><td>Celková cena:</td><td style='padding-right:1em;margin-right:1em'>$tickets_price Kč</td></tr>\n";
    $res .= "</table></td><td style='padding-left:1em'><div>Lístky si kupte buď u vybraného prodejce (knihovna Malá Strana, Karlín, lab 17.listopadu), nebo zaplaťte převodem. <br><br>Pro platbu převodem pošlete částku <b>$tickets_price Kč</b> na účet <b>670100-2208523328/6210</b> s variabilním symbolem <b>330".$customer_id."</b>. <br><br>Do knihovny je nutné donést <b>celou částku $tickets_price Kč</b>.</div></td></tr></table></div>\n";    
    return $res;
}



?>
