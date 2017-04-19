<?php

class AsiakasController extends BaseController {

	// Renderöidään lomake, jolla uusi asiakas rekisteröidään tietokantaan
	public static function uusi() {
		View::make( 'asiakas/uusi.html' );
	}

	// Asiakkaan tietojen muokkaus (lomakkeen esittäminen)
	public static function muokkaa( $ktunnus ) {
		self::check_logged_in();

		$asiakas = Asiakas::hae( $ktunnus );
		View::make( 'asiakas/muokkaa.html', array( 'asiakas' => $asiakas ) );
	}

	// Asiakkaan tietojen päivitys (tiedot lomakkeen kautta)
	public static function paivita() {
		self::check_logged_in();

		$params = $_POST;
		$attribuutit = array(
			'ktunnus' => $params[ 'ktunnus' ],
			'on_paakayttaja' => false,
			'salasana' => $params[ 'salasana' ],
			'etunimi' => $params[ 'etunimi' ],
			'sukunimi' => $params[ 'sukunimi' ],
			'puhelinnumero' => $params[ 'puhelinnumero' ],
			'sahkopostiosoite' => $params[ 'sahkopostiosoite' ]
		);

		// Luodaan Asiakas-olio
		$asiakas = new Asiakas( $attribuutit );
		$virheilmoitukset = $asiakas->virheilmoitukset();

		if( count( $virheilmoitukset ) > 0 ) {
			View::make( 'asiakas/muokkaa.html', array(
				'virheilmoitukset' => $virheilmoitukset,
				'asiakas' => $asiakas ) );
			return;
		}

		$asiakas->paivita();

		Redirect::to( '/asiakas/' . $asiakas->ktunnus, array(
			'paivitys_onnistui_viesti' => 'Asiakastietojen päivitys onnistui' ) );
	}

	// Listataan kaikkien asiakkaiden tiedot ylläpidon tarkastelua varten
	public static function index() {
		self::kayttaja_on_yllapitaja();

		$asiakkaat = Asiakas::hae_kaikki();
		View::make( 'asiakas/index.html', array( 'asiakkaat' => $asiakkaat ) );
	}

	// Renderöidään asiakkaan esittelysivu
	public static function esittely( $ktunnus ) {
		self::check_logged_in();

		$asiakas = Asiakas::hae( $ktunnus );
		$asiakkaan_osoitekirja
			= mm_Asiakas_Osoite::hae_asiakkaan_osoitteet( $ktunnus );
		View::make( 'asiakas/esittely.html', array(
			'asiakas' => $asiakas,
			'asiakkaan_osoitekirja' => $asiakkaan_osoitekirja ) );
	}

	// Asiakastilin poistaminen tietokannasta
	public static function poista( $ktunnus ) {
		self::check_logged_in();

		$poistettava_asiakas = Asiakas::hae( $ktunnus );
		if( $poistettava_asiakas == null ) {
			// Bugien nopean löytämisen edesauttamiseksi
			exit( 'AsiakasController::poista() – null-viite' );
		}

		// Taulussa mm_Asiakas_Osoite voi olla viiteavaimia poistettavaan
		// Asiakas-olioon. Poistetaan ne.
		mm_Asiakas_Osoite::poista_ktunnus( $poistettava_asiakas->ktunnus );
		// Poistetaan myös asiakkaan tilaukset
		Tilaus::poista_asiakkaan_tilaukset( $poistettava_asiakas->ktunnus );

		// Poistetaan asiakastili
		$poistettava_asiakas->poista();

		Redirect::to( '/asiakas', array(
			'poisto_onnistui' => 'Poistettiin '
				. $poistettava_asiakas->etunimi . ' '
				. $poistettava_asiakas->sukunimi . ' ('
				. $poistettava_asiakas->ktunnus . ')' ) );
	}

	// Rekisteröidään uusi asiakastili. Tarvittavat tiedot on saatu
	// käyttäjän lähettämästä lomakkeesta.
	public static function rekisteroi() {
		$params = $_POST;
		$attribuutit = array(
			'ktunnus' => $params[ 'ktunnus' ],
			'salasana' => $params[ 'salasana' ],
			'etunimi' => $params[ 'etunimi' ],
			'sukunimi' => $params[ 'sukunimi' ]
		);

		// Varmistetaan aluksi, ettei käyttäjätiliä ole jo olemassa
		if( Asiakas::hae( $attribuutit[ 'ktunnus' ] ) != null ) {
			View::make( 'asiakas/uusi.html', array(
				'ktunnus_jo_olemassa' => 1,
				'attribuutit' => $attribuutit ) );
			return;
		}

		// Luodaan Asiakas-olio
		$uusi_asiakas = new Asiakas( $attribuutit );
		$virheilmoitukset = $uusi_asiakas->virheilmoitukset();

		if( count( $virheilmoitukset ) > 0 ) {
			View::make( 'asiakas/uusi.html', array(
				'virheilmoitukset' => $virheilmoitukset,
				'attribuutit' => $attribuutit ) );
			return;
		}

		// Tallennetaan Asiakas-olio tietokantaan
		$uusi_asiakas->tallenna();

		// Ohjataan käyttäjä kirjautumissivulle, jotta hän voi kirjautua
		// sisään juuri luomallaan käyttäjätunnuksella
		Redirect::to( '/asiakas/kirjaudu', array(
			'tervetulotoivotus' => 'Tervetuloa asiakkaaksi, ' . $params[ 'etunimi' ]
			. '! Voit nyt kirjautua sisään luomallasi käyttäjätunnuksella '
			. $params[ 'ktunnus' ] . '.' ) );
	}

	// Sisäänkirjautumissivun näyttäminen
	public static function kirjaudu() {
		View::make( 'asiakas/kirjaudu.html' );
	}

	public static function sisaankirjautumisen_kasittely() {
		$params = $_POST;

		$asiakas = Asiakas::todenna(
			$params[ 'ktunnus' ], $params[ 'salasana' ] );

		if( ! $asiakas ) {
			View::make( 'asiakas/kirjaudu.html', array(
				'kirjautumisvirhe' => 'Väärä käyttäjätunnus tai salasana!' ) );
		} else {
			$_SESSION[ 'user' ] = $asiakas->ktunnus;
			Redirect::to( '/', array(
				'tervetuloa_takaisin' => 'Tervetuloa takaisin, '
				. $asiakas->ktunnus . '!' ) );
		}
	}

	public static function kirjaudu_ulos() {
		$_SESSION[ 'user' ] = null;
		Redirect::to( '/asiakas/kirjaudu' );
	}
}
