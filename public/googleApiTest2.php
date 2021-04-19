<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../google-api-php-client/src/Google/Client.php";
require_once "../google-api-php-client/src/Google/Service/Calendar.php";

$client = new Google_Client();
$serviceAccountName = '524423998484-gkrduknadvdmp7nsi7734snbjdte3kfb@developer.gserviceaccount.com';
$scopes= array('https://www.googleapis.com/auth/calendar');
$privateKey=file_get_contents("/home/ewp/public_html/mis/google-api-php-client/src/certificates/My Project-4f8fcc30ee1e.p12");
$privateKeyPassword='notasecret'; 
$credential = new Google_Auth_AssertionCredentials($serviceAccountName,$scopes, $privateKey, $privateKeyPassword);
$client->setAssertionCredentials($credential);
$service = new Google_Service_Calendar($client);

//SET EVENT
$event = new Google_Service_Calendar_Event();

//Property Name - Party Name x Pax
$event->setSummary('Ehte Party x 2');

//Property Name
$event->setLocation('OLERAI HOUSE');

//Descrpition - Booking Details
$event->setDescription('');

$start = new Google_Service_Calendar_EventDateTime();
$start->setDateTime('2015-01-06T10:00:00.000-07:00');
$start->setTimeZone('Africa/Nairobi');
$event->setStart($start);

$end = new Google_Service_Calendar_EventDateTime();
$end->setDateTime('2015-01-06T10:25:00.000-07:00');
$end->setTimeZone('Africa/Nairobi');
$event->setEnd($end);

//COLOR:
//Olerai House - 2
//EWP - 8
//Cabin - 11
$event->setColorId("11");


//INSERT:
//$event1 = $service->events->insert('elephantwatch.camp@gmail.com', $event);
$event1 = $service->events->insert('ehteshamm@gmail.com', $event);
echo $event1->getId();
//STORE ID for this event in case we need to edit it...!


//UPDATE:
//$event1 = $service->events->insert('ehteshamm@gmail.com', $eventId, $event);



?>