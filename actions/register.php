<?php


function action_register($loggedIn, $id_spojeni, $dbPrefix, $raw_login, $raw_password, $raw_firstname, $raw_lastname, $raw_email, $instituce, $jsem){

    if ($loggedIn) {
        return array("", "welcome", 1, "Už jste jednou přihlášen.");
    }

     global $OK, $DB_ERROR_UNIQUE;
     $login = $raw_login;
     $password = $raw_password;
     $firstname = $raw_firstname;
     $lastname = $raw_lastname;
     $email = $raw_email;
     
     if ( $login == "" || $password == "" || $firstname == "" || $lastname == "" 
            || $email == "" ) {
                    return array("", "register", 1, "Některé pole je buď prázdné nebo neplatné"); 
            }
            

     $check_mail_sql_query = "SELECT * FROM ${dbPrefix}User "
        ."WHERE address = '$email' ; ";

      $check_mail_result = mysql_query ( $check_mail_sql_query, $id_spojeni );
      if ($check_mail_result == false) {
            return array("", "register", 1, "SQL chyba.  $check_mail_sql_query"); 
                  
      } else if (mysql_num_rows($check_mail_result) != 0) {
            return array("", "register", 1, "Mail už v databázi existuje."); 
	 
      } else {
             
            $custId = 0;
            $err = "";
            $result = CreateCustomer(
                $id_spojeni, $dbPrefix,
                $login, $firstname, $lastname, $email, $password, 
                $custId, $err);
                
            if ($result == $OK) {
                 if ($instituce) {
                 
                  $fh = fopen("stat.txt", 'a');
                  if ($fh){
                     # nechci umirat na tehle blbosti :)
                    fwrite($fh, $instituce . ":" . $jsem . "\n");
                    fclose($fh);
                  }//fi fh
                  
                }//fi instituce
                
                return array("<div class='alert alert-success'>Registrace proběhla úspěšně. Nyní se můžete přihlásit.</div>", "welcome", 0, "");
                //$res = "Registrace proběhla úspěšně. Nyní se můžete přihlásit.";
            }//fi result==OK
            else
            {
                if ($result == $DB_ERROR_UNIQUE)
                {
                    return array("", "register", 1, "Uživatelské jméno $login již existuje, zkuste jiné, prosím");
                }
                else
                {
                    return array("", "register", 1, "Chyba databáze : $err");
                }
                
            }

      }//end mysql else
    
}

function action_show_register_form($loggedIn, $old_login, $old_firstname, $old_lastname, $old_email) {
    if ($loggedIn) {
        return array("", null, 1, "Už jste jednou přihlášen.");
    }
    $res = '
          <form 
              name="form-register" 
              action="'.$_SERVER['SCRIPT_NAME'].'" 
              method="post" 
              onsubmit="return check_login_form(\'form-register\');"
              >
          <div class="section" style="text-align: left">
            <table>
            <tr><td>Přihlašovací jméno<td><input type="text" name="login" id="login" size="30" value="'.$old_login.'">
            <tr><td>Heslo<td><input type="password" name="password" id="password" size="30">
            <tr><td>Heslo (pro kontrolu)<td><input type="password" name="password2" id="password2" size="30">
            <tr><td>Jméno<td><input type="text" name="firstname" id="firstname" size="30" value="'.$old_firstname.'">
            <tr><td valign=top>Příjmení<td><input type="text" name="lastname" id="lastname" size="30" value="'.$old_lastname.'">
            <tr><td>E-mail<td><input type="text" name="email" id="email" size="30">
            
            </table>
            
            <fieldset>
              <legend>Nepovinné</legend>
              <div>Instituce, ke které nejvíc náležím:<br>
              <select name="instituce" size="1">
                <option value="-" selected>—</option>
                <optgroup label="Univerzita Karlova">
                  <option value="1lf">1. lékařská fakulta</option>
                  <option value="2lf">2. lékařská fakulta</option>
                  <option value="3lf">3. lékařská fakulta</option>
                  <option value="etf">Evangelická teologická fakulta</option>
                  <option value="fhs">Fakulta humanitních studií</option>
                  <option value="fsv">Fakulta sociálních věd</option>
                  <option value="ftvs">Fakulta tělesné výchovy a sportu</option>
                  <option value="ffhk">Farmaceutická fakulta v Hradci Králové</option>
                  <option value="ff">Filozofická fakulta</option>
                  <option value="htf">Husitská teologická fakulta</option>
                  <option value="ktf">Katolická teologická fakulta</option>
                  <option value="lfhk">Lékařská fakulta v Hradci Králové</option>
                  <option value="lfp">Lékařská fakulta v Plzni</option>
                  <option value="mff">Matematicko-fyzikální fakulta</option>
                  <option value="pedf">Pedagogická fakulta</option>
                  <option value="pf">Právnická fakulta</option>
                  <option value="natur">Přírodovědecká fakulta</option>
                </optgroup>
                <optgroup label="Jiné školy">
                  <option value="cvut">ČVUT</option>
                  <option value="vscht">VŠChT</option>
                  <option value="vse">VŠE</option>
                </optgroup>
                <optgroup label="Další">
                  <option value="av">Akademie věd</option>
                </optgroup>
              </select>
              </div><div>Jsem:<br>
              <select name="jsem" size="1">
                <option value="-" selected>—</option>
                <option value="bc">Student bakalářského studia</option>
                <option value="mgr">Student magisterského studia</option>
                <option value="dc">Doktorand</option>
                <option value="uci">Učitel/vědecký pracovník</option>
              </select>
              </div>
            </fieldset>
            
          </div>
          <div class="section">
            <input type="submit" class="btn btn-success" value="Registrovat"/>
            <input type="hidden" name="act" value="do_register"/>
          </div>
          </form>
        ';
        return array($res, null, 0, "");
}

?>
