<?php

$routes->get( '/',
	function() { EtusivuController::index(); } );
$routes->get('/hiekkalaatikko/',
	function() { EtusivuController::sandbox(); } );

$routes->get( '/asiakas/',
	function() { AsiakasController::index(); } );
$routes->get( '/asiakas/uusi/',
	function() { AsiakasController::uusi(); } );
$routes->post( '/asiakas/uusi/',
	function() { AsiakasController::rekisteroi(); } );
$routes->get( '/asiakas/kirjaudu/',
	function() { AsiakasController::kirjaudu(); } );
$routes->post( '/asiakas/kirjaudu/',
	function() { AsiakasController::sisaankirjautumisen_kasittely(); } );
$routes->post( '/asiakas/kirjaudu_ulos/',
	function() { AsiakasController::kirjaudu_ulos(); } );
$routes->get( '/asiakas/:ktunnus/',
	function( $ktunnus ) { AsiakasController::esittely( $ktunnus ); } );
$routes->get( '/asiakas/:ktunnus/muokkaa/',
	function( $ktunnus ) { AsiakasController::muokkaa( $ktunnus ); } );
$routes->post( '/asiakas/:ktunnus/muokkaa/',
	function() { AsiakasController::paivita(); } );
$routes->post( '/asiakas/:ktunnus/poista/',
	function( $ktunnus ) { AsiakasController::poista( $ktunnus ); } );

$routes->get( '/ongelma/',
	function() { OngelmaController::index( null ); } );
$routes->get( '/ongelma/:ktunnus/',
	function( $ktunnus ) { OngelmaController::index( $ktunnus ); } );

$routes->get( '/osoite/',
	function() { OsoiteController::index( null ); } );
$routes->post( '/osoite/',
	function() { OsoiteController::index( null ); } );
$routes->post( '/osoite/poista_ao_parit/',
	function() { OsoiteController::poista_valitut_asiakas_osoite_parit(); } );

$routes->get( '/tilaus/',
	function() { TilausController::index(); } );
$routes->get( '/tilaus/uusi/',
	function() { TilausController::uusi_tilaus( null, null ); } );
$routes->post( '/tilaus/uusi/',
	function() { TilausController::tee_tilaus(); } );
$routes->get( '/tilaus/:tilaus_id/',
	function( $tilaus_id ) { TilausController::esittely( $tilaus_id ); } );
$routes->get( '/tilaus/:tilaus_id/muokkaa/',
	function( $tilaus_id ) { TilausController::muokkaa( $tilaus_id, null, null ); } );
$routes->post( '/tilaus/:tilaus_id/paivita/',
	function() { TilausController::paivita(); } );
$routes->post( '/tilaus/:tilaus_id/poista/',
	function( $tilaus_id ) { TilausController::poista( $tilaus_id ); } );
$routes->post( '/tilaus/:tilaus_id/merkitse_toimitetuksi/',
	function( $tilaus_id ) { TilausController::merkitse_toimitetuksi( $tilaus_id ); } );
