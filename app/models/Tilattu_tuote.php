<?php

class Tilattu_tuote extends BaseModel {
	// Attribuutit
	public
		$tilausviite,
		$tuotelaskuri,
		$tuoteviite,
		$lukumaara;

	// Konstruktori
	public function __construct( $attribuutit ) {
		parent::__construct( $attribuutit );

		$this->tilausviite = Tilaus::hae( $attribuutit[ 'tilaus_id' ] );
		// Bugtrap
		if( $this->tilausviite == null ) {
			exit( 'Tilattu_tuote.__construct() – $tilausviite == null' );
		}

		$this->tuoteviite = Tuote::hae(
			$attribuutit[ 'tuotetyyppi_id' ], $attribuutit[ 'tuoteversio' ] );
		// Bugtrap
		if( $this->tuoteviite == null ) {
			exit( 'Tilattu_tuote.__construct() – $this->tuoteviite' );
		}
	}

	public static function hae_kaikki() {
		$kysely = DB::connection()->prepare( 'select * from Tilattu_tuote;' );
		$kysely->execute();
		$rivit = $kysely->fetchAll();

		$tilatut_tuotteet = array();

		foreach( $rivit as $rivi ) {
			$tilatut_tuotteet[] = new Tilattu_tuote( $rivi );
		}

		return $tilatut_tuotteet;
	}

	// Poistaa taulusta Tilattu_tuote kaikki rivit, joissa
	// esiintyy $tilaus_id
	public static function poista( $tilaus_id ) {
		$kysely = DB::connection()->prepare(
			'delete from Tilattu_tuote where tilaus_id = :tilaus_id;' );
		$kysely->execute( array( 'tilaus_id' => $tilaus_id ) );
	}
}
