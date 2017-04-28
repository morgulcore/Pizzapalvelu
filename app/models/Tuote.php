<?php

class Tuote extends BaseModel {
	// Attribuutit
	public
		$tuotetyyppiviite, // olioviite
		$tuoteversio,
		$hinta; // staattinen hinta (siis hinta ilman hintamuunnosta)

	// Konstruktori
	public function __construct( $attribuutit ) {
		parent::__construct( $attribuutit );
		$this->tuotetyyppiviite
			= Tuotetyyppi::hae( $attribuutit[ 'tuotetyyppi_id' ] );
	}

	// Dynaaminen hinta saadaan, kun tuotteen (staattinen) hinta kerrotaan
	// taulusta Hintamuunnos saadulla kertoimella. Näin tuotteen hintaa
	// voidaan automaattisesti vaihdella esim. vuorokaudenajan mukaan.
	public function dynaaminen_hinta() {
		return $this->hinta;
	}

	public static function hae_kaikki() {
		$kysely = DB::connection()->prepare( 'select * from Tuote;' );
		$kysely->execute();
		$rivit = $kysely->fetchAll();

		$tuotteet = array();

		foreach( $rivit as $rivi ) {
			$tuotteet[] = new Tuote( $rivi );
		}

		return $tuotteet;
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
