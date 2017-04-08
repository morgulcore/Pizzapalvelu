<?php

class Kayttaja extends BaseModel {
	// Attribuutit
	public $ktunnus, $salasana, $tyyppi;

	// Konstruktori
	public function __construct( $attributes ) {
		parent::__construct( $attributes );
		// Seuraava attribuutti on määritelty BaseModelissa
		$this->validaattorit = array(
			'validoi_ktunnus', 'validoi_salasana', 'validoi_tyyppi' );
	}

	public function validoi_ktunnus() {
		$virheilmoitukset = BaseModel::tyhja_merkkijono(
			'Käyttäjätunnus: ', $this->ktunnus );

		// \A tarkoittaa merkkijonon alkua, \z sen loppua. Käyttäjätunnus siis
		// alkaa aina pienellä kirjaimella, jota seuraa 2–14 pientä kirjainta
		// tai numeroa. En käytä kompaktia merkintää [a-z], koska en ole
		// varma, miten se käyttäytyy lokalisointiasetuksien kanssa.
		$sl = '/\A[abcdefghijklmnopqrstuvwxyz][abcdefghijklmnopqrstuvwxyz0123456789]{2,14}\z/';

		if( preg_match( $sl, $this->ktunnus ) == 0 ) {
			$virheilmoitukset[] = 'Käyttäjätunnus: Pituus on 3–15 merkkiä, '
				. 'sisältää vain pieniä kirjaimia (a-z) ja numeroita (0-9), '
				. 'alkaa pienellä kirjaimella';
		}

		return $virheilmoitukset;
	}

	public function validoi_salasana() {
		$virheilmoitukset = BaseModel::tyhja_merkkijono(
			'Salasana: ', $this->salasana );

		// Salasana voi siis sisältää kirjaimia ja numeroita, ja se on
		// pituudeltaan 4–15 merkkiä
		$sl = '/\A[ABCDEFGHIJKLMNOPQRSTUVWXYZÅÄÖabcdefghijklmnopqrstuvwxyzåäö0123456789]{4,15}\z/';

		if( preg_match( $sl, $this->salasana ) == 0 ) {
			$virheilmoitukset[] = 'Salasana: Pituus 4–15 merkkiä, voi sisältää '
				. 'kirjaimia ja numeroita';
		}

		return $virheilmoitukset;
	}

	public function validoi_tyyppi() {
		$virheilmoitukset = array();

		if( ! ( is_numeric( $this->tyyppi )
			&& ( $this->tyyppi == 0 || $this->tyyppi == 1 ) ) ) {
			$virheilmoitukset[] = 'Tyyppi: Jokin muu arvo kuin 0 tai 1!';
		}

		return $virheilmoitukset;
	}

	// Päivittää Kayttaja-olion tiedot tietokantaan. Huomaa, että vain
	// salasana- ja tyyppi-kenttien arvot voidaan päivittää tämän
	// funktion avulla.
	public function paivita() {
		$query = DB::connection()->prepare(
			'update Kayttaja set salasana = :salasana, tyyppi = :tyyppi '
				. 'where ktunnus = :ktunnus;' );
		$query->execute( array(
			'ktunnus' => $this->ktunnus,
			'salasana' => $this->salasana,
			'tyyppi' => $this->tyyppi
		) );
	}

	// Käyttäjä-olion poistamiseen tietokannasta
	public function poista() {
		// Varmistetaan, että poistettavaksi tarkoitettu olio
		// on tallennettuna tietokantaan
		if( self::find( $this->ktunnus ) == null ) {
			return null;
		}

		$query = DB::connection()->prepare(
			'delete from Kayttaja where ktunnus = :ktunnus;' );
		$query->execute( array(
			'ktunnus' => $this->ktunnus
		) );

		return $this;
	}

	// Uuden käyttäjätunnuksen tallentaminen tietokantaan
	public function save() {
		// Ei tehdä muuta kuin palautetaan null, jos tallennettava
		// käyttäjätunnus on jo tietokannassa
		if( self::find( $this->ktunnus ) ) {
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
		}

		return null;
	}

	// Hakee tietokannasta kaikki taulun Kayttaja oliot (rivit)
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

	// Palauttaa taulun Käyttäjä olion, jonka avain on $ktunnus
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
}
