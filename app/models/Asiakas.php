<?php

class Asiakas extends BaseModel {
	// Attribuutit
	public $asiakas_id, $ktunnus, $etunimi, $sukunimi,
		$puhelinnumero, $sahkopostiosoite;

	// Konstruktori
	public function __construct( $attributes ) {
		parent::__construct( $attributes );
	}

	public static function all() {
		$query = DB::connection()->prepare( 'select * from Asiakas' );
		$query->execute();
		$rows = $query->fetchAll();

		$asiakkaat = array();

		foreach( $rows as $row ) {
			$asiakkaat[] = new Asiakas( array(
				'asiakas_id' => $row[ 'asiakas_id' ],
				'ktunnus' => $row[ 'ktunnus' ],
				'etunimi' => $row[ 'etunimi' ],
				'sukunimi' => $row[ 'sukunimi' ],
				'puhelinnumero' => $row[ 'puhelinnumero' ],
				'sahkopostiosoite' => $row[ 'sahkopostiosoite' ]
			) );
		}

		return $asiakkaat;
	}

	// Tallennetaan Asiakas-olio tietokantaan
	public function save() {
		$kayttaja = new Kayttaja( array(
			'ktunnus' => $this->ktunnus, 'salasana' => null, 'tyyppi' => 0 ) );
		$kayttaja->save();

		$query = DB::connection()->prepare(
			'insert into Asiakas ( ktunnus, etunimi, sukunimi, puhelinnumero, '
			. 'sahkopostiosoite ) values ( :ktunnus, :etunimi, :sukunimi, '
			. ':puhelinnumero, :sahkopostiosoite ) returning asiakas_id' );
		$query->execute( array(
			'ktunnus' => $this->ktunnus,
			'etunimi' => $this->etunimi,
			'sukunimi' => $this->sukunimi,
			'puhelinnumero' => $this->puhelinnumero,
			'sahkopostiosoite' => $this->sahkopostiosoite
		) );

		$row = $query->fetch();
		$this->asiakas_id = $row[ 'asiakas_id' ];
	}

	/*
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
    } */
}
