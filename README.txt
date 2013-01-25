Matfyzacky rezervacni system
---

Matfyzacky rezervacni system je system k rezervaci listku na zofine.

V podstate neni dobry k nicemu jinemu, nez k rezervaci listku na matfyzackem plese na Zofine. A i k tomu neni zdaleka idealni.

Bezpecny by ale mel byt. Protoze verim v jeho bezpecnost, davam jeho kod verejne.

---

Je to cele licencovano pod Affero GPLv3 licenci, informace o autorech nize. Affero GPLv3 licence zde - http://www.gnu.org/licenses/agpl-3.0.html .

Affero licence je velmi copyleftova - tj. jakmile rezervacni system nebo z nej kod odvozeny nasadite jako verejny system, tak musite zverejnit zdrojovy kod sveho produktu pod agpl.

Pro tento projekt plati navic vyjimka z AGPLv3 (ktera v nem neni, ale ja jsem se pro ni rozhodl): informace o databazi (jako napriklad pristupove udaje apod.) neni nutne zverejnovat jako cast zdrojoveho kodu. 

---

Ke kodu samotmenu:

Je to pomerne chaoticky kus kodu, ktery byl sdilen z generace na generaci a je videt, ze vyrostl velice organicky jeste v drevnich dobach PHP; a ja (Karel Bilek) ho nikdy nemel odvahu prepisovat do objektu nebo dokonce frameworku. 

Verte mi, ze uz ted je lepsi, nez byl predtim, predtim to byl HODNE velky spaghetti code; ted se aspon volaji funkce. V predchozich verzich se funkce temer nevolaly a misto nich se volalo include a pouzivaly globalni promenne.

Funguje to tak, ze system predpoklada, ze je pouze jeden soubor index.php, ktery je pristupny, kteremu pres POST formular prijde $_POST[action], ktery rika, co se bude presne delat; krome toho mu pres tentyz formular prijdou parametry $_POST[par1], $_POST[par2] a pripadne $_POST[par3], ktere rikaji, jak to presne delat. Vsechny kliknuti jsou potom javascriptem zmenena na odeslani neviditelneho POST formulare.

To samo o sobe je trochu zvlastni, ale menit jsem to nechtel, protoze by mohlo prestat neco nekde fungovat :(

Akce samotne (tj. co se stane pri ruznych action) jsou ulozene v action/ a action_admin/

Vsechno je to pomerne zbesile michani PHP kodu, MYSQL dotazu, HTML kodu a obcas javascriptu. Puvodne jsem se domnival, ze ruzne kusy kodu budu schopen dat do ruznych adresaru a oddelim HTML od logiky apod., ale to se mi nepovedlo. Presto jsou tu pozustatky tohoto meho pocatecniho nadseni - slozky htmlTemplate, mailing, reservations, users, dbs.

Slozky js, css, styles, img jsou pouze pro javascripty/css/obrazky.

Slozka mbank/ je trochu extra - provadi automaticke nalogovani na systemy mBanky a nasledne vygenerovani PDFek, ktere je mozne si stahnout. Neni to ale soucast "hlavniho" systemu (ale take je pod agplv3!).

Opet - cele by to nemel nikdo pouzivat, protoze je to zbesile (napr. to stale pouziva tradicni mysql_query ) a melo by se to nekdy prepsat pod nejaky rozumny framework. Navic je to urceno jen presne pro nase potreby.

---

Autori hlavniho systemu:
Karel Bilek (2012-2013)
Lukas Lansky (2010-2012)
Zdenek Kavalir (kdysi davno)
David Senkerik (mozna nejake kusy kodu odkudsi)

Krome toho vyuziva:
bootstrap (Twitter, 2012)
jquery-countdown (Keith Wood, 2008)

jQuery Easing (George McGinley Smith, 2008)
jQuery FancyBox 1.3.4 (2008 - 2010 Janis Skarnelis)
jQuery mousewheel (2010 Brandon Aaron) (tyto 3 projekty pouze kvuli animaci zvetsovani :))

FPDI (2004-2011 Setasign - Jan Slabon)
FPDF (2011 Olivier PLATHEY)
