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
			'validoi_etunimi', 'validoi_sukunimi',
			'validoi_puhelinnumero', 'validoi_sahkopostiosoite' );
	}

	public function validoi_ktunnus() {
		$virheet = array();

		// \A tarkoittaa merkkijonon alkua, \z sen loppua.
		// Käyttäjätunnus koostuu vähintään kolmesta ja enintään 15:sta
		// pienestä kirjaimesta (ei ääkkösiä).
		$sl = '/\A[a-z]{3,15}\z/';

		if( BaseModel::tyhja_merkkijono( $this->ktunnus ) ) {
			$virheet[] = 'Käyttäjätunnus: Ei saa olla tyhjä';
		} else if( preg_match( $sl, $this->ktunnus ) === 0 ) {
			$virheet[] = 'Käyttäjätunnus: Pituus 3–15 merkkiä, '
				. 'sisältää vain pieniä kirjaimia (ei ääkkösiä)';
		}

		return $virheet;
	}

	public function validoi_salasana() {
		$virheet = array();

		// Salasana voi siis sisältää kirjaimia ja numeroita, ja se on
		// pituudeltaan 3–15 merkkiä
		$sl = '/\A[a-zA-Z0-9]{3,15}\z/';

		if( BaseModel::tyhja_merkkijono( $this->salasana ) ) {
			$virheet[] = 'Salasana: Ei saa olla tyhjä';
		} else if( preg_match( $sl, $this->salasana ) === 0 ) {
			$virheet[] = 'Salasana: Pituus 3–15 merkkiä, sisältää '
				. 'numeroita ja kirjaimia (ei ääkkösiä)';
		}

		return $virheet;
	}

	public function validoi_etunimi() {
		return $this->validoi_nimi( false );
	}

	public function validoi_sukunimi() {
		return $this->validoi_nimi( true );
	}

	private function validoi_nimi( $kyseessa_sukunimi ) {
		$virheet = array();

		// Sovitaan yksinkertaisuuden vuoksi, että nimi sisältää vain
		// kirjaimia, jolloin esim. "Anna-Maija" ei käy nimestä
		$sl = '/\A[a-zA-ZäÄöÖåÅ]{2,20}\z/';

		if( BaseModel::tyhja_merkkijono(
			$kyseessa_sukunimi ? $this->sukunimi : $this->etunimi ) ) {
			$virheet[] = ( $kyseessa_sukunimi ? 'Sukunimi: ' : 'Etunimi: ' )
				. 'Ei saa olla tyhjä';
		} else if( preg_match( $sl, $kyseessa_sukunimi ?
			$this->sukunimi : $this->etunimi ) === 0 ) {
			$virheet[] = $kyseessa_sukunimi ? 'Sukunimi: ' : 'Etunimi: '
				. 'Pituus 2–20 merkkiä, koostuu vain kirjaimista';
		}

		return $virheet;
	}

	public function validoi_puhelinnumero() {
		// Puhelinnumero saa olla tyhjä merkkijono
		if( BaseModel::tyhja_merkkijono( $this->puhelinnumero ) ) {
			return array();
		}

		$virheet = array();
		$sl = '/\A([+]358|0)[0-9 ]{8,15}\z/';

		if( preg_match( $sl, $this->puhelinnumero ) === 0 ) {
			$virheet[] = 'Puhelinnumero: 8–15 merkkiä pitkä, alkaa joko "0" tai '
				. '"+358", voi sisältää numeroiden lisäksi välilyöntejä';
		}

		if( BaseModel::merkkilaskuri( $this->puhelinnumero, ' ' ) > 3 ) {
			$virheet[] = 'Puhelinnumero: Välilyöntejä saa olla enintään kolme';
		}

		return $virheet;
	}

	public function validoi_sahkopostiosoite() {
		// Sähköpostiosoite saa olla tyhjä merkkijono
		if( BaseModel::tyhja_merkkijono( $this->sahkopostiosoite ) ) {
			return array();
		}

		$virheet = array();

		// Tämän pitäisi olla kohtuullisen tarkka määritelmä sähköpostiosoitteen
		// muodolle. Ei kuitenkaan riittävän tarkka tuotantokäyttöön. Esim.
		// ".@..ab" vastaa tätä säännöllistä lauseketta, mikä ei tietenkään
		// ole hyväksyttävissä.
		$sl_1 = '/\A[a-zA-Z0-9._-]{1,20}@[a-zA-Z0-9.-]{1,20}\.[a-zA-Z]{2,4}\z/';

		if( preg_match( $sl_1, $this->sahkopostiosoite ) === 0 ) {
			$virheet[] = 'Sähköpostiosoite: Tarkista, että muoto on oikea. '
				. 'Huomaa, että ääkköset eivät ole sallituja.';
		}

		// Pitäisi matchata minkä tahansa merkkijonon, jossa on osamerkki-
		// jonona "..". Tämä on vain yksi erityistapaus, parempi olisi
		// tietysti yleiskäyttöinen function, jolla voisi tutkia, sisältääkö
		// merkkijono $mj osamerkkijonon, joka koostuu kahdesta peräkkäisestä
		// merkistä $c.
		$sl_2 = '/\A.*\.\..*\z/';
		if( preg_match( $sl_2, $this->sahkopostiosoite ) === 1 ) {
			$virheet[] = 'Sähköpostiosoite: Sisältää kaksi peräkkäistä pistettä';
		}

		return $virheet;
	}

	// Asiakastietojen päivitys. Huomaa, että attribuutteja ktunnus ja
	// on_paakayttaja ei voi päivittää tällä funktiolla.
	public function paivita() {
		// Bugtrap
		if( self::hae( $this->ktunnus ) == null ) {
			exit( 'Asiakas->paivita() – Yritettiin päivittää olematonta riviä' );
		}

		$kysely = DB::connection()->prepare(
			'update Asiakas set salasana = :salasana, etunimi = :etunimi, '
				. 'sukunimi = :sukunimi, puhelinnumero = :puhelinnumero, '
				. 'sahkopostiosoite = :sahkopostiosoite '
				. 'where ktunnus = :ktunnus;' );
		$kysely->execute( array(
			'ktunnus' => $this->ktunnus,
			'salasana' => $this->salasana,
			'etunimi' => $this->etunimi,
			'sukunimi' => $this->sukunimi,
			'puhelinnumero' => $this->puhelinnumero,
			'sahkopostiosoite' => $this->sahkopostiosoite,
		) );
	}

	// Poistetaan Asiakas-olio tietokannasta
	public function poista() {
		// Bugtrap
		if( self::hae( $this->ktunnus ) == null ) {
			exit( 'Asiakas.poista() – Yritettiin poistaa olematonta riviä' );
		}

		$kysely = DB::connection()->prepare(
			'delete from Asiakas where ktunnus = :ktunnus;' );
		$kysely->execute( array( 'ktunnus' => $this->ktunnus ) );
	}

	// Tallennetaan Asiakas-olio tietokantaan
	public function tallenna() {
		if( self::hae( $this->ktunnus ) != null ) {
			exit( 'Asiakas::tallenna() – ktunnus jo olemassa' );
		}

		$kysely = DB::connection()->prepare(
			'insert into Asiakas values ( :ktunnus, :on_paakayttaja, :salasana, '
				. ':etunimi, :sukunimi, :puhelinnumero, :sahkopostiosoite );' );
		$kysely->execute( array(
			'ktunnus' => $this->ktunnus,
			'on_paakayttaja' => 'false',
			'salasana' => $this->salasana,
			'etunimi' => $this->etunimi,
			'sukunimi' => $this->sukunimi,
			'puhelinnumero' => $this->puhelinnumero,
			'sahkopostiosoite' => $this->sahkopostiosoite
		) );
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
				exit( 'Asiakas::todenna() – Null-viite' );
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

			return $asiakas;
		}

		return null;
	}
}
