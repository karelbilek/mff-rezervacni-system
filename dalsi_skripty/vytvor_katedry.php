<?php
//na vytvoření kateder.
//nic extra komplikovaného.
//NEnastaví je to jako P, musí se to udělat ručně nebo na chvíli změnit CreateCustomer


error_reporting(-1);

include "../localSettings.php";
include "../dbs/inc.php";
include "../users/CreateCustomer.php";


$id_spojeni = connectOrDie($dbAddress, $dbLogin, $dbPasswd, $dbName);


function vytvor_katedru($jmeno, $heslo, $mail) {
    global $id_spojeni;
    $custId="";
    $err = "";
    $res = CreateCustomer($id_spojeni, "rzrv_", $jmeno, $jmeno, "přednostní", $mail, $heslo, &$custId, &$err);
    if ($res != 0) {
        die("ERROR - $err");
    } else {
        echo "OK - $custId <br>";
    }
}


function posli_mail($jmeno, $heslo, $mail) {
    $from = 'ples@matfyzak.cz';
    $to = $mail;
    $date = date('r');
    
    
    $subject="Prednostni rezervace na ples MFF";
   
    $body = "Vazena pani sekretarko / pane sekretari,
    
dovolujeme si timto e-mailem co nejsrdecneji pozvat zamestnance MFF UK na tradicni Matfyzacky ples, který se uskutecni ve stredu 6. 3. 2013 v palaci Zofin.
    
Jako kazdy rok probihaji rezervace prostrednictvim rezervacniho systemu, ktery jsme letos vylepsili mimo jine o prednostni rezervace pro katedry.
    
Pro prednostni rezervace pouzijte tyto prihlasovaci udaje:
Jmeno: $jmeno
Heslo: $heslo
Adresa: http://matfyzak.cz/rzrv/
    
Tyto udaje muzete dle sveho uvazeni bud sdelit vsem zajemcum z Vaseho pracoviste, pripadne bychom Vam doporucili, aby Vas zajemci navstivili v kancelari a vybrali si mista v rezervacnim systemu podle sveho prani a pod Vasim dohledem, jakmile se do systemu prihlasite. Popripade muzete rezervaci provest sama podle jejich pokynu.
    
Prednostne rezervovane listky si muzete najednou vyzvednout proti hotovosti v prodejnich mistech od 18. 2. (12 hodin), nebo po domluve je mohou organizatori predat osobne. Prevod na ucet prosim nepouzivejte (ac Vam ho system bude nabizet), je urcen pro neprednostni rezervace.
    
Rezervacni system byl upraven s ohledem na pripominky z lonskeho roku a zkusenosti z dosavadniho provozu, ale jeho obsluha a funkce jsou velmi podobne lonske verzi. Za pripadne technicke nedostatky se predem omlouvame. V systemu jeste nejsou doplneny konkretni informace o prodejnich mistech, nedejte se tim zmast.
    
Predpokladany harmonogram:
29.1. - 8.2. : prednostni rezervace pro pracoviste MFF
11.2. (23 h) - 3.3. : rezervace pro vsechny zajemce a spusteni prodeje e-vstupenek
18.2. (12 h) - 5.3. : volny prodej a vyzvedavani rezervovanych klasickych vstupenek v prodejnich mistech (knihovny MFF)
    
S uctou a podekovanim za spolupraci
spolek Matfyzak
    
E-mail pro technickou podporu a dotazy: 
ples@matfyzak.cz"; 
    
    if (mail($to, $subject, $body, "From: $from\r\nDate: $date\r\n")) {
        echo "OK2<br>";
	} else {
        die("AAAA");
	}

}


die ("NOPE");


$file1 = "../../../seznam_kateder";
$lines = file($file1);

foreach($lines as $line_num => $line) {
    $line=chop($line);
    $arr = explode("\t", $line);
    $mail = $arr[0];
    $name = $arr[1];
    $pass = $arr[2];
//vybrat jedno z toho :)
//    vytvor_katedru($name, $pass, $mail);
//    posli_mail($name, $pass, $mail);
}

?>
