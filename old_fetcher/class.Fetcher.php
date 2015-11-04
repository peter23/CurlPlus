<?php

/**
 * Fetcher
 * @author peter23 <i@peter23.com>
 */

class Fetcher {

	private $base_options_httpheader = array(
		'Expect:',
		'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
		'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4',
		'Accept-Charset: UTF-8,*;q=0.5',
	);
	private $opts_headers = array();
	private $base_options = array(
		CURLOPT_HEADER => true,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_SSL_VERIFYHOST => 0,
		CURLOPT_TIMEOUT => 600,
		CURLOPT_CONNECTTIMEOUT => 30,
		CURLOPT_ENCODING => '',
	);
	private $custom_headers = array();
	private $opts_save_cookies = false;
	private $opts_send_cookies = false;
	private $conv_enc_from = false;
	private $conv_enc_to = false;
	private $opts_follow_location = false;
	private $opts_maxredirs = 5;
	private $var_proxy_id = 0;
	private $save_file_h = false;
	private $header_file_h = false;
	private $null_handler = false;
	private $custom_request = false;
	private $prox_cookies = array();
	private $uas = array();
	private $params4script = array();
	private $addr4script = false;
	private $curl_handler4script = false;
	public $curl_handler = false;
	public $curr_url = false;
	public $curr_host = false;
	public $curr_proxy = false;
	public $curr_ua = false;
	public $cookies = array();
	public $delay = 0;
	public $delay2 = 0;
	public $auto_ref = false;
	public $ref = false;
	public $last_error = false;
	public $proxylist = false;
	public $change_cookies_with_proxy = true;
	public $random_user_agent_on_each_proxy = true;
	public $log_func = 'fetcher_log';
	public $proxy_func = false;
	private $random_agents_components = array(
		'mozilla' => 'Mozilla/5.{0,999} ',
		'all_os' => '[Windows NT|Linux|X11; Linux|Mac OSX] {3,20}.{1,9}[|; x86|; i386|; i686|; x86-64|; x86_64|; x64]',
		'win_os' => 'Windows NT {3,20}.{1,9}[|; x86|; i386|; i686|; x86-64|; x86_64|; x64]',
		'chrome' => 'Chrome/{30,90}.{1,9}.{100,9999}.{100,999}',
		'applewebkit' => 'AppleWebKit/{300,999}.{10,99} (KHTML, like Gecko)',
		'safari' => 'Safari/{300,999}.{10,99}',
		'gecko' => 'Gecko/{2001,2020}{0,1}{1,9}{0,1}{1,9}',
		'firefox_rv' => 'rv:{1,10}.{1,9}.{1,9}',
		'firefox' => 'Firefox/{4,90}.{1,9}.{1,9}',
	);
	private $random_agents = array(
		//Chrome
		'%mozilla%(%all_os%) %applewebkit% %chrome% %safari%',
		//Edge
		'%mozilla%(%win_os%) %applewebkit% %chrome% %safari% Edge/{10,50}.{1000,999999}',
		//Epiphany Gecko
		'%mozilla%(%all_os%; %firefox_rv%) %gecko% Epiphany/{1,10}.{10,99}.{10,99} %firefox%',
		//Epiphany Webkit
		'%mozilla%(%all_os%) %applewebkit% Epiphany/{1,10}.{10,99}.{10,99}',
		//Firefox
		'%mozilla%(%all_os%; %firefox_rv%) %gecko% %firefox%',
		//Flock Gecko
		'%mozilla%(%all_os%; %firefox_rv%) %gecko% %firefox% Flock/{1,10}.{1,9}.{1,9}',
		//Flock Webkit
		'%mozilla%(%all_os%) %applewebkit% Flock/{1,10}.{1,9}.{1,9} %chrome% %safari%',
		//IE
		'%mozilla%(compatible; MSIE {9,15}.{1,9}; %win_os%; Trident/{3,15}.{1,9})',
		//Iron
		'%mozilla%(%all_os%) %applewebkit% %chrome% Iron/{30,90}.{1,9}.{100,9999}.{100,999} %safari%',
		//K-Meleon
		'%mozilla%(%all_os%; %firefox_rv%) %gecko% K-Meleon/{30,90}.{1,9}',
		//Konqueror
		'%mozilla%(compatible; Konqueror/{3,10}.{1,9}.{1,9}; %all_os%) KHTML/{3,10}.{1,9}.{1,9} (like Gecko)',
		//Maxthon
		'%mozilla%(%all_os%) %applewebkit% Maxthon/{2,30}.{1,99}.{1,99}.{1,99} %safari%',
		//Opera
		'%mozilla%(%all_os%) %applewebkit% %chrome% %safari% OPR/{15,90}.{1,9}.{100,9999}.{100,999}',
		//Safari
		'%mozilla%(%all_os%) %applewebkit% Version/{4,30}.{1,9}.{1,9} %safari%',
		//SeaMonkey
		'%mozilla%(%all_os%; %firefox_rv%) %gecko% %firefox% SeaMonkey/{1,10}.{1,99}',
		//UCBrowser
		'%mozilla%(%all_os%) %applewebkit% %chrome% UBrowser/{2,30}.{1,9}.{100,9999}.{100,999} %safari%',
		//Vivaldi
		'%mozilla%(%all_os%) %applewebkit% %chrome% %safari% Vivaldi/{1,20}.{1,9}.{100,9999}.{100,999}',
		//Yandex.Browser
		'%mozilla%(%all_os%) %applewebkit% %chrome% YaBrowser/{10,50}.{1,9}.{100,9999}.{100,999} %safari%',
	);


	public function __construct() {
		$this->curl_handler4script = curl_init();
		curl_setopt_array($this->curl_handler4script, array(
			CURLOPT_POST => true,
			CURLOPT_HEADER => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_TIMEOUT => 600,
			CURLOPT_CONNECTTIMEOUT => 30,
			CURLOPT_HTTPHEADER => array('Expect:'),
			CURLOPT_ENCODING => '',
		));
		$this->base_options[CURLOPT_HTTPHEADER] = $this->base_options_httpheader;
		$this->params4script = $this->base_options;
		$this->curl_handler = curl_init();
		if($this->curl_handler) {
			curl_setopt_array($this->curl_handler, $this->base_options);
			$this->random_user_agent();
			return true;
		} else
			return false;
	}

	public function __destruct() {
		curl_close($this->curl_handler);
		unset($this->curl_handler);
	}

	public function log($s) {
		if(is_callable($this->log_func)) call_user_func_array($this->log_func, array($s, &$this));
	}

	private function get_delay() {
		if($this->delay2 <= $this->delay)
			$delay = $this->delay;
		else
			$delay = mt_rand($this->delay, $this->delay2);
		$this->log('sleep '.$delay);
		return $delay;
	}

	public function proxy($proxy = false) {
		$proxytype = '';
		$proxy = trim($proxy);
		if($proxy) {
			$old_curr_proxy = $this->curr_proxy;
			$this->curr_proxy = $proxy;
			$this->log('proxy change to "'.$proxy.'"');
			if(preg_match('#^([^\:]+)\://#', $proxy, $m)) {
				$proxy = preg_replace('#^[^\:]+\://#', '', $proxy);
				$proxytype = $m[1];
			}
			$this->addr4script = false;
			if(strtolower(substr($proxytype,0,6))=='script') {
				$this->addr4script = $proxy;
				curl_setopt_array($this->curl_handler, array(
					CURLOPT_PROXY => false,
					CURLOPT_PROXYTYPE => false,
					CURLOPT_INTERFACE => '0.0.0.0',
				));
			} elseif(strtolower(substr($proxytype,0,2))!='ip') {
				if(strtolower(substr($proxytype,0,5))=='socks') $proxytype = CURLPROXY_SOCKS5;
				else $proxytype = CURLPROXY_HTTP;
				curl_setopt_array($this->curl_handler, array(
					CURLOPT_PROXY => $proxy,
					CURLOPT_PROXYTYPE => $proxytype,
					CURLOPT_INTERFACE => '0.0.0.0',
				));
			} else {
				curl_setopt_array($this->curl_handler, array(
					CURLOPT_PROXY => false,
					CURLOPT_PROXYTYPE => false,
					CURLOPT_INTERFACE => $proxy,
				));
			}
			if($this->change_cookies_with_proxy) {
				if($old_curr_proxy) $this->prox_cookies[$old_curr_proxy] = $this->cookies;
				if(isset($this->prox_cookies[$this->curr_proxy])) $this->cookies = $this->prox_cookies[$this->curr_proxy];
				else $this->cookies = array();
			}
			if($this->random_user_agent_on_each_proxy) {
				if($old_curr_proxy) $this->uas[$old_curr_proxy] = $this->curr_ua;
				if(isset($this->uas[$this->curr_proxy])) $this->user_agent($this->uas[$this->curr_proxy]);
				else $this->random_user_agent();
			}
		} else {
			curl_setopt_array($this->curl_handler, array(
				CURLOPT_PROXY => false,
				CURLOPT_PROXYTYPE => false,
				CURLOPT_INTERFACE => '0.0.0.0',
			));
		}
	}

	public function load_proxylist($s) {
		if(!is_array($s))
			$a = explode("\n", trim($s));
		else
			$a = $s;
		if(!$a) return false;
		$this->proxylist = array();
		foreach($a as $s) {
			$s = trim($s);
			if(!$s) continue;
			$this->proxylist[] = $s;
		}
		if(count($this->proxylist) > 0) {
			shuffle($this->proxylist);
			$this->var_proxy_id = mt_rand(0, (count($this->proxylist)-1));
			$this->proxylist_change();
			return true;
		} else
			return false;
	}

	public function user_agent($ua = false) {
		if($ua) $this->opts_headers['User-Agent'] = $ua;
		else unset($this->opts_headers['User-Agent']);
		$this->log('set User-Agent: '.$ua);
		$this->curr_ua = $ua;
	}

	public function random_user_agent() {
		$a1 = $this->random_agents[round(mt_rand(0, count($this->random_agents)-1))];
		if(preg_match_all('#\{(\d+),(\d+)\}#', $a1, $m)) {
			foreach($m[0] as $n=>$m0) {
				$a1 = str_replace($m0, round(mt_rand($m[1][$n],$m[2][$n])), $a1);
			}
		}
		if(preg_match_all('#\[([^\[]+)\]#', $a1, $m)) {
			foreach($m[0] as $n=>$m0) {
				$m1 = explode('|', $m[1][$n]);
				$m1 = $m1[round(mt_rand(0, count($m1)-1))];
				$a1 = str_replace($m0, $m1, $a1);
			}
		}
		$this->user_agent($a1);
	}

	private function curl_set_http_headers() {
		$opts_headers = array();
		foreach($this->opts_headers as $n=>$v) {
			if(!$v)
				$opts_headers[] = $n.':';
			else
				$opts_headers[] = $n.': '.$v;
		}
		curl_setopt($this->curl_handler, CURLOPT_HTTPHEADER, array_merge($opts_headers, $this->base_options_httpheader, $this->custom_headers));
		$this->params4script[CURLOPT_HTTPHEADER] = array_merge($opts_headers, $this->base_options_httpheader, $this->custom_headers);
	}

	public function http_headers($hdrs = false) {
		if(!is_array($hdrs)) $hdrs = array();
		$this->custom_headers = $hdrs;
	}

	public function save_to_file($filename = false) {
		if($filename) {
			$this->log('save to file '.$filename);
			$this->save_file_h = fopen($filename, 'w');
			if(!$this->save_file_h) return false;
			$this->header_file_h = fopen('php://temp', 'w');
			curl_setopt_array($this->curl_handler, array(
				CURLOPT_WRITEHEADER => $this->header_file_h,
				CURLOPT_FILE => $this->save_file_h,
			));
			curl_setopt($this->curl_handler4script, CURLOPT_FILE, $this->save_file_h);
		} else {
			$this->save_file_h = false;
			$this->header_file_h = false;
			if($this->null_handler === false)  $this->null_handler = file_exists('/dev/null') ? fopen('/dev/null', 'w') : (file_exists('NUL') ? fopen('NUL', 'w') : null);
			curl_setopt_array($this->curl_handler, array(
				CURLOPT_FILE => $this->null_handler,
				CURLOPT_WRITEHEADER => $this->null_handler,
				CURLOPT_HEADER => true,
				CURLOPT_RETURNTRANSFER => true,
			));
			curl_setopt_array($this->curl_handler4script, array(
				CURLOPT_FILE => $this->null_handler,
				CURLOPT_RETURNTRANSFER => true,
			));
		}
		return true;
	}

	public function follow_location($val = true, $maxredirs = 5, $follow_post = false) {
		$this->opts_follow_location = $val;
		$this->opts_maxredirs = $maxredirs;
		$this->opts_follow_location_post = $follow_post;
	}

	public function only_header($val = true) {
		if($val) {
			curl_setopt_array($this->curl_handler, array(
				CURLOPT_NOBODY => true,
				CURLOPT_CUSTOMREQUEST => 'HEAD',
			));
			$this->params4script[CURLOPT_NOBODY] = true;
			$this->params4script[CURLOPT_CUSTOMREQUEST] = 'HEAD';
		} else {
			curl_setopt_array($this->curl_handler, array(
				CURLOPT_NOBODY => false,
				CURLOPT_CUSTOMREQUEST => 'GET',
			));
			$this->params4script[CURLOPT_NOBODY] = false;
			$this->params4script[CURLOPT_CUSTOMREQUEST] = 'GET';
		}
	}

	public function custom_request($val = false) {
		$this->custom_request = strtoupper(trim($val));
	}

	public function cookies_process($save_cookies = true, $send_cookies = true) {
		$this->opts_save_cookies = $save_cookies;
		$this->opts_send_cookies = $send_cookies;
	}

	public function timeout($val1 = 600, $val2 = 30) {
		curl_setopt_array($this->curl_handler, array(
			CURLOPT_TIMEOUT => $val1,
			CURLOPT_CONNECTTIMEOUT => $val2,
		));
		$this->params4script[CURLOPT_TIMEOUT] = $val1;
		$this->params4script[CURLOPT_CONNECTTIMEOUT] = $val2;
	}

	public function conv_enc($from = false, $to = false) {
		$this->conv_enc_from = $from;
		$this->conv_enc_to = $to;
	}

	public function fetch($url, $post = false) {
		if($this->opts_follow_location) {
			$del = $this->delay;
			$del2 = $this->delay2;
			$nredir = 0;
		}
		while(true) {
			$this->log('fetch('.($this->custom_request ? $this->custom_request.' ' : '').($post ? 'post' : 'get').') '.$url);
			$host = parse_url($url, PHP_URL_HOST);
			$this->curr_host = $host;
			curl_setopt($this->curl_handler, CURLOPT_URL, $url);
			$this->params4script[CURLOPT_URL] = $url;
			$this->curr_url = $url;
			if($this->ref) $this->opts_headers['Referer'] = $this->ref;
			else unset($this->opts_headers['Referer']);
			if($this->custom_request) {
				curl_setopt($this->curl_handler, CURLOPT_CUSTOMREQUEST, $this->custom_request);
				$this->params4script[CURLOPT_CUSTOMREQUEST] = $this->custom_request;
			}
			if($this->custom_request != 'PUT') {
				curl_setopt($this->curl_handler, CURLOPT_PUT, false);
				$this->params4script[CURLOPT_PUT] = false;
			} else {
				curl_setopt($this->curl_handler, CURLOPT_PUT, true);
				$this->params4script[CURLOPT_PUT] = true;
			}
			if(($post)||($post==='')) {
				if($this->custom_request != 'PUT') {
					if(!$this->custom_request) {
						curl_setopt($this->curl_handler, CURLOPT_CUSTOMREQUEST, 'POST');
						$this->params4script[CURLOPT_CUSTOMREQUEST] = 'POST';
					}
					curl_setopt_array($this->curl_handler, array(
						CURLOPT_POST => true,
						CURLOPT_POSTFIELDS => $post,
					));
					$this->params4script[CURLOPT_POST] = true;
					$this->params4script[CURLOPT_POSTFIELDS] = $post;
				} else {
					curl_setopt_array($this->curl_handler, array(
						CURLOPT_CUSTOMREQUEST => 'PUT',
						CURLOPT_POST => false,
					));
					$this->params4script[CURLOPT_CUSTOMREQUEST] = 'PUT';
					$this->params4script[CURLOPT_POST] = false;
					$fput = fopen('php://temp', 'w');
					fwrite($fput, $post);
					$this->params4script['data_for_put'] = $post;
					fseek($fput, 0);
					curl_setopt_array($this->curl_handler, array(
						CURLOPT_INFILE => $fput,
						CURLOPT_INFILESIZE => strlen($post),
					));
				}
			} else {
				if(!$this->custom_request) {
					curl_setopt_array($this->curl_handler, array(
						CURLOPT_CUSTOMREQUEST => 'GET',
						CURLOPT_HTTPGET => true,
					));
					$this->params4script[CURLOPT_CUSTOMREQUEST] = 'GET';
					$this->params4script[CURLOPT_HTTPGET] = true;
				}
				curl_setopt($this->curl_handler, CURLOPT_POST, false);
				$this->params4script[CURLOPT_POST] = false;
			}
			if($this->opts_send_cookies) {
				$cooks = array();
				if(isset($this->cookies[$host])) {
					foreach($this->cookies[$host] as $cooknam=>$cookval) {
						if(!isset($cooks[$cooknam])) {
							$cooks[$cooknam] = $cooknam.'='.$cookval;
						}
					}
				}
				$host_split = explode('.', $host);
				foreach($host_split as $n_host_split=>$host_split1) {
					if($n_host_split == count($host_split)-1)  break;
					$host2 = implode('.', array_slice($host_split, $n_host_split));
					if(isset($this->cookies['.'.$host2])) {
						foreach($this->cookies['.'.$host2] as $cooknam=>$cookval) {
							if(!isset($cooks[$cooknam])) {
								$cooks[$cooknam] = $cooknam.'='.$cookval;
							}
						}
					}
					if(isset($this->cookies[$host2])) {
						foreach($this->cookies[$host2] as $cooknam=>$cookval) {
							if(!isset($cooks[$cooknam])) {
								$cooks[$cooknam] = $cooknam.'='.$cookval;
							}
						}
					}
				}
				if($cooks) {
					$cooks = implode('; ', $cooks);
					$this->opts_headers['Cookie'] = $cooks;
				} else
					unset($this->opts_headers['Cookie']);
			}
			$this->curl_set_http_headers();

			if($this->save_file_h) {
				curl_setopt($this->curl_handler, CURLOPT_HEADER, false);
				$this->params4script[CURLOPT_HEADER] = false;
			}
			if(!$this->addr4script) {
				$s = curl_exec($this->curl_handler);
			} else {
				curl_setopt_array($this->curl_handler4script, array(
					CURLOPT_URL => $this->addr4script,
					CURLOPT_POSTFIELDS => serialize($this->params4script),
				));
				$s = curl_exec($this->curl_handler4script);
			}
			if($this->save_file_h) {
				curl_setopt($this->curl_handler, CURLOPT_HEADER, true);
				$this->params4script[CURLOPT_HEADER] = true;

				fseek($this->header_file_h, 0);
				$s = stream_get_contents($this->header_file_h);
				fclose($this->header_file_h);
			}
			if(isset($fput)) {
				fclose($fput);
				curl_setopt_array($this->curl_handler, array(
					CURLOPT_INFILESIZE => 0,
					CURLOPT_INFILE => STDIN,
				));
			}
			sleep($this->get_delay());

			if(!$s) {
				$this->last_error = curl_error($this->curl_handler);
				$this->log('fail to fetch!  "'.$this->last_error.'"');
				return false;
			}
			if($this->auto_ref) $this->ref = $this->curr_url;
			$s = new FetcherResponse($s, curl_getinfo($this->curl_handler, CURLINFO_HEADER_SIZE), $this->curr_host, $this->curr_url, $this->conv_enc_from, $this->conv_enc_to);
			$s->f = &$this;
			if($this->opts_save_cookies) {
				if(preg_match_all('/\sSet-Cookie:\s*([^=]+)=([^;\n]+)([^\n]*)/i', $s->h, $m)) {
					if(!isset($this->cookies[$this->curr_host])) $this->cookies[$this->curr_host] = array();
					foreach($m[1] as $mkey=>$cookname) {
						$val = trim($m[2][$mkey]);
						if(!strlen($val)) continue;
						if(strtolower($val) == 'deleted') {
							unset($this->cookies[$this->curr_host][$cookname]);
							continue;
						}
						if(preg_match('#domain\s*=([^;\n]+)#i', $m[3][$mkey], $m3)) {
							$cook_domain = strtolower(trim($m3[1]));
						} else {
							$cook_domain = $this->curr_host;
						}
						$this->cookies[$cook_domain][$cookname] = $val;
					}
				}
			}
			if($this->save_file_h) {
				fflush($this->save_file_h);
				fclose($this->save_file_h);
				$this->save_to_file();
			}

			if(!$s)
				break;
			if($this->opts_follow_location) {
				$url = $s->get_loc();
				if(!$url) break;
				else {
					$nredir++;
					if($nredir > $this->opts_maxredirs) break;
					$this->log('follow redirect to '.$url);
					if(!$this->opts_follow_location_post) $post = false;
					$this->delay = 0;
					$this->delay2 = 0;
					continue;
				}
			} else
				break;
		}
		if($this->opts_follow_location) {
			$this->delay = $del;
			$this->delay2 = $del2;
		}
		return $s;
	}

	public function fetchs($url, $post = false) {
		$x = $this->fetch($url, $post);
		if(!$x) return false;
		else {
			$s = $x->b;
			unset($x);
			return $s;
		}
	}

	public function proxylist_change() {
		if(!$this->proxy_func) {
			$this->var_proxy_id++;
			if($this->var_proxy_id >= count($this->proxylist)) $this->var_proxy_id = 0;
			if(isset($this->proxylist[$this->var_proxy_id]))
				$pr = $this->proxylist[$this->var_proxy_id];
			else
				$pr = false;
		} else
			$pr = call_user_func($this->proxy_func);
		$this->proxy($pr);
	}

	public function get_last_http_code() {
		return curl_getinfo($this->curl_handler, CURLINFO_HTTP_CODE);
	}

	public function make_post_str($post_arr) {
		if(function_exists('http_build_query')) return http_build_query($post_arr);
		$spost = array();
		foreach($post_arr as $key=>$val) {
			$spost[]=urlencode($key).'='.urlencode($val);
		}
		$spost = implode('&', $spost);
		return $spost;
	}

	public function resolve_url($base, $url) {
		if (!strlen($base)) return $url;
		// Step 2
		if (!strlen($url)) return $base;
		// Step 3
		if (preg_match('!^[a-z]+:!i', $url)) return $url;
		$base = parse_url($base);
		if ($url{0} == "#") {
			// Step 2 (fragment)
			$base['fragment'] = substr($url, 1);
			return $this->unparse_url($base);
		}
		unset($base['fragment']);
		unset($base['query']);
		if (substr($url, 0, 2) == "//") {
			// Step 4
			return $this->unparse_url(array(
				'scheme'=>$base['scheme'],
				'path'=>substr($url,2),
			));
		} else if ($url{0} == "/") {
			// Step 5
			$base['path'] = $url;
		} else {
			// Step 6
			$path = explode('/', $base['path']);
			$url_path = explode('/', $url);
			// Step 6a: drop file from base
			array_pop($path);
			// Step 6b, 6c, 6e: append url while removing "." and ".." from
			// the directory portion
			$end = array_pop($url_path);
			foreach ($url_path as $segment) {
				if ($segment == '.') {
					// skip
				} else if ($segment == '..' && $path && $path[sizeof($path)-1] != '..') {
					array_pop($path);
				} else {
					$path[] = $segment;
				}
			}
			// Step 6d, 6f: remove "." and ".." from file portion
			if ($end == '.') {
				$path[] = '';
			} else if ($end == '..' && $path && $path[sizeof($path)-1] != '..') {
				$path[sizeof($path)-1] = '';
			} else {
				$path[] = $end;
			}
			// Step 6h
			$base['path'] = join('/', $path);
		}
		// Step 7
		return $this->unparse_url($base);
	}

	public function unparse_url($parsed_url) {
		$scheme		= isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
		$host		= isset($parsed_url['host']) ? $parsed_url['host'] : '';
		$port		= isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
		$user		= isset($parsed_url['user']) ? $parsed_url['user'] : '';
		$pass		= isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
		$pass		= ($user || $pass) ? "$pass@" : '';
		$path		= isset($parsed_url['path']) ? $parsed_url['path'] : '';
		$query		= isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
		$fragment	= isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
		return "$scheme$user$pass$host$port$path$query$fragment";
	}

}


///////////////////////////////////
// CLASS FetcherResponse

class FetcherResponse {
	public $h = false;
	public $b = false;
	public $att_filename = false;
	public $host = false;
	public $url = false;
	public $f = false;


	function __construct($s, $header_size, $host, $url, $conv_enc_from, $conv_enc_to) {
		$this->url = $url;
		$this->host = $host;
		$this->h = substr($s, 0, $header_size);
		$this->b = substr($s, $header_size);
		if(preg_match('/\sContent-Disposition:[^\n]*attachment;\sfilename=(.+)(\n|$)/i', $this->h, $m)) {
			$this->att_filename = trim($m[1]);
			$c = substr($this->att_filename, 0, 1);
			if(($c == '"')||($c == "'")) {
				$spos = strpos($this->att_filename, $c, 1);
				if($spos) {
					$this->att_filename = substr($this->att_filename, 1, $spos-1);
					$this->att_filename = urldecode($this->att_filename);
				}
			}
			$this->att_filename = trim($this->att_filename, '"\'');
			if(substr($this->att_filename, 0, 10) == '=?UTF-8?B?')
				$this->att_filename = base64_decode(substr($this->att_filename, 10, strlen($this->att_filename)-12));
		}
		if($conv_enc_to) {
			if(!$conv_enc_from) $conv_enc_from = $this->get_enc();
			$this->b = mb_convert_encoding($this->b, $conv_enc_to, $conv_enc_from);
		}
	}

	function __destruct() {
		unset($this->h);
		unset($this->b);
		unset($this->att_filename);
		unset($this->host);
	}

	public function __get($name) {
		if($name=='s') return $this->h."\r\n\r\n".$this->b;
		else return null;
	}


	public function get_loc() {
		if(preg_match('/\sLocation:(.*?)(\n|$)/i', $this->h, $m)) {
			$ret = trim(html_entity_decode($m[1]));
			$ret = $this->f->resolve_url($this->url, $ret);
		} else
			$ret = false;
		return $ret;
	}

	public function get_enc() {
		if(preg_match('/\sContent-Type:(.+)(\n|$)/i', $this->h, $m)) $ret[] = trim($m[1]);
		if(preg_match_all('/<meta[^>] *?(.*) *?\/?>/isU', $this->s, $meta)) {
			foreach($meta[1] as $attr) {
				if (preg_match('/(http-equiv|name) *?= *?([\'"]{0,1})content-type\\2/i', $attr, $key)) {
					$ret[] = $attr;
				}
			}
			foreach($meta[1] as $attr) {
				$ret[] = $attr;
			}
		}
		$ret[] = 'charset=windows-1251';
		foreach($ret as $ret2) {
			if(preg_match('/charset\s*=[\s\'"]*([^\'"\s>$]+)/i', $ret2, $m)) {
				$ret = explode(';', $m[1]);
				$ret = trim(strtoupper(trim($ret[0])));
				if($ret == 'CP-1251') {
					$ret = 'CP1251';
				}
				return $ret;
			}
		}
		return false;
	}

}

?>