<?php

class Tilattu_tuote extends BaseModel {
	// Attribuutit
	public
		$tilausviite,
		$tuotelaskuri,
		$tuoteviite,
		$lukumaara,
		$rivihinta;

	// Konstruktori
	public function __construct( $attribuutit ) {
		parent::__construct( $attribuutit );

		if( array_key_exists( 'tilausviite', $attribuutit )
			&& $attribuutit[ 'tilausviite' ] != null ) {
			$this->tilausviite = $attribuutit[ 'tilausviite' ];
		} else {
			$this->tilausviite = Tilaus::hae( $attribuutit[ 'tilaus_id' ] );
		}
		// Bugtrap
		if( $this->tilausviite == null ) {
			exit( 'Tilattu_tuote.__construct() – $tilausviite == null' );
		}

		$this->tuoteviite = Tuote::hae(
			$attribuutit[ 'tuotetyyppi_id' ], $attribuutit[ 'tuoteversio' ] );
		// Bugtrap
		if( $this->tuoteviite == null ) {
			exit( 'Tilattu_tuote.__construct() – $this->tuoteviite' );
		}

		$this->rivihinta = $this->laske_rivihinta();
	}

	// Tallentaa olion tietokantaan
	public function tallenna() {
		$kysely = DB::connection()->prepare(
			'insert into Tilattu_tuote values ( :tilaus_id, :tuotelaskuri, '
			. ':tuotetyyppi_id, :tuoteversio, :lukumaara );' );
		$kysely->execute( array(
			'tilaus_id' => $this->tilausviite->tilaus_id,
			'tuotelaskuri' => $this->tuotelaskuri,
			'tuotetyyppi_id' => $this->tuoteviite->tuotetyyppiviite->tuotetyyppi_id,
			'tuoteversio' => $this->tuoteviite->tuoteversio,
			'lukumaara' => $this->lukumaara
		) );
	}

	// Laskee Tilattu_tuote-ilmentymän rivihinnan. Rivihinta tarkoittaa
	// tilatun tuotteen hintaa kerrottuna lukumäärällä. Esim. jos on tilattu
	// kaksi pizzaa, joiden kappalehinta on 5 €, rivihinta on 2 * 5 € = 10 €.
	private function laske_rivihinta() {
		return $this->lukumaara * $this->tuoteviite->dynaaminen_hinta();
	}

	public static function hae_tilaukseen_liittyvat_tuotteet( $tilaus_id ) {
		$kaikki = self::hae_kaikki();
		$tilaukseen_liittyvat_tuotteet = array();

		foreach( $kaikki as $tilattu_tuote ) {
			if( $tilattu_tuote->tilausviite->tilaus_id == $tilaus_id ) {
				$tilaukseen_liittyvat_tuotteet[] = $tilattu_tuote;
			}
		}

		return $tilaukseen_liittyvat_tuotteet;
	}

	public static function hae_kaikki() {
		$kysely = DB::connection()->prepare( 'select * from Tilattu_tuote;' );
		$kysely->execute();
		$rivit = $kysely->fetchAll();

		$tilatut_tuotteet = array();

		foreach( $rivit as $rivi ) {
			$tilatut_tuotteet[] = new Tilattu_tuote( $rivi );
		}

		return $tilatut_tuotteet;
	}

	// Poistaa taulusta Tilattu_tuote kaikki rivit, joissa
	// esiintyy $tilaus_id
	public static function poista( $tilaus_id ) {
		$kysely = DB::connection()->prepare(
			'delete from Tilattu_tuote where tilaus_id = :tilaus_id;' );
		$kysely->execute( array( 'tilaus_id' => $tilaus_id ) );
	}
}
