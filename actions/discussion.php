<?php


function action_discussion_delete($user, $id_spojeni, $dbPrefix, $messageID, $user_type) {


    if ($user_type!='A') {
         $authorship = 'AND U.id='.$user;
    } else{
        $authorship = "";
    }
    
    $sql_query = "
      SELECT
       P.forced_key, U.id, P.text, U.login, unix_timestamp(P.time) AS time
      FROM ${dbPrefix}Prispevek P
      LEFT JOIN ${dbPrefix}User U ON P.user_ID = U.id
      WHERE P.forced_key=${messageID} $authorship
      ORDER BY P.time DESC";
      //die($sql_query);
      
       $vysledek = mysql_query ( $sql_query, $id_spojeni );
    
    if ( $vysledek == FALSE ) {

	    return array("", null, 1, "?Error reading database.".$sql_query);
    }
    if (mysql_num_rows($vysledek)==1) {
        
        $sql_query = "
      DELETE
      FROM ${dbPrefix}Prispevek
      WHERE forced_key=${messageID};";
        $vysledek = mysql_query ( $sql_query, $id_spojeni );
        
        if ( $vysledek == FALSE ) {

	        return array("", null, 1, "?Error deleting..".$sql_query);
        } else {
                return array("", "discussion", 0, "");
        }
        
        
    } else {
        return array("", null, 1, "Error deleting.");
    } 

}

function action_discussion_send($user, $id_spojeni, $dbPrefix, $pureMessage) {
    
    if ($pureMessage=="") {
        	    return array("", "discussion", 1, "Prázdná zpráva.");
    }
    
    $now = date("YmdHis", Time());
    $sql_query = " INSERT INTO ${dbPrefix}Prispevek (user_id, time, text) VALUES ($user, '$now', '$pureMessage')";
    $vysledek = mysql_query ( $sql_query, $id_spojeni );
    
    if ( $vysledek == FALSE ) {

	    return array("", null, 1, "?Error reading database.".$sql_query);
    }
    else {
        return array("", "discussion", 0, "");
    }
    
}


function action_discussion_display($user, $id_spojeni, $dbPrefix, $userType) {

    $sql_query = "
      SELECT
       P.forced_key, U.id, P.text, U.login, unix_timestamp(P.time) AS time
      FROM ${dbPrefix}Prispevek P
      LEFT JOIN ${dbPrefix}User U ON P.user_ID = U.id
      ORDER BY P.time DESC";
      
    $vysledek = mysql_query ( $sql_query, $id_spojeni );
    if ( $vysledek == FALSE ) {

	    return array("", null, 1, "?Error reading database.".$sql_query);
    }
    
    

    $res = '<h3>Diskuze</h3>';
    
    
    
    
    if ($user!=-1) {
        $res .= '<p>Můžete se zde seznamovat nebo nabízet lístky. Svoje příspěvky můžete smazat, nejnovější příspěvky jsou nahoře. Uveďte i svůj kontakt, pokud chcete.</p>';
    
    $onClick =  "fc=forms[\"form-menu\"]; "
                    ."fc.act.value = \"discussion-send\"; "
                    ."fc.par1.value = $(\"#novytext\").val();"
					."fc.submit(); "
					."return false; ";
//					$onClick = 'alert('')'
        $res .= '<div class="well"><h4>Nový příspěvek</h4><textarea id="novytext" name="novytext" style="width: 460px;min-width: 50%; max-width: 100%; height: 13em;"></textarea><br><button class="btn btn-primary" type="button" onClick=\''.$onClick.'\'>Odeslat</button></div>';
    }
    else {
        $res .= '<p>Pokud chcete přispívat, přihlašte se.</p>';
    }
    
    
    
    while ( $row = mysql_fetch_array ( $vysledek )) {
        $res .= '<div class="clearfix">';
        
        $res .= '<div class="prispevek dropdown-menu">';
        $res .= '<p style="margin-left:1em;margin-right:1em">'.$row['login'].' ('.StrFTime ("%d.%m.%Y, %H:%M",$row['time']).')';
        
        $text = $row['text'];
        $text = str_replace("<", "&lt;", $text);
    $text = str_replace( ">", "&gt;", $text);
    $text = str_replace("\n", "<br>", $text);
    $text = str_replace("\r", "", $text);
            $text = str_replace("\\'", "'", $text);
                    $text = str_replace('\"', '"', $text);
        if ($userType=='A' || $user == $row['id']) {
        
            $onClick = 
					 "fc=forms['form-menu']; "
                    ."fc.act.value = 'discussion-delete'; "
                    ."fc.par1.value = ".$row['forced_key']."; "
					."fc.submit(); "
					."return false; ";
					
            $res .= ' - <a href="" onclick="'.$onClick.'">smazat</a>';
        }
        
        $res .= '</p><hr style="margin-top: 0; margin-bottom: 0.4em;">';
        $res .= '<p style="margin-left:1em;margin-right:1em">'.$text.'</p>';
        
        $res .= '</div>';
        
        $res .= '</div>';
        
    }

    
    return array($res, null, 0, "");

}

?>

