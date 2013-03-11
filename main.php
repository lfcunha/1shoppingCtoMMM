<?php

/*
******************************************
*** 03/06/12
*** created by Luis Cunha, PhD
*** for each order:
*** 1) read client data/purchases from 1shoppingcart
*** 2) check if client's email exists in madmimi database. if not, create new contact with all info
*** 3) for each item bought by client:
***     3.1) if list with product name does not exist, create it
***     3.2) if client does not already subscribe to this, add membership to list
*** 4) log all activity to log file
*** 
*** edit keys/keys.ini file to add API credentials, and how far in time to process orders
*** 
*** USAGE: call script with a cron job
*** 
*** NOTE: log file has a bug that accumulates updated subscriptiosn if client buys the same thing more than once in a day
******************************************
*/




function __autoload($class)  {    //this will load the required class only when necessary (when the class get's called)
    include_once("classes/{$class}.class.php");
                             }
include_once("keys/keys.ini");   //includes API KEYS
require('madmimi/MadMimi.class.php');
include_once("keys/keys.ini");   //includes API KEYS
$user=MADMIMI_USERNAME;
$key=MADMIMI_KEY;
$merchantID=MERCHANT_ID;
$merchantKey=SHOPPING_CART_KEY;

date_default_timezone_set('America/New_York');





//*****************SHOW ORDERS FOR THIS MANY PAST DAYS**************************
//******************************************************************************
$days=DAYS; //show orders for this last number of days

//******************************************************************************
//******************************************************************************
$time=time()-((60*60*24)*$days); //two days ago 
$date= date("m/j/Y", $time);  

$mimi = new MadMimi($user, $key, $debug = false);
$orders= new GetOrders($merchantID, $merchantKey, $date);
$orderList=$orders->getOrders(); //get orders for the past 24 hours
//var_dump($orderList);   /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


//***************GET LOG FILE READY*********************************************
//******************************************************************************
$myFile = "logs/log_" . date(m_Y) . ".txt";
file_exists($myFile) ?($fh = fopen($myFile, 'a') or die("can't open file")) : ($fh = fopen($myFile, 'w') or die("can't open file"));
      
$stringData = "Date: " .$date . "\n\n";
fwrite($fh, $stringData);
// $stringData = "*************  SHOPPING CART DATA  *****************\n";
// fwrite($fh, $stringData);
//******************************************************************************
//******************************************************************************





//********************************GET ALL LISTS ********************************
//******************************************************************************
$xml=$mimi->Lists();
$sxml=new SimpleXMLElement($xml);
$lists=array();
foreach ($sxml->{'list'} as $key) {
	array_push($lists, $key);		//get array of current lists
}


//******************************** PROCESS EACH ORDER***************************
//******************************************************************************
foreach($orderList as &$key){ 
    $order= new GetOrder((string)$key, $merchantID, $merchantKey);
    $transaction=$order->getDetails(); //get order details
   //echo $transaction['clientID']; /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $stringData = "*************************\n";
    fwrite($fh, $stringData);

    $stringData = "ORDER ID: " .$key . "\n\n";
    fwrite($fh, $stringData);
    $listmemership0='';
    $listmemership='';

//******************************** GET CLIENT INFO FOR ORDER *******************
//******************************************************************************
$client= new Client($transaction['clientID'], $merchantID, $merchantKey);
$clientInfo=$client->getClientInfo(); //associative array of client info


$clientEmail=$clientInfo['email'];

$stringData = "\tClient ID: " . $transaction['clientID'] . "; Email: " . $clientEmail .  "\n";
fwrite($fh, $stringData);

$listmemership0='';
$oldMemberships=$mimi->Memberships($clientEmail);
$sxml0 = new SimpleXMLElement($oldMemberships);
//echo ((string)$sxml2->{'list'}['name']);
        foreach ($sxml0->{'list'} as $key0){ //subscribed lists
            $listmembership0 .= $key0['name'];
            $listmembership0 .="; ";}

$stringData = "\t\tOld subscriptions: " . $listmembership0 . "\n";
fwrite($fh, $stringData);





$stringData = "\t\tClient info: \n";
fwrite($fh, $stringData);


    foreach($clientInfo as $key=>$value){
    
        if($value!=""){echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $key . ": " . $value . "<br>"; }

        $stringData = "\t\t\t" . $key . ": " . $value . "\n";
        fwrite($fh, $stringData);

    
       }



//CHECK IF CLIENT IS MEMBER OF ANY LISTS, OTHERWISE, CREATE ITS CONTACT

$memberships=$mimi->Memberships($clientEmail);
$sxml2=new SimpleXMLElement($memberships);
//echo ((string)$sxml2->{'list'}['name']);
if(($sxml2->{'list'}['name'])==NULL){




	   $mimi->AddUser($clientInfo);
	    echo "created new contact for " . $transaction['clientID'] . " : " . $clientEmail; /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	    $stringData = "\t\t\t*CREATED NEW CONTACT IN MADMIMI DATABASE*\n";
        fwrite($fh, $stringData);

    }


else {
        $stringData = "\t\t\t*CONTACT ALREADY IN MADMIMI DATABASE*\n";
        fwrite($fh, $stringData);
}


//******************************************************************************
//******************************************************************************

        $stringData = "\t\tItems Purchased:\n";
        fwrite($fh, $stringData);


//********process in item purchased*********************************************
//******************************************************************************
foreach($transaction['items'] as $value){
        $stringData = "\t\t\t" . $value  ." ";
        fwrite($fh, $stringData);

	$prodInList=0;
	$subscribed=0;
	foreach($lists as $key){
		if($key['name'][0]==$value){     //key[name]-> lists    $value -> item
			$prodInList=1;
				
		foreach ($sxml2->{'list'} as $key2){ //subscribed lists
			 //echo "lists: " . $key2['name'];/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                         // echo "value: " . $value;  //var_dump($key2)
                        if($key2['name']==$value) {  //subscribed lists include prod name
                        $subscribed=1;
                        }
                    }
                    			}
                    		}

		if($prodInList==0){
			if($mimi->NewList($value)){
                        echo "list created for: " . $value;
                        $stringData = "(new list created) " . "- ";
                        fwrite($fh, $stringData);}
                    }

		if($subscribed==0){
			if($mimi->AddMembership($value, $clientEmail)){
                echo "added " . $clientEmail . "to " . $value;
                $stringData = "added membership. \n";
                fwrite($fh, $stringData);

        }

		}
        else {
                 $stringData = "(already subscribed). \n";
                fwrite($fh, $stringData);
        }

} //end each item processing




$listmemership='';
$newMemberships=$mimi->Memberships($clientEmail);
$sxml3 = new SimpleXMLElement($newMemberships);
//echo ((string)$sxml2->{'list'}['name']);
        foreach ($sxml3->{'list'} as $key3){ //subscribed lists
            $listmembership .= $key3['name'];
            $listmembership .="; ";}

$stringData = "\t\tUpdated subscriptions: " . $listmembership . "\n\n";
fwrite($fh, $stringData);

//////////*/

//USE PYTHON:

//$first="john";
//$last="smith";

//$mystring = system('python myscript.py -f john -l smith', $retval);
//echo $retval;



} //end foreach order
$stringData = "#############################\n\n";
fwrite($fh, $stringData);
fclose($fh);

?>