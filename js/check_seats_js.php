<?php 

// check_seats - hlida max. pocet vstupenek 

$TICKET_COUNT_LIMIT = $_GET['limit'];

?>

		function check_seats()
		{
			ticket_count_limit = <?php echo $TICKET_COUNT_LIMIT; ?>;

			if (navigator.appName=='Netscape')
				mapa=document.forms['mapa'];

			if ( mapa['count'].value * 1 + mapa['other_count'].value * 1 > ticket_count_limit )
			{
				alert ('Ve vsech salech muzete rezervovat celkem nejvyse ' + ticket_count_limit + ' vstupenek' );
				return false;
			} 
			return true;
		}


<?php 

//zmeni obrazek pri kliknuti

?>

var choosen_color = 'red';

function change_image ( obr, price, color, canDo )
{
        if (canDo != 1) {
            alert('V soucasne dobe nelze menit rezervace.');
            return;
        }
        
		if (navigator.appName=='Netscape')
			mapa=document.forms['mapa'];
		
		if ( mapa[obr].value == 0 )
		{
			//document.images[obr].src = './pics/choosen.jpg';
			el = document.getElementById('id'+obr).style.backgroundColor = choosen_color;
			
			mapa[obr].value=1;
			mapa['count'].value++;
			mapa['price'].value= mapa['price'].value * 1 + price * 1;
		}
		else
		{
			//document.images[obr].src = './pics/free.jpg';
			el = document.getElementById('id'+obr).style.backgroundColor = color;
			mapa[obr].value=0;
			mapa['count'].value--;
			mapa['price'].value=mapa['price'].value - price;
		}
		check_seats();
		return;		
}
