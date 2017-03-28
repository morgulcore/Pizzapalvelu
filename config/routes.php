<?php

$routes->get( '/',
	function() { HelloWorldController::index(); } );
$routes->get( '/tuotteet',
	function() { HelloWorldController::tuotteet(); } );

$routes->get( '/login',
	function() { UserController::login(); } );
$routes->post( '/login',
	function() { UserController::handle_login(); } );

$routes->get('/hiekkalaatikko',
	function() { HelloWorldController::sandbox(); } );
