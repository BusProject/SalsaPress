<?php

// if both logged in and not logged in users can send this AJAX request,
// add both of these actions, otherwise add only the appropriate one
add_action( 'wp_ajax_nopriv_myajax-submit', 'salsapress_salsa_supporter_submit' );
add_action( 'wp_ajax_myajax-submit', 'salsapress_salsa_supporter_submit' );

function salsapress_salsa_supporter_submit() {

	$nonce = $_POST['SalsaAjax'];
	$object = $_POST['object'];
	$doing = $_POST['doing'];

	// check to see if the submitted nonce matches with the
	// generated nonce we created earlier
	if ( ! wp_verify_nonce( $nonce, 'myajax-post-comment-nonce' ) || $object != 'supporter' || $doing != 'save' ) die ( 'Busted!');

	// get the submitted parameters
	$request = $_POST['request'];
	$obj = SalsaConnect::singleton();
	$go = $obj->rawjson($doing,$request);
	// $go = '[{ "result": "success" }]';


	// generate the response

	// response output
	header( "Content-Type: application/json" );
	echo $go;

	// IMPORTANT: don't forget to "exit"
	exit;
}

add_action( 'wp_ajax_nopriv_myajax-event-lookup', 'salsapress_salsa_event_lookup' );
add_action( 'wp_ajax_myajax-event-lookup', 'salsapress_salsa_event_lookup' );

function salsapress_salsa_event_lookup() {

	$nonce = $_POST['SalsaAjax'];
	$object = $_POST['object'];
	$doing = $_POST['doing'];

	// check to see if the submitted nonce matches with the
	// generated nonce we created earlier
	if ( ! wp_verify_nonce( $nonce, 'myajax-post-comment-nonce' ) || $object != 'event' || $doing != 'gets' ) die ( 'Busted!');

	// get the submitted parameters
	$request = $_POST['request'];
	$obj = SalsaConnect::singleton();
	$go = $obj->rawjson($doing,$request);


	// generate the response

	// response output
	header( "Content-Type: application/json" );
	echo $go;

	// IMPORTANT: don't forget to "exit"
	exit;
}


add_action( 'wp_ajax_nopriv_myajax-salsa-pop', 'salsapress_salsa_form_pop' );
add_action( 'wp_ajax_myajax-salsa-pop', 'salsapress_salsa_form_pop' );


function salsapress_salsa_form_pop() {

	$nonce = $_POST['SalsaAjax'];

	// check to see if the submitted nonce matches with the
	// generated nonce we created earlier
	if ( ! wp_verify_nonce( $nonce, 'myajax-post-comment-nonce' ) ) die ( 'Busted!');

	// get the submitted parameters
	$info = json_decode(stripslashes($_POST['shortcode']), true);
	$render = new SalsaRender('event');
	$done = $render->render($info);
	echo $done;

	// IMPORTANT: don't forget to "exit"
	exit;
}


?>