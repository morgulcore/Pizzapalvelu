<?php

class OngelmaController extends BaseController {

	public static function index() {
		if( ! self::kayttaja_on_yllapitaja() ) {
			return;
		}

		$ongelmat = Ongelma::hae_kaikki();
		View::make( 'ongelma/index.html', array( 'ongelmat' => $ongelmat ) );
	}
}
