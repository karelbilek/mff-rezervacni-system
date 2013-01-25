<?php


function if_active_action($wat, $action) {
    if ($wat == $action) {
        return 'class="active"';
    } else {
        return '';
    }
    
}


function horni_start() {
    $menu = '
    <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
        
        <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </a>';
    return $menu;
}
function horni_brand($logged_in, $user_type) {
    global $rootAddress;
    if ($logged_in) {
        return '<a class="brand" href="" onClick="return onNavigate(\'form-menu\', \'welcome\');">Matfyzácký ples 2013</a>';
    }
    else {
        return '<a class="brand" href="'.$rootAddress.'">Matfyzácký ples 2013</a>';
    }
}

function horni_basic($logged_in, $user_id, $action) {
    if (!$logged_in) {
        $r= horni_basic_unlogged($action);
    } else {
        $r= horni_basic_logged($user_id, $action);
    }
    $r .= '</ul>
                </div><!--/.nav-collapse -->
            </div><!--/container-fluid-->
          </div><!--/navbar-inner-->
        </div><!--/navbar-->';
    return $r;
}

function horni_basic_logged($user_id, $action) {
    $res = adli($action, 'welcome', 'Hlavní', '');
    $res .= adli($action, 'reservations', 'Rezervace', ', '.$user_id);
        $res .= adli($action, 'sold', 'Koupené', ', 0');

        $res .= adli_($action, 'discussion', 'Diskuze', '', 'discussion-send', "");
    
            //neni adli, protoze tam neni if_active_action
    $res .= '<li><a href="" onClick="return onNavigate(\'form-menu\', \'logout\');">Odhlásit</a></li>';
    
    return $res;

}

function horni_basic_unlogged($action) {
    $res = adli($action, 'welcome', 'Hlavní stránka', '');
    
       //plusminus stejne, jako adli, jen dve moznosti
    $res .= '<li '.
        if_active_action('register', $action).
        if_active_action('do_register', $action).
        '><a href="" onclick="return onNavigate(\'form-login\', \'register\')">Registrace</a>';
        $res .= adli_($action, 'discussion', 'Diskuze', '', 'discussion-send', "");
    return $res;
}

function menu_navic($logged_in, $user_type, $action, $user_id, $horni) {
    if (!$logged_in) {
        return "";
    }
    if ($user_type == "A") {
        return menu_navic_admin($action, $user_id, $horni);
    }
    if ($user_type == "S") {
        return menu_navic_seller($action, $user_id, $horni);
    }
    return "";
}

function adli($activeAction, $proposedAction, $text, $otherStuffToForm) {
    return adli_($activeAction, $proposedAction, $text, $otherStuffToForm, "","");
}

function adli_($activeAction, $proposedAction, $text, $otherStuffToForm, $otherProposedA, $otherProposedB) {
            
    if ($otherProposedA=="") {
    //if ( $proposedAction=="discussion") 
               // die ("AA". $otherProposedA."!". $otherProposedB);
    return '<li '.
        if_active_action($proposedAction, $activeAction).
        '><a href="" onClick="return onNavigate(\'form-menu\', \''.
        $proposedAction.'\''.
        $otherStuffToForm.
        ');">'.
        $text.
        '</a></li>';
    }

    if ($otherProposedB=="") {

    return '<li '.
        if_active_action($proposedAction, $activeAction).' '.
        if_active_action($otherProposedA, $activeAction).
        '><a href="" onClick="return onNavigate(\'form-menu\', \''.
        $proposedAction.'\''.
        $otherStuffToForm.
        ');">'.
        $text.
        '</a></li>';
    }
    return '<li '.
        if_active_action($proposedAction, $activeAction).' '.
        if_active_action($otherProposedA, $activeAction).' '.
        if_active_action($otherProposedB, $activeAction).' '.
        '><a href="" onClick="return onNavigate(\'form-menu\', \''.
        $proposedAction.'\''.
        $otherStuffToForm.
        ');">'.
        $text.
        '</a></li>';
    
}

function menu_navic_seller($action, $user_id, $horni) {
    return adli($action, 'admin_customers', 'Prodej', '').
           adli($action, 'history', 'Historie', ', -1, -1, '.$user_id);
}

function menu_navic_admin($action, $user_id, $horni) {
    $res = menu_navic_seller($action, $user_id, $horni);
    if (!$horni) {
        $res .=
           adli($action, 'admin_vip_customers', 'VIP uživatelé', '').
           adli($action, 'admin_saleplaces_pre', 'Místa jen pro knihovny', '').
           adli($action, 'stavy', 'Stavy', '');
    }
    return $res;
}

function nalogovan_info($logged_in, $user_name, $user_type) {
    $res = '<div class="nav-collapse collapse">
            <p class="navbar-text pull-right">';
    if (!$logged_in) {
        $res .= 'Jste nenalogován.';
    } else {
        if ($user_type=='A') {
            $jmeno_navic = ' (admin)';
        } else if ($user_type=='S') {
            $jmeno_navic = ' (prodejce)';
        } else {
            $jmeno_navic = '';
        }
        $res .= ''.$user_name.$jmeno_navic;
    }
    $res .= '</p> <ul class="nav">';
    return $res;
}

function horni_menu($logged_in, $user_type, $action, $user_id, $user_name) {

    $menu = horni_start();
    $menu .= horni_brand($logged_in, $user_type);
    $menu .= nalogovan_info($logged_in, $user_name, $user_type);
    
    $menu .= menu_navic($logged_in, $user_type, $action, $user_id, true);
    $menu .= horni_basic($logged_in, $user_id, $action);
    
    return $menu;


}

function levy_start() {
    return '<div id="nevimSiRadySOdsazenim"></div>
 <div class="container-fluid">
      <div class="row-fluid">
        <div class="span3">
          <div class="well sidebar-nav">
            <ul class="nav nav-list">';
}

function leve_menu($logged_in, $user_type, $action, $user_id) {
    $res = levy_start();
    $res .= menu_navic($logged_in, $user_type, $action, $user_id, false);
    $res .= levy_basic($logged_in, $user_id, $action);
    $res .= '</div>';//---well
    return $res;
}

function levy_basic($logged_in, $user_id, $action) {
    if ($logged_in) {
        return levy_basic_logged($user_id, $action);
    } else {
        return levy_basic_unlogged($action);
    }
}

function hdr($wat) {
    return '<li class="nav-header">'.$wat.'</li>';
}

function levy_basic_logged($user_id, $action) {
    $menu = hdr('Nákupy a prodeje lístků');
    
    
    $menu .= adli($action, 'welcome', 'Nové rezervace', '');
    $menu .= adli($action, 'reservations', 'Seznam rezervací', ', '.$user_id);
    $menu .= adli($action, 'sold', 'Koupené lístky', ', 0');
        $menu .= adli_($action, 'discussion', 'Diskuze', '', 'discussion-send', "");

        $menu .= hdr('Účet');
    
    //neni adli, protoze tam neni if_active_action
    $menu .= '<li><a href="" onClick="return onNavigate(\'form-menu\', \'logout\');">Odhlásit</a></li>';
    return $menu;

}

function levy_basic_unlogged($action) {
    $res = login_form();
    
       //plusminus stejne, jako adli, jen dve moznosti
    $res .= '<li '.
        if_active_action('register', $action).
        if_active_action('do_register', $action).
        '><a href="" onclick="return onNavigate(\'form-login\', \'register\')">Registrace</a>';
    $res .= adli_($action, 'discussion', 'Diskuze', '', 'discussion-send', "");
    return $res;
}

function login_form() {
    return '<li><div id="login">
              <form 
                  name="form-login" 
                  action="'.$_SERVER['SCRIPT_NAME'].'" 
                  method="post" 
                  onsubmit="return check_login_form(\'form-login\');"
                  >
              <div class="section">
                <div>Login <input type="text" name="login" id="login"  style="width:90%"/></div>
                <div>Heslo <input type="password" name="password" id="password"  style="width:90%"/></div>
              </div>
              <div class="section">
                <input type="submit" value="Přihlásit" class="btn btn-primary"/>
                <input type="hidden" name="act" value="login"/>
              </div>
              </form>
            </div>
            </li>';
}

function reservation_info($hall_if_display, $id_spojeni, $dbPrefix) {

    $menu = ' 
          <div class="well">
          <p>
          <table><tr><td><div class="seat" style="background-color:#99FFFF; border:1px solid black"></div></td><td> Jen u prodejců</td></tr>
          <tr><td><div class="seat" style="background-color:DarkGray; border:1px solid black"></div></td><td> Prodaná či nedostupná</td></tr>
          <tr><td><div class="seat" style="background-color:yellow; border:1px solid black"></div></td><td> Volná - klik=rezervace</td></tr>
          <tr><td><div class="seat" style="background-color:red; border:1px solid black"></div></td><td> Vaše - klik=odrezervace</td></tr>
          <tr><td><div class="seat" style="background-color:PaleGreen; border:1px solid black"></div></td><td> Rezervovaná někým jiným</td></tr>
          </table>
          </p><p>(Místa rezervovaná někým jiným ještě můžou propadnout. Místa pouze u prodejců nejsou rezervovatelná online. Nedostupná místa se možná ještě uvolní, ale nedá se s tím počítat.)</p>
          <hr>';
          
          $sql_query = "SELECT HT.name hall_type, H.name hall_name,
          H.model, H.model_small
        FROM ".$dbPrefix."Hall H
          INNER JOIN ".$dbPrefix."Hall_type HT ON H.hall_type_ID = HT.id
        WHERE H.id = $hall_if_display;";
        
          $result = mysql_query ( $sql_query, $id_spojeni );
        
		  if ($result == FALSE) {
     	        log_write("DB_ERR - $sql_query");
     	        $error = 1;
     	        die("Chyba přístupu k databázi $sql_query");
     	    
          } 
          
          $row = mysql_fetch_array ($result);
  		  $hall_name = $row['hall_name']; //nakonec neni potreba :)
  		  $hall_type = $row['hall_type'];

  		  $model = $row["model"];
  		  $model_small = $row["model_small"];

  		  $menu .= "\n<p>Sál: $hall_name</p>\n";
          if ($model == "") {		
  				//$res .= "  <div class=\"title\">Sál nemá plánek</div>\n";
  			} else {
  			    //$menu .= '<script src="http://code.jquery.com/jquery-latest.js"></script>';
  			    

  			    $menu .= '<script type="text/javascript" src="js/jquery.easing-1.3.pack.js"></script>';
  			    
	
	

  				$menu .= "  <p><a href=\"./img/maps/$model\" target=\"_blank\" id=\"mapka\" >\n";
  				$menu .= "    <img id=\"map\" src=\"./img/maps/$model_small\" title=\"$hall_name\" alt=\"plánek sálu\" style='padding: 5px;
background: white;
border: 1px solid #BBB;'/>\n";
  				$menu .= "  </a><br><i>(klikněte pro zvětšení)</i></p>\n";
  				$menu .= '<script type="text/javascript" src="js/jquery.mousewheel-3.0.4.pack.js"></script>
	
	<script type="text/javascript" src="js/jquery.fancybox-1.3.4.js"></script>
	<link rel="stylesheet" type="text/css" href="js/jquery.fancybox-1.3.4.css" media="screen">';
	            $menu .= '<script>$("a#mapka").fancybox({
		\'titleShow\'     : false,
		\'transitionIn\'	: \'elastic\',
		\'transitionOut\'	: \'elastic\',
		\'easingIn\'      : \'easeOutBack\',
		\'easingOut\'     : \'easeInBack\'
	});</script>';
  			}
           
          $menu .='</div>';//well

    return $menu;
}


function HTML_menu($logged_in, $user_type, $user_id, $user_name, $action, $hall_if_display, $dbPrefix, $id_spojeni) {

    global $rootAddress;
    $menu = horni_menu($logged_in, $user_type, $action, $user_id, $user_name);
       
    $menu .= leve_menu($logged_in, $user_type, $action, $user_id);
          

    if ($action=="display_reservation" || $action=="display_vip_reservation" || $action=="admin_saleplaces_map" ) {
        $menu .= reservation_info($hall_if_display, $id_spojeni, $dbPrefix);
    }


    $menu .='</div><!--/span--><div class="span9">';
    return($menu);
}

?>
