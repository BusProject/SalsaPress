<?php

// Used to genrate a salas form, buth for events and sign up forms

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

		$this->SalsaConnect = SalsaConnect::singleton(true);
		$myform = $this->SalsaConnect->post('get','object='.$data['type'].'&key='.$key);

		if( (!isset($myform->Request) || strlen($myform->Request) < 1 ) && $this->obj == 'event' ) {
			$myform->Request = "First_Name,Last_Name,Email,Phone";
			$myform->Required = "First_Name,Last_Name,Email,Phone";
		}
		$this->form = $myform;
	}


	public function render() {
		// Checking and making sure the query was successful
		if( !isset($this->form->key) ) { return '<!-- Invalid Salsa Query-->'; }

		$options = get_option('salsapress_options');
		$chapter = isset($options['salsapress_salsa_chapter_base']) && strlen($options['salsapress_salsa_chapter_base']) > 1 ? '/c/'.$options['salsapress_salsa_chapter_base'] : '';
		$fallback_url = $options['salsapress_salsa_base_url'].'/o/'.$options['salsapress_salsa_org_base'].$chapter;
		
		$inputs = explode(",",$this->form->Request);
		$required = explode(",",$this->form->Required);
		$diff_fields = array( 
			//'Phone' => '<input type="text" name="Phone" id="Phone" fillin="Phone"><br><label class="text_me"><em>A text\'s as good as an email</em></label><input type="checkbox" name="tag" id="tag" value="Can Text" checked>',
			'Zip' => '<input type="text" name="Zip" id="Zip" fillin="Zip" maxlength="5" size="6">',
			'State' => '<select id="state" name="State" ><option value="">Select a state</option>  <option value="AL">Alabama</option>  <option value="AK">Alaska</option><option value="AS">American Samoa</option><option value="AZ">Arizona</option><option value="AR">Arkansas</option><option value="CA">California</option><option value="CO">Colorado</option><option value="CT">Connecticut</option><option value="DE">Delaware</option><option value="DC">D.C.</option><option value="FL">Florida</option><option value="GA">Georgia</option><option value="GU">Guam</option><option value="HI">Hawaii</option><option value="ID">Idaho</option><option value="IL">Illinois</option><option value="IN">Indiana</option><option value="IA">Iowa</option><option value="KS">Kansas</option><option value="KY">Kentucky</option><option value="LA">Louisiana</option><option value="ME">Maine</option><option value="MD">Maryland</option><option value="MA">Massachusetts</option><option value="MI">Michigan</option><option value="MN">Minnesota</option><option value="MS">Mississippi</option><option value="MO">Missouri</option><option value="MT">Montana</option><option value="NE">Nebraska</option><option value="NV">Nevada</option><option value="NH">New Hampshire</option><option value="NJ">New Jersey</option><option value="NM">New Mexico</option><option value="NY">New York</option><option value="NC">North Carolina</option><option value="ND">North Dakota</option><option value="MP">Northern Mariana Islands</option><option value="OH">Ohio</option><option value="OK">Oklahoma</option><option value="OR" >Oregon</option><option value="PA">Pennsylvania</option><option value="PR">Puerto Rico</option><option value="RI">Rhode Island</option><option value="SC">South Carolina</option><option value="SD">South Dakota</option><option value="TN">Tennessee</option><option value="TX">Texas</option><option value="UT">Utah</option><option value="VT">Vermont</option><option value="VI">Virgin Islands</option><option value="VA">Virginia</option><option value="WA">Washington</option><option value="WV">West Virginia</option><option value="WI">Wisconsin</option><option value="WY">Wyoming</option><option value="AA">Armed Forces (the) Americas</option><option value="AE">Armed Forces Europe</option><option value="AP">Armed Forces Pacific</option><option value="AB">Alberta</option><option value="BC">British Columbia</option><option value="MB">Manitoba</option><option value="NF">Newfoundland</option><option value="NB">New Brunswick</option><option value="NS">Nova Scotia</option><option value="NT">Northwest Territories</option><option value="NU">Nunavut</option><option value="ON">Ontario</option><option value="PE">Prince Edward Island</option><option value="QC">Quebec</option><option value="SK">Saskatchewan</option><option value="YT">Yukon Territory</option><option value="ot">Other</option></select>'
		);
		$diff_labels = array(
			'sign_up_page_comments' => 'How can you get involved?'
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

			$this->form->Start = fixDate($this->form->Start);
			$this->form->End = fixDate($this->form->End);

			$html = str_get_html($description);
			$ftimage = $html->find('img',0) != null ? $html->find('img',0) : '';
			$description = better_excerpt($html->plaintext,500);
			$location_url = trim($this->form->Address.' '.$this->form->City.' '.$this->form->Zip.' '.$this->form->State);
			$location_name = empty($this->form->Location_Common_Name) ? trim($this->form->Address.' '.$this->form->City) : $this->form->Location_Common_Name;
			$location = empty($location_url) ? $location_name : $location_name.' (<a target="_blank" href="http://http://maps.google.com/maps?q='.$location_url.'" >Google Map It</a>)';
			$location = empty($location) ? '' : '<li><strong>Where:</strong> '.$location.'</li> ';

// used for calendar			$url = isset($this->options['event_url']) ? $this->options['event_url'] : 'http://'.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"].'#'.$this->form->event_KEY;
			if( empty($obj->chapter_filter ) ) {
				$chapter_link = salsapress_salsa_chapter_base;
			} else {
				$chapter_link = $obj->chapter_filter;
			}
			$chapter_link = salsapress_salsa_chapter_base == '' ? '' : '/c/'.$chapter_link;

			$url = 'https://'.salsapress_salsa_base_url.'/o/'.salsapress_salsa_org_base.$chapter_link.'/p/salsa/event/common/public/?event_KEY='.$this->form->event_KEY;
			$social = '<div class="social"><iframe src="http://www.facebook.com/plugins/like.php?app_id=194627797268503&amp;href='.$url.'&amp;send=false&amp;layout=standard&amp;width=54&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font&amp;height=21;" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:54px; height:25px;margin-bottom:-4px;" allowTransparency="true"></iframe>';
			$social .= '&nbsp;&nbsp;&nbsp;&nbsp;<a href="http://twitter.com/share" class="twitter-share-button" data-url="'.$url.'" data-text="Just signed up for '.$this->form->Event_Name.', you should too..." data-count="none" data-via="busproject" data-related="busproject:Follow us on Twitter, we\'re pretty hilarious\">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>';
			$social .= '&nbsp;&nbsp;&nbsp;&nbsp;<g:plusone size="medium" count="false" href="'.$url.'"></g:plusone><script type="text/javascript">(function() {var po = document.createElement(\'script\'); po.type = \'text/javascript\'; po.async = true;po.src = \'https://apis.google.com/js/plusone.js\';var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(po, s);})();</script>';
			$social .= '<br><em>Link: <input onClick="Javascript: jQuery(this).select()" readonly="readonly" type="text" value="'.$url.'"></em></div>';
			$gcal = ' (<a href="https://www.google.com/calendar/b/0/render?action=TEMPLATE&text='.$this->form->Event_Name.'&dates='.date('Ymd\THis',strtotime($this->form->Start)).'/'.date('Ymd\THis',strtotime	($this->form->End)).'&details='.better_excerpt($html->plaintext,500).'&trp=true&sprop=website:'.$url.'&sprop=name:'.$this->form->Location_Common_Name.'&location='.$location_url.'&pli=1&sf=true&output=xml" target="_blank" >Add to GCal</a>) ';
			$below = $social.'<ul class="deets">'.$location.'<li><strong>When:</strong> '.date_smoosh($this->form->Start,$this->form->End).$gcal.'</li></ul>';
			$form_return .= $title.'<div class="event_compact"><div class="description">'.$ftimage.$description.'</div>';
			
			$extra = '<h2>Sign Up</h2>';
			$end = '</div>';
		} else { 
			if ( isset($this->options['salsa_title']) && $this->options['salsa_title']) $form_return .= $title;
			if ( isset($this->options['salsa_description']) && $this->options['salsa_description'] ) $form_return .= $description;
		}
		if( $this->form->object != 'event' || $this->form->No_Registration != 'true' && $this->form->This_Event_Costs_Money != 'true'   ) {
			
			$form_return .= '<form class="salsa-form" ';
			$form_return .= 'action="http://'.$fallback_url.'" method="GET" target="_blank" ';
			if( isset($this->form->redirect_path) ) $form_return .= 'redirect_path="'.$this->form->redirect_path.'"';
			$form_return .= ' >';
			$form_return .= $extra;
			$form_return .= '<input type="hidden" value="save" name="operation" id="operation">';
			$form_return .= '<input type="hidden" value="supporter" name="object" id="object">';
			$form_return .= '<input type="hidden" value="Web" name="Source" >';
			if( isset($this->form->Default_Tracking_Code) ) $form_return .= '<input type="hidden" value="'.$this->form->Default_Tracking_Code.'" name="Source_Tracking_Code" >';
			else $form_return .= '<input type="hidden" value="(Added via SalsaPress form - no tracking code set)" name="Source_Tracking_Code" >';
			$form_return .= '<input type="hidden" value="'.( strpos($_SERVER['SERVER_PROTOCOL'],'HTTP') === false ? 'https://' : 'http://' ).$_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] .'" name="Source_Details" >';
			$form_return .= '<input type="hidden" value="'.$this->form->organization_KEY.'" name="organization_KEY" id="organization_KEY">';
			$form_return .= '<input type="hidden" value="'.$this->form->chapter_KEY.'" name="chapter_KEY" id="chapter_KEY">';
			$form_return .= '<input type="hidden" value="'.$this->form->email_trigger_KEYS.'" name="email_trigger_KEYS" id="email_trigger_KEYS">';
			foreach ($inputs as $thing) {
				if(  $thing != '0' && $thing != '__v2__'  && !empty($thing) ) {
					if( !isset($diff_labels[$thing]) ) {
						if( $thing[0] != strtolower($thing[0]) )  { // If a custom field - use Custom Fields to display label
							$form_return .= '<label for="'.$thing.'">'.str_replace('_',' ',$thing);
							if( in_array($thing,$required) ) $form_return .= ' <span class="required">*</span> ';
							$form_return .= "</label>";
						}
					} else {
						$form_return .= '<label for="'.$thing.'">'.$diff_labels[$thing]."</label>";
					}
					if( !isset($diff_fields[$thing]) ) {
						if( $thing[0] == strtolower($thing[0]) ) { // Detects if is a custom field
							require_once('simple_html_dom.php');
							if( !isset($form_html) ) $form_html = file_get_html('http://'.$fallback_url);
							$el = $form_html->find('textarea[name='.$thing.'], input[name='.$thing.'], select[name='.$thing.'],
								textarea[name='.$thing.strtoupper($thing).'], input[name='.$thing.strtoupper($thing).'], select[name='.$thing.strtoupper($thing).']'); // Added to accomodate weirdness I was seeing with TIME
							if( isset($el[0]) ) { // Can find
								$parent = $el[0]->parent();
								while( $parent->class != 'formRow' ) { // Makes sure selecting the .form row from the div - nothing earlier
									$parent = $parent->parent();
								}
								$html = $parent->innertext;
								// Capitalizing first letter of label
								$label = str_replace('_',' ',$thing);
								$uplabel = strtoupper($label[0]).substr($label,1);
								$html = str_replace('>'.$label,'>'.$uplabel,$html);
								// Adding a small space next to "required" * span
								$html = str_replace("<span class='required"," <span class='required",$html);
								$form_return .= $html;
							} else { // Couldn't find the custom field in the HTML - falling back to normal form
								$form_return .= '<!-- could not parse '.str_replace('_',' ',$thing).'-->';
								$form_return .= '<label for="'.$thing.'">'.str_replace('_',' ',$thing);
								if( in_array($thing,$required) ) $form_return .= ' <span class="required">*</span> ';
								$form_return .= "</label>";
								$form_return .= '<input type="text" name="'.$thing.'" id="'.$thing.'" fillin="'.strtolower($thing).'">';
							}
						} else $form_return .= '<input type="text" name="'.$thing.'" id="'.$thing.'" fillin="'.strtolower($thing).'">'; // Just a normal field - displaying normally
					} else $form_return .= $diff_fields[$thing];
					$form_return .= "<br>";
				}
			}
			if( count($required) > 0 ) $form_return .= "<label class='required'><em>* Required</em></label>";

			// Setting up groups 
			if( isset($this->form->PreGroup_Text) ) $form_return .= '<p>'.$this->form->PreGroup_Text.'</p>';

			if( isset($this->form->optionally_add_to_groups_KEYS) ) $optional_groups = $this->form->optionally_add_to_groups_KEYS;
			if( isset($this->form->groups_KEYS) ) $optional_groups = $this->form->groups_KEYS;
			
			if( isset($optional_groups) && strlen($optional_groups) > 0  ) {
			//If groups are optional, grabbing the group names
				$group_pull = explode(",",$optional_groups);
				foreach ( $group_pull as  $thing) {
					$i = 0;
					if( strlen($thing) > 2 )  { 
						$group = $this->SalsaConnect->post('gets','object=groups&condition=groups_KEY='.$thing.'&include=Group_Name');
						$form_return .= '<label for="'.$group['0']->Group_Name.'">'.$group['0']->Group_Name.'</label>';
						$form_return .= '<input type="hidden" name="groups_KEY'.$group['0']->key.'" id="link" value="true">';
						$form_return .= '<input type="checkbox" name="groups_KEY'.$group['0']->key.'_checkbox" ><br>';
						$i++;
					}
				}
			} 

			if( isset($this->form->{'required$groups_KEYS'}) ) $required_groups = $this->form->{'required$groups_KEYS'};
			if( isset($this->form->add_to_groups_KEYS) ) $required_groups = $this->form->add_to_groups_KEYS;
			
			if( isset($required_groups) && strlen($required_groups) > 0  ) {
			// If groups are not optional, creating hidden links
				$group_pull = explode(",",$required_groups);
				foreach ( $group_pull as  $thing) {
					if( strlen($thing) > 2 )  { 
						$form_return .= '<input type="hidden" name="groups_KEY'.$thing.'" id="link" value="true">';
						$form_return .= '<input type="hidden" name="groups_KEY'.$thing.'_checkbox" value="on"><br>';
					}
				}
			}

			//Setting up Tags
			if( isset($this->form->PreInterest_Text) ) $form_return .= '<p>'.$this->form->PreInterest_Text.'</p>';
			if( isset($this->form->tag_KEYS) && strlen($this->form->tag_KEYS) > 0 ) {
				$tags_pull = explode(",",$this->form->tag_KEYS);
				foreach ( $tags_pull as  $thing) {
					$i = 0;
					if( strlen($thing) > 4 )  { 
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

			if( isset($this->options['after-save']) ) $form_return .= '<div class="after_save" style="display: none;">'.rawurldecode($this->options['after-save']).'</div>';
			$form_return .= $below;
		} else {
			$url = 'https://'.salsapress_salsa_base_url.'/o/'.$this->form->organization_KEY;
			$url .= isset($this->form->chapter_KEY) && strlen($this->form->chapter_KEY) > 1 ? '/c/'.$this->form->chapter_KEY : '';
			$form_return .= '<button onclick="location.href = \''.$url.'/p/salsa/event/common/public/?event_KEY='.$this->form->key.'#register\';" >Click here to sign up</button>';
		}
		$form_return .= isset($end) ? $end : '';
		return $form_return;
	} 
}


?>