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
			    <li><a href="" onclick="return onNavigate('form-menu', 'reservations');">Zaplaťte podle instrukcí zde</a> - buď převodem, či v knihovnách. Při platbě převodem si lístky stáhněte a vytiskněte.</li>
			    
			    <li>Pokud nemůžete sehnat lístky, zkuste to v <a href="" onclick="return onNavigate('form-menu', 'discussion');">diskuzi</a>.</li>
			   
				</ol>
				<h2>Lístky u prodejců</h2>
				<p> 
				    Některá místa jde rezervovat a koupit pouze u fyzických prodejců. Na mapě je poznáte podle barvy.
				</p>
				<h2>Prodejci</h2>
				<p>
				    U prodejců jde nakupovat až od 18. 2. 11:30, do té doby je možné nakupovat pouze online a platit pouze účtem.<br><br>
				    Prodejci jsou:
				    <ul>
				        <li><a href="http://www.mff.cuni.cz/fakulta/lib/mo.htm">Knihovna Karlín</a> - pondělí - pátek 8.30-18.00 (patky jen do 15.00)</li>
				        <li><a href="http://www.mff.cuni.cz/fakulta/lib/io.htm">Knihovna Malá Strana</a> - pondělí - pátek 9.00-16.00 (patky jen do 15.00)</li>
				        <!--li><a href="http://www.mff.cuni.cz/fakulta/lib/fo.htm">Knihovna Karlov</a> - pondělí - pátek 8.30-18.00 (patky jen do 15.00)</li-->
				        <li>Počítačová laboratoř na koleji na Troji - pouze středy (20. a 27. 2. 2013) v dobe 0-16 h a čtvrtek 28. 2. v době 8-11 h</li> 
				    </ul>
				</p>
			   </div>
			   <div class="span4">
			  <h2>Cena</h2>
			  <p>Lístky na stání stojí 300 Kč, ty na sezení jsou za korun 350.
      </div>
      <div class="span4">
        <h2>Kontakt</h2>
        <p>Dotazy ohledně rezervací pište na adresu <a href="mailto:ples@matfyzak.cz">ples@matfyzak.cz</a>. Pokud budou v systému problémy, pište tamtéž.</p>
	
	<h2>Stránky plesu</h2>
	<p>Více informací o plesu na <a href="http://ples.matfyzak.cz">ples.matfyzak.cz</a></p>
	
      </div>
EOT;
	return array($f,$s);

}

?>
