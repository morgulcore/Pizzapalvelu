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
				'error' => 'Väärä käyttäjätunnus tai salasana!',
				'username' => $params[ 'username' ] ) );
		} else {
			$_SESSION[ 'user' ] = $kayttaja->ktunnus;

			Redirect::to( '/', array('message' => 'Tervetuloa takaisin, '
				. $kayttaja->ktunnus . '!' ) );
		}
	}
}
