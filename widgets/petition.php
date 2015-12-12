<?php

add_action( 'widgets_init', function () {
	register_widget( 'Salsapress_Petition_Widget' );
} );

/**
 * Jolokia Salsa Petition Widget
 */
class Salsapress_Petition_Widget extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'salsapress_salsa_petition_widget',
			__( 'Salsa Petition', 'salsapress' ),
			array( 'description' => __( 'Add a petition from Salsa', 'salsapress' ) )
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
		<h3 class="media-title"><?php _e('Embed a Salsa Petition','salsapress'); ?></h3>
		<p><?php _e('Will create the sign up form based off of the Salsa Petition','salsapress'); ?><br>
		<br/>
		<input type="hidden" name="type" value="action" id="type">
		<label for="salsa_key"><?php _e('Salsa Petition:'); ?></label>
		<select class="salsa_key"  name="<?php echo $this->get_field_name('form_key'); ?>" id="<?php echo $this->get_field_id('form_key'); ?>">
			<option value=""><?php _e('- Select a Petition -','salsapress'); ?></option>
				<?php $stuff = $obj->post('gets','object=action&condition=style=petition&include=Reference_Name&orderBy=-Date_Created'); ?>
				<?php foreach ($stuff as $things ) { ?>
					<option value="<?php echo $things->key; ?>" <?php if( $things->key == $form_key ) { echo 'selected'; }?>><?php echo $things->Reference_Name; ?></option>
				<?php } ?>
		</select><br>

		<label id="form-title"><?php _e('Include Title?','salsapress') ?></label>   <input <?php if( $title ) echo 'checked="checked"';?> type="checkbox" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>"><br>
		<label id="form-description"><?php _e('Include Description?','salsapress'); ?></label>   <input <?php if( $description ) echo 'checked="checked"';?> type="checkbox" id="<?php echo $this->get_field_id('description'); ?>" name="<?php echo $this->get_field_name('description'); ?>"><br><br>

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

    return $instance;
  }


 function widget($args, $instance) {
	$obj = SalsaConnect::singleton(true);

	if( $obj && $obj->on() ) {
		extract($args);
		$form_key  = ( $instance['form_key'] != '' ) ? esc_attr($instance['form_key']) : '';
		$title  = ( $instance['title'] != ''  && isset($instance['title']) ) ? esc_attr($instance['title']) != '' : false;
		$description  = ( $instance['description'] != '' && isset($instance['description']) ) ? esc_attr($instance['description']) != '' : false;

		if( $form_key != '' ) {
			$render = new SalsaRender('action');
			$done = $render->render( array('type' => 'action','salsa_key' => $form_key, 'salsa_title' => $title, 'salsa_description' => $description ) );
			echo '<div class="petition action-'.$form_key.'">'.$done.'</div>';
		}
  	} else {
		echo "<!-- Activate SalsPress to use -->";
	}
  }
}


?>