<?php

add_action('widgets_init', create_function('', 'return register_widget("salsa_signup_widget");'));

// Jolokia Salsa Event Widget
class salsa_signup_widget extends WP_Widget
{
  function salsa_signup_widget()
  {
    $widget_ops = array('description' => __('Add a sign up form from Salsa'));
    $this->WP_Widget('salsapress_signup_form_widget', __('Salsa Sign Up Form'), $widget_ops);
  }

  function form($instance)
  {
	$obj = SalsaConnect::singleton();

	if( $obj && $obj->on() ) {

		$form_key = isset($instance['form_key']) ? esc_attr($instance['form_key']) : '';
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		$description = isset($instance['description']) ? esc_attr($instance['description']) : ''; 
		$after_save = isset($instance['after_save']) ? esc_attr($instance['after_save']) : ''; 
		?>
		<h3 class="media-title">Embed a Salsa Contact Form</h3>
		<p>Will create the sign up form based off the one you created in Salsa and will add directly into Salsa<br>
		<em>Hint: Click to <a target="_blank" href="https://hq-<?php echo salsapress_salsa_base_url;?>/dia/hq/surf/edit.jsp?table=signup_page">Create</a> and <a target="_blank" href="https://hq-<?php echo salsapress_salsa_base_url;?>/salsa/hq/p/salsa/web/staging/list?table=signup_page">edit</a> your Contact Forms in Salsa</em></p>
		<input type="hidden" name="type" value="signup_page" id="type">
		<label for="salsa_key"><?php _e('Salsa Form:'); ?></label>
		<select class="salsa_key" style="width: 220px;"  id="<?php echo $this->get_field_id('form_key'); ?>" name="<?php echo $this->get_field_name('form_key'); ?>">
			<option value="">- Select a Sign Up Form -</option>
				<?php $obj = SalsaConnect::singleton(); ?>
				<?php $stuff = $obj->post('gets','object=signup_page&include=title'); ?>
				<?php foreach ($stuff as $things ) { ?>
					<option value="<?php echo $things->key; ?>" <?php if( $things->key == $form_key ) { echo 'selected'; }?> ><?php echo $things->title; ?></option>
				<?php } ?>
		</select><br>
		<label id="form-title">Include Sign Up Form Title?</label>   <input <?php if( $title ) echo 'checked="checked"';?> type="checkbox" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>"><br>
		<label id="form-description">Include Sign Up Form Description?</label>   <input <?php if( $description ) echo 'checked="checked"';?> type="checkbox" id="<?php echo $this->get_field_id('description'); ?>" name="<?php echo $this->get_field_name('description'); ?>"><br><br>
		<label id="form-confirmation">After Saving the form:</label><br>
		<textarea style="width: 200px;" id="<?php echo $this->get_field_id('after_save'); ?>" name="<?php echo $this->get_field_name('after_save'); ?>"><?php echo rawurldecode($after_save);?></textarea><br>
		Accepts text and HTML. After the form saves, will replace the form with this content. If you left it blank it'll thank em for signing up and reset the form.
		<br><br><strong>HINT:</strong> This is a great time to ask them for something else, like a facebook Like or tell a friend or something.
		<?php
	} else {
		?>
		<h2><a href="<?php echo admin_url('admin.php?page=salsa'); ?>">Activate SalsaPress</a></h2>
		<?php 
	}
  }


  function update($new_instance, $old_instance)
  {
    $instance = $old_instance;
    $instance['form_key'] = (int)strip_tags($new_instance['form_key']);
    $instance['title'] = strip_tags($new_instance['title']);
    $instance['description'] = strip_tags($new_instance['description']);
    $instance['after_save'] = rawurlencode($new_instance['after_save']);

    return $instance;
  }


 function widget($args, $instance) {
	$obj = SalsaConnect::singleton(true);

	if( $obj && $obj->on() ) {
		extract($args);
		$form_key  = ( $instance['form_key'] != '' ) ? esc_attr($instance['form_key']) : '';
		$title  = ( $instance['title'] != ''  && isset($instance['title']) ) ? esc_attr($instance['title']) != '' : false;
		$description  = ( $instance['description'] != '' && isset($instance['description']) ) ? esc_attr($instance['description']) != '' : false;
		$after_save = ( $instance['after_save'] != '' ) ? esc_attr($instance['after_save']) : '';

		if( $form_key == '' ) { die(); }

		$render = new SalsaRender('signup_page');
		$done = $render->render( array('type' => 'signup_page','salsa_key' => $form_key, 'salsa_title' => $title, 'salsa_description' => $description, 'after_save' => $after_save) );
		echo '<div class="signup_widget signup-form-'.$form_key.'">'.$done.'</div>';
  	} else {
		echo "<!-- Activate SalsPress to use -->";
	}
  }
}


?>