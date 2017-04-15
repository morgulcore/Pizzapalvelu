<?php

class EtusivuController extends BaseController {

	public static function index() {
		self::get_user_logged_in();
		View::make( 'etusivu.html' );
	}

	public static function sandbox() {
		// $asiakkaan_osoitteet = mm_Asiakas_Osoite::hae_asiakkaan_osoitteet( 6 );
		// $tilaukset = Tilaus::hae_kaikki();
		// $tilaus = Tilaus::hae( 1 );
		// $_SESSION[ 'user' ] = 'admin';
		// $user = BaseController::get_user_logged_in();
		$tilatut_tuotteet = Tilattu_tuote::hae_kaikki();
		Kint::dump( $tilatut_tuotteet );
	}
}
