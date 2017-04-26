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

	// Jos parametrin arvo on null, tulkitaan sen tarkoittavan "kaikki".
	// Esim. jos molempien parametrien arvo on null, lisätään tauluun
	// mm_Asiakas_Osoite Asiakas- ja Osoite-joukkojen karteesinen tulo,
	// siis kaikki mahdolliset asiakat–osoite-parit.
	public static function lisaa_asiakas_osoite_pari( $ktunnus, $osoite_id ) {
		$kaikki_asiakkaat = Asiakas::hae_kaikki();
		$kaikki_osoitteet = Osoite::hae_kaikki();
		$lisattyjen_rivien_lkm = 0;

		foreach( $kaikki_asiakkaat as $asiakas ) {
			foreach( $kaikki_osoitteet as $osoite ) {
				if( self::tarkista_asiakas_osoite_parin_olemassaolo(
					$asiakas->ktunnus, $osoite->osoite_id ) ) {
					continue;
				} else if(
					( ! $ktunnus && ! $osoite_id ) ||
					( ! $ktunnus && $osoite->osoite_id == $osoite_id ) ||
					( ! $osoite_id && $asiakas->ktunnus == $ktunnus ) ||
					( $asiakas->ktunnus == $ktunnus && $osoite->osoite_id == $osoite_id ) ) {

					$kysely = DB::connection()->prepare(
						'insert into mm_Asiakas_Osoite values ( :ktunnus, '
						. ':osoite_id );' );
					$kysely->execute( array(
						'ktunnus' => $asiakas->ktunnus,
						'osoite_id' => $osoite->osoite_id ) );
					$lisattyjen_rivien_lkm++;
				}
			}
		}

		return $lisattyjen_rivien_lkm;
	}

	// Tarkistetaan, että taulussa mm_Asiakas_Osoite todella on rivi
	// pääavaimella ( $ktunnus, $osoite_id )
	public static function tarkista_asiakas_osoite_parin_olemassaolo(
		$ktunnus, $osoite_id ) {
		$kysely = DB::connection()->prepare(
			'select * from mm_Asiakas_Osoite where ktunnus = :ktunnus '
				. 'and osoite_id = :osoite_id limit 1;' );
		$kysely->execute( array( 'ktunnus' => $ktunnus,
			'osoite_id' => $osoite_id ) );
		$rivi = $kysely->fetch();

		return $rivi ? true : false;
	}

	public static function hae_asiakkaan_osoitteet( $ktunnus ) {
		$asiakkaan_osoitteet = array();
		$kaikki_asiakas_osoite_parit = self::hae_asiakas_osoite_parit( null, null );

		foreach( $kaikki_asiakas_osoite_parit as $ao_pari ) {
			if( $ao_pari->asiakasviite->ktunnus == $ktunnus ) {
				$asiakkaan_osoitteet[] = $ao_pari->osoiteviite;
			}
		}

		return $asiakkaan_osoitteet;
	}
}
