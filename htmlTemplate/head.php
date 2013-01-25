<?php

function HTML_base($logged_in,$action) {
$h = <<<EOT
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-type" content="text/html; charset=utf-8"/>
<link rel="stylesheet" type="text/css" href="./styles/style.css"/>
<title>Matfyzácký Ples 2013</title>
<link href="css/bootstrap.css" rel="stylesheet" media="screen">
<link href="css/bootstrap-responsive.css" rel="stylesheet" media="screen">
<style type="text/css">
      body {
        
        padding-bottom: 40px;
      }
      .sidebar-nav {
        padding: 9px 0;
      }
    </style>
EOT;


	if (!$logged_in){
 	  $h .= '<script type="text/javascript" src="js/form_check.js"></script>';
 	} else {
     	  if ($action == "generate_map" || $action == "change_reservation") {
            //$h .= '<script type="text/javascript" src="check_seats.js"></script>';
    	    $h .= '<script type="text/javascript" src="seat.js"></script>';
          }
        }	
  
        if ($action == "history") {
           $h .= '<script type="text/javascript" src="js/history_select.js"></script>';
        }
  
        $h .= <<<EOT
        <script src="http://code.jquery.com/jquery-latest.js"></script>
    <script src="js/bootstrap.min.js"></script>
	
<script type="text/javascript">
    function hash_password(form)
    {
      document.forms[form].password.value = hex_md5(document.forms[form].password.value);
      alert(document.forms[form].password.value);
      return true;
    }	

    function onNavigate(form, act, par1, par2, par3)
    {


      formular = document.forms[form];


      if (typeof par1 != "undefined") formular.par1.value = par1;
      if (typeof par2 != "undefined") formular.par2.value = par2;
      if (typeof par3 != "undefined") formular.par3.value = par3;                 

      formular.act.value = act; 
      formular.submit(); 
      return false;
    }
</script>
</head>
<body>
        
EOT;
    $e = "</div>". //<#content>
       "</div><!--/span-->
          </div><!--/row-->
        </div><!--/span-->
      </div><!--/row-->

      <hr>

      <footer>
        <p>Spolek Matfyzák 2013</p>
      </footer>
      ".
		"</body></html>"; 
    return array($h,$e);
}
?>

