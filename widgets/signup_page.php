<?php

add_action( 'widgets_init', function () {
	register_widget( 'Salsa_Signup_Widget' );
} );

/**
 * Jolokia Salsa Signup Widget
 */
class Salsa_Signup_Widget extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'salsa_signup_form_widget',
			__( 'Salsa Sign Up Form', 'salsapress' ),
			array( 'description' => __( 'Add a sign up form from Salsa', 'salsapress' ) )
		);
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
		<h3 class="media-title"><?php _e('Embed a Salsa Contact Form','salsapress'); ?></h3>
		<p><?php _e('Will create the sign up form based off the one you created in Salsa and will add directly into Salsa','salsapress'); ?><br>
		<br/>
		<input type="hidden" name="type" value="signup_page" id="type">
		<select class="salsa_key" style="width: 220px;"  id="<?php echo $this->get_field_id('form_key'); ?>" name="<?php echo $this->get_field_name('form_key'); ?>">
			<option value=""><?php _e('- Select a Sign Up Form -','salsapress');?></option>
				<?php $obj = SalsaConnect::singleton(); ?>
				<?php $stuff = $obj->post('gets','object=signup_page&include=title'); ?>
				<?php foreach ($stuff as $things ) { ?>
					<option value="<?php echo $things->key; ?>" <?php if( $things->key == $form_key ) { echo 'selected'; }?> ><?php echo $things->title; ?></option>
				<?php } ?>
		</select><br>
		<label id="form-title"><?php _e('Include Title?','salsapress') ?></label>   <input <?php if( $title ) echo 'checked="checked"';?> type="checkbox" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>"><br>
		<label id="form-description"><?php _e('Include Description?','salsapress'); ?></label>   <input <?php if( $description ) echo 'checked="checked"';?> type="checkbox" id="<?php echo $this->get_field_id('description'); ?>" name="<?php echo $this->get_field_name('description'); ?>"><br><br>

		<label id="form-confirmation"><?php _e("After Saving the form:","salsapress"); ?></label>
		<textarea style="width: 200px;" id="<?php echo $this->get_field_id('after_save'); ?>" name="<?php echo $this->get_field_name('after_save'); ?>"><?php echo rawurldecode($after_save);?></textarea><br>
		<?php _e("Accepts text and HTML. After the form saves, will replace the form with this content. If you left it blank it'll thank em for signing up and reset the form.","salsapress"); ?>

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

		if( $form_key != '' ) {

			$render = new SalsaRender('signup_page');
			$done = $render->render( array('type' => 'signup_page','salsa_key' => $form_key, 'salsa_title' => $title, 'salsa_description' => $description, 'after_save' => $after_save) );
			echo '<div class="signup_widget signup-form-'.$form_key.'">'.$done.'</div>';
		}
  	} else {
		echo "<!-- Activate SalsPress to use -->";
	}
  }
}


?>