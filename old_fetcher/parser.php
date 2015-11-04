<?php
	
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
	ini_set('memory_limit', '1024M');
	ini_set('max_execution_time', 0);
	set_time_limit(0);
	ignore_user_abort(true);
	
	require('phpQuery-onefile.php');
	require('class.Fetcher.php');
	$f = new Fetcher;
	//$f->follow_location();
	//$f->auto_ref = true;
	$f->delay = 3;
	$f->ref = '';
	//$f->cookies_process();
	
	
?>