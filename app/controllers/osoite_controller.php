<?php

class OsoiteController extends BaseController {

	public static function index( $poistettujen_ao_parien_lkm ) {
		if( ! self::kayttaja_on_yllapitaja() ) {
			return;
		}

		$viesti_ao_pareja_poistettu = null;
		if( $poistettujen_ao_parien_lkm ) {
			$viesti_ao_pareja_poistettu
				= 'Taulusta mm_Asiakas_Osoite poistettiin '
					. $poistettujen_ao_parien_lkm . ' riviä';
		}

		$lisaystoiminto_kaytossa = false;
		$lisattyjen_rivien_lkm = 0;

		if( $_POST && isset( $_POST[ 'tehty_haku' ] ) ) {
			$tehty_haku = true;

			$ktunnus = $_POST[ 'asiakasvalitsin' ] == '*' ? null
				: $_POST[ 'asiakasvalitsin' ];
			$osoite_id = $_POST[ 'osoitevalitsin' ] == '*' ? null
				: $_POST[ 'osoitevalitsin' ];

			if( isset( $_POST[ 'lisaystoiminto' ] ) ) {
				$lisaystoiminto_kaytossa = true;
				$lisattyjen_rivien_lkm
					= Osoite::lisaa_asiakas_osoite_pari( $ktunnus, $osoite_id );
			}

			$asiakas_osoite_parit
				= Osoite::hae_asiakas_osoite_parit( $ktunnus, $osoite_id );
		} else {
			$tehty_haku = false;
			$asiakas_osoite_parit = Osoite::hae_asiakas_osoite_parit(
				self::get_user_logged_in()->ktunnus, null );
		}

		$ao_parien_lkm = count( $asiakas_osoite_parit );
		$kaikki_asiakkaat = Asiakas::hae_kaikki();
		$kaikki_osoitteet = Osoite::hae_kaikki();

		View::make( 'osoite/index.html', array(
			'viesti_ao_pareja_poistettu' => $viesti_ao_pareja_poistettu,
			'tehty_haku' => $tehty_haku,
			'valittu_asiakas' => $tehty_haku ? $ktunnus : '*',
			'valittu_osoite' => $tehty_haku ? $osoite_id : '*',
			'lisaystoiminto_kaytossa' => $lisaystoiminto_kaytossa,
			'lisattyjen_rivien_lkm' => $lisattyjen_rivien_lkm,
			'asiakas_osoite_parit' => $asiakas_osoite_parit,
			'ao_parien_lkm' => $ao_parien_lkm,
			'kaikki_asiakkaat' => $kaikki_asiakkaat,
			'kaikki_osoitteet' => $kaikki_osoitteet ) );
	}

	public static function poista_valitut_asiakas_osoite_parit() {
		if( ! self::kayttaja_on_yllapitaja() ) {
			return;
		}

		$poistettujen_ao_parien_lkm = 0;

		$post_avaimet = array_keys( $_POST );
		foreach( $post_avaimet as $avain ) {
			// Tutkitaan, onko avain muotoa <ktunnus>/<osoite_id>
			if( preg_match( '/\A[a-z]+\/[1-9][0-9]*\z/', $avain ) === 1 ) {
				// Esim. "mruusu/5" jakaantuu tässä merkkijonoihin "mruusu" ja "5"
				$kaksi_mjonoa = explode( '/', $avain );
				// Bugtrap
				if( count( $kaksi_mjonoa ) != 2 ) {
					exit( 'OsoiteController.poista_valitut_asiakas_osoite_parit() '
						. '– Lukumäärävirhe' );
				}

				$asiakas = Asiakas::hae( $kaksi_mjonoa[ 0 ] );
				$osoite = Osoite::hae( $kaksi_mjonoa[ 1 ] );

				//Bugtrap
				if( ! Osoite::tarkista_asiakas_osoite_parin_olemassaolo(
					$asiakas->ktunnus, $osoite->osoite_id ) ) {
					exit( 'OsoiteController.poista_valitut_asiakas_osoite_parit() '
						. '– Paria ei olemassa' );
				}

				$asiakas_osoite_pari = new mm_Asiakas_Osoite(
					$asiakas->ktunnus, $osoite->osoite_id );

				// Nyt itse asiaan
				$asiakas_osoite_pari->poista();
				$poistettujen_ao_parien_lkm++;
			}
		}

		self::index( $poistettujen_ao_parien_lkm );
	}
}
