<?php

class KayttajaController extends BaseController {

	// Sisäänkirjautumissivun näyttäminen
	public static function login() {
		View::make( 'asiakas/login.html' );
	}

	// Sisäänkirjautumisen käsittely
	public static function handle_login() {
		$params = $_POST;

		$asiakas = Asiakas::todenna(
			$params[ 'username' ], $params[ 'password' ] );

		if( !$asiakas ) {
			View::make( 'asiakas/login.html', array(
				'kirjautumisvirhe' => 'Väärä käyttäjätunnus tai salasana!' ) );
		} else {
			$_SESSION[ 'user' ] = $asiakas->ktunnus;
			Redirect::to( '/', array(
				'tervetuloa_takaisin' => 'Tervetuloa takaisin, '
				. $asiakas->ktunnus . '!' ) );
		}
	}
}
