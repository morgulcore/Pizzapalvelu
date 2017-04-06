<?php

class BaseController {
	public static function get_user_logged_in() {
		// Katsotaan onko user-avain sessiossa
		if( isset( $_SESSION[ 'user' ] ) ) {
			$ktunnus = $_SESSION[ 'user' ];
			$asiakas_id = Asiakas::hae_asiakas_id( $ktunnus );
			$sisaankirjautunut_asiakas = Asiakas::find( $asiakas_id );

			return $sisaankirjautunut_asiakas;
		}

		return null;
	}

	public static function check_logged_in(){
		// Toteuta kirjautumisen tarkistus tähän.
		// Jos käyttäjä ei ole kirjautunut sisään, ohjaa hänet toiselle sivulle (esim. kirjautumissivulle).
	}
}
