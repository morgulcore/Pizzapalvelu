<?php

$routes->get( '/',
	function() { EtusivuController::index(); } );
$routes->get('/hiekkalaatikko',
	function() { EtusivuController::sandbox(); } );

$routes->get( '/tuote',
	function() { TuoteController::index(); } );

$routes->get( '/asiakas/uusi',
	function() { AsiakasController::uusi(); } );
$routes->post( '/asiakas/uusi',
	function() { AsiakasController::rekisteroi(); } );

$routes->get( '/kayttaja/login',
	function() { KayttajaController::login(); } );
$routes->post( '/kayttaja/login',
	function() { KayttajaController::handle_login(); } );
