<?PHP
define('SCHOOL', 1);
if(!defined('JPATH_ROOT'))
	require_once('cli_bootstrap.php');
/**
* Call with the string object that needs to be modified ($formLayout) at minimum
**/
function liverpoolCountryOptions(
    &$formLayout = '', $target = 'Select Country', $selectedCountryCode = ''
)
{
    require_once(JPATH_ROOT.'/../bin/Country.class.php');
    //get the singleton
    $lpCountry = LiverpoolCountry::getInstance();
    //get the list
    $options = $lpCountry->getOptions($selectedCountryCode);
    //stuck  it in the output
    $search = array("<option  value=\"\">$target</option>",
        "<option selected=\"selected\" value=\"\">$target</option>");
    $replace = "<option  value=\"\"></option>".$options;
    $formLayout = str_replace($search, $replace, $formLayout);
}

function liverpoolProgrammeOptions(
    &$formLayout = '', 
	$target = 'Select Programme', 
	$selectedProgrammeCode = ''
)
{
    require_once(JPATH_ROOT.'/../bin/Program.class.php');
    require_once(JPATH_ROOT.'/../bin/program_codes.php');
	//what page is this? lets set order
	//try and set based on url
	$pageUrl = ungarbleUrl($_SERVER['REQUEST_URI']);
	//echo "<!-- page url $pageUrl -->";
	$firstGroup = $lastGroup = $pageGroup = '';

	  //get the singleton
    $lpProgram = LiverpoolProgram::getInstance();
    //get the list
    $options = $lpProgram->getOptions(
        $selectedProgrammeCode, $firstGroup, $lastGroup, $pageGroup
    );
    //stick  it in the output
    $search = array("<option  value=\"\">$target</option>",
        "<option selected=\"selected\" value=\"\">$target</option>");
    $replace = "<option  value=\"\"></option>".$options;
    $formLayout = str_replace($search, $replace, $formLayout);
}

function lpGraduationOptions(&$formLayout='', $target='Select Graduation'){
		
	$options='';
	for($year=date('Y')+2; $year>date('Y')-100; $year--)
		$options.='<option value="'.$year.'">'.$year.'</option>';
	//stick  it in the outp
	$search=array("<option  value=\"\">$target</option>",
		"<option selected=\"selected\" value=\"\">$target</option>");
        $replace="<option selected value=\"\">Select your Program of Interest</option>".$options;
        $formLayout=str_replace($search, $replace, $formLayout);
}
function lpSetPostData(){

    $session =& JFactory::getSession();
    $session->set( 'bachelorexit', '/liverpool-thank-you-2');
	//$_POST['form']['referrer']=$_SERVER['HTTP_REFERER'];
	$_POST['form']['RTAG']='RTAG'; //from the spec doc
	$_POST['form']['first_name']= $_SESSION['form']['firstname']; //First Name Conversion
	$_POST['form']['last_name']= $_SESSION['form']['lastname']; //Last Name Conversion
	$_POST['form']['email']= $_SESSION['form']['email'];
	$_POST['form']['city']= $_SESSION['form']['city'];
	$_POST['form']['state']='blank'; //our vendor code
	$_POST['form']['country']= $_SESSION['form']['country'];
	$_POST['form']['evening_phone']= $_SESSION['form']['hometel']; //First Name Conversion
	$_POST['form']['program_of_interest1']= $_SESSION['form']['program']; //First Name Conversion
	$_POST['form']['comments']='blank'; //our vendor code
	$_POST['form']['activity_id']= $_SESSION['form']['activity_id'];
	$_POST['form']['supplier_id']='2300'; //our vendor code
	$_POST['form']['work_experience']= $_SESSION['form']['WorkExperience']; //Age Range Conversion
	$_POST['form']["age range"]= $_SESSION['form']['age']; //Age Range Conversion
	//$_POST['form']['college']='Liverpool';
	$path=JPATH_ROOT.'/../bin/program_codes.php';
	require_once($path); //for getCommCode
	//$_POST['form']['activity_id']=getCommCode();
	
}

function liverpoolCountryConvert($twoLetter = '')
{
        require_once JPATH_ROOT.'/../bin/Country.class.php';
        //get the singleton
        $lpCountry = LiverpoolCountry::getInstance();
        $object = $lpCountry->getByCode3($twoLetter);
        return $object->country;
}
