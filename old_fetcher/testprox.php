<?php
	
	require('class.Fetcher.php');
	
	$f = new Fetcher;
	
	$f->load_proxylist(file_get_contents('proxy.txt'));
	
	var_dump($f->curr_proxy);
	
	var_dump($f->fetchs('http://home.peter23.com/ip2.php'));
	
?>