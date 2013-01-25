<?php



function action_all_reservations($user_type, $user_id, $user_to_change, $id_spojeni, $dbPrefix, $session_id){

    global $MODE_SELL;
    global $MODE_CHANGE;
    global $newRandomPassword;
    global $OK;
    
    if ($user_type == 'S' || $user_type == 'A') {
        if ($user_to_change != "" && is_numeric($user_to_change) )
    {
      $mode = $MODE_SELL;
      
      if ($user_to_change != -1)
      {
        // byl vybrán existujici uzivatel
        //
        $customer_id = $user_to_change;
      } //fi  user_to_change!=-1
      else
      { //user to change = -1
        // jedna se o volny prodej, vytvor fiktivniho uzivatele
        //
        $timestamp_id = (time() - mktime(0,0,0,1,1))."-".$session_id;
        $tmp_login = "volny-".$timestamp_id;
        
        $customer_id = -1;
        
        $errormsg="";
        
        $result = CreateCustomer($id_spojeni, $dbPrefix, $tmp_login, "Volný prodej", date("j. n. Y H:i:s"), "", $newRandomPassword, $customer_id, $errormsg);
        if ($result == $OK)
        {
        
          log_write("NEW_CUSTOMER login='$tmp_login', customer_id = $customer_id", "all_reservations");
        }
        else
        {
            return array("", null, 1, "Chyba databáze. $errormsg", null, null);
          
          log_write("INTERNAL ERROR - Can not create a customer, login=$tmp_login, result=$result", "$action");
        } //fi result OK
      } //fi user_to_change == -1
    } //fi user_to_change != ""
    else
    {
      return array("", null, 1, "Špatný parametr user_to_change = $user_to_change !");
      log_write("INTERNAL ERROR - Wrong parameter user_to_change = $user_to_change", "$action");
    }
  } //fi user type
  else if ($user_type == 'C'||$user_type == 'P')
  {
    // je prihlaseny zakaznik
    // zakaznik muze menit sve rezervace
    //
    $customer_id = $user_id;
    $mode = $MODE_CHANGE;
  }
  else 
  {
        return array("", null, 1, "Není přihlášen ani zákazník ani prodejce", null, null);
  }
//    die($customer_id);
	return array("", "choosen_table", 0, "", $mode, $customer_id);

}

?>
