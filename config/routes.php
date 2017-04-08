<?php

$routes->get( '/',
	function() { EtusivuController::index(); } );
$routes->get('/hiekkalaatikko',
	function() { EtusivuController::sandbox(); } );

$routes->get( '/tuote',
	function() { TuoteController::index(); } );

$routes->get( '/asiakas',
	function() { AsiakasController::index(); } );
$routes->get( '/asiakas/uusi',
	function() { AsiakasController::uusi(); } );
$routes->post( '/asiakas/uusi',
	function() { AsiakasController::rekisteroi(); } );
$routes->get( '/asiakas/login',
	function() { AsiakasController::login(); } );
$routes->post( '/asiakas/login',
	function() { AsiakasController::handle_login(); } );
$routes->get( '/asiakas/:ktunnus',
	function( $ktunnus ) { AsiakasController::esittely( $ktunnus ); } );
$routes->get( '/asiakas/:ktunnus/muokkaa',
	function( $ktunnus ) { AsiakasController::muokkaa( $ktunnus ); } );
$routes->post( '/asiakas/:ktunnus/muokkaa',
	function() { AsiakasController::paivita(); } );
$routes->post( '/asiakas/:ktunnus/poista',
	function( $ktunnus ) { AsiakasController::poista( $ktunnus ); } );
