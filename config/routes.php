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
$routes->get( '/asiakas/:asiakas_id',
	function( $asiakas_id ) { AsiakasController::esittely( $asiakas_id ); } );
$routes->get( '/asiakas/:asiakas_id/muokkaa',
	function( $asiakas_id ) { AsiakasController::muokkaa( $asiakas_id ); } );
$routes->post( '/asiakas/:asiakas_id/muokkaa',
	function() { AsiakasController::paivita(); } );
$routes->post( '/asiakas/:asiakas_id/poista',
	function( $asiakas_id ) { AsiakasController::poista( $asiakas_id ); } );

$routes->get( '/kayttaja/login',
	function() { KayttajaController::login(); } );
$routes->post( '/kayttaja/login',
	function() { KayttajaController::handle_login(); } );
