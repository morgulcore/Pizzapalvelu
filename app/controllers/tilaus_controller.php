<?php

class TilausController extends BaseController {

	public static function index() {
		if( ! self::kayttaja_on_yllapitaja() ) {
			return;
		}

		$tilaukset = Tilaus::hae_kaikki();
		foreach( $tilaukset as $tilaus ) {
			$tilaus->aseta_tilauksen_kokonaishinta();
		}

		View::make( 'tilaus/index.html', array(
			'tilaukset' => $tilaukset ) );
	}

	public static function uusi_tilaus( $virheilmoitukset, $jo_taytetyt_kentat ) {
		if( ! self::check_logged_in() ) {
			return;
		}

		$kaikki_tuotteet = Tuote::hae_kaikki();
		// Bugtrap
		if( $kaikki_tuotteet == null ) {
			exit( 'TilausController.uusi_tilaus() – Null-viite (1)' );
		}

		$asiakas = self::get_user_logged_in();
		if( $asiakas == null ) {
			exit( 'TilausController.uusi_tilaus() – Null-viite (2)' );
		}

		$asiakkaan_osoitteet
			= Osoite::hae_asiakkaan_osoitteet( $asiakas->ktunnus );
		// Bugtrap
		if( $asiakkaan_osoitteet == null ) {
			exit( 'TilausController.uusi_tilaus() – Uuden käyttäjän osoitekirja on tyhjä, joten hän ei voi tilata pizzaa :( Ongelmaa korjataan... Sillä välin voit kirjautua sisään käyttäjätunnuksella mruusu ja salasanalla Tsoh4, jos haluat tehdä tilauksen' );
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

	// Ymmärrän, että tämä funktio on aivan liian pitkä. Refaktoroin sen
	// ennen lopullista palautusta.
	public static function tee_tilaus() {
		if( ! self::check_logged_in() ) {
			return;
		}

		$uusi_tilaus = self::luo_uusi_tilaus_olio( null, $_POST[ 'ktunnus' ],
			date( "Y-m-d H:i:s" ), $_POST[ 'toivottu_toimitusajankohta' ],
			$_POST[ 'toimitusosoite' ] );

		$virheilmoitukset = $uusi_tilaus->virheilmoitukset();
		$jo_taytetyt_kentat = array(
			'toivottu_toimitusajankohta'
			=> $_POST[ 'toivottu_toimitusajankohta' ] );

		if( count( $virheilmoitukset ) > 0 ) {
			self::uusi_tilaus( $virheilmoitukset, $jo_taytetyt_kentat );
			return;
		}

		// Funktio -->
		//
		// Ollaan lähinnä kiinnostuneita avaimista, jotka ovat muotoa
		// <luonnollinen luku>/<merkkijono>. Esimerkkinä "6/iso"
		// tai "2/tavallinen".
		$post_avaimet = array_keys( $_POST );
		$tilatut_tuotteet = array();
		$tuotelaskuri = 0;

		foreach( $post_avaimet as $avain ) {
			if( preg_match( '/\A[1-9][0-9]*\/[a-z]+\z/', $avain ) === 1
				&& $_POST[ $avain ] > 0 ) {
				$tuotelaskuri++;
				// Esim. "6/iso" jakaantuu tässä merkkijonoihin "6" ja "iso"
				$kaksi_mjonoa = explode( '/', $avain );
				// Bugtrap
				if( count( $kaksi_mjonoa ) != 2 ) {
					exit( 'TilausController.tee_tilaus() – Lukumäärävirhe' );
				}

				$tuotetyyppi_id = $kaksi_mjonoa[ 0 ];
				$tuoteversio = $kaksi_mjonoa[ 1 ];

				$tilattu_tuote = new Tilattu_tuote( array(
					'tilausviite' => $uusi_tilaus,
					'tuotelaskuri' => $tuotelaskuri,
					'tuotetyyppi_id' => $tuotetyyppi_id,
					'tuoteversio' => $tuoteversio,
					'lukumaara' => $_POST[ $avain ] ) );

				$tilatut_tuotteet[] = $tilattu_tuote;
			}
		}
		// <-- Funktio

		if( count( $tilatut_tuotteet ) < 1 ) {
			$virheilmoitukset[]
				= 'Tilaukseen on kuuluttava ainakin yksi tilattu tuote';
			self::uusi_tilaus( $virheilmoitukset, $jo_taytetyt_kentat );
			return;
		}

		$uusi_tilaus->tallenna();
		$uusi_tilaus_id = Tilaus::hae_tilaus_id(
			$_POST[ 'ktunnus' ], $uusi_tilaus->ts_tilauksen_teko );
		$uusi_tilaus->tilaus_id = $uusi_tilaus_id;

		foreach( $tilatut_tuotteet as $tilattu_tuote ) {
			$tilattu_tuote->tallenna();
		}

		Redirect::to( '/tilaus/' . $uusi_tilaus_id );
	}

	public static function esittely( $tilaus_id ) {
		$tilaus = Tilaus::hae( $tilaus_id );

		if( ! self::check_logged_in() ) {
			return;
		} else if( self::kayttajalle_kuulumaton_asia(
			$tilaus->asiakasviite->ktunnus,
			'Tavallisena käyttäjänä et voi tarkastella toisten tilauksia' ) ) {
			return;
		}

		$tilaus = Tilaus::hae( $tilaus_id );
		// Bugtrap
		if( $tilaus == null ) {
			exit( 'TilausController.esittely() – Null-viite (1)' );
		}

		$tilatut_tuotteet
			= Tilattu_tuote::hae_tilaukseen_liittyvat_tuotteet( $tilaus_id );
		// Bugtrap
		if( $tilatut_tuotteet == null ) {
			exit( 'TilausController.esittely() – Tilaukseen ' . $tilaus_id
				. ' ei liity yhtään tilattua tuotetta' );
		}

		$tilaus->aseta_tilauksen_kokonaishinta();

		View::make( 'tilaus/esittely.html', array(
			'tilaus' => $tilaus,
			'tilatut_tuotteet' => $tilatut_tuotteet ) );
	}

	// Tilauksen tietojen muokkaus (lomakkeen esittäminen)
	public static function muokkaa( $tilaus_id, $virheilmoitukset,
		$jo_taytetyt_kentat ) {
		$tilaus = Tilaus::hae( $tilaus_id );

		if( ! self::check_logged_in() ) {
			return;
		} else if( self::kayttajalle_kuulumaton_asia(
			$tilaus->asiakasviite->ktunnus,
			'Tavallisena käyttäjänä voit muokata vain omia tilauksiasi' ) ) {
			return;
		}

		$tilaukseen_liittyvat_tuotteet
			= Tilattu_tuote::hae_tilaukseen_liittyvat_tuotteet( $tilaus_id );
		$kaikki_tuotteet = Tuote::hae_kaikki();
		$asiakkaan_osoitteet
			= Osoite::hae_asiakkaan_osoitteet( $tilaus->asiakasviite->ktunnus );

		View::make( 'tilaus/muokkaa.html', array(
			'tilaus' => $tilaus,
			'tilaukseen_liittyvat_tuotteet' => $tilaukseen_liittyvat_tuotteet,
			'kaikki_tuotteet' => $kaikki_tuotteet,
			'asiakkaan_osoitteet' => $asiakkaan_osoitteet,
			'valittu_osoite' => $tilaus->osoiteviite,
			'virheilmoitukset' => $virheilmoitukset,
			'jo_taytetyt_kentat' => $jo_taytetyt_kentat ) );
	}

	public static function paivita() {
		if( ! self::check_logged_in() ) {
			return;
		} else if( self::kayttajalle_kuulumaton_asia(
			$_POST[ 'ktunnus' ],
			'Tavallisena käyttäjänä voit muokata vain omia tilauksiasi' ) ) {
			return;
		}

		$uusi_tilaus = self::luo_uusi_tilaus_olio( $_POST[ 'tilaus_id' ],
			$_POST[ 'ktunnus' ], $_POST[ 'ts_tilauksen_teko' ],
			$_POST[ 'toivottu_toimitusajankohta' ], $_POST[ 'toimitusosoite' ] );

		$virheilmoitukset = $uusi_tilaus->virheilmoitukset();
		if( count( $virheilmoitukset ) > 0 ) {
			$jo_taytetyt_kentat = array(
				'toivottu_toimitusajankohta'
				=> $_POST[ 'toivottu_toimitusajankohta' ] );
			self::muokkaa( $_POST[ 'tilaus_id' ], $virheilmoitukset,
				$jo_taytetyt_kentat );
			return;
		}

		$uusi_tilaus->paivita_ts_tak_toivottu_ja_osoite_id();

		Redirect::to( '/tilaus/' . $uusi_tilaus->tilaus_id,
			array( 'paivitys_onnistui_viesti'
			=> 'Tilaustietojen päivitys onnistui' ) );
	}

	// Copy-paste-koodin eliminointia
	private static function luo_uusi_tilaus_olio(
		$tilaus_id, $ktunnus, $ts_tilauksen_teko, $ts_tak_toivottu, $osoite_id ) {
		$attribuutit = array(
			'tilaus_id' => $tilaus_id,
			'ktunnus' => $ktunnus,
			'ts_tilauksen_teko' => $ts_tilauksen_teko,
			'ts_tak_toivottu' => $ts_tak_toivottu,
			'osoite_id' => $osoite_id
		);

		return new Tilaus( $attribuutit );
	}
}
