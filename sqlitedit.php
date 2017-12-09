<?php

// Prepare template engine
require('smarty/Smarty.class.php');
$smarty = new Smarty();

$smarty->setTemplateDir('smarty/templates');
$smarty->setCompileDir('smarty/templates_c');
$smarty->setCacheDir('smarty/cache');
$smarty->setConfigDir('smarty/configs');

// load configuration file
$config_filename = './sqlitedit.config.php';
if (is_readable($config_filename)) {
	include_once $config_filename;
}

// load incudes file
$includes_filename = './sqlitedit.includes.php';
include_once $includes_filename;

/*
 *
 * Initialization
 *
 */
//constants 1
define("PROJECT", "SQLitEdit");
define("VERSION", "0.1");
define("PAGE", basename(__FILE__));
define("FORCETYPE", false); //force the extension that will be used (set to false in almost all circumstances except debugging)
define("SYSTEMPASSWORD", $password); // Makes things easier.

// don't mess with this - required for the login session
ini_set('session.cookie_httponly', '1');
session_start();

if($debug==true)
{
	ini_set("display_errors", 1);
	error_reporting(E_STRICT | E_ALL);
} else
{
	@ini_set("display_errors", 0);
}

// version-number added so after updating, old session-data is not used anylonger
// cookies names cannot contain symbols, except underscores
define("COOKIENAME", preg_replace('/[^a-zA-Z0-9_]/', '_', $cookie_name . '_' . VERSION) );

//- Check user authentication, login and logout
$auth = new Authorization(); //create authorization object

// check if user has attempted to log out
if (isset($_GET['id']) && $_GET['id'] == -100 )
	$auth->revoke();
// check if user has attempted to log in
else if (isset($_POST['login']) && isset($_POST['password']))
	$auth->attemptGrant($_POST['password'], isset($_POST['remember']));

//- Actions on database files and bulk data
if ($auth->isAuthorized())
{
/*
 *
 * is Authorized
 *
 */
 
	//initialise databases
	$db = new PDO('sqlite:'.$dbfile);

	$seltable = isset($_GET['t']) && $_GET['t']!=''?$_GET['t']:key($tables);
	$selid = isset($_GET['id'])?$_GET['id']:0;
	$message = "";
/*
 * Insert FORM
 */
	if (isset($_POST['submit']) && $_POST['submit'] == 'Crea') {
		$onetable = $tables[$seltable];
		$querystring = "INSERT INTO ".$seltable." (";
		$queryvalues = "";
		foreach ($tables[$seltable] as $field=>$type) {
			$querystring.= $field;
			if (!isset($_POST[$field]) || $type == 'READ') {
				$queryvalues .= 'NULL';
			} else {
				$queryvalues .= "'".$_POST[$field]."'";
			}
			if( next( $onetable )!==FALSE ) {
				$querystring.=", ";
				$queryvalues.=", ";
			}
		}
		$querystring.=") VALUES (".$queryvalues.")";
		$stmt = $db->prepare($querystring);
		if (!$stmt) {
			echo "Error: ".$querystring;
			die();
			}
		

		$stmt->execute();
		$message = "Record creato correttamente";
	}

/*
 * Update FORM
 */
	if (isset($_POST['submit']) && $_POST['submit'] == 'Salva') {
		$onetable = $tables[$seltable];
		$querystring = "UPDATE ".$seltable." SET ";
		foreach ($tables[$seltable] as $field=>$type) {
			$querystring.= $field."= :".$field;
			if( next( $onetable )!==FALSE ) {
					$querystring.=", ";
			}

		}
		$querystring.=" WHERE rowid=".$_POST['rowid'];
		$stmt = $db->prepare($querystring);
		if (!$stmt) {
			echo "Error: ".$querystring;
			die();
			}
		
		foreach ($tables[$seltable] as $field=>$type) {
			$stmt->bindParam(':'.$field, $_POST[$field]);       
		}
		$stmt->execute();

		$message = "Record salvato correttamente";
	}


	if ($seltable) {
		$querystring = 'SELECT rowid as rowid,* FROM "'.$seltable.'" WHERE 1';
		$statement = $db->prepare($querystring);
		$statement->execute();
		$mydata = $statement->fetchAll();
	} else {
		$mydata = array();
	}

	$smarty->assign('isAuthorized', $auth->isAuthorized());
	$smarty->assign('tables', $tables);
	$smarty->assign('seltable', $seltable);
	$smarty->assign('selid', $selid);
	$smarty->assign('message', $message);
	$smarty->assign('url', basename($_SERVER['REQUEST_URI']));
	$smarty->assign('urlbase', basename($_SERVER["PHP_SELF"]));
	$smarty->assign('mydata', $mydata);

} else {
/*
 *
 * is NOT Authorized
 *
 */
	$smarty->assign('isAuthorized', $auth->isAuthorized());
	if($auth->isFailedLogin()) $smarty->assign('message', '<span class="glyphicon glyphicon-alert"></span> Password incorrect');
	$smarty->assign('urlbase', basename($_SERVER["PHP_SELF"]));



}

$smarty->display('edit.tpl.html');
