<?php

class Ongelma extends BaseModel {
	// Attribuutit
	public $tilausviite, $ongelman_tyyppi, $ts_ongelma, $ongelman_kuvaus;

	// Konstruktori
	public function __construct( $tilausviite, $ongelman_tyyppi,
		$ts_ongelma, $ongelman_kuvaus ) { // ts = timestamp
		$this->tilausviite = $tilausviite;
		$this->ongelman_tyyppi = $ongelman_tyyppi;
		$this->ts_ongelma = $ts_ongelma;
		$this->ongelman_kuvaus =$ongelman_kuvaus;
	}

	// Poistaa taulusta Ongelma kaikki rivit, joissa
	// esiintyy $tilaus_id
	public static function poista( $tilaus_id ) {
		$kysely = DB::connection()->prepare(
			'delete from Ongelma where tilaus_id = :tilaus_id;' );
		$kysely->execute( array( 'tilaus_id' => $tilaus_id ) );
	}

	public static function hae_kaikki() {
		$kysely = DB::connection()->prepare(
			'select * from Ongelma;' );
		$kysely->execute();
		$rivit = $kysely->fetchAll();

		$ongelmat = array();

		foreach( $rivit as $rivi ) {
			$ongelmat[] = new Ongelma(
				null,
				$rivi[ 'ongelman_tyyppi' ],
				$rivi[ 'ts_ongelma' ],
				$rivi[ 'ongelman_kuvaus' ]
			);
		}

		return $ongelmat;
	}
}
