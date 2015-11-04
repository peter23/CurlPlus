<?php

///////////////////////////////////
// CLASS MultiFetcher

// not processed:
// follow_location
// save_file_h

class MultiFetcher {
	
	public $curl_handler = false;
	public $f_curl_handlers = array();
	public $delay = 0;
	public $delay2 = 0;
	
	
	public function __construct() {
		$this->curl_handler = curl_multi_init();
		if(!$this->curl_handler) return false;
		else return true;
	}
	
	
	public function __destruct() {
		curl_multi_close($this->curl_handler);
		unset($this->f_curl_handlers, $this->curl_handler);
	}
	
	private function get_delay() {
		if($this->delay2 <= $this->delay)
			$delay = $this->delay;
		else
			$delay = mt_rand($this->delay, $this->delay2);
		return $delay;
	}
	
	
	public function push(Fetcher &$f) {
		curl_multi_add_handle($this->curl_handler, $f->curl_handler);
		$this->f_curl_handlers[] = $f;
	}
	
	
	public function run() {
		$res = false;
		
		do {
			curl_multi_exec($this->curl_handler, $active);
			usleep(100);
		} while($active);
		
		foreach($this->f_curl_handlers as $i=>&$fch) {
			$ch = $fch->curl_handler;
			$res[$i] = $fch->after_run(curl_multi_getcontent($ch));
			curl_multi_remove_handle($this->curl_handler, $ch);
			unset($ch, $this->f_curl_handlers[$i], $fch);
		} unset($fch);
		
		sleep($this->get_delay());
		
		return $res;
	}
}

?>