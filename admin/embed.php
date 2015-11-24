<?php

// Creates the button for embedding Salsas in posts

//Functions for the Buttons
function salsapress_form_button(){
	$title = __('Insert Salsa Element','salsapress');
	$button = '<a href="'.admin_url('admin-ajax.php').'?action=salsapress_form_button_iframe&amp;TB_iframe=true&amp;height=150&amp;respect_dimensions=true" class="button thickbox add-salsapress" title="'.$title.'" onclick="return false;"><img src="'.SALSAPRESS_BASE.'images/salsa.png'.'" alt="'.$title.'" width="18" height="18" /> Add Salsa</a>';
	echo $button;
}

function salsapress_form_button_iframe(){
	wp_enqueue_script( 'SalsaPress', SALSAPRESS_BASE.'utils/SalsaPress.js',array( 'jquery' ), '1.0', true );
	wp_enqueue_style( 'SalsaPress_Admin', SALSAPRESS_BASE.'admin/salsapress_admin.css','', '0.5', 'all' );
	wp_enqueue_script( 'SalsaPress_Admin', SALSAPRESS_BASE.'admin/salsapress_admin.js',array( 'jquery' ), '1.0', true );
	localize_scripts();
	remove_action( 'admin_enqueue_scripts', 'wp_auth_check_load' );
	wp_iframe('salsapress_form_button_iframe_content');
	exit();
}


function salsapress_salsa_report_render() {
	if (!current_user_can('edit_posts'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	$obj = SalsaConnect::singleton();

	$user_input = $obj->post('gets','object=report_condition&condition=value_type=User%20variable&condition=report_KEY='.$_GET['key'], true);

	if( count($user_input) > 0 && empty($_GET['inputs']) ){
		?><!--0--><h3>Hmm looks like this report takes user inputs, so why doncha put in some inputs?</h3>
		<p><em>Confused? Scared? <a target="_blank" href="<?php echo SALSAPRESS_SALSA_BASE_URL; ?>/dia/hq/reports/edit?table=report&key=<?php echo $_GET['key']; ?>'">Check out your report here</a></em></p>
		<ul>
		<?php foreach( $user_input as $i ): ?>
			<?php $label =  strlen($i->user_variable_label) > 0 ? $i->user_variable_label.' ('.str_replace('_',' ',substr($i->field,strpos($i->field,".")+1)).')' : str_replace('_',' ',substr($i->field,strpos($i->field,".")+1)); ?>
			<li class="user_variables" ><label for="<?php echo $i->key; ?>"><?php echo $label; ?></label>
			<input name="u<?php echo $i->key; ?>" type="text"></li>
		<?php endforeach; ?>
		</ul>
			<button id="resubmit">Get the report</button>
				<ul>
					<em>Advanced: You can use environmental variables when calling reports. So far this includes:</em>
					<li><strong>Dates:</strong> Use DATE() to add relative dates to the report, for example you can use: DATE(today) for today, DATE(+1 week) for one week later, or DATE(-6 months) for six months previous.</li>
					<li><strong>Environmental Variables: </strong> Used GET() or POST() to add environmental variables to your report. For example GET(user) would pass the string 'steve' specified by the variable 'user' in the following URL http://testurl.com?user=steve</li>
				</ul>
			<?php
	} else {

		$inputs = isset($_GET['inputs']) ? $_GET['inputs'] : array();
		$report = $obj->reportsplit($_GET['key'], $inputs);
		if( count($report) > 0 ):
			$titles = get_object_vars($report[0]);

		?><!--1-->
		<h3>Your Report!</h3>
		<p>
			Here's a preview of the first few rows of the report (there are <?php echo count($report); ?> total). You can rename (or remove) column's title, just click to edit. You can also hide a whole column, just click 'hide'.<br>
		</p>
		<p>
			<strong>WARNING:</strong> Changing this report's columns or conditions in Salsa <em>will</em> mess with the way it's displayed on the site.
		</p>
		<p>
			Click to edit a columns title or hide them all <input type="checkbox" value="hide" name="headers" id="header">
		</p>
		<p>
			Check to display the report as a list (no columns) <input type="checkbox" id="list" value="true" name="list"><br>
			<div id="list_options">
				<p>In lists, each column is smooshed together with a column to it's left and right.<br>
					You can specify below the separator you'd like placed between the smooshed columns (it defaults to a space).</p>
				<p>
					<strong>Row 1</strong>
					<?php $i = 1; while($i < count($titles) ):?>
						|<input class="list_gap" value="&nbsp;" name="row_<?php echo $i; ?>" type="text">|
						<?php $i+=1; ?>
						<strong>Row <?php echo $i; ?></strong>
					<?php endwhile;?>
				</p>
			</div>
		</p>
			<table>
			<tbody>
			<tr>

			<?php $i = 0;?>
			<?php foreach($titles as $t=>$v): ?>
				<th><input name="header_<?php echo $i; ?>" value="<?php echo str_replace('_',' ',substr($t, strpos($t,".")+1)); ?>" type="text"  ><br /><span class="hide">(hide)</span></th>
				<?php $i+=1; ?>
			<?php endforeach; ?>
			</tr>
			<?php $i=0; while( $i < 5 && $i < count($report) ): ?>
			<tr>
				<?php foreach($report[$i] as $c): ?>
					<td><?php echo $c;?></td>
				<?php endforeach; ?>
			<tr>
			<?php $i++; endwhile; ?>
			</tbody>
			</table>
				<?php if( !empty($_GET['inputs'] ) ): ?>
					<br>
					<br><strong>Environmental Variables </strong><br>
					Changing these will alter your report's results
					<ul>
					<?php $a = 0; foreach( $user_input as $i ): ?>
						<?php $label =  strlen($i->user_variable_label) > 0 ? $i->user_variable_label.' ('.str_replace('_',' ',substr($i->field,strpos($i->field,".")+1)).')' : str_replace('_',' ',substr($i->field,strpos($i->field,".")+1)); ?>
						<li class="user_variables" ><label  for="<?php echo $i->key; ?>"><?php echo $label; ?></label>
						<input name="u<?php echo $i->key; ?>" type="text" value="<?php echo $_GET['inputs'][$a]; ?>"></li>
					<?php $a++; endforeach; ?>
					</ul>
				<?php endif;?>
				<button id="resubmit">Refresh report</button> (warning: clears all the style changes like hidden columns, etc)


		<?php
		else: ?>
				<!--0--><h3>Yeah I'm not seeing anything. Did you screw up or is Salsa being shitty?</h3>
				<button id="resubmit">Resubmit and try again!</button>
			<?php
		endif;
	}

	exit();
}

function salsapress_form_button_iframe_content(){
	if (!current_user_can('edit_posts'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	?>
	<?php $obj = SalsaConnect::singleton(); ?>
	<div class="embeddable">
		<h1 style="margin-bottom: 60px; "><?php _e('Add some Salsa to your page!','salsapress'); ?></h1>
		<form id="form" class="option">
			<h3 class="media-title"><?php _e('Embed a Salsa Contact Form','salsapress'); ?></h3>
			<p><?php _e('Will create the sign up form based off the one you created in Salsa and will add directly into Salsa','salsapress'); ?><br>
			<br />

			<input type="hidden" name="type" value="signup_page" id="type">

			<select class="salsa_key"  name="salsa_key">
				<option value=""><?php _e('- Select a Sign Up Form -','salsapress');?></option>
					<?php $obj = SalsaConnect::singleton(); ?>
					<?php $stuff = $obj->post('gets','object=signup_page&include=title'); ?>
					<?php foreach ($stuff as $things ) { ?>
						<option value="<?php echo $things->key; ?>"><?php echo $things->title; ?></option>
					<?php } ?>
			</select><br>

			<label id="form-title"><?php _e('Include Title?','salsapress') ?></label>   <input type="checkbox" name="salsa_title" id="salsa_title"><br>
			<label id="form-description"><?php _e('Include Description?','salsapress'); ?></label>   <input type="checkbox" name="salsa_description" id="salsa_description"><br><br>

			<label id="form-confirmation"><?php _e("After Saving the form:","salsapress"); ?></label><br>
			<textarea style="width: 450px;" name="after-save"></textarea><br>
			<?php _e("Accepts text and HTML. After the form saves, will replace the form with this content. If you left it blank it'll thank em for signing up and reset the form.","salsapress"); ?>
			<br><br>
		</form>

		<form id="event" class="option">
			<h3 class="media-title"><?php _e('Embed a Salsa Event','salsapress'); ?></h3>
			<p><?php _e('Will create the sign up form based off of the event\'s form. Only works for non-paying events.','salsapress'); ?><br>
			<br/>
			<input type="hidden" name="type" value="event" id="type">
			<label for="salsa_key"><?php _e('Salsa Event:'); ?></label>
			<select class="salsa_key"  name="salsa_key">
				<option value=""><?php _e('- Select an Event -','salsapress'); ?></option>
					<?php $stuff = $obj->post('gets','object=event&include=Event_Name&orderBy=-Start&limit=50&condition=Start>='.date("Y-m-d")); ?>
					<?php foreach ($stuff as $things ) { ?>
						<option value="<?php echo $things->key; ?>"><?php echo $things->Event_Name; ?></option>
					<?php } ?>
			</select><br>

			<label id="form-title"><?php _e('Include Title?','salsapress') ?></label>   <input type="checkbox" name="salsa_title" id="salsa_title" ><br>
			<label id="form-description"><?php _e('Include Description?','salsapress'); ?></label>    <input type="checkbox" name="salsa_description" id="salsa_description" value="on"><br>

			<label id="form-description"><?php _e('Show Compact Event View?','salsapress'); ?></label>   <input type="checkbox" name="event_compact" id="event_compact" value="on"><br>
			<em><?php _e("Compact view strips out the first Image from the Description and displays it along with the Event Name, Date, Time, Address, and Signup Form. The Full Description is placed in a hidden 'Read More' box",'salsapress'); ?></em><br><br>

			<label id="form-confirmation"><?php _e("After Saving the form:","salsapress"); ?></label><br>
			<textarea style="width: 450px;" name="after-save"></textarea><br>
			<?php _e("Accepts text and HTML. After the form saves, will replace the form with this content. If you left it blank it'll thank em for signing up and reset the form.","salsapress"); ?>
			<br><br>
		</form>

		<form id="petition" class="option">
			<h3 class="media-title"><?php _e('Embed a Salsa Petition','salsapress'); ?></h3>
			<p><?php _e('Will create the sign up form based off of the Salsa Petition','salsapress'); ?><br>
			<br/>
			<input type="hidden" name="type" value="action" id="type">
			<label for="salsa_key"><?php _e('Salsa Petition:'); ?></label>
			<select class="salsa_key"  name="salsa_key">
				<option value=""><?php _e('- Select a Petition -','salsapress'); ?></option>
					<?php $stuff = $obj->post('gets','object=action&condition=style=petition&include=Reference_Name&orderBy=-Date_Created'); ?>
					<?php foreach ($stuff as $things ) { ?>
						<option value="<?php echo $things->key; ?>"><?php echo $things->Reference_Name; ?></option>
					<?php } ?>
			</select><br>

			<label id="form-title"><?php _e('Include Title?','salsapress') ?></label>   <input type="checkbox" name="salsa_title" id="salsa_title" ><br>
			<label id="form-description"><?php _e('Include Description?','salsapress'); ?></label>    <input type="checkbox" name="salsa_description" id="salsa_description" value="on"><br>

			<br><br>
		</form>

		<form id="report" class="option">
			<h3 class="media-title">Embed a Salsa Report</h3>
			<input type="hidden" name="type" value="report" id="type">
			<p><a href="<?php echo SALSAPRESS_SALSA_BASE_URL; ?>/dia/hq/reports/list.jsp?table=report" target="_blank">View your reports</a>.</p>
			<p>Paste or enter a Report URL or KEY <input name="key" style="width: 420px;"  type="text" class="salsa_key"></p>
			<input type="hidden" value="" name="columns">
			<div class="preview">
				<em style="text-align: center;">Press Enter or Tab to preview the report.</em>
			</div>
		</form>

		<div id="submit">
			<input type="submit" value="<?php _e('Insert Into Post','salsapress'); ?>" name="insert" id="insert" class="button savebutton" />
			<a href="#" id="cancel">Cancel</a>
		</div>

	</div>
	<?php
}


?>
