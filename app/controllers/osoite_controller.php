<?php

class OsoiteController extends BaseController {

	public static function index() {
		if( ! self::kayttaja_on_yllapitaja() ) {
			return;
		}

		$asiakas_osoite_parit = Osoite::hae_kaikki_asiakas_osoite_parit();
		$kaikki_asiakkaat = Asiakas::hae_kaikki();
		View::make( 'osoite/index.html', array(
			'asiakas_osoite_parit' => $asiakas_osoite_parit,
			'kaikki_asiakkaat' => $kaikki_asiakkaat ) );
	}
}
