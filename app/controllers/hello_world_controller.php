<?php

class HelloWorldController extends BaseController {

	public static function index() {
		// make-metodi renderöi app/views-kansiossa sijaitsevia tiedostoja
		View::make('home.html');
	}

	public static function pizzat() {
		View::make('pizzat.html');
	}

	public static function sandbox() {
		// Testaa koodiasi täällä
		View::make('helloworld.html');
	}
}
