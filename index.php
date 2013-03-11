<?php
error_reporting(-1);
//var_dump($_POST);
//die();


include "localSettings.php";
include "texts/inc.php";
include "actions/inc.php";
include "dbs/inc.php";
include "reservations/inc.php";
include "users/inc.php";
include "htmlTemplate/inc.php";
include "mailing/inc.php";
include "admin_actions/inc.php";
  	//include "dbs/Functions.php";
  
  	//bezpecnostni funkce
  	//
  	//include "SecurityFunctions.php";
  
  	//pripojeni k databazi
  	
//TODO:
//pro adminy:
//$TICKET_COUNT_LIMIT = 1000;
//$allow_reserve_before_start = true;




$SESS_DOESNT_EXIST = -1;
$DB_ERROR = -2;
$OK = 1;

//pomocna funkce
function startsWith($haystack,$needle) {
    return strpos($haystack, $needle, 0) === 0;
}


$id_spojeni = connectOrDie($dbAddress, $dbLogin, $dbPasswd, $dbName);

function maybe_post($what) {
    global $id_spojeni;
    if (array_key_exists($what, $_POST)) {
        return isSQLSafe($_POST[$what], $id_spojeni);
    } else {
        return "";
    }
}

//TODO: odstranit magickou hodnotu
delOldRes($id_spojeni, $dbPrefix);

  	  
    // nacti session_id, je-li
    //
    if (!isset($_POST['sid']))
    {
        $session_key = "";
    }
    else
    {
        $session_key = isSQLSafe( $_POST['sid'], $id_spojeni );
    }
		
    // nacti action, je-li
    //
    if (!isset($_POST['act']))
    {
        $action = "";
        $action_par1="";
        $action_par2="";
        $action_par3="";
    }
    else
    {
        $action = isSQLSafe( $_POST['act'] , $id_spojeni);
        
        if (!isset($_POST['par1'])) {$action_par1 = ""; } else { $action_par1 = isSQLSafe( $_POST['par1'] , $id_spojeni); }
        if (!isset($_POST['par2'])) {$action_par2 = ""; } else { $action_par2 = isSQLSafe( $_POST['par2'] , $id_spojeni); }
        if (!isset($_POST['par3'])) {$action_par3 = ""; } else { $action_par3 = isSQLSafe( $_POST['par3'] , $id_spojeni); }
    }
    		
    // zaloguj vstupni parametry
    //
    logWrite("", "$action, par1=$action_par1, par2=$action_par2, par3=$action_par3, sid=$session_key");    		
    
//Vsechno vstupuje jednim indexem a nicim jinym (povetsinou).


    // Registrace noveho uzivatele?

    $new_registration = (
        $session_key == "" 
        && $action == "do_register"  || $action == "register"
        );

    
    $error = 0;
    $msg = "";
    

    $logged_in = false;
    //$session_id = -1;
    
    $user_id = -1;
    $user_type = "";
    $user_name="";
    
    // pokousi se uzivatel prihlasit?
    //
    if ($session_key == "" && $action == "login" )
    {
	
        $login = isSQLSafe( $_POST['login'],$id_spojeni);
        $password = isSQLSafe ( $_POST['password'],$id_spojeni);
        // zkus prihlasit uzivatele, podle vysledku se nastavi 
        //   - $msg - chybova hlaska
        //   - $logged_in - zdalipak je uzivatel prihlasen
        //
        list($logged_in, $msg, $session_key, $error) = login($login, $password, $id_spojeni, $dbPrefix);
        
        $action = "";
        
    }  
      		

    if ($session_key != "")
    {
        list($result, $session_id, $user_id, $user_type, $user_name) = touchSession($id_spojeni, $session_key, $dbPrefix);


        if ($result == $SESS_DOESNT_EXIST)
        {

            $msg = "Nejste přihlášen";
            $error = 1;
            log_write("Session doesn't exist, session_key = $session_key, result = $result");
        }
        else if ($result == $DB_ERROR)
        {

            $msg = "Došlo k chybě databáze";
            $error = 1;
        }
        else
        {
            $logged_in = true;
            log_write("Session touched, user_id = $user_id, user_type = $user_type");
        }
    }

    // pokud se uzivatel chce odhlasit, odhlas ho jeste pred zacatkem
    // vykreslovani stranky -- sice je prihlasen, ale stranka se bude
    // vykreslovat jako by uz nebyl
    //    		
    if ($action == "logout" && $error!=1)
    {
      list($logged_in, $msg) = logout($id_spojeni, $session_id, $dbPrefix);
      $action = "";  // prihlasovaci stranka
    }		

    // neni-li zadna akce, zobraz uvitaci stranku, Vitejte na Kypru
    //
    if ($action == "") $action = "welcome";
    

    // neni-li uzivatel prihlasen, jsou povoleny jen vyjmenovane akce
    // 
    if (!(
      $logged_in || $action == "welcome" || $action == "discussion" || $new_registration
      ))
    {
        $msg = "Nejste přihlášen";
        $error = 1;
        log_write("Not authorized, session_key = $session_key, action = $action");      
    }

    list($HTML_begin,$HTML_end) = HTML_base($logged_in, $action);
//HTML_menu($logged_in, $user_type, $user_id, $user_name, $action, $hall_if_display, $dbPrefix, $id_spojeni)
    $HTML_begin .= HTML_menu($logged_in, $user_type, $user_id, $user_name, $action, $action_par1, $dbPrefix, $id_spojeni);

	//hack
    list($HTML_begin_c, $HTML_end_c) = HTML_content_div();
    $HTML_begin .= $HTML_begin_c;
    $HTML_end = $HTML_end_c.$HTML_end;
    $HTML_end = HTML_hiddenForm($session_key).$HTML_end;
    
    $HTML_main="";

    //hlavni "smycka"
    //probiha tak, ze z kodu nad tim prijde action, ktera rekne, co se ma delat, pripadne msg a error
    //to se zpracuje, muze z toho pripadne vyskocit dalsi action

    //puvodne se podle stringu volalo jmeno souboru, coz je trosku hloupe    

    if ($error != 0 && !($action=="welcome"||$action=="discussion")) {
        $action = "";
    }
    $HTML_main.="<div class='alert alert-info'><strong>Online rezervace byly ukončeny.</strong> Lístky jsou k dispozici pouze u prodejců.</div>";
    //Online rezervace byly ukončeny
    
    //$HTML_main.="<div class='alert alert-info'><strong>Update!</strong> Dnes v pondělí 25.2. ve 20:00 uvolníme dalších 60 lístků na stání do prodeje, pouze 20 bude k dispozici online, zbylých 40 půjde koupit pouze u prodejců (tj. hlavně v knihovnách).</div>";
    
    if (($user_type!="S" and $user_type!="A") and $zavreno) {
	    $HTML_main="Rezervacni system je docasne uzavren z duvodu udrzby. Prosime vydrzte.";
    } else {
       

	    $next_action = $action;

	    // dokud jsou pozadovane akce, postupne je vkladej
	    //
	    do
	    {
		// pokud nam predchozi akce chce neco sdelit, nechme ji
		//
		if ($msg != "") {
		    if ($error) {
		        $HTML_main.="<div class='alert alert-error'>$msg</div>";
            } else {
                $HTML_main.="<div class='alert alert-success'>$msg</div>";
            }
        }
		// doslo-li k chybe, loguj
		//
		if ($error != 0) log_write("ERROR id = $error, msg = $msg");

		// jiz byly vycerpany vsechny akce, vyskoc z cyklu
		//
		if ($next_action == "") {
		    break;
		}
		// pozaduje-li akce naslednou akci, je nasledna akce
        	// provedena i kdyby v predchozi doslo k chybe
        	//
        	$action = $next_action;

		
        	$msg = "";
        	$error = 0;
	    	//do action
		if ($action == "welcome") {
			list($HTML_add, $new_next_action, $error, $msg, $remember_heros)=action_welcome();
		} 
		
		else if ($action == "counts_on_homepage") {
			list($HTML_add, $new_next_action, $error, $msg)=action_counts_on_homepage($id_spojeni, $dbPrefix, $logged_in, $user_type);
			$HTML_add.=$remember_heros;
		} 
	
	
		else if ($action == "show_model") {
			//list($HTML_add, $new_next_action, $error, $msg)=action_show_model($action_par1, $id_spojeni, $dbPrefix);
			list($HTML_add, $new_next_action, $error, $msg)=action_display_reservations($action_par1, $action_par2, $id_spojeni, $dbPrefix, $session_key, $user_id, $user_type,false,false);
	
	
		} else if ($action == "display_reservation") {
			list($HTML_add, $new_next_action, $error, $msg)=action_display_reservations($action_par1, $action_par2, $id_spojeni, $dbPrefix, $session_key, $user_id, $user_type, false, false);
		} 
		
		
		
		//reserve -> uzivatel chce rezervovat konkretni misto
		  //reservations -> seznam rezervaci (jakysi preprocessing)
		  //choosen_table -> skutecne seznam rezervaci (choosen je chyba predchoziho autora :))
	
	
		else if ($action == "reserve") {
		
		    foreach ($_POST as $key=>$value) {
		        if (startsWith($key,"seat_")) {
		            $seat_array[isSQLSafe($key, $id_spojeni)] = isSQLSafe($value, $id_spojeni);
		        }
		    }
			list($HTML_add, $new_next_action, $error, $msg)=action_reserve($seat_array, $action_par1, $action_par2, $user_id,$user_type, $id_spojeni, $dbPrefix, false);
	
		}
		else if ($action == "admin_saleplaces_do") {
		    foreach ($_POST as $key=>$value) {
		        if (startsWith($key,"seat_")) {
		            $seat_array[isSQLSafe($key, $id_spojeni)] = isSQLSafe($value, $id_spojeni);
		        }
		    }
		    
		  //  admin_action_saleplaces_do($seat_array, $id_spojeni, $dbPrefix, $action_par1, $user_type)
		    
		    list($HTML_add, $new_next_action, $error, $msg)=admin_action_saleplaces_do($seat_array, $id_spojeni, $dbPrefix, $action_par1, $user_type);
		    //list($HTML_add, $new_next_action, $error, $msg)=admin_action_saleplaces_pre($id_spojeni, $dbPrefix, $user_type);
		    
		}
		
		else if ($action == "admin_reserve_vip") {
		
		    foreach ($_POST as $key=>$value) {
		        if (startsWith($key,"seat_")) {
		            $seat_array[isSQLSafe($key, $id_spojeni)] = isSQLSafe($value, $id_spojeni);
		        }
		    }
			list($HTML_add, $new_next_action, $error, $msg)=action_reserve($seat_array, $action_par1, $action_par2, $user_id,$user_type, $id_spojeni, $dbPrefix, true);
	
		}
		
		else if ($action == "vip_new_pre") {
		
		   
			list($HTML_add, $new_next_action, $error, $msg)=admin_action_new_vip_customers_pre($id_spojeni, $dbPrefix, $user_type);
			
			
	
		}
		//new_vip_do
		else if ($action == "new_vip_do") {
		
		   
			list($HTML_add, $new_next_action, $error, $msg, $new_vip_usr)=admin_action_new_vip_customers_do($id_spojeni, $dbPrefix, $user_type, $action_par1);
			
		}
		
		 else if ($action == "reservations") {
		    list($HTML_add, $new_next_action, $error, $msg, $res_mode, $res_user)=action_all_reservations($user_type, $user_id, $action_par1, $id_spojeni, $dbPrefix, $session_id);
	
	
		} else if ($action == "choosen_table") {

		    list($HTML_add, $new_next_action, $error, $msg)=
		        action_choosen_table($res_mode, $res_user, $user_id, $id_spojeni, $dbPrefix, $session_id, $user_type, false);
		    

		} else if ($action == "vip_choosen_table") {
			global $MODE_SELL;
			if (isset($new_vip_usr)) {
			    $vip_usr = $new_vip_usr;
			} else {
			    $vip_usr = $action_par1;
			}
		    list($HTML_add, $new_next_action, $error, $msg)=
		        action_choosen_table($MODE_SELL, $vip_usr, $user_id, $id_spojeni, $dbPrefix, $session_id, $user_type, true);
		    

		} else if ($action == "admin_customers") {
		    list($HTML_add, $new_next_action, $error, $msg)=
		        admin_action_customers($id_spojeni, $dbPrefix, $user_type);
		}else if ($action == "admin_sale") {
		
		    list($HTML_add, $new_next_action, $error, $msg)=
		        admin_action_sale($action_par1, $id_spojeni, $dbPrefix, $user_id, $user_type);
		}
		else if ($action == "admin_vip_customers") {
		
		    list($HTML_add, $new_next_action, $error, $msg)=
		        admin_action_vip_customers($id_spojeni, $dbPrefix, $user_type);
		}
		else if ($action == "display_vip_reservation") {
		    //var_dump($_POST);
		    //die("A");
		    list($HTML_add, $new_next_action, $error, $msg)=action_display_reservations($action_par1, $action_par2, $id_spojeni, $dbPrefix, $session_key, $user_id, $user_type, true,false);
		    //list($HTML_add, $new_next_action, $error, $msg)=
		    //    admin_action_vip_customers($id_spojeni, $dbPrefix, $user_type);
		}
		///admin_action_saleplaces_pre
		else if ($action == "admin_saleplaces_pre") {
		    
		    list($HTML_add, $new_next_action, $error, $msg)=admin_action_saleplaces_pre($id_spojeni, $dbPrefix, $user_type);
		    
		}
		else if ($action == "admin_saleplaces_map") {
		    
		    list($HTML_add, $new_next_action, $error, $msg)=action_display_reservations($action_par1, $action_par2, $id_spojeni, $dbPrefix, $session_key, $user_id, $user_type, false, true);
		    
		}
		else if ($action == "history") {
		
		    list($HTML_add, $new_next_action, $error, $msg)=
		        admin_action_history($id_spojeni, $dbPrefix, $user_type, $action_par1, $action_par2, $action_par3);
		}
		        //register je formular, do_register je fakt registrace
		else if ($action == "register") {
		
		    list($HTML_add, $new_next_action, $error, $msg)=
		        action_show_register_form($logged_in, maybe_post('login'), maybe_post('firstname'), maybe_post('lastname'), maybe_post('email'));
		        //$old_firstname, $old_lastname, $old_email
		}
		else if ($action == "do_register") {
		
		    list($HTML_add, $new_next_action, $error, $msg)=
		        action_register($logged_in, $id_spojeni, $dbPrefix, maybe_post('login'), maybe_post('password'), maybe_post('firstname'), maybe_post('lastname'), maybe_post('email'), maybe_post('instituce'), maybe_POST('jsem'));

		}
		
		else if ($action == "stavy") {
		
		    list($HTML_add, $new_next_action, $error, $msg)=action_stavy($user_type, $id_spojeni, $dbPrefix);
		}
		
		else if ($action == "sold") {
		
		    list($HTML_add, $new_next_action, $error, $msg)=action_sold($user_id, $id_spojeni, $dbPrefix);

		        
		} else if ($action == "discussion") {
		    		    list($HTML_add, $new_next_action, $error, $msg)=action_discussion_display($user_id, $id_spojeni, $dbPrefix, $user_type);
	
	
		} 
		else if ($action == "discussion-send") {
		    		    list($HTML_add, $new_next_action, $error, $msg)=action_discussion_send($user_id, $id_spojeni, $dbPrefix, $action_par1);
	
	
		} 
		else if ($action == "discussion-delete") {
		    		    list($HTML_add, $new_next_action, $error, $msg)=action_discussion_delete($user_id, $id_spojeni, $dbPrefix, $action_par1, $user_type);
	
	
		} 
		
		
		// action_stavy($user_type, $id_spojeni, $dbPrefix)
		else {
		    die("Unknown action $action.");
		}
		
		
		$HTML_main.= $HTML_add;
		$next_action = $new_next_action;
	    } while (true);
    }


/*
    // konec hlavni sekce
    //
    echo "\n</div>  <!-- #main -->"; 

?>
		
    
    
    <form name="config" action="" method="post">
      <input type="hidden" name="ticket-count-limit" value="<?php echo $TICKET_COUNT_LIMIT; ?>">
      <input type="hidden" name="temp1" value="">
      <input type="hidden" name="temp2" value="">
    </form>
	
<?php

	//odpojeni od databaze
  //
	mysql_close ($id_spojeni);
	
  echo $page_footer;*/

   echo $HTML_begin;
   echo $HTML_main;
   echo $HTML_end;

?> 
