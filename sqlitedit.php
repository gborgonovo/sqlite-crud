<?php

// Prepare template engine
require('smarty/Smarty.class.php');
$smarty = new Smarty();

$smarty->setTemplateDir('smarty/templates');
$smarty->setCompileDir('smarty/templates_c');
$smarty->setCacheDir('smarty/cache');
$smarty->setConfigDir('smarty/configs');

//filename of the databases
$dbfile = 'mydb.sqlite';
$db = new PDO('sqlite:'.$dbfile);

// description of DB tables
$tables = array (
	'main' => array (
		'rowid' => 'READ',
		'title' => 'INPUT',
		'author' => 'INPUT',
		'year' => 'INPUT'
	)
);

$seltable = isset($_GET['t'])?$_GET['t']:'';
$selid = isset($_GET['id'])?$_GET['id']:0;
$message = "";

// Insert
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

// Update
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

$smarty->assign('tables', $tables);
$smarty->assign('seltable', $seltable);
$smarty->assign('selid', $selid);
$smarty->assign('message', $message);
$smarty->assign('url', basename($_SERVER['REQUEST_URI']));
$smarty->assign('urlbase', basename($_SERVER["PHP_SELF"]));
$smarty->assign('mydata', $mydata);

$smarty->display('edit.tpl.html');
