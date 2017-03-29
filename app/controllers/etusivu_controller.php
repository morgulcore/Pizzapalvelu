<?php

class EtusivuController extends BaseController {

	public static function index() {
		View::make( 'etusivu.html' );
	}

	public static function sandbox() {
		// Testaa koodiasi täällä
		// View::make('helloworld.html');
		// $asiakkaat = Asiakas::all();
		// $kayttajat = Kayttaja::all();
		// $tero = Kayttaja::find( 'tmansikka' );
		// $olematon = Kayttaja::find( 'olematon' );
		// Kint::dump( $kayttajat );
		$uusi_asiakas = new Asiakas( array(
			'ktunnus' => 'jkristus',
			'etunimi' => 'Jeesus',
			'sukunimi' => 'Kristus',
			'puhelinnumero' => '040 123 4567',
			'sahkopostiosoite' => 'jeesus.kristus@heaven.com'
		) );
		$uusi_asiakas->save();
		Kint::dump( $uusi_asiakas );
	}
}
