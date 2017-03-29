<?php

class Kayttaja extends BaseModel {
	// Attribuutit
	public $ktunnus, $salasana, $tyyppi;

	// Konstruktori
	public function __construct( $attributes ) {
		parent::__construct( $attributes );
	}

	// Tutkitaan, löytyykö taulusta Kayttaja annettua
	// käyttäjätunnus–salasana-yhdistelmää
	public static function authenticate( $ktunnus, $salasana ) {
		$query = DB::connection()->prepare(
			'select * from Kayttaja where ktunnus = :ktunnus '
			. 'and salasana = :salasana limit 1' );
		$query->execute( array(
			'ktunnus' => $ktunnus, 'salasana' => $salasana ) );
		$row = $query->fetch();
		if( $row ) {
			return new Kayttaja(
				array( 'ktunnus' => $row[ 'ktunnus' ],
				'salasana' => $row[ 'salasana' ],
				'tyyppi' => $row[ 'tyyppi' ] ) );
		} else {
			return null;
		}
	}

	public static function all() {
		$query = DB::connection()->prepare( 'select * from Kayttaja' );
		$query->execute();
		$rows = $query->fetchAll();

		$kayttajat = array();

		foreach( $rows as $row ) {
			$kayttajat[] = new Kayttaja( array(
				'ktunnus' => $row[ 'ktunnus' ],
				'salasana' => $row[ 'salasana' ],
				'tyyppi' => $row[ 'tyyppi' ]
			) );
		}

		return $kayttajat;
	}

	public static function find( $ktunnus ) {
		$query = DB::connection()->prepare(
			'select * from Kayttaja where ktunnus = :ktunnus limit 1' );
		$query->execute( array( 'ktunnus' => $ktunnus ) );
		$row = $query->fetch();

		if( $row ) {
			$kayttaja = new Kayttaja( array(
				'ktunnus' => $row[ 'ktunnus' ],
				'salasana' => $row[ 'salasana' ],
				'tyyppi' => $row[ 'tyyppi' ]
			) );

			return $kayttaja;
		}

		return null;
	}

	// Uuden käyttäjätunnuksen tallentaminen tietokantaan
	public function save() {
		// Ei tehdä muuta kuin palautetaan null, jos tallennettava
		// käyttäjätunnus on jo tietokannassa
		if( Kayttaja::find( $this->ktunnus ) ) {
			return null;
		}

		$query = DB::connection()->prepare(
			'INSERT INTO Kayttaja ( ktunnus, salasana, tyyppi ) '
				. 'VALUES ( :ktunnus, :salasana, :tyyppi )' );
		$query->execute( array( 'ktunnus' => $this->ktunnus,
			'salasana' => $this->salasana, 'tyyppi' => $this->tyyppi ) );
		// Haetaan kyselyn tuottama rivi
		// $row = $query->fetch();

		// Palautetaan ei-null merkiksi siitä, että tietokantaan lisättiin
		// uusi käyttäjätunnus
		return $this;
    }
}
