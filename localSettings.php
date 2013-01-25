<?php

//database settings
$dbLogin = "yourDBLogin";
$dbPrefix = "yourDBPrefix";
$dbPasswd = "yourDBPassword";
$dbName = "yourDBName";
$dbAddress = "localhost";

//root address of the system
$rootAddress = "http://matfyzak.cz/rzrv/";

//heslo do mbanky
$mbankpass = "yourMBankPassword";

//some settings
$SEAT_WIDTH = 25;
$SEAT_HEIGHT = 20;

//TODO: this should be system settings
$CANCEL_RESERVATION_AFTER = 8;
$TICKET_COUNT_LIMIT = 10;

//if the system is closed or open
//TODO: to English :)
//0 - open; 1 - closed
$zavreno = 0;


//zacatek akce (skoro k nicemu to neni)
$opening_dt = mktime(19,30,0, 3, 6, 2013);

//for "default" users
$newRandomPassword="password";


?>
