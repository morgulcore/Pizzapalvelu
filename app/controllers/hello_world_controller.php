<?php

// require 'app/models/Asiakas.php';

class HelloWorldController extends BaseController {

	public static function index() {
		// make-metodi renderöi app/views-kansiossa sijaitsevia tiedostoja
		View::make( 'home.html' );
	}

	public static function tuotteet() {
		View::make( 'tuotteet.html' );
	}

	public static function login() {
		View::make( 'login.html' );
	}

	public static function sandbox() {
		// Testaa koodiasi täällä
		// View::make('helloworld.html');
		$asiakkaat = Asiakas::all();
		Kint::dump( $asiakkaat );
	}
}
