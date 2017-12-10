<?php
//password to gain access
$password = 'AdminPasswd';

//filename of the databases
$dbfile = 'mydb.sqlite';

// description of DB tables
$tables = array (
	'main' => array (
		'rowid' => 'READ',
		'title' => 'INPUT',
		'author' => 'INPUT',
		'year' => 'INPUT',
		'editor' => 'INPUT',
		'description' => 'TEXTAREA'
	)
);

// list: show the list as table; block: show a list of full record
$display = 'list';
$nrfields = 4;

/* ---- Advanced options ---- */

//changing the following variable allows multiple phpLiteAdmin installs to work under the same domain.
$cookie_name = 'test';

//whether or not to put the app in debug mode where errors are outputted
$debug = false;
