<?php

// Adding the menu to the WordPress admin page
add_action('admin_menu', 'buspress_menu');
add_action('admin_menu','buspress_options_menu_init');

function buspress_menu() {
	add_menu_page(
		'BusPress',
		'Connect to Salsa',
		'manage_options',
		'salsa',
		'buspress_salsa_setup',
		base.'/images/salsa.png');
}

// BusPress options
function buspress_options_menu_init(){
	register_setting('buspress', 'buspress_options','buspress_validate_fix');
	add_settings_field('buspress_salsa_activate', 'Connect with Salsa?', 'buspress_salsa_activate', __FILE__, 'buspress_salsa_credentials');
	add_settings_field('buspress_salsa_username', 'Salsa Login (email)', 'buspress_salsa_username', __FILE__, 'buspress_salsa_credentials');
	add_settings_field('buspress_salsa_pass', 'Salsa Password', 'buspress_salsa_pass', __FILE__, 'buspress_salsa_credentials');
	add_settings_field('buspress_salsa_base_url', 'Salsa Base URL (salsa.democracyinaction.org, org2.democracyinaction.org, salsa.wiredforchange.com, etc) ', 'buspress_salsa_base_url', __FILE__, 'buspress_salsa_credentials');
	add_settings_field('buspress_salsa_chapter_filter', 'Chapter Filter (Only show data from a single chapter)', 'buspress_salsa_chapter_filter', __FILE__, 'buspress_salsa_credentials');
	add_settings_field('buspress_salsa_chapter_base', 'Base Chapter KEY', 'buspress_salsa_chapter_base', __FILE__, 'buspress_salsa_credentials');
	add_settings_field('buspress_salsa_org_base', 'Base Organization KEY', 'buspress_salsa_org_base', __FILE__, 'buspress_salsa_credentials');
	add_settings_section('buspress_salsa_credentials', 'Salsa Credentials', 'buspress_salsa_credentials', __FILE__);
	wp_enqueue_script( 'buspress_admin_script', base.'admin/buspress_admin.js',array( 'jquery' ), '0.5', true );
	wp_enqueue_style( 'jolokia', base.'admin/buspress_admin.css','', '0.5', 'all' );	
}

// BusPress defaults
function buspress_defaults() {
	$tmp = get_option('buspress_options');
    if(( empty($tmp['buspress_salsa_activate']) )||(!is_array($tmp))) {
		$arr = array(
			"buspress_salsa_username" => "",
			"buspress_salsa_pass" => "", 
			"buspress_salsa_base_url" => "",
			"buspress_salsa_activate" => false,
			'buspress_salsa_org_base' => '',
			'buspress_salsa_chapter_base' => ''
		);
		update_option('buspress_options', $arr);
	}
}

// Setting up some constants from the BusPress options
$buspress = get_option('buspress_options');
if( isset($buspress['buspress_salsa_activate']) && $buspress['buspress_salsa_activate'] ) {
	define( 'buspress_salsa_username', $buspress['buspress_salsa_username'] );

	// Defining password as decrypted
	define( 'buspress_salsa_pass', $buspress['buspress_salsa_pass']  );

	define( 'buspress_salsa_base_url', $buspress['buspress_salsa_base_url']);
	$chapter_filter = isset( $buspress['buspress_salsa_chapter_filter']) ? $buspress['buspress_salsa_chapter_filter'] : '';
	define('buspress_salsa_chapter_filter', $chapter_filter);
	$chapter_base = isset( $buspress['buspress_salsa_chapter_base']) ? $buspress['buspress_salsa_chapter_base'] : '';
	define('buspress_salsa_chapter_base', $chapter_base);
	$org_base = isset( $buspress['buspress_salsa_org_base']) ? $buspress['buspress_salsa_org_base'] : '';
	define('buspress_salsa_org_base', $org_base);
}


//BusPress settings validator / fixer 
function buspress_validate_fix($input) {
	if( isset($input['buspress_salsa_pass']) ) {
		$crpt = new SalsaCrypt;
		$input['buspress_salsa_pass'] = $crpt->store($input['buspress_salsa_pass']);
	}
	return $input;
}

// Specific functions for each option
function  buspress_salsa_credentials() {
	echo '<p>Enter your Salsa Configuration, Salsa Widgets and Pages will not activate unless this is active</p>';
}

function buspress_salsa_activate() {
	$options = get_option('buspress_options');
	$checked = $options['buspress_salsa_activate'] ? ' checked="checked" ' : '';
	echo "<input ".$checked." id='buspress_salsa_activate' name='buspress_options[buspress_salsa_activate]' type='checkbox' />";
}


function buspress_salsa_username() {
	$options = get_option('buspress_options');
	$readonly = !$options['buspress_salsa_activate'] ? ' readonly="true" ' : '';
	echo "<input ".$readonly." id='buspress_salsa_username' name='buspress_options[buspress_salsa_username]' size='40' type='text' value='{$options['buspress_salsa_username']}' />";
}

function buspress_salsa_pass() {
	$options = get_option('buspress_options');
	$readonly = !$options['buspress_salsa_activate'] ? ' readonly="true" ' : '';
	$crypt = new SalsaCrypt( buspress_salsa_pass  );
	$pass = $crypt->pass;
	echo "<input ".$readonly." id='buspress_salsa_pass' name='buspress_options[buspress_salsa_pass]' size='40' type='password' value='{$pass}' />";
}


function buspress_salsa_base_url() {
	$options = get_option('buspress_options');
	$readonly = !$options['buspress_salsa_activate'] ? ' readonly="true" ' : '';
	echo "<input ".$readonly." id='buspress_salsa_base_url' name='buspress_options[buspress_salsa_base_url]' size='40' type='text' value='{$options['buspress_salsa_base_url']}' />";
}


function buspress_salsa_chapter_filter() {
	$options = get_option('buspress_options');
	$filter =  isset($options['buspress_salsa_chapter_filter']) ? $options['buspress_salsa_chapter_filter'] : '';
	if( $options['buspress_salsa_activate'] ) { 
		$obj = new SalsaConnect;
		if( $obj->status() == "Successful Login" ):
			$chapters = $obj->post('gets-nofilter','object=chapter');
			echo "<select id='buspress_salsa_chapter_filter' name='buspress_options[buspress_salsa_chapter_filter]' />";
				echo "<option value=''>-- Show All Chapters</option>";
			foreach( $chapters as $chapter):
				$selected = $filter == $chapter->chapter_KEY ? 'selected="selected"' : '';
				echo '<option '.$selected.' value="'.$chapter->chapter_KEY.'">'.$chapter->Name.'</option>';
			endforeach;
			echo "</select>";
		endif;
	}
}


function buspress_salsa_chapter_base() {
	$options = get_option('buspress_options');
	if( isset($options['buspress_salsa_chapter_base']) && !empty($options['buspress_salsa_chapter_base']) ) {
		echo '<h2>#'.$options['buspress_salsa_chapter_base'].'</h2>';
	}
	else echo '---';
}

function buspress_salsa_org_base() {
	$options = get_option('buspress_options');
	if( isset($options['buspress_salsa_org_base']) && !empty($options['buspress_salsa_org_base']) ) {
		echo '<h2>#'.$options['buspress_salsa_org_base'].'</h2>';
	}
	else echo '---';
}


// Function for render the adming page
function buspress_salsa_setup() {
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	$options = get_option('buspress_options');
	if( isset($options['buspress_salsa_activate']) && $options['buspress_salsa_activate'] ) {
		$obj = new SalsaConnect;
		if( $obj->status() == "Successful Login" && empty($options['buspress_salsa_org_base']) ) {
			$connect = $obj->post('gets','object=campaign_manager&include=chapter_KEY&include=organization_KEY&condition=Email='.$options['buspress_salsa_username'] );
			$options['buspress_salsa_chapter_base'] = $connect[0]->chapter_KEY; 
			$options['buspress_salsa_org_base'] = $connect[0]->organization_KEY;
			$crypt = new SalsaCrypt( buspress_salsa_pass  );
			$options['buspress_salsa_pass'] = $crypt->pass;
			update_option('buspress_options',$options);
		}
	} 
	?>
	<div class="wrap">
		<div class="icon32" style="background: transparent url(<?php echo base.'/images/salsa-big.png'; ?>) no-repeat 0px 0px; height: 38px; " id="icon-options-general"><br></div>
		<h2>Set Up Your Salsa Connection</h2>
		Connect WordPress to <a href="http://salsalabs.com" target="_blank">Salsa</a> and and add synchronized reports, events, and sign-up forms.
		<form autocomplete='off' action="options.php" method="post">
			<?php settings_fields('buspress'); ?>

			<?php do_settings_sections(__FILE__); ?>

		<p class="submit">
			<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
		</p>
		<?php
			if( $options['buspress_salsa_activate'] )  {
				$color = $obj->status() == "Successful Login" ? $color = 'style="color: green;"' : 'style="color:red;"';
				echo '<h3 >Login Status: <span '.$color.'>'.$obj->status().'</span></h3>';
			} 
		?>
		</form>
	</div>
	<?php
}


?>