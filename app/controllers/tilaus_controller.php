<?php

class TilausController extends BaseController {

	/*
	public static function index() {
		$ongelmat = Ongelma::hae_kaikki();
		View::make( 'ongelma/index.html', array( 'ongelmat' => $ongelmat ) );
	} */

	public static function esittely( $tilaus_id ) {
		$tilaus = Tilaus::hae( $tilaus_id );

		// Bugtrap
		if( $tilaus == null ) {
			exit( 'TilausController.esittely() – Null-viite' );
		}

		View::make( 'tilaus/esittely.html', array(
			'tilaus' => $tilaus ) );
	}
}
