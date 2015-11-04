<?php
	
	$d = @unserialize(@file_get_contents('php://input'));
	if(!$d)  die();
	$data_for_put = @$d['data_for_put'];
	unset($d['data_for_put']);
	$ch = curl_init();
	curl_setopt_array($ch, $d);
	if(@$d[CURLOPT_CUSTOMREQUEST] == 'PUT') {
		$fput = fopen('php://temp', 'w');
		fwrite($fput, $data_for_put);
		fseek($fput, 0); 
		curl_setopt($ch, CURLOPT_INFILE, $fput);
		curl_setopt($ch, CURLOPT_INFILESIZE, strlen($data_for_put));
	}
	echo curl_exec($ch);
	
?>