<?php

// What's called when rendering a Salsa object, either from a shortcode embed or a widget

class SalsaRender {
	private $type;

	function __construct($type){
		if( !isset($type) ) die( __('Something must have gotten lost in translation, this embed is no good. Try adding it again from Salsa','salsapress'));
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
			case 'signup_page' || 'event' || 'action':
				$form = new SalsaForm($data);
				return $form->render();
				break;
			default:
				return 'Hmm can\'t render that. Something may have gone awry.';
		endswitch;
	}
}

?>