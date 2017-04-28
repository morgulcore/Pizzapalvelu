<?php

class Ongelma extends BaseModel {
	// Attribuutit
	public
		$tilausviite, // olioviite
		$ongelman_tyyppi,
		$ts_ongelma, // ts = timestamp
		$ongelman_kuvaus;

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
			$tilausviite = Tilaus::hae( $rivi[ 'tilaus_id' ] );

			// Bugtrap
			if( $tilausviite == null ) {
				exit( 'Ongelma.hae_kaikki() – Null-viite' );
			}

			$ongelmat[] = new Ongelma(
				$tilausviite,
				$rivi[ 'ongelman_tyyppi' ],
				$rivi[ 'ts_ongelma' ],
				$rivi[ 'ongelman_kuvaus' ]
			);
		}

		return $ongelmat;
	}

	// Jokainen ongelma liittyy suoranaisesti vain tiettyyn tilaukseen,
	// mutta toisaalta jokaiseen tilaukseen liittyy tietty asiakas.
	public static function hae_asiakkaaseen_liittyvat_ongelmat( $ktunnus ) {
		$asiakkaan_kaikki_tilaukset
			= Tilaus::hae_asiakkaan_tilaukset( $ktunnus );
		// Jos ei ole tilauksia, ei voi myöskään olla ongelmia
		if( count( $asiakkaan_kaikki_tilaukset ) == 0 ) {
			return array();
		}

		$asiakkaaseen_liittyvat_ongelmat = array();
		$kaikki_ongelmat = self::hae_kaikki();
		$asiakkaan_kaikki_tilaukset = Tilaus::hae_asiakkaan_tilaukset( $ktunnus );

		// Käydään jokainen ongelma yksi kerrallaan lävitse ja katsotaan,
		// liittyykö se johonkin asiakkaan tilauksista
		foreach( $kaikki_ongelmat as $ongelma ) {
			foreach( $asiakkaan_kaikki_tilaukset as $asiakkaan_tilaus ) {
				if( $ongelma->tilausviite->tilaus_id
					== $asiakkaan_tilaus->tilaus_id ) {
					$asiakkaaseen_liittyvat_ongelmat[] = $ongelma;
				}
			}
		}

		return $asiakkaaseen_liittyvat_ongelmat;
	}
}
