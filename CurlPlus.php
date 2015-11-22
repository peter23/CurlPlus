<?php

/**
 * CurlPlus
 * @author peter23 <i@peter23.com>
 */

	class CurlPlus {

		//curl handler
		protected $ch;

		//basic curl options
		protected $basic_options = array(
			CURLOPT_HEADER => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_TIMEOUT => 600,
			CURLOPT_CONNECTTIMEOUT => 30,
			CURLOPT_ENCODING => ''
		);

		//basic http headers
		protected $basic_headers = array(
			'Expect:'
		);

		//data variables
		protected $additional_headers = array();
		public $cookies = array();
		private $proxylist_idx = -1;

		//parameters
		public $base_url = false;
		public $delay = 0;
		public $delay2 = 0;
		public $follow_location = true;
		public $logger = null;
		public $max_redirects = 6;
		private $proxy = null;
		private $proxylist = array();
		private $referer = null;
		public $save_cookies = false;
		public $send_cookies = false;
		private $userAgent = null;


		// ===== MAGIC METHODS
		public function __construct($headers = false) {
			$this->ch = curl_init();
			curl_setopt_array($this->ch, $this->basic_options);
			$this->setHeaders($headers);
		}


		public function __destruct() {
			curl_close($this->ch);
			unset($this->ch);
		}


		public function __call($method, $args) {
			if( ($method == 'logger') && is_callable($this->logger) ) {
				call_user_func_array($this->logger, $args);
			}
		}


		// ===== PARAMETERS
		public function __get($n) {
			if($n == 'headers') {
				return array_merge($this->basic_headers, $this->additional_headers);

			} elseif(($n == 'proxy') || ($n == 'proxylist') || ($n == 'userAgent')) {
				return $this->{$n};

			} elseif(($n == 'referer') || ($n == 'referrer')) {
				return $this->referer;

			} else {
				throw new Exception('Trying to get nonexistent property');

			}
		}


		public function __set($n, $v) {
			if($n == 'headers') {
				if($v) {
					if(!is_array($v)) {
						$v = array($v);
					}
					curl_setopt($this->ch, CURLOPT_HTTPHEADER, array_merge($this->basic_headers, $v));
					$this->additional_headers = $v;
				} else {
					curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->basic_headers);
					$this->additional_headers = array();
				}

			} elseif($n == 'proxy') {
				$this->logger('set proxy '.$v);
				curl_setopt($this->ch, CURLOPT_PROXY, $v);
				$this->proxy = $v;

			} elseif($n == 'proxylist') {
				if(!is_array($v)) {
					$v = explode("\n", trim($v));
				}
				$this->proxylist = array();
				foreach($v as $v1) {
					$v1 = trim($v1);
					if(!$v1) continue;
					$this->proxylist[] = $v1;
				}
				shuffle($this->proxylist);
				$this->proxylist_idx = -1;

			} elseif(($n == 'referer') || ($n == 'referrer')) {
				//$this->logger('set referer '.$v);
				curl_setopt($this->ch, CURLOPT_REFERER, $v);
				$this->referer = $v;

			} elseif($n == 'userAgent') {
				//$this->logger('set userAgent '.$v);
				curl_setopt($this->ch, CURLOPT_USERAGENT, $v);
				$this->userAgent = $v;

			} else {
				throw new Exception('Trying to set nonexistent property');

			}
		}


		// ===== FUNCTIONS-WORKERS
		protected function sleep() {
			if($this->delay2) {
				$delay = mt_rand($this->delay, $this->delay2);
			} else {
				$delay = $this->delay;
			}
			if($delay) {
				$this->logger('sleep '.$delay);
				sleep($delay);
			}
		}


		protected function _req($url, $headers = false, $redirect_counts = 0) {
			if($headers) {
				//headers for this request only
				$old_headers = $this->additional_headers;
				$this->setHeaders( array_merge(
					$old_headers,
					is_array($headers) ? $headers : array($headers)
				) );
			}

			if($this->base_url) {
				$url = $this->resolve_url($this->base_url, $url);
			}

			//Yes, we're using our own cookies parser instead of CURL's
			//This is because we want to:
			//1) Store cookies in memory
			//2) Have ability to dump and restore cookies

			if($this->send_cookies) {
				$curr_host = parse_url($url, PHP_URL_HOST);

				$cooks = array();
				if(isset($this->cookies[$curr_host])) {
					foreach($this->cookies[$curr_host] as $cooknam=>$cookval) {
						$cooks[$cooknam] = $cooknam.'='.$cookval;
					}
				}
				$host_split = explode('.', $curr_host);
				foreach($host_split as $n_host_split=>$host_split1) {
					if($n_host_split >= count($host_split)-1)  break;
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

				curl_setopt($this->ch, CURLOPT_COOKIE, implode('; ', $cooks));
			}

			//RUN IT
			$s = curl_exec($this->ch);

			if($headers) {
				$this->setHeaders($old_headers);
			}

			$this->sleep();

			if($s === false) {
				throw new Exception('CURL error: '.curl_error($this->ch));
			}

			$code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
			$header_size = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
			$headers = substr($s, 0, $header_size);
			$location = curl_getinfo($this->ch, CURLINFO_REDIRECT_URL);

			$this->logger('  HTTP '.$code.' ('.$header_size.'+'.(strlen($s) - $header_size).' b)'.($location ? ' -> '.$location : ''));

			if($this->save_cookies) {
				if(!isset($curr_host)) {
					$curr_host = parse_url($url, PHP_URL_HOST);
				}
				if(preg_match_all('#\sSet-Cookie:\s*([^=]+)=([^;\n]+)([^\n]*)#i', $headers, $m)) {
					if(!isset($this->cookies[$curr_host])) $this->cookies[$curr_host] = array();
					foreach($m[1] as $mkey=>$cookname) {
						if(preg_match('#domain\s*=([^;\n]+)#i', $m[3][$mkey], $m2)) {
							$cook_domain = strtolower(trim($m2[1]));
						} else {
							$cook_domain = $curr_host;
						}
						if(preg_match('#expires\s*=([^;\n]+)#i', $m[3][$mkey], $m2)) {
							$expires = strtotime(trim($m2[1]));
							if($expires && ($expires < time())) {
								//$this->logger('server set expired cookie '.$cookname.'@'.$cook_domain);
								unset($this->cookies[$cook_domain][$cookname]);
								continue;
							}
						}
						//$this->logger('server set cookie '.$cookname.'@'.$cook_domain);
						$this->cookies[$cook_domain][$cookname] = trim($m[2][$mkey]);
					}
				}
			}

			//Yes, we're using our own follow_location implementation instead of CURL's
			//This is because we have to process cookies between redirects

			if($location && $this->follow_location) {

				$redirect_counts++;
				if($redirect_counts > $this->max_redirects) {
					throw new Exception('Too much redirects');
				}
				return $this->Get($this->resolve_url($url, $location), false, $redirect_counts);

			} else {

				//Yes, we're not using class for response
				//This is because we want to keep the code as fast as possible
				//But anyway we want to process cookies by ourselves

				return array(
					'code' => $code,
					'headers' => $headers,
					'body' => substr($s, $header_size),
					'location' => $location,
					'url' => $url,
				);

			}
		}


		// ===== MAIN ACTIONS
		public function Get($url, $headers = false, $redirect_counts = 0) {
			curl_setopt_array($this->ch, array(
				CURLOPT_POSTFIELDS => false,
				CURLOPT_POST => false,
				CURLOPT_CUSTOMREQUEST => 'GET',
				CURLOPT_HTTPGET => true,
				CURLOPT_URL => $url,
			));
			$this->logger('GET '.($redirect_counts ? '(redirect '.$redirect_counts.') ' : '').$url);
			return $this->_req($url, $headers, $redirect_counts);
		}


		public function Post($url, $body, $headers = false) {
			curl_setopt_array($this->ch, array(
				CURLOPT_HTTPGET => false,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => $body,
				CURLOPT_URL => $url,
			));
			$this->logger('POST '.$url);
			return $this->_req($url, $headers);
		}


		public function Put($url, $body, $headers = false) {
			curl_setopt_array($this->ch, array(
				CURLOPT_POST => false,
				CURLOPT_HTTPGET => false,
				CURLOPT_CUSTOMREQUEST => 'PUT',
				CURLOPT_POSTFIELDS => $body,
				CURLOPT_URL => $url,
			));
			$this->logger('PUT '.$url);
			return $this->_req($url, $headers);
		}


		public function Delete($url, $headers = false) {
			curl_setopt_array($this->ch, array(
				CURLOPT_POSTFIELDS => false,
				CURLOPT_POST => false,
				CURLOPT_HTTPGET => false,
				CURLOPT_CUSTOMREQUEST => 'DELETE',
				CURLOPT_URL => $url,
			));
			$this->logger('DELETE '.$url);
			return $this->_req($url, $headers);
		}


		public function changeProxy() {
			$this->proxylist_idx++;
			if($this->proxylist_idx >= count($this->proxylist)) {
				$this->proxylist_idx = 0;
			}
			$this->__set('proxy', @$this->proxylist[$this->proxylist_idx]);
		}


		// ===== MISC FUNCTIONS
		public function getRandomUserAgent() {
			static $random_agents_components, $random_agents;
			if(!isset($random_agents_components)) {
				$random_agents_components = array(
					'mozilla' => 'Mozilla/5.{0,999} ',
					'all_os' => '[Windows NT|Linux|X11; Linux|Mac OSX] {3,20}.{1,9}[|; x86|; i386|; i686|; x86-64|; x86_64|; x64]',
					'win_os' => 'Windows NT {3,20}.{1,9}[|; x86|; i386|; i686|; x86-64|; x86_64|; x64]',
					'chrome' => 'Chrome/{30,90}.{1,9}.{100,9999}.{100,999}',
					'applewebkit' => 'AppleWebKit/{300,999}.{10,99} (KHTML, like Gecko)',
					'safari' => 'Safari/{300,999}.{10,99}',
					'gecko' => 'Gecko/{2001,2020}{0,1}{1,9}{0,1}{1,9}',
					'firefox_rv' => 'rv:{1,90}.{1,9}.{1,9}',
					'firefox' => 'Firefox/{4,90}.{1,9}.{1,9}',
				);
			}
			if(!isset($random_agents)) {
				$random_agents = array(
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
			}
			$rnd_agent = mt_rand(0, count($random_agents)-1);
			$ua = $random_agents[$rnd_agent];
			if(strpos($ua, '%') !== false) {
				foreach($random_agents_components as $comp_name=>$comp_val) {
					$ua = str_replace('%'.$comp_name.'%', $comp_val, $ua);
				}
				$random_agents[$rnd_agent] = $ua;
			}
			if(preg_match_all('#\{(\d+),(\d+)\}#', $ua, $m)) {
				foreach($m[0] as $n=>$m0) {
					$ua = str_replace($m0, mt_rand($m[1][$n], $m[2][$n]), $ua);
				}
			}
			if(preg_match_all('#\[([^\[]+)\]#', $ua, $m)) {
				foreach($m[0] as $n=>$m0) {
					$m1 = explode('|', $m[1][$n]);
					$m1 = $m1[mt_rand(0, count($m1)-1)];
					$ua = str_replace($m0, $m1, $ua);
				}
			}
			return $ua;
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

?>