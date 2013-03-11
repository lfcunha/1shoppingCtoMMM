<?php

class GetOrder
{
//1shopping cart

private $url;
private $order;
private $orders;
private $xml;
private $sxml;
private $clientID;
private $items;

//run at 12:01am
//get orders places in the last day:

function __construct($order, $merchantId, $merchantKey)
{
$this->order=$order;
$this->merchantId=$merchantId;
$this->merchantKey=$merchantKey;



}



//url to get orders placed since yesterday. to run at 12:01am. if a order is 
//placed in the minute since midnight, it will be picked up again the next day,
//but it's ok, it's not too much data and the system will check that it is 
// already on the list



//use curl to get the contents

private function download_page($path){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$path);
	curl_setopt($ch, CURLOPT_FAILONERROR,1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 15);
	$retValue = curl_exec($ch);			 
	curl_close($ch);
	return $retValue;
}




private function process()    //create an associative array with each property for each bus
{
$this->orders=array();
$this->items=array();
$this->orders2=array();
$this->url="https://www.mcssl.com/API/" . $this->merchantId . "/Orders/" . $this->order . "?key=" . $this->merchantKey;

//echo $this->path;

$xml = $this->download_page($this->url);
$this->sxml=new SimpleXMLElement($xml);


echo "<br><br>";
$this->clientID=(string)$this->sxml->{'OrderInfo'}->{'ClientId'};
$this->orders['clientID']= $this->clientID;
   
//var_dump($this->clientID);   


//array_push($this->orders['products'],  


foreach ($this->sxml->{'OrderInfo'}->{'LineItems'}->{'LineItemInfo'} as $key) {
	# code...
	array_push($this->items, (string)$key->{'ProductName'});
        }
        
$this->orders['items']= $this->items;
//var_dump($this->orders);     
        

}//end process();



public function getDetails(){
$this->process();
return $this->orders;
}



}//end class

?>