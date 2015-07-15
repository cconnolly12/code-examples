<?PHP
error_reporting(E_ALL);
/**
*This is to submit leads to the liverpool university system.
*The docs are non-existant, so here goes....
*works kind of like laureate.php
**/
define('SUBMITURL', 'URLHERE');					

if(defined('COLLEGE'))
	die('configureation error; this file must be included first');
define('COLLEGE', 'University of COLLEGE');
if(defined('TATTLEES'))
	die('config error; this file must be included first');
define('TATTLEES', 'address@email.com');

define('NOREPLYADDRESS', 'no-reply@email.com');
require_once('laureatefuncs.php');
$path=JPATH_ROOT.'/../bin/curlFunctions.php';
//set more vars before we run
require_once($path);

lpSetPostData();

if(!defined('SUPPRESSAUTOSEND')) { //this is so I can resubmit data without $_POST
	submitlpLead();
}
/**
* can pass direct to function with arg0 being an array
*
**/
function submitLPLead($post=array()){
	$errorMsg=array();
	$result=false;
	//get the array part of the form
	if(isset($_POST['form']))
		$post=array_merge($_POST['form'], $post);
	if (count($post)<5) 
		$errorMsg[]='The $_POST form data was not in the form of an array, or did not have enough fields.';
	arrTrim($post);

	//make all the array objects, not be... since there are no multi selects in our forms 
	foreach($post as $var=>$data)
		if(is_array($data))
			$post[$var]=$data[0];
	//make sure all the required vars are set we need
	$requiredMap=array('first_name', 'last_name', 'email', 'evening_phone', 'program_of_interest1'); //start_date
	foreach($requiredMap as $id=>$requiredKey)
		if(!isset($post[$requiredKey]))
			$errorMsg[]="The required key '$requiredKey' was not set.";
		elseif($post[$requiredKey]=='')
			$errorMsg[]="The required key '$requiredKey' cannot be ''.";

$post['evening_phone']=preg_replace('/\D/', '', $post['evening_phone']); // Numbers only.

	//yes, its the swear list, plus 'free' per their requirement
	$swears=array("shit ", "cunt ", "fuck", "pussy", "asshole", "sex ", "anal ");
	//do this special validation
	foreach($post as $var=>$data)
		foreach($swears as $swear)
			if(stripos(" ".$data, $swear)){
				$firstletter=substr($swear,0,1);
				$length=strlen($swear);
				$subWord=$firstletter;
				for($i=1; $i<$length; $i++)
					$subWord.='X';
				$errorMsg[]="Value '$var' contains banned word '$subWord'";
			}
	if(!isset($post['RTAG']))
		$post['RTAG']='RTAG'; //from the spec doc, WTF
	if(!isset($post['state']))
		$post['state']='blank'; //state
	if(!isset($post['comments']))
		$post['comments']='blank'; //our vendor code
	if(!isset($post['supplier_id']))
		$post['supplier_id']='2300'; //our vendor code
	//if(!isset($post['activity_id']))
	//	$post['activity_id']='4416104'; //our comm code
	if(!isset($post['activity_id'])){
	$url = $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
    $activity_id = $_GET['activity_id'];

        if (false !== strpos($url,'&activity_id=')) {
            $_POST['form']['activity_id']= $activity_id;
        }
     }
	//unset($post['activity_id']);

	$resultMessage='';
	unset($post['referrer']);
	if (count($errorMsg)) {
		$resultMessage.= join("<br/>\n", $errorMsg);
	}else{ //continue trying to send
		$result=curlPostlp(SUBMITURL, $post, $resultMessage);
	}
	if($result){
		//reportError($resultMessage, $post);	
		//reportSuccess($post);
		postSuccessStatus('true');
		return true;
	}else{
		//reportError($resultMessage, $post);	
		printDebug($resultMessage.'<br>'.print_r($post,1));
		postSuccessStatus('false', $resultMessage);
		return false;
	}	
}
/**
* a bunch of code pulled from laureate send to save time
**/		
function curlPostlp($postUrl='', $fieldArray=array(), &$returnMessage=''){

    $postString='';
	$post_hr='';//used for displaying output
    foreach ($fieldArray as $key=>$value) {
        $postString .= rawurlencode($key) . '=' . rawurlencode($value) . '&';
	$post_hr .= "$key -> $value\n";
    }
	//delete the final ampersand
    $postString=substr($postString, 0, -1);

    $ch = curl_init();
    $curlopts=array(
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1',
        CURLOPT_HTTP_VERSION, 1.0,
        CURLOPT_URL => $postUrl,
        CURLOPT_AUTOREFERER  => true,
        CURLOPT_HEADER => true,
        CURLINFO_HEADER_OUT => true,
        CURLOPT_ENCODING => "",
        CURLOPT_PORT => 80, //// LIVE
        //CURLOPT_PORT => 9090, //// TESTING
        CURLOPT_VERBOSE => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postString,
        CURLOPT_RETURNTRANSFER => true,
		CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 120,
        CURLOPT_MAXREDIRS => 10
    );
    curl_setopt_array($ch, $curlopts);
    $returnMessage=curl_exec($ch);
    $post_string=trim(curl_getinfo($ch, CURLINFO_HEADER_OUT));
    curl_close($ch);

    printDebug("Form data:\n$post_hr\nLead Insertion POST String:\n$post_string\nSubmitted:\n$postString\n\nResult:\n-------\n$returnMessage");
//    $returnMessage=parseResponse($results);
	//see if we succeeded
	if(parseResponse($returnMessage))
		if(!dupeCheck($returnMessage))
			return true;
		else
			$returnMessage='duplicate of existing lead';
}
