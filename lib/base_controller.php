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

	public static function check_logged_in() {
		if( ! isset( $_SESSION[ 'user' ] ) ) {
			Redirect::to( '/asiakas/kirjaudu', array(
				'kirjaudu_ensin_sisaan' => 'Kirjaudu ensin sisään!' ) );
		}
	}

	public static function kayttaja_on_yllapitaja() {
		self::check_logged_in();

		$kayttaja = Asiakas::hae( $_SESSION[ 'user' ] );
		if( ! $kayttaja->on_paakayttaja ) {
			Redirect::to( '/asiakas/kirjaudu', array(
				'kirjaudu_ensin_sisaan' => 'Kirjaudu sisään ylläpitäjänä' ) );
		}
	}
}
