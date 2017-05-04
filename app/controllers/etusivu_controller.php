<?php

class EtusivuController extends BaseController {

	public static function index() {
		// self::get_user_logged_in();
		View::make( 'etusivu.html' );
	}

	public static function sandbox() {
		echo 'PHP version: ' . phpversion();
	}
}
