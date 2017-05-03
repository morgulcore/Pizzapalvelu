<?php

class Tilaus extends BaseModel {
	// Attribuutit. Lyhenteet: ts = timestamp, tak = toimitusajankohta.
	public
		$tilaus_id,
		$asiakasviite, // viite, olioviite
		$ts_tilauksen_teko,
		$ts_tak_toivottu,
		$ts_tak_toteutunut,
		$osoiteviite,
		$tilauksen_kokonaishinta;

	// Konstruktori
	public function __construct( $attribuutit ) {
		parent::__construct( $attribuutit );
		$this->asiakasviite = Asiakas::hae( $attribuutit[ 'ktunnus' ] );
		$this->osoiteviite = Osoite::hae( $attribuutit[ 'osoite_id' ] );
		$this->validaattorit = array( 'validoi_ts_tak_toivottu' );

		// SQL-kysely funktiossa tallenna() ei toimi ilman tätä
		if( $this->ts_tak_toivottu == '' ) {
			$this->ts_tak_toivottu = null;
		}

		// Tilattuja tuotteita ei tässä vaiheessa ole vielä liitetty
		// tilaukseen, joten kokonaishinta lasketaan vasta myöhemmin
		// (tarvittaessa)
		$this->tilauksen_kokonaishinta = -1.0;
	}

	// Asettaa attribuutille $this->tilauksen_kokonaishinta sen oikean
	// arvon. Tilaus-olio luodaan ennen Tilattu_tuote-olioita, joten
	// tilauksen kokonaishintaa ei voida laskea vielä Tilaus-olion
	// luomisen yhteydessä.
	public function aseta_tilauksen_kokonaishinta() {
		$tilaukseen_liittyvat_tuotteet
			= Tilattu_tuote::hae_tilaukseen_liittyvat_tuotteet(
			$this->tilaus_id );
		$tilauksen_kokonaishinta = 0.0;

		foreach( $tilaukseen_liittyvat_tuotteet as $tilattu_tuote ) {
			$tilauksen_kokonaishinta += $tilattu_tuote->rivihinta;
		}

		$this->tilauksen_kokonaishinta = $tilauksen_kokonaishinta;
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
		$sl = '/\A20[12][0-9]-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01]) '
			. '([01][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]\z/';

		if( preg_match( $sl, $timestamp ) === 0 ) {
			return false;
		}

		return true;
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

	// Oletus on, että kenttien ts_tak_toivottu ja osoite_id arvot on
	// validoitu ennen tämän funktion kutsumista.
	public function paivita_ts_tak_toivottu_ja_osoite_id() {
		$kysely = DB::connection()->prepare(
			'update Tilaus set ts_tak_toivottu = :ts_tak_toivottu, '
			. 'osoite_id = :osoite_id where tilaus_id = :tilaus_id;' );
		$kysely->execute( array(
			'ts_tak_toivottu' => $this->ts_tak_toivottu,
			'osoite_id' => $this->osoiteviite->osoite_id,
			'tilaus_id' => $this->tilaus_id
		) );
	}

	// Oletus on, että $this->asiakasviite->ktunnus on validoitu ennen
	// tämän funktion kutsumista
	public function paivita_ktunnus() {
		$kysely = DB::connection()->prepare(
			'update Tilaus set ktunnus = :ktunnus where tilaus_id = :tilaus_id;' );
		$kysely->execute( array(
			'ktunnus' => $this->asiakasviite->ktunnus,
			'tilaus_id' => $this->tilaus_id
		) );
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

	// Poistaa Tilaus-ilmentymää vastaavan rivin taulusta Tilaus. Ensin
	// poistetaan tarpeen mukaan rivejä tauluista Tilattu_tuote ja Ongelma
	// (viite-eheyden takaamiseksi).
	public function poista() {
		Ongelma::poista( $this->tilaus_id );
		Tilattu_tuote::poista( $this->tilaus_id );

		$kysely = DB::connection()->prepare(
			'delete from Tilaus where tilaus_id = :tilaus_id;' );
		$kysely->execute( array( 'tilaus_id' => $this->tilaus_id ) );
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

	public static function laske_asiakkaan_toimitettujen_tilausten_kokonaisarvo(
		$ktunnus ) {
		$asiakkaan_tilaukset = self::hae_asiakkaan_tilaukset( $ktunnus );

		$kokonaisarvo = 0.0;
		foreach( $asiakkaan_tilaukset as $tilaus ) {
			if( $tilaus->ts_tak_toteutunut ) {
				$tilaus->aseta_tilauksen_kokonaishinta();
				$kokonaisarvo += $tilaus->tilauksen_kokonaishinta;
			}
		}

		return $kokonaisarvo;
	}

	// Hakee taulusta Tilaus yksittäisen asiakkaan toimittamattomat tilaukset
	public static function hae_asiakkaan_toimittamattomat_tilaukset(
		$ktunnus ) {
		$asiakkaan_tilaukset = self::hae_asiakkaan_tilaukset( $ktunnus );
		$asiakkaan_toimittamattomat_tilaukset = array();

		foreach( $asiakkaan_tilaukset as $tilaus ) {
			if( ! $tilaus->ts_tak_toteutunut ) {
				$asiakkaan_toimittamattomat_tilaukset[] = $tilaus;
			}
		}

		return $asiakkaan_toimittamattomat_tilaukset;
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
			'select * from Tilaus order by ts_tak_toteutunut desc, '
			. 'ts_tilauksen_teko desc;' );
		$kysely->execute();
		$rivit = $kysely->fetchAll();

		$tilaukset = array();

		foreach( $rivit as $rivi ) {
			$tilaukset[] = new Tilaus( array(
				'tilaus_id' => $rivi[ 'tilaus_id' ],
				'ktunnus' => $rivi[ 'ktunnus' ],
				'ts_tilauksen_teko' => $rivi[ 'ts_tilauksen_teko' ],
				'ts_tak_toivottu' => $rivi[ 'ts_tak_toivottu' ],
				'ts_tak_toteutunut' => $rivi[ 'ts_tak_toteutunut' ],
				'osoite_id' => $rivi[ 'osoite_id' ]
			) );
		}

		return $tilaukset;
	}

	// Pari ( $ktunnus, $ts_tilauksen_teko ) on yksikäsitteinen, joten sitä
	// vastaa täsmälleen yksi tilaus_id (olettaen, että pari on olemassa)
	public static function hae_tilaus_id( $ktunnus, $ts_tilauksen_teko ) {
		$kysely = DB::connection()->prepare(
			'select * from Tilaus where ktunnus = :ktunnus and '
				. 'ts_tilauksen_teko = :ts_tilauksen_teko limit 1;' );
		$kysely->execute( array(
			'ktunnus' => $ktunnus, 'ts_tilauksen_teko' => $ts_tilauksen_teko ) );

		$rivi = $kysely->fetch();
		if( $rivi ) {
			return $rivi[ 'tilaus_id' ];
		}

		return null;
	}
}
