<?php

function HTML_hiddenForm($session_key) {
    $sname = $_SERVER['SCRIPT_NAME'];
    $skey = $session_key;
    $r = <<<EOT
<div class="section">
      <form name="form-menu" action="$sname" method="post">
        <input type="hidden" name="sid" value="$skey"/>
        <input type="hidden" name="act" value=""/>
        <input type="hidden" name="par1" value=""/>
        <input type="hidden" name="par2" value=""/>
        <input type="hidden" name="par3" value=""/>
      </form>

    </div>  <!-- #section -->
EOT;

    return $r;
}

?>
