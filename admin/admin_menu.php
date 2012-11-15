<?php

// Adding the menu to the WordPress admin page
add_action('admin_menu', 'salsapress_menu');
add_action('admin_menu','salsapress_options_menu_init');

function salsapress_menu() {
	add_menu_page(
		'SalsaPress',
		'Connect to Salsa',
		'manage_options',
		'salsa',
		'salsapress_salsa_setup',
		base.'images/salsa.png');
}

// BusPress options
function salsapress_options_menu_init(){
	register_setting('salsapress', 'salsapress_options','salsapress_validate_fix');
	add_settings_field('salsapress_salsa_activate', 'Connect with Salsa?', 'salsapress_salsa_activate', __FILE__, 'salsapress_salsa_credentials');
	add_settings_field('salsapress_salsa_username', 'Salsa Login (email)', 'salsapress_salsa_username', __FILE__, 'salsapress_salsa_credentials');
	add_settings_field('salsapress_salsa_pass', 'Salsa Password', 'salsapress_salsa_pass', __FILE__, 'salsapress_salsa_credentials');
	add_settings_field('salsapress_salsa_base_url', 'Salsa Base URL (salsa.democracyinaction.org, org2.democracyinaction.org, salsa.wiredforchange.com, etc) ', 'salsapress_salsa_base_url', __FILE__, 'salsapress_salsa_credentials');\
	add_settings_section('salsapress_salsa_credentials', 'Salsa Credentials', 'salsapress_salsa_credentials', __FILE__);

	if( salsapress_active ) {
		add_settings_field('salsapress_salsa_status', 'Salsa Status ', 'salsapress_salsa_status', __FILE__, 'salsapress_salsa_credentials');

		add_settings_field('salsapress_salsa_chapter_filter', 'Chapter Filter (Only show data from a single chapter)', 'salsapress_salsa_chapter_filter', __FILE__, 'salsapress_salsa_filters');
		add_settings_field('salsapress_salsa_chapter_base', 'Base Chapter KEY', 'salsapress_salsa_chapter_base', __FILE__, 'salsapress_salsa_filters');
		add_settings_field('salsapress_salsa_org_base', 'Base Organization KEY', 'salsapress_salsa_org_base', __FILE__, 'salsapress_salsa_filters');
		add_settings_section('salsapress_salsa_filters', 'Salsa Settings', 'salsapress_salsa_filters', __FILE__);
		

		add_settings_field('salsapress_stop_cache', '<strong>NEVER</strong> Cache SalsaPress', 'salsapress_cache', __FILE__, 'salsapress_cache_section');
		add_settings_field('salsapress_cache_reset', 'Reset Current Cache', 'salsapress_cache_reset', __FILE__, 'salsapress_cache_section');
		add_settings_section('salsapress_cache_section', 'SalsaPress Caching', 'salsapress_cache_section', __FILE__);
		
	}


	wp_enqueue_script( 'SalsaPress', base.'admin/salsapress_admin.js',array( 'jquery' ), '0.5', true );
	wp_enqueue_style( 'SalsaPress', base.'admin/salsapress_admin.css','', '0.5', 'all' );
	localize_scripts();
}

// BusPress defaults
function salsapress_defaults() {
	$tmp = get_option('salsapress_options');
    if(( empty($tmp['salsapress_salsa_activate']) )||(!is_array($tmp))) {
		$arr = array(
			"salsapress_salsa_username" => "",
			"salsapress_salsa_pass" => "", 
			"salsapress_salsa_base_url" => "",
			'salsapress_salsa_org_base' => '',
			'salsapress_salsa_chapter_base' => ''
		);
		update_option('salsapress_options', $arr);
	}
}

// Setting up some constants from the BusPress options
$salsapress = get_option('salsapress_options');
$active = isset($salsapress['salsapress_salsa_activate']) && $salsapress['salsapress_salsa_activate'];
define('salsapress_active', $active);

if( $active ) {
	define( 'salsapress_salsa_username', $salsapress['salsapress_salsa_username'] );
	define( 'salsapress_salsa_pass', $salsapress['salsapress_salsa_pass']  );
	define( 'salsapress_salsa_base_url', $salsapress['salsapress_salsa_base_url']);
	$chapter_filter = isset( $salsapress['salsapress_salsa_chapter_filter']) ? $salsapress['salsapress_salsa_chapter_filter'] : '';
	define('salsapress_salsa_chapter_filter', $chapter_filter);
	$chapter_base = isset( $salsapress['salsapress_salsa_chapter_base']) ? $salsapress['salsapress_salsa_chapter_base'] : '';
	define('salsapress_salsa_chapter_base', $chapter_base);
	$org_base = isset( $salsapress['salsapress_salsa_org_base']) ? $salsapress['salsapress_salsa_org_base'] : '';
	define('salsapress_salsa_org_base', $org_base);
	$cache = isset( $salsapress['salsapress_stop_cache']) ? false : true;
	define('salsapress_cache', $cache);
}


//BusPress settings validator / fixer 
function salsapress_validate_fix($input) {
	if( isset($input['salsapress_salsa_pass']) ) {
		$crpt = new SalsaCrypt;
		$input['salsapress_salsa_pass'] = $crpt->store($input['salsapress_salsa_pass']);
	}
	if( isset($input['salsapress_stop_cache']) ) {
		salsapress_reset_cache();
	}
	return $input;
}

// Specific functions for each option
function  salsapress_salsa_credentials() {
	echo '<p>Enter your Salsa Configuration, Salsa Widgets and Pages will not activate unless this is active</p>';
}

function salsapress_salsa_activate() {
	$options = get_option('salsapress_options');
	$checked = isset($options['salsapress_salsa_activate']) ? ' checked="checked" ' : '';
	echo "<input ".$checked." id='salsapress_salsa_activate' name='salsapress_options[salsapress_salsa_activate]' type='checkbox' />";
}


function salsapress_salsa_username() {
	$options = get_option('salsapress_options');
	$readonly = !isset($options['salsapress_salsa_activate']) ? ' readonly="true" ' : '';
	echo "<input ".$readonly." id='salsapress_salsa_username' name='salsapress_options[salsapress_salsa_username]' size='40' type='text' value='{$options['salsapress_salsa_username']}' />";
}

function salsapress_salsa_pass() {
	$options = get_option('salsapress_options');
	$readonly = !isset($options['salsapress_salsa_activate']) ? ' readonly="true" ' : '';
	$pass = !isset($options['salsapress_salsa_activate']) ?  '' : salsapress_salsa_pass;
	$crypt = new SalsaCrypt(  $pass );
	$pass = $crypt->pass;
	echo "<input ".$readonly." id='salsapress_salsa_pass' name='salsapress_options[salsapress_salsa_pass]' size='40' type='password' value='{$pass}' />";
}


function salsapress_salsa_base_url() {
	$options = get_option('salsapress_options');
	$readonly = !isset($options['salsapress_salsa_activate']) ? ' readonly="true" ' : '';
	echo "<input ".$readonly." id='salsapress_salsa_base_url' name='salsapress_options[salsapress_salsa_base_url]' size='40' type='text' value='{$options['salsapress_salsa_base_url']}' />";
}
function salsapress_salsa_status() {
	$obj = SalsaConnect::singleton();
	$color = $obj->status() == "Successful Login" ? $color = 'style="color: green;"' : 'style="color:red;"';
	echo '<h3 >Login Status: <span '.$color.'>'.$obj->status().'</span></h3>';
}

/** Filters Seciton **/

function  salsapress_salsa_filters() {
	echo '<p>Extra settings to further help configure Salsa</p>';
}


function salsapress_salsa_chapter_filter() {
	$options = get_option('salsapress_options');
	$filter =  isset($options['salsapress_salsa_chapter_filter']) ? $options['salsapress_salsa_chapter_filter'] : '';
	if( isset($options['salsapress_salsa_activate']) ) { 
		$obj = SalsaConnect::singleton();
		if( $obj->status() == "Successful Login" ):
			$chapters = $obj->post('gets-nofilter','object=chapter');
			echo "<select id='salsapress_salsa_chapter_filter' name='salsapress_options[salsapress_salsa_chapter_filter]' />";
				echo "<option value=''>-- Show All Chapters</option>";
			foreach( $chapters as $chapter):
				$selected = $filter == $chapter->chapter_KEY ? 'selected="selected"' : '';
				echo '<option '.$selected.' value="'.$chapter->chapter_KEY.'">'.$chapter->Name.'</option>';
			endforeach;
			echo "</select>";
		endif;
	}
}


function salsapress_salsa_chapter_base() {
	$options = get_option('salsapress_options');
	if( isset($options['salsapress_salsa_chapter_base']) && !empty($options['salsapress_salsa_chapter_base']) ) {
		echo '<h2>#'.$options['salsapress_salsa_chapter_base'].'</h2>';
	}
	else echo '---';
}

function salsapress_salsa_org_base() {
	$options = get_option('salsapress_options');
	if( isset($options['salsapress_salsa_org_base']) && !empty($options['salsapress_salsa_org_base']) ) {
		echo '<h2>#'.$options['salsapress_salsa_org_base'].'</h2>';
	}
	else echo '---';
}


// Function for render the adming page
function salsapress_salsa_setup() {
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	$options = get_option('salsapress_options');
	if( isset($options['salsapress_salsa_activate']) && $options['salsapress_salsa_activate'] ) {
		$obj = SalsaConnect::singleton();
		if( $obj->on() && empty($options['salsapress_salsa_org_base']) ) {
			$connect = $obj->post('gets','object=campaign_manager&include=chapter_KEY&include=organization_KEY&condition=Email='.$options['salsapress_salsa_username'] );
			$options['salsapress_salsa_chapter_base'] = $connect[0]->chapter_KEY; 
			$options['salsapress_salsa_org_base'] = $connect[0]->organization_KEY;
			$crypt = new SalsaCrypt( salsapress_salsa_pass  );
			$options['salsapress_salsa_pass'] = $crypt->pass;
			update_option('salsapress_options',$options);
		}
	} 
	?>
	<div class="wrap">
		<div class="icon32" style="background: transparent url(<?php echo base.'images/salsa-big.png'; ?>) no-repeat 0px 0px; height: 38px; " id="icon-options-general"><br></div>
		<h2>Set Up Your Salsa Connection</h2>
		Connect WordPress to <a href="http://salsalabs.com" target="_blank">Salsa</a> and and add synchronized reports, events, and sign-up forms.
		<form autocomplete='off' action="options.php" method="post">
			<?php settings_fields('salsapress'); ?>

			<?php do_settings_sections(__FILE__); ?>

		<p class="submit">
			<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
		</p>
		</form>
		
		<h2>About SalsaPress / The Bus Federation</h2>
		<p>SalsaPress is provided - for free - by the <a href="http://busfederation.com/">Bus Federation</a> and built by <a href="http://twitter.com/mojowen">Scott</a>. It is <a href="https://github.com/BusProject/SalsaPress">totally open source</a>.</p>
		<p>We get young people involved in Democracy through field organizing and leadership development across the country<br />- and are increasingly building cool shit (we also built <a href="http://theballot.org">TheBallot.org</a> along with the League of Young Voters).</p>
		<h3>Want to keep up on SalsaPress? Sign up for news</h3>
		<form name="data" action="http://salsa.wiredforchange.com/save" method="POST" target="_blank" style="width: 300px; text-align: right">
			<input type="hidden" name="chapter_KEY" value="9262">
			<input type="hidden" name="redirect" value="https://github.com/BusProject/SalsaPress">
			<input type="hidden" name="table" value="supporter">
			<input type="hidden" name="key" value="0">
			<label for="f1">First Name</label>
			<input title="First Name" type="text" id="f1" name="First_Name" class="blockInput salsa_first_name" value="" size="25"><br />
			<label for="f2">Last Name</label>
			<input title="Last Name" type="text" id="f2" name="Last_Name" class="blockInput salsa_last_name" value="" size="25"><br />
			<label for="f3">Email</label>
			<input title="Email" type="text" id="f3" name="Email" class="blockInput salsa_email" value="" size="25"><br />
			<input type="hidden" name="required" value="Email,">
			<input type="hidden" name="organization_KEY" value="6121">
			<input type="hidden" name="Receive_Email" value="3">
			<input type="hidden" name="email_trigger_KEYS" value="13012">
			<input type="hidden" name="link" value="groups">
			<input type="hidden" name="linkKey" value="63716">
			<input type="hidden" name="tag" id="tag" value="signup:page6502">
			<input type="submit" value="Sign Up">
		</form>
	</div>

	<?php
}


?>