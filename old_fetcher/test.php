<?php
	
	require('class.Fetcher.php');
	require('class.MultiFetcher.php');
	
	/*$f = new Fetcher;
	
	$f->save_to_file('test.txt');
	
	$s = $f->fetch('http://home.peter23.com/ip2.php');
	var_dump($s->h);
	var_dump($s->b);
	
	$s = $f->fetch('http://home.peter23.com/ip2.php');
	var_dump($s->h);
	var_dump($s->b);
	
	$s = $f->fetch('http://home.peter23.com/ip2.php');
	var_dump($s->h);
	var_dump($s->b);*/
	
	/*$f = new Fetcher;
	$f->proxy('script://http://test.peter.am/fscript.php');
	$s = $f->fetchs('http://home.peter.am/ip2.php');
	var_dump($s);
	file_put_contents('111.txt', $s);*/
	
	/*$f = new Fetcher;
	$f->proxy('127.0.0.1:8888');
	$f->proxy('ip://192.168.1.23');
	$f->save_to_file('test.dat');
	$f->fetchs('https://www.google.ru/');
	$f->custom_request('PUT');
	$s = $f->fetch('https://www.google.ru/', 'sdgsdgsdfgertwert');
	var_dump($s->h);
	$f->custom_request();
	$s = $f->fetch('https://www.google.ru/');
	var_dump($s->h);*/
	
	/*function fetcher_log($s, &$f) {
		var_dump($s);
		//var_dump($f->ref);
		//$f->ref = '56785678';
	}
	
	$f = new Fetcher;
	//$f->ref = '456';
	//$f->log('123');
	//var_dump($f->ref);
	$f->fetchs('http://www.yandex.ru/');*/
	
	//$f->cookies_process(true,true);
	
	//$f->proxy('127.0.0.1:8888');
	
	//$f->delay = 1;
	
	/*
	$f = new Fetcher;
	var_dump($f->fetch('http://www.yandex.ru/')->host);
	*/
	
	//$f = new Fetcher;
	//var_dump($f->fetchs('http://www.yandex.ru/'));
	
	/*
	$m = new MultiFetcher;
	
	$f1 = new Fetcher;
	$f2 = new Fetcher;
	
	$f1->before_run('http://ya.ru/');
	$f2->before_run('http://r0.ru/');
	
	$m->push($f1);
	$m->push($f2);
	
	var_dump($m->run());
	*/
	
?>