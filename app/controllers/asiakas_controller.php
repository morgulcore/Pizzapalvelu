<?php

class AsiakasController extends BaseController {

	// LISÄYSNÄKYMÄ:
	// Renderöidään lomake, jolla uusi asiakas rekisteröidään tietokantaan
	public static function uusi() {
		View::make( 'asiakas/uusi.html' );
	}

	// LISTAUSNÄKYMÄ:
	// Listataan kaikkien asiakkaiden tiedot ylläpidon tarkastelua varten
	public static function index() {
		$asiakkaat = Asiakas::all();
		View::make( 'asiakas/index.html', array( 'asiakkaat' => $asiakkaat ) );
	}

	// ESITTELYNÄKYMÄ:
	// Renderöidään asiakkaan esittelysivu
	public static function esittely( $asiakas_id ) {
		$asiakas = Asiakas::find( $asiakas_id );
		View::make( 'asiakas/esittely.html', array( 'asiakas' => $asiakas ) );
	}

	public static function poista( $asiakas_id ) {
		$poistettava_asiakas = Asiakas::find( $asiakas_id );
		if( $poistettava_asiakas == null ) {
			// Mitään ei poisteta, jos poistettavaa ei löydy. Pitäisi
			// vielä laittaa jokin virheilmoitus.
			return;
		}

		// Tietokannasta pitäisi pakostakin löytyä asiakastiliä
		// vastaava käyttäjätunnus
		$poistettava_kayttaja = Kayttaja::find( $poistettava_asiakas->ktunnus );

		// Poistetaan asiakastili
		$poistettava_asiakas->poista();
		// Poistetaan vastaava käyttäjätunnus
		$poistettava_kayttaja->poista();

		// Ohjataan käyttäjä kirjautumissivulle, jotta hän voi kirjautua
		// sisään juuri luomallaan käyttäjätunnuksella
		Redirect::to( '/asiakas', array(
			'poisto_onnistui' => 'Poistettiin '
				. $poistettava_asiakas->etunimi . ' '
				. $poistettava_asiakas->sukunimi . ' ('
				. $poistettava_asiakas->asiakas_id . ', '
				. $poistettava_asiakas->ktunnus . ')' ) );

		//return $poistettava_asiakas;
	}

	// Rekisteröidään uusi asiakastili ja siihen liittyvä käyttäjätunnus.
	// Tarvittavat tiedot on saatu käyttäjän lähettämästä lomakkeesta.
	public static function rekisteroi() {
		$params = $_POST;
		$kentat = array(
			'ktunnus' => $params[ 'ktunnus' ],
			'salasana' => $params[ 'salasana' ],
			'etunimi' => $params[ 'etunimi' ],
			'sukunimi' => $params[ 'sukunimi' ]
		);

		// Jokaista asiakasta vastaa yksi käyttäjätunnus
		$uusi_kayttaja = new Kayttaja( array(
			'ktunnus' => $params[ 'ktunnus' ],
			'salasana' => $params[ 'salasana' ],
			'tyyppi' => 0 ) );
		$kayttajavirheilmoitukset = $uusi_kayttaja->virheilmoitukset();

		// Luodaan Asiakas-olio
		$uusi_asiakas = new Asiakas( array(
			'ktunnus' => $params[ 'ktunnus' ],
			'etunimi' => $params[ 'etunimi' ],
			'sukunimi' => $params[ 'sukunimi' ] ) );
		$asiakasvirheilmoitukset = $uusi_asiakas->virheilmoitukset();

		// Asiakas- ja käyttäjävirheilmoitukset samassa nipussa
		$virheilmoitukset = array_merge(
			$kayttajavirheilmoitukset, $asiakasvirheilmoitukset );

		if( count( $virheilmoitukset ) > 0 ) {
			View::make( 'asiakas/uusi.html', array(
				'virheilmoitukset' => $virheilmoitukset,
				'kentat' => $kentat ) );
			return;
		}

		// Käyttäjätunnuksen tiedot tallennetaan sillä ehdolla, että
		// vastaavannimistä käyttäjätunnusta ei ole jo olemassa
		if( ! $uusi_kayttaja->save() ) {
			$virheilmoitukset[] = 'Käyttäjätunnus: ' . $uusi_kayttaja->ktunnus
				. ' on jo olemassa';
			View::make( 'asiakas/uusi.html', array(
				'virheilmoitukset' => $virheilmoitukset,
				'kentat' => $kentat ) );
			return;
		}

		// Tallennetaan Asiakas-olio tietokantaan
		$uusi_asiakas->save();

		// Ohjataan käyttäjä kirjautumissivulle, jotta hän voi kirjautua
		// sisään juuri luomallaan käyttäjätunnuksella
		Redirect::to( '/kayttaja/login', array(
			'welcome' => 'Tervetuloa asiakkaaksi, ' . $params[ 'etunimi' ]
			. '! Voit nyt kirjautua sisään luomallasi käyttäjätunnuksella '
			. $params[ 'ktunnus' ] . '.' ) );
	}
}
