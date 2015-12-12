<?php

add_action( 'widgets_init', function () {
	register_widget( 'Salsapress_Coming_Events' );
} );

/**
 * Jolokia Salsa Coming Events Widget
 */
class Salsapress_Coming_Events extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'salsapress_coming_events_widget',
			__( 'Salsa Events', 'salsapress' ),
			array( 'description' => __( 'Coming events, pulled out of Salsa.', 'salsapress' ) )
		);
	}

  function form($instance)
  {
	$obj = SalsaConnect::singleton();

	if( $obj && $obj->on() ) {

    	$template = isset($instance['template']) ? esc_attr($instance['template']) : '';
	    $event_number = isset($instance['event_number']) ? esc_attr($instance['event_number']) : '';
	    $link_to_cal = isset($instance['link_to_cal']) ? esc_attr($instance['link_to_cal']) : '';

		if( empty($event_number) ) $event_number = 4;
		if( empty($link_to_cal) ) $link_to_cal = false;
		$stuff = $obj->post('gets','object=template&condition=Type=Website%20Template&include=Name');

	    ?>
		<p>
			<label for="<?php echo $this->get_field_id('template'); ?>"> <?php echo __('The widget can limit the events pulled from Salsa by their assgined Salsa website template, select the template to screen here:','salsapress') ?>
				<select style="height: auto;" class="widefat" id="<?php echo $this->get_field_id('template'); ?>" name="<?php echo $this->get_field_name('template'); ?>">
				<option value="">--</option>
				<?php
					foreach ($stuff as $thing) {
						$selected = $thing->key == $template ? "selected" : '';
						if( strpos($thing->Name,"deleted") === false && strpos($thing->Name,"Default") === false ) echo '<option value="'.$thing->key.'"'.$selected.'>'.$thing->Name.'</option>';
					}
				?>
				</select>
			</label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('event_number'); ?>"> <?php echo __('The number of events to display','salsapress') ?>
				<input id="<?php echo $this->get_field_id('event_number'); ?>" name="<?php echo $this->get_field_name('event_number'); ?>" type="text" value="<?php echo $event_number;?>">
			</label>
		</p>

		<?php /* Not done yet
		<p>
			<label for="<?php echo $this->get_field_id('link_to_cal'); ?>"> <?php echo __('Link events to the built in calendar page, instead of Salsa','salsapress') ?><br>
				<input id="<?php echo $this->get_field_id('link_to_cal'); ?>" name="<?php echo $this->get_field_name('link_to_cal'); ?>" type="checkbox" <?php if( $link_to_cal ) echo 'checked="checked"';?>>
			</label>
		</p>
		*/ ?>

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
    $instance['template'] = (int)strip_tags($new_instance['template']);
    $instance['event_number'] = (int)strip_tags($new_instance['event_number']);
    $instance['link_to_cal'] = strip_tags($new_instance['link_to_cal']);

    return $instance;
  }

  function widget($args, $instance)
  {
	$obj = SalsaConnect::singleton(true);
	if( $obj && $obj->on() ) {

		extract($args);
		$template  = ( $instance['template'] != '' ) ? esc_attr($instance['template']) : '';
		$event_number  = ( $instance['event_number'] != '' ) ? esc_attr($instance['event_number']) : 4;
		$link_to_cal  = ( $instance['link_to_cal'] != '' ) ? esc_attr($instance['link_to_cal']) : false;

		if( $link_to_cal ){
			$options = get_option('my_theme_options');
			$options['cal_options_page'] = !empty($options['cal_options_page']) ? $options['cal_options_page'] : get_bloginfo('url').'/cal/';
			$cal_link = substr($options['cal_options_page'],-1) == '/' ? $options['cal_options_page'] : $options['cal_options_page'].'/';
		}

		$screen = !empty($template) ? "&condition=Template=".$template : '';
		$template = !empty($template) ? "/t/".$template : '';

		$obj = SalsaConnect::singleton(true);
		$stuff = $obj->post('gets','object=event&condition=Status=Active&condition=Start>='.date("Y-m-d").$screen."&limit=".$event_number."&include=Event_Name&include=Start&include=End&include=This_Event_Costs_Money&include=Description&orderBy=Start");

		if( empty($obj->chapter_filter ) ) {
			$chapter_link = SALSAPRESS_SALSA_CHAPTER_BASE;
		} else {
			$chapter_link = $obj->chapter_filter;
		}
		$chapter_link = SALSAPRESS_SALSA_CHAPTER_BASE == '' ? '' : '/c/'.$chapter_link;

		?>
		<div class="salsapress_coming_events">
			<h2><?php _e('UPCOMING','salsapress'); ?><a href="
			<?php
				$secure = str_replace("http://", "https://", SALSAPRESS_SALSA_BASE_URL);
				if( $link_to_cal && $thing->This_Event_Costs_Money == false ) {
					echo $cal_link;
				} else {
					echo $secure.'/o/'.SALSAPRESS_SALSA_ORG_BASE.$chapter_link.$template.'/p/salsa/event/common/public/';
				}
			?>
			" style="margin-top: -8px; float: right; font-size: 50%;"><?php _e('Full Calendar','salsapress'); ?></a></h2>
			<ul style="margin-top: 10px;" class="event_list" >
			<?php if( count($stuff) > 0 ) { foreach ($stuff as $thing ) {
			if( $link_to_cal && $thing->This_Event_Costs_Money == false ) $link = $cal_link.'#'.$thing->key;
			else $link = SALSAPRESS_SALSA_BASE_URL.'/o/'.SALSAPRESS_SALSA_ORG_BASE.$chapter_link.$template."/p/salsa/event/common/public/?event_KEY=".$thing->key;
			?>
				<li class="event" ><strong><?php echo date_smoosh($thing->Start,$thing->End).': <em>'.$thing->Event_Name; ?></em></strong><br>
				<?php if( strlen($thing->Description) > 16 ) echo better_excerpt($thing->Description,200)."<br>";?>
				<a target="_blank" href="<?php echo $link; ?>"><strong><?php _e('Sign up and more details','salsapress'); ?></strong></a></li>
			<?php };
			} else echo '<li><em>'.__('Big stuff, coming soon!','salsapress').'</em></li>';?>
			</ul>
		</div>
		<?php
	} else {
		echo "<!-- Active SalsPress to use -->";
	}
  }
}

?>
