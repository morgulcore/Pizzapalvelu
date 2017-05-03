<?php

class OngelmaController extends BaseController {

	public static function index( $ktunnus ) {
		if( ! self::kayttaja_on_yllapitaja() ) {
			return;
		}

		$ongelmat = array();

		// Listataan $ktunnukseen liittyvät ongelmat
		if( $ktunnus ) {
			$ongelmat = Ongelma::hae_asiakkaaseen_liittyvat_ongelmat( $ktunnus );
		} else { // Listataan kaikki ongelmat
			$ongelmat = Ongelma::hae_kaikki();
		}

		View::make( 'ongelma/index.html', array( 'ongelmat' => $ongelmat ) );
	}
}
