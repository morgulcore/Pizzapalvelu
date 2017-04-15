<?php

class Tuotetyyppi extends BaseModel {
	// Attribuutit
	public
		$tuotetyyppi_id,
		$tuotekategoria,
		$hintamuunnosviite,
		$tuotenimi,
		$tuotekuvaus,
		$kuva_tuotteesta;

	// Konstruktori
	public function __construct( $attribuutit ) {
		parent::__construct( $attribuutit );
	}

	public static function hae( $tuotetyyppi_id ) {
		$kysely = DB::connection()->prepare(
			'select * from Tuotetyyppi where tuotetyyppi_id = :tuotetyyppi_id limit 1;' );
		$kysely->execute( array( 'tuotetyyppi_id' => $tuotetyyppi_id ) );

		$rivi = $kysely->fetch();
		if( $rivi ) {
			$tuotetyyppi = new Tuotetyyppi( $rivi );

			return $tuotetyyppi;
		}

		return null;
	}
}
