<?php

class EtusivuController extends BaseController {

	public static function index() {
		View::make( 'etusivu.html' );
	}

	public static function sandbox() {
		echo 'PHP version: ' . phpversion();
	}
}
