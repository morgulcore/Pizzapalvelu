<?php

class EtusivuController extends BaseController {

	public static function index() {
		View::make( 'etusivu.html' );
	}

	public static function sandbox() {
		// $asiakkaan_osoitteet = mm_Asiakas_Osoite::hae_asiakkaan_osoitteet( 6 );
		// Kint::dump( $asiakkaan_osoitteet );
		// $tilaukset = Tilaus::hae_kaikki();
		// Kint::dump( $tilaukset );
		$tilaus = Tilaus::hae( 1 );
		Kint::dump( $tilaus );
	}
}
