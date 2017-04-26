<?php

class OsoiteController extends BaseController {

	public static function index() {
		if( ! self::kayttaja_on_yllapitaja() ) {
			return;
		}

		if( $_POST && $_POST[ 'tehty_haku' ] ) {
			$tehty_haku = true;

			$ktunnus = $_POST[ 'asiakasvalitsin' ] == '*' ? null
				: $_POST[ 'asiakasvalitsin' ];
			$osoite_id = $_POST[ 'osoitevalitsin' ] == '*' ? null
				: $_POST[ 'osoitevalitsin' ];

			$asiakas_osoite_parit
				= Osoite::hae_asiakas_osoite_parit( $ktunnus, $osoite_id );
		} else {
			$tehty_haku = false;
			$asiakas_osoite_parit = Osoite::hae_asiakas_osoite_parit(
				self::get_user_logged_in()->ktunnus, null );
		}

		$ao_parien_lkm = count( $asiakas_osoite_parit );
		$kaikki_asiakkaat = Asiakas::hae_kaikki();
		$kaikki_osoitteet = Osoite::hae_kaikki();

		View::make( 'osoite/index.html', array(
			'tehty_haku' => $tehty_haku,
			'valittu_asiakas' => $tehty_haku ? $ktunnus : '*',
			'valittu_osoite' => $tehty_haku ? $osoite_id : '*',
			'asiakas_osoite_parit' => $asiakas_osoite_parit,
			'ao_parien_lkm' => $ao_parien_lkm,
			'kaikki_asiakkaat' => $kaikki_asiakkaat,
			'kaikki_osoitteet' => $kaikki_osoitteet ) );
	}
}
