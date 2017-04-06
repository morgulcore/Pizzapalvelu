<?php

class Tilaus extends BaseModel {
	// Attribuutit. Lyhenteet: ts = timestamp, tak = toimitusajankohta.
	public $tilaus_id, $asiakasviite, $ts_tilauksen_teko, $ts_tak_toivottu,
		$ts_tak_toteutunut, $osoiteviite;

	// Konstruktori
	public function __construct( $attribuutit ) {
		parent::__construct( $attribuutit );
		// Seuraava attribuutti on määritelty BaseModelissa
		// $this->validaattorit = array( 'validoi_...', 'validoi_...' );
	}

	// Poistaa taulusta Tilaus kaikki tiettyyn asiakkaaseen liittyvät tilaukset
	public static function poista_asiakkaan_tilaukset( $asiakas_id ) {
		// Taulun Tilaus pääavain tilaus_id on tauluissa Ongelma
		// ja Tilattu_tuote viiteavaimina
		$poistettavan_asiakkaan_tilaukset
			= self::hae_asiakkaan_tilaukset( $asiakas_id );
		foreach( $poistettavan_asiakkaan_tilaukset as $tilaus ) {
			// Poistetaan mikäli poistettavaa on
			Ongelma::poista( $tilaus->tilaus_id );
			Tilattu_tuote::poista( $tilaus->tilaus_id );
		}

		$kysely = DB::connection()->prepare(
			'delete from Tilaus where asiakas_id = :asiakas_id;' );
		$kysely->execute( array( 'asiakas_id' => $asiakas_id ) );
	}

	// Hakee taulusta Tilaus yksittäisen asiakkaan kaikki tilaukset
	public static function hae_asiakkaan_tilaukset( $asiakas_id ) {
		$kaikki_tilaukset = self::hae_kaikki();

		$asiakkaan_tilaukset = array();

		foreach( $kaikki_tilaukset as $tilaus ) {
			if( $tilaus->asiakasviite->asiakas_id == $asiakas_id ) {
				$asiakkaan_tilaukset[] = $tilaus;
			}
		}

		return $asiakkaan_tilaukset;
	}

	// Haetaan tietokannasta tietty yksittäinen tilaus
	public static function hae( $tilaus_id ) {
		$kysely = DB::connection()->prepare(
			'select * from Tilaus where tilaus_id = :tilaus_id limit 1;' );
		$kysely->execute( array( 'tilaus_id' => $tilaus_id ) );
		$rivi = $kysely->fetch();

		if( $rivi ) {
			$tilaus = new Tilaus( array(
				'tilaus_id' => $rivi[ 'tilaus_id' ],
				'asiakasviite' => Asiakas::find( $rivi[ 'asiakas_id' ] ),
				'ts_tilauksen_teko' => $rivi[ 'ts_tilauksen_teko' ],
				'ts_tak_toivottu' => $rivi[ 'ts_tak_toivottu' ],
				'ts_tak_toteutunut' => $rivi[ 'ts_tak_toteutunut' ],
				'osoiteviite' => Osoite::hae( $rivi[ 'osoite_id' ] )
			) );

			return $tilaus;
		}

		return null;
	}

	public static function hae_kaikki() {
		$kysely = DB::connection()->prepare(
			'select * from Tilaus;' );
		$kysely->execute();
		$rivit = $kysely->fetchAll();

		$tilaukset = array();

		foreach( $rivit as $rivi ) {
			$tilaukset[] = new Tilaus( array(
				'tilaus_id' => $rivi[ 'tilaus_id' ],
				'asiakasviite' => Asiakas::find( $rivi[ 'asiakas_id' ] ),
				'ts_tilauksen_teko' => $rivi[ 'ts_tilauksen_teko' ],
				'ts_tak_toivottu' => $rivi[ 'ts_tak_toivottu' ],
				'ts_tak_toteutunut' => $rivi[ 'ts_tak_toteutunut' ],
				'osoiteviite' => Osoite::hae( $rivi[ 'osoite_id' ] )
			) );
		}

		return $tilaukset;
	}
}
