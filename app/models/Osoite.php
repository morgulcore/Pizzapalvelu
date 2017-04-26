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

	// Haetaan tietokannasta kaikki Osoite-oliot
	public static function hae_kaikki() {
		$kysely = DB::connection()->prepare( 'select * from Osoite;' );
		$kysely->execute();
		$rivit = $kysely->fetchAll();

		$osoitteet = array();

		foreach( $rivit as $rivi ) {
			$osoitteet[] = new Osoite( $rivi );
		}

		return $osoitteet;
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

	public static function hae_asiakas_osoite_parit( $ktunnus, $osoite_id ) {
		if( $ktunnus == null && $osoite_id == null ) {
			$selectin_loppuosa = ';';
		} else if( $ktunnus == null ) {
			$selectin_loppuosa = ' where osoite_id = :osoite_id;';
		} else if( $osoite_id == null ) {
			$selectin_loppuosa = ' where ktunnus = :ktunnus;';
		} else {
			$selectin_loppuosa
				= ' where ktunnus = :ktunnus and osoite_id = :osoite_id;';
		}

		$kysely = DB::connection()->prepare(
			'select * from mm_Asiakas_Osoite' . $selectin_loppuosa );

		if( $ktunnus == null && $osoite_id == null ) {
			$kysely->execute();
		} else if( $ktunnus == null ) {
			$kysely->execute( array( 'osoite_id' => $osoite_id ) );
		} else if( $osoite_id == null ) {
			$kysely->execute( array( 'ktunnus' => $ktunnus ) );
		} else {
			$kysely->execute( array(
				'ktunnus' => $ktunnus, 'osoite_id' => $osoite_id ) );
		}

		$rivit = $kysely->fetchAll();

		$asiakas_osoite_parit = array();

		foreach( $rivit as $rivi ) {
			$asiakas_osoite_parit[] = new mm_Asiakas_Osoite(
				$rivi[ 'ktunnus' ], $rivi[ 'osoite_id' ] );
		}

		return $asiakas_osoite_parit;
	}

	public static function hae_asiakkaan_osoitteet( $ktunnus ) {
		$asiakkaan_osoitteet = array();
		$asiakas_osoite_parit = self::hae_kaikki_asiakas_osoite_parit();

		foreach( $asiakas_osoite_parit as $ao_pari ) {
			if( $ao_pari->asiakasviite->ktunnus == $ktunnus ) {
				$asiakkaan_osoitteet[] = $ao_pari->osoiteviite;
			}
		}

		return $asiakkaan_osoitteet;
	}
}
