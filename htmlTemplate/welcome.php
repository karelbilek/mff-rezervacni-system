<?php

function HTML_welcome() {
	$f = <<<EOT
	
	<!--div class="hero-unit">
            <h1>Matfyzácký ples 2013</h1>
            <p>Vítejte v rezervačním systému na matfyzácký ples 2013!</p>
          </div-->
EOT;
          
    $s=<<<EOT
<div class="span7">
			<h2>Jak rezervovat?</h2>
			   <ol>
			    <li><a href="" onclick="return onNavigate('form-login', 'register')">Zaregistrujte se</a></li>
			    <li>Vyberte si místnost v tabulce nahoře a tam židle.</li>
			    <li><a href="" onclick="return onNavigate('form-menu', 'reservations');">Zaplaťte podle instrukcí zde</a> - buď převodem, či v knihovnách. Při platbě v převodem si lístky stáhněte a vytiskněte.</li>
			    
			    <li>Pokud nemůžete sehnat lístky, zkuste to v <a href="" onclick="return onNavigate('form-menu', 'discussion');">diskuzi</a>.</li>
			   
				</ol>
				<h2>Lístky u prodejců</h2>
				<p> 
				    Některá místa jde rezervovat a koupit pouze u fyzických prodejců. Na mapě je poznáte podle barvy.
				</p>
				<h2>Prodejci</h2>
				<p>
				    
				    U všech prodejců můžete platit online rezervace a rezervovat se. Prodejci jsou:
				    TODO
				</p>
			   </div>
			   <div class="span4">
			  <h2>Cena</h2>
			  <p>Lístky na stání stojí 300 Kč, ty na sezení jsou za korun 400.
      </div>
      <div class="span4">
        <h2>Kontakt</h2>
        <p>Dotazy ohledně rezervací pište na adresu <a href="mailto:spolek@matfyzak.cz">spolek@matfyzak.cz</a>. Pokud budou v systému problémy, piště tamtéž.
	
	<h2>Stránky plesu</h2>
	<p>Více informací o plesu na <a href="http://ples.matfyzak.cz">ples.matfyzak.cz</a>
	
      </div>
EOT;
	return array($f,$s);

}

?>
