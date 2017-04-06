<?php

class mm_Asiakas_Osoite extends BaseModel {
	// Attribuutit
	public $asiakasviite, $osoiteviite;

	// Konstruktori
	public function __construct( $asiakas_id, $osoite_id ) {
		if( ! self::tarkista_olemassaolo( $asiakas_id, $osoite_id ) ) {
			// Jossain on bugi. Vihelletään peli poikki.
			exit( 'Luokan mm_Asiakas_Osoite konstruktorissa tapahtui kauheita!' );
		}

		$this->asiakasviite = Asiakas::find( $asiakas_id );
		$this->osoiteviite = Osoite::hae( $osoite_id );
	}

	public static function hae_asiakkaan_osoitteet( $asiakas_id ) {
		$asiakkaan_osoitteet = array();
		$asiakas_osoite_parit = self::hae_kaikki();

		foreach( $asiakas_osoite_parit as $ao_pari ) {
			if( $ao_pari->asiakasviite->asiakas_id == $asiakas_id ) {
				$asiakkaan_osoitteet[] = $ao_pari->osoiteviite;
			}
		}

		return $asiakkaan_osoitteet;
	}

	// Hakee kaikki rivit taulusta ja palauttaa ne taulukkona
	// asiakas–osoite-pareja
	public static function hae_kaikki() {
		$kysely = DB::connection()->prepare(
			'select * from mm_Asiakas_Osoite;' );
		$kysely->execute();
		$rivit = $kysely->fetchAll();

		$asiakas_osoite_parit = array();

		foreach( $rivit as $rivi ) {
			$asiakas_osoite_parit[] = new mm_Asiakas_Osoite(
				$rivi[ 'asiakas_id' ], $rivi[ 'osoite_id' ]
			);
		}

		return $asiakas_osoite_parit;
	}

	// Tarkistetaan, että taulussa mm_Asiakas_Osoite todella on rivi
	// pääavaimella ( $asiakas_id, $osoite_id )
	public static function tarkista_olemassaolo( $asiakas_id, $osoite_id ) {
		$kysely = DB::connection()->prepare(
			'select * from mm_Asiakas_Osoite where asiakas_id = :asiakas_id '
				. 'and osoite_id = :osoite_id limit 1;' );
		$kysely->execute( array( 'asiakas_id' => $asiakas_id,
			'osoite_id' => $osoite_id ) );
		$rivi = $kysely->fetch();

		return $rivi ? true : false;
	}
}