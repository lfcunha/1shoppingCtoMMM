<?php

class GetOrders
{
//1shopping cart

private $url;
private $time;
private $date;
private $orders;
private $orders2;
private $xml;
private $sxml;

//run at 12:01am
//get orders places in the last day:

function __construct($merchantId, $merchantKey, $date)
{

$this->merchantId=$merchantId;
$this->merchantKey=$merchantKey;
$this->date=$date;
} //end _construct



//get orders placed since yesterday. to run at 12:01am. if a order is 
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
//$time=time()-60*60*24*2; //two days ago 
//$date= date("m/j/Y", $time);  
echo "Orders Since: " . $this->date;
$this->orders=array();
$this->orders2=array();
$this->url="https://www.mcssl.com/API/" . $this->merchantId ."/Orders/LIST?key=" .$this->merchantKey ."&LimitStartDate=" . $this->date . "&SortOrder=DESC";
//echo $this->path;

$xml = $this->download_page($this->url);
$this->sxml=new SimpleXMLElement($xml);


foreach ($this->sxml as $key) {
	array_push($this->orders, $key->{'Order'});}
        
for($i=0; $i<count($this->orders[0])-1; $i++){
    array_push($this->orders2, (string)$this->orders[0][$i]);
    }


}//end process();



public function getOrders(){
$this->process();
return $this->orders2;
}



}//end class

?>