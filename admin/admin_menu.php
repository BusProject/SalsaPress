<?php

// Adding the menu to the WordPress admin page
add_action('admin_menu', 'salsapress_menu');
add_action('admin_menu','salsapress_options_menu_init');

function salsapress_menu() {
	add_menu_page(
		'SalsaPress',
		'SalsaPress',
		'manage_options',
		'salsa',
		'salsapress_salsa_setup',
		SALSAPRESS_BASE . 'images/salsa-mono.png');
}

// BusPress options
function salsapress_options_menu_init(){
	register_setting('salsapress', 'salsapress_options','salsapress_validate_fix');
	add_settings_field('salsapress_salsa_activate', __('Connect with Salsa?','salsapress'), 'salsapress_salsa_activate', __FILE__, 'salsapress_salsa_credentials');
	add_settings_field('salsapress_salsa_username', __('Salsa Login (email)','salsapress'), 'salsapress_salsa_username', __FILE__, 'salsapress_salsa_credentials');
	add_settings_field('salsapress_salsa_pass', __('Salsa Password','salsapress'), 'salsapress_salsa_pass', __FILE__, 'salsapress_salsa_credentials');
	add_settings_field('salsapress_salsa_base_url', __('Salsa Base URL (salsa.democracyinaction.org, org2.democracyinaction.org, salsa.wiredforchange.com, etc)','salsapress'), 'salsapress_salsa_base_url', __FILE__, 'salsapress_salsa_credentials');\
	add_settings_section('salsapress_salsa_credentials', __('Salsa Credentials','salsapress'), 'salsapress_salsa_credentials', __FILE__);

	if( SALSAPRESS_ACTIVE ) {
		add_settings_field('salsapress_salsa_status', __('Salsa Status','salsapress'), 'salsapress_salsa_status', __FILE__, 'salsapress_salsa_credentials');

		add_settings_field('salsapress_salsa_chapter_filter', __('Chapter Filter (Only show data from a single chapter)','salsapress'), 'salsapress_salsa_chapter_filter', __FILE__, 'salsapress_salsa_filters');
		add_settings_field('salsapress_salsa_chapter_base', __('Base Chapter KEY','salsapress'), 'salsapress_salsa_chapter_base', __FILE__, 'salsapress_salsa_filters');
		add_settings_field('salsapress_salsa_org_base', __('Base Organization KEY','salsapress'), 'salsapress_salsa_org_base', __FILE__, 'salsapress_salsa_filters');
		add_settings_section('salsapress_salsa_filters', __('Salsa Settings','salsapress'), 'salsapress_salsa_filters', __FILE__);

		add_settings_field('salsapress_stop_cache', __('<strong>NEVER</strong> Cache SalsaPress','salsapress'), 'salsapress_cache', __FILE__, 'salsapress_cache_section');
		add_settings_field('salsapress_cache_reset', __('Reset Current Cache','salsapress'), 'salsapress_cache_reset', __FILE__, 'salsapress_cache_section');
		add_settings_section('salsapress_cache_section', __('SalsaPress Caching',"salsapress"), 'salsapress_cache_section', __FILE__);
	}


	wp_enqueue_script( 'SalsaPress', SALSAPRESS_BASE . 'admin/salsapress_admin.js', array( 'jquery' ), '0.5', true );
	wp_localize_script( 'SalsaPress', 'objectL10n', array(
		'hold_tight_ok' => __('Grabbing a preview, holdtightok?','salsapress'),
		'success' => __('Success!','salsapress')
	));
	wp_enqueue_style( 'SalsaPress', SALSAPRESS_BASE . 'admin/salsapress_admin.css', '', '0.5', 'all' );
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
define('SALSAPRESS_ACTIVE', $active);

if( $active ) {
	define( 'SALSAPRESS_SALSA_USERNAME', $salsapress['salsapress_salsa_username'] );
	define( 'SALSAPRESS_SALSA_PASS', $salsapress['salsapress_salsa_pass']  );

	if( strpos($salsapress['salsapress_salsa_base_url'], "http") === false ) $salsapress['salsapress_salsa_base_url'] = 'http://'.$salsapress['salsapress_salsa_base_url'];
	define( 'SALSAPRESS_SALSA_BASE_URL', $salsapress['salsapress_salsa_base_url']);

	$chapter_filter = isset( $salsapress['salsapress_salsa_chapter_filter']) ? $salsapress['salsapress_salsa_chapter_filter'] : '';
	define('SALSAPRESS_SALSA_CHAPTER_FILTER', $chapter_filter);
	$chapter_base = isset( $salsapress['salsapress_salsa_chapter_base']) ? $salsapress['salsapress_salsa_chapter_base'] : '';
	define('SALSAPRESS_SALSA_CHAPTER_BASE', $chapter_base);
	$org_base = isset( $salsapress['salsapress_salsa_org_base']) ? $salsapress['salsapress_salsa_org_base'] : '';
	define('SALSAPRESS_SALSA_ORG_BASE', $org_base);
	$cache = isset( $salsapress['salsapress_stop_cache']) ? false : true;
	define('SALSAPRESS_CACHE', $cache);
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
	echo '<p>'.__('Enter your Salsa Configuration, Salsa Widgets and Pages will not activate unless this is active','salsapress').'</p>';
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
	$pass = !isset($options['salsapress_salsa_activate']) ?  '' : SALSAPRESS_SALSA_PASS;
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
	echo '<h3 >'.__('Login Status','salsapress').': <span '.$color.'>'.__($obj->status(),'salsapress').'</span></h3>';
}

/** Filters Seciton **/

function  salsapress_salsa_filters() {
	echo '<p>'.__('Extra settings to further help configure Salsa','salsapress').'</p>';
}


function salsapress_salsa_chapter_filter() {
	$options = get_option('salsapress_options');
	$filter =  isset($options['salsapress_salsa_chapter_filter']) ? $options['salsapress_salsa_chapter_filter'] : '';
	if( isset($options['salsapress_salsa_activate']) ) {
		$obj = SalsaConnect::singleton();
		if( $obj->status() == "Successful Login" ):
			$chapters = $obj->post('gets-nofilter','object=chapter');
			echo "<select id='salsapress_salsa_chapter_filter' name='salsapress_options[salsapress_salsa_chapter_filter]' />";
				echo "<option value=''>".__('-- Show All Chapters','salsapress')."</option>";
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
			salsapress_reset_cache(); # Reset the cache
			$connect = $obj->post('gets','object=campaign_manager&include=chapter_KEY&include=organization_KEY&condition=Email='.$options['salsapress_salsa_username'] );
			$options['salsapress_salsa_chapter_base'] = $connect[0]->chapter_KEY;
			$options['salsapress_salsa_org_base'] = $connect[0]->organization_KEY;
			$crypt = new SalsaCrypt( SALSAPRESS_SALSA_PASS  );
			$options['salsapress_salsa_pass'] = $crypt->pass;
			update_option('salsapress_options',$options);
		}
	}
	?>
	<div class="wrap">
		<div class="icon32" style="background: transparent url(<?php echo SALSAPRESS_BASE . 'images/salsa-big.png'; ?>) no-repeat 0px 0px; height: 38px; " id="icon-options-general"><br></div>
		<h2><?php _e('Set Up Your Salsa Connection','salsapress'); ?></h2>
		<?php _e('Connect WordPress to and and add synchronized reports, events, and sign-up forms.','salsapress'); ?>
		<form autocomplete='off' action="options.php" method="post">
			<?php settings_fields('salsapress'); ?>

			<?php do_settings_sections(__FILE__); ?>

		<p class="submit">
			<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes','salsapress'); ?>" />
		</p>
		</form>

		<h2><?php _e('Add-on Calendar Plugin','salsapress'); ?></h2>
		<p><?php _e('Want your Salsa events in a calendar? We might be able to help. <a href="mailto:srduncombe@gmail.com">Contact Scott</a> about adding a salsa-powered-calendar.','salsapress') ?></p>
		<h3><a target="_blank" href="http://busproject.org/cal/"><?php _e('See it in action here','salsapress') ?></a></h3>

		<h2>About SalsaPress</h2>
		<p>Follow or contribute to the development of SalsaPress on <a href="https://github.com/BusProject/SalsaPress">Github</a>. It's mostly built and maintained by <a href="http://scottduncombe.com">Scott</a>.</p>

		<h3>The Bus Federation</h3>
		<p>Initial funding for the development of SalsaPress was provided by the <a href="http://busfederation.com/">Bus Federation</a>.</p>

		<h3>Kampaweb</h3>
		<p>Additional development has been funded by <a href="http://www.kampaweb.ch/">Kampaweb</a>.</p>

		<h2>Translations</h2>
		<p>SalsaPress now supports translations! Find the <code>.pot</code> file in the <code>lang</code> subfolder. Send <a href="mailto:srduncombe@gmail.com">me</a> your translated <code>.po</code> and <code>.mo</code> files - or submit a pull request on github - and I'll add them to the project.</p>

	</div>

	<?php
}


?>