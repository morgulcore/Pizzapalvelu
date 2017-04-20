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
			return false;
		}

		return true;
	}

	public static function kayttaja_on_yllapitaja() {
		if( ! self::check_logged_in() ) {
			return false;
		}

		$kayttaja = Asiakas::hae( $_SESSION[ 'user' ] );
		if( ! $kayttaja->on_paakayttaja ) {
			Redirect::to( '/asiakas/kirjaudu', array(
				'kirjaudu_ensin_sisaan' => 'Kirjaudu sisään ylläpitäjänä' ) );
			return false;
		}

		return true;
	}

	// Tavallisen käyttäjän (asiakkaan) pitää voida katsoa ja muuttaa
	// vain sellaisia asioita, jotka liittyvät häneen. Esimerkkinä
	// tästä käy esittelysivu. Ei ole mielekästä antaa asiakkaiden
	// tutkia eikä varsinkaan muokata toistensa esittelysivuja.
	public static function kayttajalle_kuulumaton_asia( $ktunnus, $viesti ) {
		if( ! self::get_user_logged_in()->on_paakayttaja
			&& self::get_user_logged_in()->ktunnus != $ktunnus ) {
			Redirect::to( '/asiakas/kirjaudu', array(
				'riittamattomat_oikeudet' => $viesti ) );
			return true;
		}

		return false;
	}
}
