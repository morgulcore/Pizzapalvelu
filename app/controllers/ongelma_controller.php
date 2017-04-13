<?php

class OngelmaController extends BaseController {

	public static function index() {
		$ongelmat = Ongelma::hae_kaikki();
		View::make( 'ongelma/index.html', array( 'ongelmat' => $ongelmat ) );
	}
}
