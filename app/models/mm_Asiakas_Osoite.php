<?php

class mm_Asiakas_Osoite extends BaseModel {
	// Attribuutit
	public $asiakasviite, $osoiteviite;

	// Konstruktori
	public function __construct( $ktunnus, $osoite_id ) {
		if( ! self::tarkista_olemassaolo( $ktunnus, $osoite_id ) ) {
			// Jossain on bugi. Vihelletään peli poikki.
			exit( 'Luokan mm_Asiakas_Osoite konstruktorissa tapahtui kauheita!' );
		}

		$this->asiakasviite = Asiakas::hae( $ktunnus );
		$this->osoiteviite = Osoite::hae( $osoite_id );
	}

	// Poistaa taulusta mm_Asiakas_Osoite kaikki rivit, joissa
	// esiintyy $asiakas_id
	public static function poista_asiakas_id( $asiakas_id ) {
		$kysely = DB::connection()->prepare(
			'delete from mm_Asiakas_Osoite where asiakas_id = :asiakas_id;' );
		$kysely->execute( array( 'asiakas_id' => $asiakas_id ) );
	}

	public static function hae_asiakkaan_osoitteet( $ktunnus ) {
		$asiakkaan_osoitteet = array();
		$asiakas_osoite_parit = self::hae_kaikki();

		foreach( $asiakas_osoite_parit as $ao_pari ) {
			if( $ao_pari->asiakasviite->ktunnus == $ktunnus ) {
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
				$rivi[ 'ktunnus' ], $rivi[ 'osoite_id' ]
			);
		}

		return $asiakas_osoite_parit;
	}

	// Tarkistetaan, että taulussa mm_Asiakas_Osoite todella on rivi
	// pääavaimella ( $ktunnus, $osoite_id )
	public static function tarkista_olemassaolo( $ktunnus, $osoite_id ) {
		$kysely = DB::connection()->prepare(
			'select * from mm_Asiakas_Osoite where ktunnus = :ktunnus '
				. 'and osoite_id = :osoite_id limit 1;' );
		$kysely->execute( array( 'ktunnus' => $ktunnus,
			'osoite_id' => $osoite_id ) );
		$rivi = $kysely->fetch();

		return $rivi ? true : false;
	}
}
