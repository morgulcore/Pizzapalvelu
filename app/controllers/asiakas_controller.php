<?php

class AsiakasController extends BaseController {

	// Renderöidään lomake, jolla uusi asiakas rekisteröidään tietokantaan
	public static function uusi() {
		View::make( 'asiakas/uusi.html' );
	}

	// Asiakkaan tietojen muokkaus (lomakkeen esittäminen)
	public static function muokkaa( $ktunnus ) {
		if( ! self::check_logged_in() ) {
			return;
		} else if( self::kayttajalle_kuulumaton_asia( $ktunnus,
			'Tavallisena käyttäjänä voit muokata vain omaa '
			. 'esittelysivuasi' ) ) {
			return;
		}

		$asiakas = Asiakas::hae( $ktunnus );
		View::make( 'asiakas/muokkaa.html', array( 'asiakas' => $asiakas ) );
	}

	// Asiakkaan tietojen päivitys (tiedot lomakkeen kautta)
	public static function paivita() {
		$params = $_POST;

		if( ! self::check_logged_in() ) {
			return;
		} else if( self::kayttajalle_kuulumaton_asia( $params[ 'ktunnus' ],
			'Tavallisena käyttäjänä voit muokata vain omaa '
			. 'esittelysivuasi' ) ) {
			return;
		}

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
		if( ! self::kayttaja_on_yllapitaja() ) {
			return;
		}

		$asiakkaat = Asiakas::hae_kaikki();
		View::make( 'asiakas/index.html', array( 'asiakkaat' => $asiakkaat ) );
	}

	// Renderöidään asiakkaan esittelysivu
	public static function esittely( $ktunnus ) {
		if( ! self::check_logged_in() ) {
			return;
		// Ilman ylläpitäjän oikeuksia käyttäjä pääsee tarkastelemaan
		// vain omaa esittelysivuaan
		} else if( self::kayttajalle_kuulumaton_asia( $ktunnus,
			'Tavallisena käyttäjänä voit tarkastella vain omaa '
			. 'esittelysivuasi' ) ) {
			return;
		}

		$asiakas = Asiakas::hae( $ktunnus );
		$asiakkaan_osoitekirja = Osoite::hae_asiakkaan_osoitteet( $ktunnus );
		$asiakkaan_toimittamattomat_tilaukset
			= Tilaus::hae_asiakkaan_toimittamattomat_tilaukset( $ktunnus );
		$asiakkaan_tilaukset = Tilaus::hae_asiakkaan_tilaukset( $ktunnus );
		$asiakkaaseen_liittyvat_ongelmat
			= Ongelma::hae_asiakkaaseen_liittyvat_ongelmat( $ktunnus );

		View::make( 'asiakas/esittely.html', array(
			'asiakas' => $asiakas,
			'asiakkaan_osoitekirja' => $asiakkaan_osoitekirja,
			'asiakkaan_toimittamattomat_tilaukset'
				=> $asiakkaan_toimittamattomat_tilaukset,
			'toimittamattomien_tilausten_lkm'
				=> count( $asiakkaan_toimittamattomat_tilaukset ),
			'toimitettujen_tilausten_lkm'
				=> count( $asiakkaan_tilaukset )
				- count( $asiakkaan_toimittamattomat_tilaukset ),
			'toimitettujen_tilausten_kokonaisarvo'
				=> Tilaus::laske_asiakkaan_toimitettujen_tilausten_kokonaisarvo(
				$ktunnus ),
			'asiakkaaseen_liittyvien_ongelmien_lkm'
				=> count( $asiakkaaseen_liittyvat_ongelmat ) ) );
	}

	// Asiakastilin poistaminen tietokannasta
	public static function poista( $ktunnus ) {
		// Vain ylläpitäjä voi poistaa asiakastilejä
		if( ! self::kayttaja_on_yllapitaja() ) {
			return;
		}

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

		// Jokaiselle uudelle asiakkaalle lisätään "oletusosoite". Tässä
		// ei ole muuta järkeä kuin se, etten ainakaan toiseksi viimeisenä
		// iltana (3.5.) ollut saanut aikaiseksi mekanismia, jolla asiakas
		// voi lisätä osoitteita osoitekirjaansa.
		// Osoite::lisaa_asiakas_osoite_pari( $uusi_asiakas->ktunnus, 11 );

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
			$viesti = 'Kirjauduit sisään käyttäjätunnuksella '
				. $asiakas->ktunnus;
			if( $asiakas->on_paakayttaja ) {
				$viesti = $viesti
					. '. Huomaa, että sinulla on nyt ylläpitäjän oikeudet.';
			}

			$_SESSION[ 'user' ] = $asiakas->ktunnus;
			Redirect::to( '/', array(
				'tervetuloa_takaisin' => $viesti ) );
		}
	}

	public static function kirjaudu_ulos() {
		$_SESSION[ 'user' ] = null;
		Redirect::to( '/asiakas/kirjaudu' );
	}
}
