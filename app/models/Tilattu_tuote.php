<?php

class Tilattu_tuote extends BaseModel {
	// Attribuutit
	public $tilausviite, $tuotelaskuri, $tuotetyyppiviite,
		$tuoteversio, $lukumaara;

	// Konstruktori
	public function __construct( $attribuutit ) {
		parent::__construct( $attribuutit );
		// Seuraava attribuutti on määritelty BaseModelissa
		// $this->validaattorit = array( 'validoi_...', 'validoi_...' );
	}

	// Poistaa taulusta Tilattu_tuote kaikki rivit, joissa
	// esiintyy $tilaus_id
	public static function poista( $tilaus_id ) {
		$kysely = DB::connection()->prepare(
			'delete from Tilattu_tuote where tilaus_id = :tilaus_id;' );
		$kysely->execute( array( 'tilaus_id' => $tilaus_id ) );
	}
}
