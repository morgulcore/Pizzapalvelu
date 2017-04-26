<?php

class mm_Asiakas_Osoite extends BaseModel {
	// Attribuutit
	public $asiakasviite, $osoiteviite;

	// Konstruktori
	public function __construct( $ktunnus, $osoite_id ) {
		if( ! Osoite::tarkista_asiakas_osoite_parin_olemassaolo(
			$ktunnus, $osoite_id ) ) {
			// Jossain on bugi. Vihelletään peli poikki.
			exit( 'Luokan mm_Asiakas_Osoite konstruktorissa tapahtui kauheita!' );
		}

		$this->asiakasviite = Asiakas::hae( $ktunnus );
		$this->osoiteviite = Osoite::hae( $osoite_id );
	}

	// Poistaa oliota vastaavan rivin taulusta mm_Asiakas_Osoite
	public function poista() {
		$kysely = DB::connection()->prepare(
			'delete from mm_Asiakas_Osoite where ktunnus = :ktunnus and '
				. 'osoite_id = :osoite_id;' );
		$kysely->execute( array(
			'ktunnus' => $this->asiakasviite->ktunnus,
			'osoite_id' => $this->osoiteviite->osoite_id ) );
	}

	// Tämäkin pitäisi siirtää luokkaan Osoite.
	// Poistaa taulusta mm_Asiakas_Osoite kaikki rivit, joissa
	// esiintyy $ktunnus (pääavaimen toinen osa)
	public static function poista_ktunnus( $ktunnus ) {
		// Bugtrap
		if( Asiakas::hae( $ktunnus ) == null ) {
			exit( 'mm_Asiakas_Osoite::poista_ktunnus() – Olematon ktunnus' );
		}

		$kysely = DB::connection()->prepare(
			'delete from mm_Asiakas_Osoite where ktunnus = :ktunnus;' );
		$kysely->execute( array( 'ktunnus' => $ktunnus ) );
	}
}
