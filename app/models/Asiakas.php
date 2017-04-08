<?php

class Asiakas extends BaseModel {

	// Attribuutit
	public
		$ktunnus,
		$on_paakayttaja,
		$salasana,
		$etunimi,
		$sukunimi,
		$puhelinnumero,
		$sahkopostiosoite;

	// Konstruktori
	public function __construct( $attribuutit ) {
		parent::__construct( $attribuutit );
		// Seuraava attribuutti on määritelty BaseModelissa
		$this->validaattorit = array(
			'validoi_ktunnus', 'validoi_salasana',
			'validoi_etunimi', 'validoi_sukunimi', // validoi_etu_ja_sukunimi()
			'validoi_puhelinnumero', 'validoi_sahkopostiosoite' );
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

	public function validoi_etunimi() {
		$errors = BaseModel::merkkijono_on_erisnimi(
			"Etunimi: ", $this->etunimi );
		return $errors;
	}

	public function validoi_sukunimi() {
		$errors = BaseModel::merkkijono_on_erisnimi(
			"Sukunimi: ", $this->sukunimi );
		return $errors;
	}

	// Hätäisesti ja kiireessä toteutettu
	public function validoi_puhelinnumero() {
		$errors = array();
		$sl = '/\A[+]?[0-9 ]{10,20}\z/';
		if( strlen( $this->puhelinnumero ) > 0
			&& preg_match( $sl, $this->puhelinnumero ) == 0 ) {
			$errors[] = 'Puhelinnumero ei kelpaa';
		}
		return $errors;
	}

	// Hätäisesti ja kiireessä toteutettu
	public function validoi_sahkopostiosoite() {
		$errors = array();
		$sl = '/\A.{1,20}@.{1,20}\z/';
		if( strlen( $this->sahkopostiosoite ) > 0
			&& preg_match( $sl, $this->sahkopostiosoite ) == 0 ) {
			$errors[] = 'Sähköpostiosoite ei kelpaa';
		}
		return $errors;
	}

	// Asiakastietojen päivitys. Huomaa, että kenttien asiakas_id ja ktunnus
	// arvoja ei voi päivittää tällä funktiolla. Se ei olisi tarkoituksenmukaista.
	public function paivita() {
		$query = DB::connection()->prepare(
			'update Asiakas set etunimi = :etunimi, sukunimi = :sukunimi, '
			. 'puhelinnumero = :puhelinnumero, sahkopostiosoite = :sahkopostiosoite '
			. 'where asiakas_id = :asiakas_id;' );
		$query->execute( array(
			'asiakas_id' => $this->asiakas_id,
			'etunimi' => $this->etunimi,
			'sukunimi' => $this->sukunimi,
			'puhelinnumero' => $this->puhelinnumero,
			'sahkopostiosoite' => $this->sahkopostiosoite
		) );
	}

	// Poistetaan Asiakas-olio tietokannasta
	public function poista() {
		// Varmistetaan, että poistettavaksi tarkoitettu olio
		// on tallennettuna tietokantaan
		if( self::find( $this->asiakas_id ) == null ) {
			return null;
		}

		$query = DB::connection()->prepare(
			'delete from Asiakas where asiakas_id = :asiakas_id;' );
		$query->execute( array(
			'asiakas_id' => $this->asiakas_id
		) );

		return $this;
	}

	// Tallennetaan Asiakas-olio tietokantaan
	public function save() {
		$kayttaja = new Kayttaja( array(
			'ktunnus' => $this->ktunnus, 'salasana' => null, 'tyyppi' => 0 ) );
		$kayttaja->save();

		$query = DB::connection()->prepare(
			'insert into Asiakas ( ktunnus, etunimi, sukunimi, puhelinnumero, '
			. 'sahkopostiosoite ) values ( :ktunnus, :etunimi, :sukunimi, '
			. ':puhelinnumero, :sahkopostiosoite ) returning asiakas_id;' );
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

    // Uudelleennimetty authenticate(). Tutkitaan, löytyykö taulusta Asiakas
    // annettua käyttäjätunnus–salasana-yhdistelmää.
	public static function todenna( $ktunnus, $salasana ) {
		$kysely = DB::connection()->prepare(
			'select * from Asiakas where ktunnus = :ktunnus and '
				. 'salasana = :salasana limit 1;' );
		$kysely->execute( array(
			'ktunnus' => $ktunnus, 'salasana' => $salasana ) );

		$rivi = $kysely->fetch();
		if( $rivi ) {
			$asiakas = self::hae( $ktunnus );
			if( ! $asiakas ) {
				// Lähdekoodissa on bugi eli on paras viheltää peli heti poikki
				exit( 'Asiakas::todenna() – Tapahtui hirveitä!' );
			}

			return $asiakas;
		}

		return null;
	}

	// Haetaan tietokannasta kaikki Asiakas-oliot
	public static function hae_kaikki() {
		$kysely = DB::connection()->prepare( 'select * from Asiakas;' );
		$kysely->execute();
		$rivit = $kysely->fetchAll();

		$asiakkaat = array();

		foreach( $rivit as $rivi ) {
			$asiakkaat[] = new Asiakas( $rivi );

			/*
			$asiakkaat[] = new Asiakas( array(
				'asiakas_id' => $rivi[ 'asiakas_id' ],
				'ktunnus' => $rivi[ 'ktunnus' ],
				'etunimi' => $rivi[ 'etunimi' ],
				'sukunimi' => $rivi[ 'sukunimi' ],
				'puhelinnumero' => $rivi[ 'puhelinnumero' ],
				'sahkopostiosoite' => $rivi[ 'sahkopostiosoite' ],
				'salasana' => $rivi[ 'salasana' ],
				'tyyppi' => $rivi[ 'tyyppi' ]
			) ); */
		}

		return $asiakkaat;
	}

	// Haetaan tietokannasta käyttäjätunnusta $ktunnus vastaava asiakas
	public static function hae( $ktunnus ) {
		$kysely = DB::connection()->prepare(
			'select * from Asiakas where ktunnus = :ktunnus limit 1;' );
		$kysely->execute( array( 'ktunnus' => $ktunnus ) );

		$rivi = $kysely->fetch();
		if( $rivi ) {
			$asiakas = new Asiakas( $rivi );
			/*
			$asiakas = new Asiakas( array(
				'asiakas_id' => $rivi[ 'asiakas_id' ],
				'ktunnus' => $rivi[ 'ktunnus' ],
				'etunimi' => $rivi[ 'etunimi' ],
				'sukunimi' => $rivi[ 'sukunimi' ],
				'puhelinnumero' => $rivi[ 'puhelinnumero' ],
				'sahkopostiosoite' => $rivi[ 'sahkopostiosoite' ],
				'salasana' => $rivi[ 'salasana' ],
				'tyyppi' => $rivi[ 'tyyppi' ]
			) );*/

			return $asiakas;
		}

		return null;
	}
}
