<?php

$routes->get( '/',
	function() { HelloWorldController::index(); } );
$routes->get( '/pizzat',
	function() { HelloWorldController::pizzat(); } );
$routes->get('/hiekkalaatikko',
	function() { HelloWorldController::sandbox(); } );
