<?php

class TilausController extends BaseController {

	/*
	public static function index() {
		$ongelmat = Ongelma::hae_kaikki();
		View::make( 'ongelma/index.html', array( 'ongelmat' => $ongelmat ) );
	} */

	public static function uusi_tilaus( $virheilmoitukset, $jo_taytetyt_kentat ) {
		self::check_logged_in();

		$kaikki_tuotteet = Tuote::hae_kaikki();
		// Bugtrap
		if( $kaikki_tuotteet == null ) {
			exit( 'TilausController.uusi() – Null-viite (1)' );
		}

		$asiakkaan_osoitteet
			= mm_Asiakas_Osoite::hae_asiakkaan_osoitteet( 'admin' );
		// Bugtrap
		if( $asiakkaan_osoitteet == null ) {
			exit( 'TilausController.uusi() – Null-viite (2)' );
		}

		$nyt = new DateTime(); // Nykyinen pvm ja kellonaika
		$nyt_plus_tunti = $nyt->modify( "+1 hour" ); // Tunti tulevaisuudessa

		View::make( 'tilaus/uusi.html', array(
			'kaikki_tuotteet' => $kaikki_tuotteet,
			'asiakkaan_osoitteet' => $asiakkaan_osoitteet,
			'nyt_plus_tunti' => $nyt_plus_tunti->format( "Y-m-d H:i:s" ),
			'virheilmoitukset' => $virheilmoitukset,
			'jo_taytetyt_kentat' => $jo_taytetyt_kentat ) );
	}

	public static function tee_tilaus() {
		self::check_logged_in();

		// Format: 2001-03-10 17:16:18
		$ts_tilauksen_teko = date("Y-m-d H:i:s");

		$attribuutit = array(
			'ktunnus' => $_POST[ 'ktunnus' ],
			'ts_tilauksen_teko' => $ts_tilauksen_teko,
			'ts_tak_toivottu' => $_POST[ 'toivottu_toimitusajankohta' ],
			'osoite_id' => $_POST[ 'toimitusosoite' ]
		);

		$uusi_tilaus = new Tilaus( $attribuutit );
		$virheilmoitukset = $uusi_tilaus->virheilmoitukset();

		if( count( $virheilmoitukset ) > 0 ) {
			$jo_taytetyt_kentat = array(
				'toivottu_toimitusajankohta' => $_POST[ 'toivottu_toimitusajankohta' ] );

			self::uusi_tilaus( $virheilmoitukset, $jo_taytetyt_kentat );
			return;
		}

		$uusi_tilaus->tallenna();
	}

	public static function esittely( $tilaus_id ) {
		self::check_logged_in();

		$tilaus = Tilaus::hae( $tilaus_id );
		$tilatut_tuotteet = Tilattu_tuote::hae_kaikki();

		// Bugtrap
		if( $tilaus == null || $tilatut_tuotteet == null ) {
			exit( 'TilausController.esittely() – Null-viite' );
		}

		View::make( 'tilaus/esittely.html', array(
			'tilaus' => $tilaus, 'tilatut_tuotteet' => $tilatut_tuotteet ) );
	}
}
