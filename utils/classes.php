<?php
date_default_timezone_set('America/Los_Angeles');

if (!function_exists('str_getcsv')) { 
define( "PHP_OLD", true);
	function str_getcsv($input, $delimiter=',', $enclosure='"', $escape=null, $eol=null) { 
		$temp=fopen("php://memory", "rw"); 
		fwrite($temp, $input); 
		fseek($temp, 0); 
		$r = array(); 
		while (($data = fgetcsv($temp, 4096, $delimiter, $enclosure)) !== false) { 
		$r[] = $data; 
		} 
		fclose($temp); 
		return $r; 
	}
} else define( "PHP_OLD", false);

class SalsaConnect {
	public $user = salsapress_salsa_username;
	protected $pass = salsapress_salsa_pass;
	public $url = salsapress_salsa_base_url;
	public $chapter = salsapress_salsa_chapter_filter;
	public $cache = false;
	public $result;

	var $urls = Array ( 
		'auth' => '/api/authenticate.sjs',
		'gets' => '/api/getObjects.sjs',
		'gets-nofilter' => '/api/getObjects.sjs',
		'get' => '/api/getObject.sjs',
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

	function __construct($cache = false) {
		if( !salsapress_active ) return false;
		$crypt = new SalsaCrypt( salsapress_salsa_pass  );
		$this->pass = $crypt->pass;
		$this->url = "https://".preg_replace(array('/http:\/\//','/https:\/\//'),array('',''),$this->url);;
		$this->ch = curl_init();
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->ch, CURLOPT_TIMEOUT, 100);
		curl_setopt($this->ch, CURLOPT_COOKIESESSION, true);
		curl_setopt($this->ch, CURLOPT_COOKIEFILE, '/tmp/cookies_file');
		curl_setopt($this->ch, CURLOPT_COOKIEJAR, '/tmp/cookies_file');

		if( !$cache ) {
			$auth = $this->post('auth', "email=".$this->user."&password=".$this->pass);
			$this->result = isset($auth->message) ? $auth->message : 'FAIL! :I';
		} else { 
			$this->cache = true;
		}

	}

	function status() {
		return $this->result;
	}
	function on() {
		return $this->result == "Successful Login";
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

		curl_setopt($this->ch, CURLOPT_POST, 1);		
		curl_setopt($this->ch, CURLOPT_URL, $this->url.$type);
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $params."&type=csv");
		$go = curl_exec($this->ch);
		$go = explode("\r\n",$go);

		$mapping = str_getcsv(array_shift($go),',','"','\\');
		if( constant("PHP_OLD") ) $mapping = $mapping[0];


		$parsed = array();
		foreach($go as $thing ){
			$temp = str_getcsv($thing,',','"','\\');
			$i = 0;
			$keyed = array();
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
		return $parsed;

	}

	function post($type, $params, $no_filter = false ) {
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
			$chapter = isset($this->chapter_fix[$type]) && !empty($this->chapter) && !$no_filter ? $this->chapter_fix[$type].$this->chapter : '';
			curl_setopt($this->ch, CURLOPT_POST, 1);
			curl_setopt($this->ch, CURLOPT_URL, $this->url.$this->urls[$type]);
			curl_setopt($this->ch, CURLOPT_POSTFIELDS, $params."&json".$chapter);

			$go = urlencode(curl_exec($this->ch));
		}

		// Knows to cache the results if they've expired or aren't there
		if( $save ) {
			set_transient( $params , $go , 60*60*12 );
		}

		return json_decode(urldecode($go));
	}

	function raw($place, $params) {
		curl_setopt($this->ch, CURLOPT_POST, 1);
		curl_setopt($this->ch, CURLOPT_URL, $this->url.$place);
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $params);

		return curl_exec($this->ch);
	}

	function rawjson($type, $params) {
		$chapter = isset($this->chapter_fix[$type]) && !empty($this->chapter) ? $this->chapter_fix[$type].$this->chapter : '';
		curl_setopt($this->ch, CURLOPT_POST, 1);
		curl_setopt($this->ch, CURLOPT_URL, $this->url.$this->urls[$type]);
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $params."&json".$chapter);
		

		$go = urlencode(curl_exec($this->ch));
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


class SalsaRender {
	private $type;
	
	function __construct($type){
		if( !isset($type) ) die('Something must have gotten lost in translation, this embed is no good. Try adding it again from Salsa');
		$this->type = $type;
	}
	
	function render($data) {
		switch ($this->type):
		    case 'report':
				$inputs = array();
				$headers = array();
				$columns = explode(',',substr($data['columns'],0,-1));
				sort($columns);
				$gaps = array();
				$headers['show'] = isset( $data['headers'] ) ? false : true;

				$type = isset( $data['list'] ) ? 'list' : 'table' ;

				foreach( $data as $k=>$v ):
					if( substr($k,0,1) == 'u' ) $inputs[] = $v;
					if( substr($k,0,4) == 'row_' ) $gaps[] = in_array( substr($k,4),$columns) ? $v : '';
					if( substr($k,0,7) == 'header_' && in_array(substr($k,7),$columns) ) $headers[] =  $v;
				endforeach;

				$report = new SalsaReport($data['key'],$inputs);
				return $report->render($type, $columns, $headers, $gaps );
				
				break;
			case 'signup_page' || 'event':
				$form = new SalsaForm($data);
				return $form->render();
				break;
			default:
				return 'Hmm can\'t render that. Something may have gone awry.';
		endswitch;
	}
}




class SalsaForm {
	public $form;

	public $obj;
	public $SalsaConnect;
	public $options;

	private $modes = array( 
		'signup_page' => array(
			"print_title" => "Title",
			'print_description'=>"Header",
			),
		'event' => array(
			"print_title" => "Event_Name",
			'print_description'=>'Description',
		),
	);


	function __construct($data) {
		$this->obj = $data['type'];
		$key = $data["salsa_key"];
		
		$this->options = $data;

		$this->SalsaConnect = new SalsaConnect(true);
		$myform = $this->SalsaConnect->post('get','object='.$data['type'].'&key='.$key);
		
		if( (!isset($myform->Request) || strlen($myform->Request) < 1 ) && $this->obj == 'event' ) {
			$myform->Request = "First_Name,Last_Name,Email,Phone";
			$myform->Required = "First_Name,Last_Name,Email,Phone";
		}
		$this->form = $myform;
	}


	public function render() {
		$options = get_option('salsapress_options');
		$chapter = isset($options['salsapress_salsa_chapter_base']) ? '/c/'.$options['salsapress_salsa_chapter_base'] : '';
		$fallback_url = $options['salsapress_salsa_base_url'].'/o/'.$options['salsapress_salsa_org_base'].$chapter;
		
		$inputs = explode(",",$this->form->Request);
		$required = explode(",",$this->form->Required);
		$diff_fields = array( 
			//'Phone' => '<input type="text" name="Phone" id="Phone" fillin="Phone"><br><label class="text_me"><em>A text\'s as good as an email</em></label><input type="checkbox" name="tag" id="tag" value="Can Text" checked>',
			'Zip' => '<input type="text" name="Zip" id="Zip" fillin="Zip" maxlength="5" size="6">',
			'State' => '<select id="state" name="State" ><option value="">Select a state</option>  <option value="AL">Alabama</option>  <option value="AK">Alaska</option><option value="AS">American Samoa</option><option value="AZ">Arizona</option><option value="AR">Arkansas</option><option value="CA">California</option><option value="CO">Colorado</option><option value="CT">Connecticut</option><option value="DE">Delaware</option><option value="DC">D.C.</option><option value="FL">Florida</option><option value="GA">Georgia</option><option value="GU">Guam</option><option value="HI">Hawaii</option><option value="ID">Idaho</option><option value="IL">Illinois</option><option value="IN">Indiana</option><option value="IA">Iowa</option><option value="KS">Kansas</option><option value="KY">Kentucky</option><option value="LA">Louisiana</option><option value="ME">Maine</option><option value="MD">Maryland</option><option value="MA">Massachusetts</option><option value="MI">Michigan</option><option value="MN">Minnesota</option><option value="MS">Mississippi</option><option value="MO">Missouri</option><option value="MT">Montana</option><option value="NE">Nebraska</option><option value="NV">Nevada</option><option value="NH">New Hampshire</option><option value="NJ">New Jersey</option><option value="NM">New Mexico</option><option value="NY">New York</option><option value="NC">North Carolina</option><option value="ND">North Dakota</option><option value="MP">Northern Mariana Islands</option><option value="OH">Ohio</option><option value="OK">Oklahoma</option><option value="OR" selected="">Oregon</option><option value="PA">Pennsylvania</option><option value="PR">Puerto Rico</option><option value="RI">Rhode Island</option><option value="SC">South Carolina</option><option value="SD">South Dakota</option><option value="TN">Tennessee</option><option value="TX">Texas</option><option value="UT">Utah</option><option value="VT">Vermont</option><option value="VI">Virgin Islands</option><option value="VA">Virginia</option><option value="WA">Washington</option><option value="WV">West Virginia</option><option value="WI">Wisconsin</option><option value="WY">Wyoming</option><option value="AA">Armed Forces (the) Americas</option><option value="AE">Armed Forces Europe</option><option value="AP">Armed Forces Pacific</option><option value="AB">Alberta</option><option value="BC">British Columbia</option><option value="MB">Manitoba</option><option value="NF">Newfoundland</option><option value="NB">New Brunswick</option><option value="NS">Nova Scotia</option><option value="NT">Northwest Territories</option><option value="NU">Nunavut</option><option value="ON">Ontario</option><option value="PE">Prince Edward Island</option><option value="QC">Quebec</option><option value="SK">Saskatchewan</option><option value="YT">Yukon Territory</option><option value="ot">Other</option></select>'
		);

		if( $this->obj == 'event' ) {
			$triggers = $this->SalsaConnect->post('gets','object=event_email_trigger&include=email_trigger_KEY&condition=event_KEY='.$this->form->key);
			$this->form->email_trigger_KEYS = '';
			foreach ( $triggers as $trigger ) {
				$this->form->email_trigger_KEYS .= $trigger->key.',';
			 }
			$fallback_url .= '/p/salsa/event/common/public/?event_KEY='.$this->form->event_KEY;
		} else {
			$fallback_url .= '/p/salsa/web/common/public/signup?signup_page_KEY='.$this->form->key;
		}

		$title = $this->modes[$this->obj]['print_title'];
		$title = "<h1>".$this->form->$title."</h1>";
		$description = $this->modes[$this->obj]['print_description'];
		$description = "<p>".$this->form->$description."</p>";
		$extra = '';
		$below = '';
		$form_return = '';


		if ( isset($this->options['event_compact']) ) {
			require_once('simple_html_dom.php');
			$html = str_get_html($description);
			$ftimage = $html->find('img',0) != null ? $html->find('img',0) : '';
			$description = better_excerpt($html->plaintext,500);
			$location_url = trim($this->form->Address.' '.$this->form->City.' '.$this->form->Zip.' '.$this->form->State);
			$location_name = empty($this->form->Location_Common_Name) ? trim($this->form->Address.' '.$this->form->City) : $this->form->Location_Common_Name;
			$location = empty($location_url) ? $location_name : $location_name.' (<a target="_blank" href="http://http://maps.google.com/maps?q='.$location_url.'" >Google Map It</a>)';
			$location = empty($location) ? '' : '<li><strong>Where:</strong> '.$location.'</li> ';
			$url = isset($this->options['event_url']) ? $this->options['event_url'] : 'http://'.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"].'#'.$this->form->event_KEY;
			$social = '<div id="social"><iframe src="http://www.facebook.com/plugins/like.php?app_id=194627797268503&amp;href='.$url.'&amp;send=false&amp;layout=standard&amp;width=54&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:54px; height:21px;" allowTransparency="true"></iframe>';
			$social .= '&nbsp;&nbsp;&nbsp;&nbsp;<a href="http://twitter.com/share" class="twitter-share-button" data-url="'.$url.'" data-text="Just signed up for '.$this->form->Event_Name.', you should too..." data-count="none" data-via="busproject" data-related="busproject:Follow us on Twitter, we\'re pretty hilarious\">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>';
			$social .= '&nbsp;&nbsp;&nbsp;&nbsp;<g:plusone size="medium" count="false" href="'.$url.'"></g:plusone><script type="text/javascript">(function() {var po = document.createElement(\'script\'); po.type = \'text/javascript\'; po.async = true;po.src = \'https://apis.google.com/js/plusone.js\';var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(po, s);})();</script>';
			$social .= '<br><em>Link: <input onClick="Javascript: jQuery(this).select()" readonly="readonly" type="text" value="'.$url.'"></em></div>';
			$gcal = ' (<a href="https://www.google.com/calendar/b/0/render?action=TEMPLATE&text='.$this->form->Event_Name.'&dates='.date('Ymd\This',strtotime($this->form->Start)).'/'.date('Ymd\This',strtotime	($this->form->End)).'&details='.better_excerpt($html->plaintext,500).'&trp=true&sprop=website:'.$url.'&sprop=name:'.$this->form->Location_Common_Name.'&location='.$location_url.'&pli=1&sf=true&output=xml" target="_blank" >Add to GCal</a>) ';
			$below = $social.'<ul id="deets">'.$location.'<li><strong>When:</strong> '.date_smoosh($this->form->Start,$this->form->End).$gcal.'</li></ul>';
			$form_return .= $title.'<div id="event_compact">'.$ftimage.$description.'</div>';
			$extra = '<h2>Sign Up</h2>';
		} else { 
			if ( isset($this->options['salsa_title']) ) $form_return .= $title;
			if ( isset($this->options['salsa_description']) ) $form_return .= $description;
		}
		if( $this->form->object != 'event' || $this->form->No_Registration != 'true' && $this->form->This_Event_Costs_Money != 'true'   ) {
			
			$form_return .= '<form class="salsa-form" ';
			$form_return .= 'action="http://'.$fallback_url.'" method="GET" target="_blank" ';
			$form_return .= ' >';
			$form_return .= $extra;
			$form_return .= '<input type="hidden" value="save" name="operation" id="operation">';
			$form_return .= '<input type="hidden" value="supporter" name="object" id="object">';
			$form_return .= '<input type="hidden" value="'.$this->form->organization_KEY.'" name="organization_KEY" id="organization_KEY">';
			$form_return .= '<input type="hidden" value="'.$this->form->chapter_KEY.'" name="chapter_KEY" id="chapter_KEY">';
			$form_return .= '<input type="hidden" value="'.$this->form->email_trigger_KEYS.'" name="email_trigger_KEYS" id="email_trigger_KEYS">';
			foreach ($inputs as $thing) {
				if( $thing != '0' ) {
					$form_return .= '<label for="'.$thing.'">'.str_replace('_',' ',$thing);
					if( in_array($thing,$required) ) $form_return .= ' <span class="required">*</span> ';
					$form_return .= "</label>";
					if( !isset($diff_fields[$thing]) ) $form_return .= '<input type="text" name="'.$thing.'" id="'.$thing.'" fillin="'.strtolower($thing).'">';
					else $form_return .= $diff_fields[$thing];
					$form_return .= "<br>";
				}
			}
			if( count($required) > 0 ) $form_return .= "<label class='required'><em>* Required</em></label>";

			// Setting up groups 
			if( isset($this->form->PreGroup_Text) ) $form_return .= '<p>'.$this->form->PreGroup_Text.'</p>';

			if( $this->form->Automatically_add_to_Groups_BOOLVALUE == 'false' && strlen($this->form->groups_KEYS) > 0 ) {
			//If groups are optional, grabbing the group names
				$group_pull = explode(",",$this->form->groups_KEYS);
				foreach ( $group_pull as  $thing) {
					$i = 0;
					if( strlen($thing) == 5 )  { 
						$group = $this->SalsaConnect->post('gets','object=groups&condition=groups_KEY='.$thing.'&include=Group_Name');
						$form_return .= '<label for="'.$group['0']->Group_Name.'">'.$group['0']->Group_Name.'</label>';
						$form_return .= '<input type="hidden" name="link" id="link" value="groups">';
						$form_return .= '<input type="checkbox" name="linkKey" id="linkKey" value="'.$group['0']->key.'"><br>';
						$i++;
					}
				}
			} else if ( $this->form->Automatically_add_to_Groups_BOOLVALUE == 'true' ) {

			// If groups are not optional, creating hidden links
				$group_pull = explode(",",$this->form->groups_KEYS);
				foreach ( $group_pull as  $thing) {
					if( strlen($thing) == 5 )  { 
						$form_return .= '<input type="hidden" name="link" id="link" value="groups">';
						$form_return .= '<input type="hidden" name="linkKey" id="linkKey" value="'.$thing.'">';
					}
				}
			}

			//Setting up Tags
			if( isset($this->form->PreInterest_Text) ) $form_return .= '<p>'.$this->form->PreInterest_Text.'</p>';
			if( isset($this->form->tag_KEYS) && strlen($this->form->tag_KEYS) > 0 ) {
				$tags_pull = explode(",",$this->form->tag_KEYS);
				foreach ( $tags_pull as  $thing) {
					$i = 0;
					if( strlen($thing) == 6 )  { 
						$tag = $this->SalsaConnect->post('gets','object=tag&condition=tag_KEY='.$thing.'&include=tag');
						$form_return .= '<label for="'.$tag['0']->tag.'">'.$tag['0']->tag.'</label>';
						$form_return .= '<input type="checkbox" name="tag" id="tag" value="'.$tag['0']->tag.'"><br>';
						$i++;
					}
				}
			}

			// Loads in event connecting data
			if( $this->obj == 'event' ) {
				$form_return .= '<input type="hidden" name="link" value="event">';
				$form_return .= '<input type="hidden" name="linkKey" value="'.$this->form->event_KEY.'">';
				$form_return .= '<input type="hidden" name="_Status" value="Signed Up">';
				$form_return .= '<input type="hidden" name="_Type" value="Supporter">';
				$form_return .= '<input type="hidden" name="event_KEY" value="'.$this->form->event_KEY.'">';
			} else {
				$form_return .= '<input type="hidden" name="signup_page_KEY" value="'.$this->form->key.'">';
			}

			$form_return .= '<input type="submit" id="salsa-submit" value="Sign Up!">';
			$form_return .= '</form>';
			
			if( isset($this->options['after_save']) ) $form_return .= '<div class="after_save" style="display: none;">'.rawurldecode($this->options['after_save']).'</div>';
			$form_return .= $below;
		} else {
			$url = 'https://'.salsapress_salsa_base_url.'/o/'.$this->form->organization_KEY;
			$url .= isset($this->form->chapter_KEY) ? '/c/'.$this->form->chapter_KEY : '';
			$form_return .= '<button onclick="location.href = \''.$url.'/p/salsa/event/common/public/?event_KEY='.$this->form->key.'#register\';" >Click here to sign up</button>';
		}
		if( $this->form->length != 0 ) return $form_return;
	} 
}


class SalsaReport {
	private $key;
	private $type;
	private $data = array();

	function __construct($key, $inputs) {
		$obj = new SalsaConnect(true);
		$this->data = $obj->reportsplit($key, $inputs);		
	}

	function render($type = 'table',$columns = array(),$headers = array(), $gaps=array() ) {

		$returned = '';

		switch( $type ):
			case 'list':
				$returned .=  '<ul class="salsa_table">';
				$start = '<li>';
				$end = '</li>';
				break;
			default:
				$returned .=  '<table class="salsa_table"><tbody>';
				$start = '<tr>';
				$end = '</tr>';
		endswitch;
		

		
		if( $headers['show'] ):
			
			unset($headers['show']);
			$returned .=  $start;
			$c = 0;
			foreach( $headers as $h):
				switch( $type ):
					case 'list':
						break;
					default:
						$returned .=  '<th>';
				endswitch;

				$returned .=  $h;

				switch( $type ):
					case 'list':
						if( isset($gaps[$c-1]) ) $returned .=  $gaps[$c-1];
						break;
					default:
						$returned .=  '</th>';
				endswitch;
				
				$c++;
			endforeach;
			$returned .=  $end;
		endif;

		foreach($this->data as $row):
			$returned .=  $start;
			$c = 0;
			foreach ($row as $col):
				if( in_array($c,$columns) ):
					switch( $type ):
						case 'list':
							break;
						default:
							$returned .=  '<td>';
					endswitch;

					$returned .=  $col;

					switch( $type ):
						case 'list':
							if( isset($gaps[$c-1]) ) $returned .=  $gaps[$c-1];
							break;
						default:
							$returned .=  '</td>';
					endswitch;
				endif;
				$c++;
			endforeach;
			$returned .=  $end;
		endforeach;



		switch( $type ):
			case 'list':
				$returned .=  '</ul>';
				break;
			default:
				$returned .=  '</tbody></table>';
				break;
		endswitch;
		return $returned;
	}

}
?>