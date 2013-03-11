<?php

function text_mailem($ticket_res, $total_count, $morf, $total_price, $customer_id) {
    $body = "Dobry den,
zasilame soucasny stav Vasich rezervaci na Matfyzacky ples 2013:

$ticket_res
Celkem $total_count $morf v celkove cene $total_price Kc.

Listky si kupte bud ve vybrane knihovne (Mala Strana, Karlin, lab 17. listopadu) nebo zaplatte prevodem. Plati tyto oteviraci doby:

Knihovna Mala Strana: pondeli-patek 9.00-16.00 (patky jen do 15.00) (od 18. 2. 11:30)
Knihovna Karlin - pondeli - patek 8.30-18.00 (patky jen do 15.00) (od 18. 2. 11:30)
Lab 17 listopadu: pouze stredy (20. a 27. 2. 2013) v dobe 0-16 h a ctvrtek 28. 2. v dobe 8-11 h.



Vstupenky budou prodany presne podle stavu rezervace, prip. zmeny si zajemci musi provest pred koupi (napr. pres PC v knihovne). Pro hladke odbaveni prosime mejte pripravenou presnou hotovost.

Pokud chcete platit prevodem, poslete $total_price Kc na ucet 670100-2208523328/6210 s variabilnim symbolem 330$customer_id . (Tyto udaje jsou take videt v rezervacnim systemu.)

Nastanou-li jakekoliv problemy, nebudete-li si cimkoliv jisti, napiste nam!

Zdravi
 Spolek Matfyzak
 ples.matfyzak.cz
";
    return $body;
}


?>
