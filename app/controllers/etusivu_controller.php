<?php

class EtusivuController extends BaseController {

	public static function index() {
		View::make( 'etusivu.html' );
	}

	public static function sandbox() {
		$uusi_asiakas = new Asiakas( array(
			'ktunnus' => 'jkristus',
			'etunimi' => 'Xx',
			'sukunimi' => 'Kristus',
			'puhelinnumero' => '040 123 4567',
			'sahkopostiosoite' => 'jeesus.kristus@heaven.com'
		) );
		$virheet = $uusi_asiakas->virheilmoitukset();
		Kint::dump( $virheet );
	}
}
