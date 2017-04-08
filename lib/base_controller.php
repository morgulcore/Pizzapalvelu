<?php

class BaseController {

	public static function get_user_logged_in() {
		// Katsotaan onko user-avain sessiossa
		if( isset( $_SESSION[ 'user' ] ) ) {
			$ktunnus = $_SESSION[ 'user' ];
			$sisaankirjautunut_asiakas = Asiakas::hae( $ktunnus );

			// Bugtrap
			if( ! $sisaankirjautunut_asiakas ) {
				exit( 'BaseController::get_user_logged_in() '
					. '– Tapahtui hirveitä!' );
			}

			return $sisaankirjautunut_asiakas;
		}

		return null;
	}

	public static function check_logged_in(){
		// Toteuta kirjautumisen tarkistus tähän.
		// Jos käyttäjä ei ole kirjautunut sisään, ohjaa hänet toiselle
		// sivulle (esim. kirjautumissivulle).
	}
}
