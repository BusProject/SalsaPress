<?php


// Generates an object that Salsa Render uses to display the report

class SalsaReport {
	private $key;
	private $type;
	private $data = array();

	function __construct($key, $inputs) {
		$obj = SalsaConnect::singleton(true);
		$this->data = $obj->reportsplit($key, $inputs);		
	}

	function json() {
		return json_encode($this->data);
	}

	function data_dump() {
		return $this->data;
	}

	function render($type = 'table',$columns = array(),$headers = array(), $gaps=array() ) {

		$returned = '';

		switch( $type ):
			case 'list':
				$returned .=  '<ul class="salsa_table">';
				$start = '<li>';
				$end = '</li>';
				break;
			default:
				$returned .=  '<table class="salsa_table"><tbody>';
				$start = '<tr>';
				$end = '</tr>';
		endswitch;
		

		
		if( $headers['show'] ):
			
			unset($headers['show']);
			$returned .=  $start;
			$c = 0;
			foreach( $headers as $h):
				switch( $type ):
					case 'list':
						break;
					default:
						$returned .=  '<th>';
				endswitch;

				$returned .=  $h;

				switch( $type ):
					case 'list':
						if( isset($gaps[$c-1]) ) $returned .=  $gaps[$c-1];
						break;
					default:
						$returned .=  '</th>';
				endswitch;
				
				$c++;
			endforeach;
			$returned .=  $end;
		endif;

		foreach($this->data as $row):
			$returned .=  $start;
			$c = 0;
			foreach ($row as $col):
				if( in_array($c,$columns) ):
					switch( $type ):
						case 'list':
							break;
						default:
							$returned .=  '<td>';
					endswitch;

					$returned .=  $col;

					switch( $type ):
						case 'list':
							if( isset($gaps[$c-1]) ) $returned .=  $gaps[$c-1];
							break;
						default:
							$returned .=  '</td>';
					endswitch;
				endif;
				$c++;
			endforeach;
			$returned .=  $end;
		endforeach;



		switch( $type ):
			case 'list':
				$returned .=  '</ul>';
				break;
			default:
				$returned .=  '</tbody></table>';
				break;
		endswitch;
		return $returned;
	}

}

?>