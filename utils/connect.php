<?php



class SalsaConnect {
	public $user = SALSAPRESS_SALSA_USERNAME;
	protected $pass = SALSAPRESS_SALSA_PASS;
	public $url = SALSAPRESS_SALSA_BASE_URL;
	public $chapter = SALSAPRESS_SALSA_CHAPTER_FILTER;
	public $cache = false;
	public $result;

	var $urls = Array (
		'auth' => '/api/authenticate.sjs',
		'gets' => '/api/getObjects.sjs',
		'gets-nofilter' => '/api/getObjects.sjs',
		'get' => '/api/getObject.sjs',
		'get-left' => '/api/getLeftJoin.sjs',
		'count' => '/api/getCount.sjs',
		'save' => '/save',
		'delete' => '/delete',
		'copy' => '/copy',
		'report' => '/api/getReport.sjs'
	);
	var $chapter_fix = Array(
		'gets' => '&condition=chapter_KEY=',
		'save' => '&chapter_KEY='
	);

	protected $ch = NULL;
	private static $instance = NULL;

	private function __construct($cache = false) {
		if( !SALSAPRESS_ACTIVE ) return false;
		$crypt = new SalsaCrypt( SALSAPRESS_SALSA_PASS  );
		$this->pass = $crypt->pass;
		$this->url = "https://".preg_replace(array('/http:\/\//','/https:\/\//'),array('',''),$this->url);;
		$this->ch = curl_init();
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->ch, CURLOPT_TIMEOUT, 100);
		curl_setopt($this->ch, CURLOPT_COOKIESESSION, true);
		curl_setopt($this->ch, CURLOPT_COOKIEFILE, '/tmp/cookies_file');
		curl_setopt($this->ch, CURLOPT_COOKIEJAR, '/tmp/cookies_file');

		if( $cache && function_exists('get_transient') && SALSAPRESS_CACHE ) {
			$this->cache = true;
		} else {
			$auth = $this->post('auth', "email=".$this->user."&password=".$this->pass);
			$this->result = isset($auth->message) ? $auth->message : 'FAIL! :I';
		}

	}
	public static function singleton($cache = false) {

		if (!isset(self::$instance)) {
			$className = __CLASS__;
			self::$instance = new $className($cache);
		}

		self::$instance->cache = $cache && function_exists('get_transient') && SALSAPRESS_CACHE;

		return self::$instance;
	}

	function status() {
		return $this->result;
	}
	function on() {
		return $this->result == "Successful Login" || $this->cache;
	}

	function reportgen($key,$values = array()){
		$conditions = $this->post('gets','object=report_condition&condition=report_KEY='.$key.'&condition=value_type=User%20variable&include=report_condition_KEY', true);

		$params = 'report_KEY='.$key;
		$i = 0;

		$better_values = array();
		if( !empty($values) ) foreach( $values as $fix):
			$code = substr($fix, 0, strpos($fix,"("));
			if( isset($better[$code]) ) {
				$fixme = substr($fix, strpos($fix,'(')+1, strlen($fix)-strpos($fix,'(')-2);
				$better = array(
					'DATE' => date('Y-m-d',strtotime($fixme)),
					'GET' => $_GET[$fixme],
					'POST' => $_POST[$fixme]
				);
				$better_values[] = $better[$code];
			}
			else $better_values[] = $fix;
		endforeach;

		if( count($conditions) > 0 ) foreach( $conditions as $con ):
			$params .= '&u'.$con->key.'='.$better_values[$i];
			$i += 1;
		endforeach;
		return $params;

	}

	function reportsplit($key,$values = array(), $type = '/dia/hq/export.jsp') {


		$params = $this->reportgen($key,$values);
		$save = false;

		// If this call is being cached, check and see if there's cacehed data
		if( $this->cache ) {
			$results = get_transient( $params );

			if( $results === false ) {
				$this->cache = false;
				$save = true;
				$auth = $this->post('auth', "email=".$this->user."&password=".$this->pass);
				$this->result = isset($auth->message) ? $auth->message : 'FAIL! :I';
			} else {
				$go = $results;
			}
		}

		// If the query isn't cached
		if( !$this->cache ) {

			curl_setopt($this->ch, CURLOPT_POST, 1);
			curl_setopt($this->ch, CURLOPT_URL, $this->url.$type);
			curl_setopt($this->ch, CURLOPT_POSTFIELDS, $params."&type=csv");
			$go = curl_exec($this->ch);
			$go = explode("\r\n",$go);
		}

		// Knows to cache the results if they've expired or aren't there
		if( $save ) {
			$caches =  get_option('salsapress_caches');
			$caches[$params] = array('expires' => date('r') );
			update_option( 'salsapress_caches', $caches);
			set_transient( $params , $go , 60*60*12 );
		}
		// $go = trim($go);
		$mapping = str_getcsv(array_shift($go),',','"','\\');
		// if( constant("PHP_OLD") ) {
		// 	$mapping = array_filter($mapping, "no_null");
		// 	$mapping = array_shift($mapping);
		// }



		$parsed = array();
		foreach($go as $thing ){
			$temp = str_getcsv($thing,',','"','\\');
			$i = 0;
			$keyed = array();
			if( !is_null($temp[0]) ) {
				while( $i<count($mapping) ) {
					$nicename = preg_replace(array('/ /','/\(/','/\)/'),array('_','_',''),$mapping[$i]);
					$ii = 1;
					while( array_key_exists($nicename, $keyed) ):
						$nicename = $nicename." ".$ii;
						$ii++;
					endwhile;
					if(strlen($nicename) > 0 && (isset($temp[$i]) || isset($temp[0][$i]) && strlen($nicename) )) {
						if( constant("PHP_OLD") ) $keyed[$nicename] = htmlspecialchars($temp[0][$i]);
						else $keyed[$nicename] = htmlspecialchars($temp[$i]);
					}
					$i++;
				}
				$k = (object)$keyed;
				$parsed = array_merge($parsed,array($k));
			}
		}
		return $parsed;

	}

	function post($type, $params, $no_filter = false ) {
		$save = false;
		// Check and make sure will never use a cache for a save
		$this->cache = $type != 'save' && $this->cache;

		// If this call is being cached, check and see if there's cacehed data
		if( $this->cache ) {
			$results = get_transient( $params );

			if( $results === false ) {
				$this->cache = false;
				$save = true;
				$auth = $this->post('auth', "email=".$this->user."&password=".$this->pass);
				$this->result = isset($auth->message) ? $auth->message : 'FAIL! :I';
			} else {
				$go = $results;
			}
		}

		// If the query isn't cached
		if( !$this->cache ) {
			$chapter = isset($this->chapter_fix[$type]) && !empty($this->chapter) && !$no_filter ? $this->chapter_fix[$type].$this->chapter : '';
			curl_setopt($this->ch, CURLOPT_POST, 1);
			curl_setopt($this->ch, CURLOPT_URL, $this->url.$this->urls[$type]);
			curl_setopt($this->ch, CURLOPT_POSTFIELDS, $params."&json".$chapter);

			$go = urlencode(curl_exec($this->ch));
		}

		// Knows to cache the results if they've expired or aren't there
		if( $save ) {
			$caches =  get_option('salsapress_caches');
			$caches[$params] = array('expires' => date('r') );
			update_option( 'salsapress_caches', $caches);
			set_transient( $params , $go , 60*60*12 );
		}

		return json_decode(urldecode($go));
	}

	function raw($place, $params) {
		$save = false;
		// Check and make sure will never use a cache for a save
		$this->cache = $type != 'save' && $this->cache;

		// If this call is being cached, check and see if there's cacehed data
		if( $this->cache ) {
			$results = get_transient( $params );

			if( $results === false ) {
				$this->cache = false;
				$save = true;
				$auth = $this->post('auth', "email=".$this->user."&password=".$this->pass);
				$this->result = isset($auth->message) ? $auth->message : 'FAIL! :I';
			} else {
				$go = $results;
			}
		}

		// If the query isn't cached
		if( !$this->cache ) {
			$chapter = isset($this->chapter_fix[$type]) && !empty($this->chapter) && !$no_filter ? $this->chapter_fix[$type].$this->chapter : '';
			curl_setopt($this->ch, CURLOPT_POST, 1);
			curl_setopt($this->ch, CURLOPT_URL, $this->url.$this->urls[$type]);
			curl_setopt($this->ch, CURLOPT_POSTFIELDS, $params."&json".$chapter);

			$go = $this->ch;
		}

		// Knows to cache the results if they've expired or aren't there
		if( $save ) {
			$caches =  get_option('salsapress_caches');
			$caches[$params] = array('expires' => date('r') );
			update_option( 'salsapress_caches', $caches);
			set_transient( $params , $go , 60*60*12 );
		}

		return $go;
	}

	function rawjson($type, $params) {
		$save = false;
		// Check and make sure will never use a cache for a save
		$this->cache = $type != 'save' && $this->cache;

		// If this call is being cached, check and see if there's cacehed data
		if( $this->cache ) {
			$results = get_transient( $params );

			if( $results === false ) {
				$this->cache = false;
				$save = true;
				$auth = $this->post('auth', "email=".$this->user."&password=".$this->pass);
				$this->result = isset($auth->message) ? $auth->message : 'FAIL! :I';
			} else {
				$go = $results;
			}
		}

		// If the query isn't cached
		if( !$this->cache ) {
			$chapter = isset($this->chapter_fix[$type]) && !empty($this->chapter) && !$no_filter ? $this->chapter_fix[$type].$this->chapter : '';
			curl_setopt($this->ch, CURLOPT_POST, 1);
			curl_setopt($this->ch, CURLOPT_URL, $this->url.$this->urls[$type]);
			curl_setopt($this->ch, CURLOPT_POSTFIELDS, $params."&json".$chapter);

			$go = urlencode(curl_exec($this->ch));
		}

		// Knows to cache the results if they've expired or aren't there
		if( $save ) {
			$caches =  get_option('salsapress_caches');
			$caches[$params] = array('expires' => date('r') );
			update_option( 'salsapress_caches', $caches);
			set_transient( $params , $go , 60*60*12 );
		}

		return urldecode($go);
	}

	function optionprep( $input ) {
		if( !isset($input[0]) ) die();
		if( !isset($input[1]) ) $input[1] = 'key';
		if( !isset($input[2]) ) $input[2] = 'key';

		$results = $this->post('gets',$input[0]);
		$returning = array();

		foreach($results as $r) {
			$merged_val = '';
			if( is_array($input[2]) ) foreach($input[2] as $v):
				$merged_val .= isset($r->$v) ? $r->$v : $v;
			endforeach; else if( isset($r->$input[2]) ) $mereged_val = $r->$input[2];

			$merged_name = '';
			if( is_array($input[1]) ) foreach($input[1] as $n):
				$merged_name .= isset($r->$n) ? $r->$n : $n;
			endforeach; else if( isset($r->$input[1]) ) $mereged_name = $r->$input[1];

			$returning[] = array( 'value'=> $mereged_val, 'name' => $merged_name );
		}

		return $returning;
	}

	function __destruct() {
		if (isset($this->ch)) curl_close($this->ch);
	}

}


?>