<?php

class KayttajaController extends BaseController {

	// Sisäänkirjautumissivun näyttäminen
	public static function login() {
		View::make( 'kayttaja/login.html' );
	}

	// Sisäänkirjautumisen käsittely
	public static function handle_login() {
		$params = $_POST;

		$kayttaja = Kayttaja::authenticate(
			$params[ 'username' ], $params[ 'password' ] );

		if( !$kayttaja ) {
			View::make( 'kayttaja/login.html', array(
				'kirjautumisvirhe' => 'Väärä käyttäjätunnus tai salasana!' ) );
		} else {
			$_SESSION[ 'user' ] = $kayttaja->ktunnus;
			Redirect::to( '/', array(
				'tervetuloa_takaisin' => 'Tervetuloa takaisin, '
				. $kayttaja->ktunnus . '!' ) );
		}
	}
}
