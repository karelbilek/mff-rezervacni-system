<?php

function admin_action_new_vip_customers_do($id_spojeni, $dbPrefix, $userType, $name) {
    global $newRandomPassword;
    global $OK;
    
    $timestamp_id = (time() - mktime(0,0,0,1,1));
    $tmp_login = "vip-".$timestamp_id;
    
    $customer_id = -1;
    
    $errormsg="";
    
    $result = CreateCustomer($id_spojeni, $dbPrefix, $tmp_login, $name, date("j. n. Y H:i:s"), "", $newRandomPassword, $customer_id, $errormsg);
        
    if ($result == $OK){
    
        $sql_query = 'UPDATE '.$dbPrefix.'User SET status=\'V\' WHERE id='.$customer_id.' LIMIT 1' ;
         $vysledek = mysql_query ( $sql_query, $id_spojeni );
	    if ( $vysledek == FALSE ) {
	        return array("", null, 1, "Chyba čtení databáze. ".$sql_query);
	    
	    }
    
        return array("Super.", "vip_choosen_table", 0, "",  $customer_id);
    } else {
        return array("", null, 1, "Chyba databáze. $errormsg");
    }
}


function admin_action_new_vip_customers_pre($id_spojeni, $dbPrefix, $userType) {
    if ($userType != 'A') {
        return array("", null, 1, "Nejsi admin!");
    }
    
    $onClickNew = 'login_el=($("#login").val());

                    fc=document.forms["form-menu"];
                    fc.act.value = "new_vip_do";
                    fc.par1.value=login_el;
                    fc.submit();
                    return false;
                    ';
    
    $html='<form name="newf" action="/rzrv/index.php" method="post">';
    $html.='Jméno:<br>';
    $html .= '<input type="text" id="login" name="login" maxlength="20"><br>';
    $html .= '<input type="submit" value="Rezervovat" onclick = \''.$onClickNew.'\' class="btn btn-success">';
    $html .= '<br>(moc se to nějak nekontroluje, tak neblbnout.)';
    $html .= '</form>';
    return array($html, null, 0, "");
    

}

function admin_action_vip_customers($id_spojeni, $dbPrefix, $userType) {
    
    if ($userType != 'A') {
    	    return array("", null, 1, "Nejsi admin!");
    }

    

    $res = "<h3>VIP pseudo-uživatelé</h3>";
    $res .= "<p>VIP pseudo-uživatele může spravovat jenom admin a jenom admin může za ně rezervovat a odrezervovávat.</p>";
    $res .= "<p>VIP rezervace nepropadají a může jich mít VIP uživatel kolik chce. Na VIP uživatele se nejde nalogovat. (Proto \"pseudo-uživatel\".)</p>";
    
    $res .= "<p>Tj. tzv. rezervy jsou typ VIP uživatele :)</p>";
    
    $onClick = 'return onNavigate(\'form-menu\', \'vip_new_pre\');';
    $res .= "<a href=\"\" onclick=\"$onClick\"><b>Nový VIP uživatel</b></a><br><br>";

    $sql_query="
    SELECT U.firstname, U.id , U.login ".
    "FROM ${dbPrefix}User U WHERE U.status = 'V' ";
   $vysledek = mysql_query ( $sql_query, $id_spojeni );
	if ( $vysledek == FALSE ) 
	{
	    return array("", null, 1, "Chyba čtení databáze. ".$sql_query);
	    
	}
	
	$res .=  "<table class=\"table table-striped\">\n"; 
    $res .=  "<tr>";
    $res .=  "<th>Jméno</th><th>Míst</th><th>Vytvořit rezervaci</th>";
	
	while ( $radek=mysql_fetch_array($vysledek))
	
    {
        $fn = $radek['firstname'];
        //$onClickN = "fc=document.forms['form-menu']; fc.act.value='reservations'; fc.par1.value=".$radek['id']."; fc.submit();";
        $onClick = 'return onNavigate(\'form-menu\', \'vip_choosen_table\', '.$radek['id'].');';

	    $res .=  "<tr>";
 		$res .=  "<td>".$fn."</td>";
 		
 		$sql_cquery=" SELECT COUNT(*) cu ".
            "FROM ${dbPrefix}Seats S WHERE S.status = 'V' AND S.customer_id='".$radek['id']."' ";
        $cvysledek = mysql_query ( $sql_cquery, $id_spojeni );
	    if ( $cvysledek == FALSE ) {
	        return array("", null, 1, "Chyba čtení databáze. ".$sql_cquery);	    
	    }
	    $cr = mysql_fetch_array($cvysledek);
	    
	    $cu = $cr['cu'];
 		
 		//SELECT * FROM ${dbPrefix}Seats S where customer_ID= U.id AND S.status='V'
    $res .=  "<td>$cu</td>";
    
  		 $res .=  '<td><a href="" onclick="'.$onClick.'">Seznam/Vytvořit</a></td>';
  		  		
  		$res .=  '</tr>';
	}
	
	$res .= '</table>';
    return array($res, null, 0, "");



}

?>
