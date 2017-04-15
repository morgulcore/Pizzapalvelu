<?php

class Tuote extends BaseModel {
	// Attribuutit
	public
		$tuotetyyppiviite, // olioviite
		$tuoteversio,
		$hinta;

	// Konstruktori
	public function __construct( $attribuutit ) {
		parent::__construct( $attribuutit );
		$this->tuotetyyppiviite
			= Tuotetyyppi::hae( $attribuutit[ 'tuotetyyppi_id' ] );
	}

	// Taulun Tuote pääavain on kaksiosainen, joten funktiossa tarvitaan
	// kaksi parametriä. Palauttaa parametrien yksilöimän rivin taulusta.
	public static function hae( $tuotetyyppi_id, $tuoteversio ) {
		$kysely = DB::connection()->prepare(
			'select * from Tuote where tuotetyyppi_id = :tuotetyyppi_id and '
				. 'tuoteversio = :tuoteversio limit 1;' );
		$kysely->execute( array( 'tuotetyyppi_id' => $tuotetyyppi_id,
			'tuoteversio' => $tuoteversio ) );

		$rivi = $kysely->fetch();
		if( $rivi ) {
			$tuote = new Tuote( $rivi );

			return $tuote;
		}

		return null;
	}
}
