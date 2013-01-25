<?php



function strip_shitty_dia ( $what ){
	$prevodni_tabulka = Array(
  'ä'=>'a',
  'Ä'=>'A',
  'á'=>'a',
  'Á'=>'A',
  'à'=>'a',
  'À'=>'A',
  'ã'=>'a',
  'Ã'=>'A',
  'â'=>'a',
  'Â'=>'A',
  'č'=>'c',
  'Č'=>'C',
  'ć'=>'c',
  'Ć'=>'C',
  'ď'=>'d',
  'Ď'=>'D',
  'ě'=>'e',
  'Ě'=>'E',
  'é'=>'e',
  'É'=>'E',
  'ë'=>'e',
  'Ë'=>'E',
  'è'=>'e',
  'È'=>'E',
  'ê'=>'e',
  'Ê'=>'E',
  'í'=>'i',
  'Í'=>'I',
  'ï'=>'i',
  'Ï'=>'I',
  'ì'=>'i',
  'Ì'=>'I',
  'î'=>'i',
  'Î'=>'I',
  'ľ'=>'l',
  'Ľ'=>'L',
  'ĺ'=>'l',
  'Ĺ'=>'L',
  'ń'=>'n',
  'Ń'=>'N',
  'ň'=>'n',
  'Ň'=>'N',
  'ñ'=>'n',
  'Ñ'=>'N',
  'ó'=>'o',
  'Ó'=>'O',
  'ö'=>'o',
  'Ö'=>'O',
  'ô'=>'o',
  'Ô'=>'O',
  'ò'=>'o',
  'Ò'=>'O',
  'õ'=>'o',
  'Õ'=>'O',
  'ő'=>'o',
  'Ő'=>'O',
  'ř'=>'r',
  'Ř'=>'R',
  'ŕ'=>'r',
  'Ŕ'=>'R',
  'š'=>'s',
  'Š'=>'S',
  'ś'=>'s',
  'Ś'=>'S',
  'ť'=>'t',
  'Ť'=>'T',
  'ú'=>'u',
  'Ú'=>'U',
  'ů'=>'u',
  'Ů'=>'U',
  'ü'=>'u',
  'Ü'=>'U',
  'ù'=>'u',
  'Ù'=>'U',
  'ũ'=>'u',
  'Ũ'=>'U',
  'û'=>'u',
  'Û'=>'U',
  'ý'=>'y',
  'Ý'=>'Y',
  'ž'=>'z',
  'Ž'=>'Z',
  'ź'=>'z',
  'Ź'=>'Z'
);

    $text = strtr($what, $prevodni_tabulka);
	return $text;

}

function createConfirmation($id_spojeni, $dbPrefix, $customer_id) {
    
    
    $sql_query = 
		"SELECT 
			SEAT.seat_number, TAABLE.label AS table_label, 
			HALL.name AS hall_name, HALLTYPE.price, HALL.name, 
      unix_timestamp(SEAT.reserv_to_dt) AS reserv_to
	 FROM ${dbPrefix}Seats SEAT  
				LEFT JOIN ${dbPrefix}Hall HALL ON SEAT.hall_ID = HALL.id
				LEFT JOIN ${dbPrefix}Hall_type HALLTYPE ON HALLTYPE.id = HALL.hall_type_ID
				LEFT JOIN ${dbPrefix}Tables TAABLE ON TAABLE.table_number = SEAT.table_number AND SEAT.hall_ID=TAABLE.hall_ID
				WHERE SEAT.customer_ID = $customer_id AND status='R'
				ORDER BY HALLTYPE.`order`, HALL.`order`, SEAT.seat_number
		";
		
			$vysledek = mysql_query ( $sql_query, $id_spojeni );
	if ( $vysledek == false )
	{
		die("Chyba cteni DB ".$sql_query);
	}

	$total_count = 0;
	$total_price = 0;

	if (mysql_num_rows($vysledek) == 0)
	{
		$ticket_res = "Nemate rezervovane zadne vstupenky\n";	
	}	
	else
	{
		$ticket_res = "Misto\tStul\tSal\tCena\tPlatnost\r\n";
		$ticket_res.= "-----\t----\t---\t----\t--------\r\n";
		while ( $arr = mysql_fetch_array ( $vysledek ))
		{
			$deadline = StrFTime ("%d.%m.%Y", $arr['reserv_to']);
			$ticket_res .= 
				$arr['seat_number']."\t".$arr['table_label']."\t".strip_shitty_dia($arr['hall_name'])."\t"
				.$arr['price']."\t".$deadline."\n";
			++$total_count;
			$total_price += $arr['price'];
		}

	}

	if ($total_count == 1) $morf = "vstupenka";
	else if($total_count > 1 && $total_count < 5) $morf = "vstupenky";
	else $morf = "vstupenek";

	$body = "Dobry den,
zasilame soucasny stav Vasich rezervaci na Matfyzacky ples 2013:

$ticket_res
Celkem $total_count $morf v celkove cene $total_price Kc.

Listky si kupte bud ve vybrane knihovne (Karlov, Karlin, ???) nebo zaplatte prevodem. Plati tyto oteviraci doby:

Knihovna Karlov: pondeli-patek 9.00-11.00 h, 13.00-16.00 (patky jen do 15.00).
Knihovna Karlin: pondeli-patek 8.30-18.00 (patky jen do 15.00)

Vstupenky budou prodany presne podle stavu rezervace, prip. zmeny si zajemci musi provest pred koupi (napr. pres PC v knihovne). Pro hladke odbaveni prosime mejte pripravenou presnou hotovost.

Pokud chcete platit prevodem, poslete $total_price Kc na ucet 670100-2205778042/6210 s variabilnim symbolem 330$customer_id . (Tyto udaje jsou take v rezervacnim systemu.)

Nastanou-li jakekoliv problemy, nebudete-li si cimkoliv jisti, napiste nam!

Zdravi
 Spolek Matfyzak
 ples.matfyzak.cz
";

    return strip_shitty_dia($body);
}

?>
