<?php

class Osoite extends BaseModel {
	// Attribuutit
	public $osoite_id, $lahiosoite, $postinumero, $postitoimipaikka;

	// Konstruktori
	public function __construct( $attribuutit ) {
		parent::__construct( $attribuutit );
		// Seuraava attribuutti on määritelty BaseModelissa
		// $this->validaattorit = array( 'validoi_...', 'validoi_...' );
	}

	// Haetaan tietokannasta määrätty osoite
	public static function hae( $osoite_id ) {
		$kysely = DB::connection()->prepare(
			'select * from Osoite where osoite_id = :osoite_id limit 1;' );
		$kysely->execute( array( 'osoite_id' => $osoite_id ) );
		$rivi = $kysely->fetch();

		if( $rivi ) {
			$osoite = new Osoite( array(
				'osoite_id' => $rivi[ 'osoite_id' ],
				'lahiosoite' => $rivi[ 'lahiosoite' ],
				'postinumero' => $rivi[ 'postinumero' ],
				'postitoimipaikka' => $rivi[ 'postitoimipaikka' ]
			) );

			return $osoite;
		}

		return null;
	}

	public static function hae_kaikki_asiakas_osoite_parit() {
		$kysely = DB::connection()->prepare( 'select * from mm_Asiakas_Osoite;' );
		$kysely->execute();
		$rivit = $kysely->fetchAll();

		$asiakas_osoite_parit = array();

		foreach( $rivit as $rivi ) {
			$asiakas_osoite_parit[] = new mm_Asiakas_Osoite(
				$rivi[ 'ktunnus' ], $rivi[ 'osoite_id' ] );
		}

		return $asiakas_osoite_parit;
	}
}
