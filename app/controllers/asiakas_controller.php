<?php

class AsiakasController extends BaseController {

	// Renderöidään lomake, jolla uusi asiakas rekisteröidään tietokantaan
	public static function uusi() {
		View::make( 'asiakas/uusi.html' );
	}

	// Rekisteröidään uusi asiakastili ja siihen liittyvä käyttäjätunnus
	public static function rekisteroi() {
		$params = $_POST;

		$uusi_kayttaja = new Kayttaja( array(
			'ktunnus' => $params[ 'ktunnus' ],
			'salasana' => $params[ 'salasana' ],
			'tyyppi' => 0 ) );

		// Asiakastilin luominen keskeytetään, jos molemmissa nimikentissä
		// ei ole tekstiä
		if( $params[ 'etunimi' ] == null || $params[ 'sukunimi' ] == null ) {
			Redirect::to( '/', array(
				'message' => 'Etu- ja sukunimesi ovat pakollisia tietoja' ) );
			return;
		}

		// Käyttäjätunnuksen tiedot tallennetaan sillä ehdolla, että
		// vastaavannimistä käyttäjätunnusta ei ole jo olemassa
		if( !$uusi_kayttaja->save() ) {
			Redirect::to( '/', array(
				'message' => 'Käyttäjätunnus ' . $params[ 'ktunnus' ]
				. ' on jo olemassa' ) );
			return;
		}

		// Luodaan Asiakas-olio
		$uusi_asiakas = new Asiakas( array(
			'ktunnus' => $params[ 'ktunnus' ],
			'etunimi' => $params[ 'etunimi' ],
			'sukunimi' => $params[ 'sukunimi' ] ) );

		// Tallennetaan Asiakas-olio tietokantaan
		$uusi_asiakas->save();

		// Vielä täytyy asettaa salasana asiakastiliä vastaavalle
		// käyttäjätunnukselle
		$query = DB::connection()->prepare(
			'update Kayttaja set salasana = :salasana where ktunnus = :ktunnus' );
		$query->execute( array(
			'ktunnus' => $params[ 'ktunnus' ],
			'salasana' => $params[ 'salasana' ] ) );

		Redirect::to( '/', array(
			'message' => 'Tervetuloa asiakkaaksi, ' . $params[ 'etunimi' ]
			. '! Voit nyt kirjautua sisään luomallasi käyttäjätunnuksella '
			. $params[ 'ktunnus' ] . '.' ) );
	}

	/*
	public static function handle_login() {
		$params = $_POST;

		$kayttaja = Kayttaja::authenticate(
			$params[ 'username' ], $params[ 'password' ] );

		if( !$kayttaja ) {
			View::make( 'kayttaja/login.html', array(
				'error' => 'Väärä käyttäjätunnus tai salasana!',
				'username' => $params[ 'username' ] ) );
		} else {
			$_SESSION[ 'user' ] = $kayttaja->ktunnus;

			Redirect::to( '/', array('message' => 'Tervetuloa takaisin, '
				. $kayttaja->ktunnus . '!' ) );
		}
	} */
}
