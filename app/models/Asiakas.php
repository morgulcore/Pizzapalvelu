<?php

class Asiakas extends BaseModel {
	// Attribuutit
	public $asiakas_id, $ktunnus, $etunimi, $sukunimi,
		$puhelinnumero, $sahkopostiosoite,
		// Tietokantatoteutuksessa seuraavat kaksi kenttää eivät löydy
		// taulusta Asiakas, vaan taulusta Kayttaja. En tiedä, onko ihan
		// korrektia hämärtää malliolioiden ja taulujen yksi yhteen
		// -vastaavuutta tällä tavalla, mutta kokeilen nyt kuitenkin.
		$salasana, $tyyppi;

	// Konstruktori
	public function __construct( $attributes ) {
		parent::__construct( $attributes );
	}

	// Haetaan tietokannasta kaikki Asiakas-oliot. Jokaiseen asiakkaaseen
	// liittyy täsmälleen yksi Kayttaja-olio, joten haetaan samalla nekin.
	public static function all() {
		$query = DB::connection()->prepare( 'select Asiakas.asiakas_id, Asiakas.etunimi, Asiakas.sukunimi, Asiakas.puhelinnumero, Asiakas.sahkopostiosoite, Kayttaja.ktunnus, Kayttaja.salasana, Kayttaja.tyyppi from Asiakas inner join Kayttaja on Asiakas.ktunnus = Kayttaja.ktunnus;' );
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
				'sahkopostiosoite' => $row[ 'sahkopostiosoite' ],
				'salasana' => $row[ 'salasana' ],
				'tyyppi' => $row[ 'tyyppi' ]
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
}
