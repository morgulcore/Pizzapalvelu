<?php

class Tilaus extends BaseModel {
	// Attribuutit. Lyhenteet: ts = timestamp, tak = toimitusajankohta.
	public
		$tilaus_id,
		$asiakasviite, // viite, olioviite
		$ts_tilauksen_teko,
		$ts_tak_toivottu,
		$ts_tak_toteutunut,
		$osoiteviite;

	// Konstruktori
	public function __construct( $attribuutit ) {
		parent::__construct( $attribuutit );
		$this->asiakasviite = Asiakas::hae( $attribuutit[ 'ktunnus' ] );
		$this->osoiteviite = Osoite::hae( $attribuutit[ 'osoite_id' ] );
		$this->validaattorit = array( 'validoi_ts_tak_toivottu' );
	}

	// Tilaus-olion tallentaminen tietokantaan
	public function tallenna() {
		$kysely = DB::connection()->prepare(
			'insert into Tilaus ( ktunnus, ts_tilauksen_teko, ts_tak_toivottu, '
				. 'ts_tak_toteutunut, osoite_id ) values ( :ktunnus, '
				. ':ts_tilauksen_teko, :ts_tak_toivottu, :ts_tak_toteutunut, '
				. ':osoite_id );' );
		$kysely->execute( array(
			'ktunnus' => $this->asiakasviite->ktunnus,
			'ts_tilauksen_teko' => $this->ts_tilauksen_teko,
			'ts_tak_toivottu' => $this->ts_tak_toivottu,
			'ts_tak_toteutunut' => $this->ts_tak_toteutunut,
			'osoite_id' => $this->osoiteviite->osoite_id
		) );
	}

	public function validoi_ts_tak_toivottu() {
		// Toivottu toimitusajankohta saa olla tyhjä merkkijono, jolloin
		// sen merkitys on "mahdollisimman pian".
		if( BaseModel::tyhja_merkkijono( $this->ts_tak_toivottu ) ) {
			return array();
		}

		$virheet = array();

		if( ! $this->on_validi_timestamp( $this->ts_tak_toivottu ) ) {
			$virheet[] = 'Toivottu toimitusajankohta: Väärä muoto tai liian '
				. 'kaukana nykyajasta. Esimerkki hyväksyttävästä arvosta '
				. 'on 2017-04-19 14:59:23';
		}

		return $virheet;
	}

	// Esimerkki validista timestamp-arvosta: "2017-04-19 14:59:23"
	private function on_validi_timestamp( $timestamp ) {
		// \A tarkoittaa merkkijonon alkua, \z sen loppua.
		$sl = '/\A20[12][0-9]-[01][0-9]-[0123][0-9] [012][0-9]:[0-5][0-9]:[0-5][0-9]\z/';

		if( preg_match( $sl, $timestamp ) === 0 ) {
			return false;
		}

		return true;
	}

	// Poistaa taulusta Tilaus kaikki tiettyyn asiakkaaseen liittyvät tilaukset
	public static function poista_asiakkaan_tilaukset( $ktunnus ) {
		// Taulun Tilaus pääavain tilaus_id on tauluissa Ongelma
		// ja Tilattu_tuote viiteavaimina
		$poistettavan_asiakkaan_tilaukset
			= self::hae_asiakkaan_tilaukset( $ktunnus );
		foreach( $poistettavan_asiakkaan_tilaukset as $tilaus ) {
			// Poistetaan mikäli poistettavaa on
			Ongelma::poista( $tilaus->tilaus_id );
			Tilattu_tuote::poista( $tilaus->tilaus_id );
		}

		$kysely = DB::connection()->prepare(
			'delete from Tilaus where ktunnus = :ktunnus;' );
		$kysely->execute( array( 'ktunnus' => $ktunnus ) );
	}

	// Hakee taulusta Tilaus yksittäisen asiakkaan kaikki tilaukset
	public static function hae_asiakkaan_tilaukset( $ktunnus ) {
		$kaikki_tilaukset = self::hae_kaikki();

		$asiakkaan_tilaukset = array();

		foreach( $kaikki_tilaukset as $tilaus ) {
			if( $tilaus->asiakasviite->ktunnus == $ktunnus ) {
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
				// 'asiakasviite' => Asiakas::hae( $rivi[ 'ktunnus' ] ),
				'ktunnus' => $rivi[ 'ktunnus' ],
				'ts_tilauksen_teko' => $rivi[ 'ts_tilauksen_teko' ],
				'ts_tak_toivottu' => $rivi[ 'ts_tak_toivottu' ],
				'ts_tak_toteutunut' => $rivi[ 'ts_tak_toteutunut' ],
				// 'osoiteviite' => Osoite::hae( $rivi[ 'osoite_id' ] )
				'osoite_id' => $rivi[ 'osoite_id' ]
			) );

			return $tilaus;
		}

		return null;
	}

	// Hae kaikki rivit taulusta Tilaus ja palauta ne taulukkona olioita
	public static function hae_kaikki() {
		$kysely = DB::connection()->prepare(
			'select * from Tilaus;' );
		$kysely->execute();
		$rivit = $kysely->fetchAll();

		$tilaukset = array();

		foreach( $rivit as $rivi ) {
			$tilaukset[] = new Tilaus( array(
				'tilaus_id' => $rivi[ 'tilaus_id' ],
				'asiakasviite' => Asiakas::hae( $rivi[ 'ktunnus' ] ),
				'ts_tilauksen_teko' => $rivi[ 'ts_tilauksen_teko' ],
				'ts_tak_toivottu' => $rivi[ 'ts_tak_toivottu' ],
				'ts_tak_toteutunut' => $rivi[ 'ts_tak_toteutunut' ],
				'osoiteviite' => Osoite::hae( $rivi[ 'osoite_id' ] )
			) );
		}

		return $tilaukset;
	}
}
