<?php

class BaseModel {
	// "protected"-attribuutti on käytössä vain luokan ja sen perivien luokkien sisällä
	protected $validaattorit;

	public function __construct($attributes = null) {
		// Käydään assosiaatiolistan avaimet läpi
		foreach($attributes as $attribute => $value) {
			// Jos avaimen niminen attribuutti on olemassa...
			if( property_exists( $this, $attribute ) ) {
				// ... lisätään avaimen nimiseen attribuuttin siihen liittyvä arvo
				$this->{$attribute} = $value;
			}
		}
	}

	// Entinen errors(), nimetty uudelleen
	// Funktio, joka kutsuu mallin kaikkia validointifunktioita. Validointi-
	// funktioiden nimet löytyvät taulukkona olion kentästä $validaattorit.
	public function virheilmoitukset() {
		// Lisätään $virheet muuttujaan kaikki virheilmoitukset taulukkona
		$virheet = array();

		foreach( $this->validaattorit as $validaattori ){
			$virheet = array_merge( $virheet, $this->{$validaattori}() );
		}

		return $virheet;
	}

	// Käsitteen "sana" merkitys lienee intuitiivisesti aika selvä. Esim.
	// "yliopisto" tai "Minna" ovat sanoja, kun taas "matti@yliopisto.fi"
	// tai "Minna81" eivät ole sanoja. Toisaalta myös "asdf" on funktioni
	// mielestä sana. Kirjainten lisäksi sana voi sisältää myös korkeintaan
	// yhden väliviivan, mutta ei kuitenkaan sanan alussa tai lopussa.
	public static function merkkijono_on_sana( $kentta, $mj ) {
		// Katsotaan aluksi, onko merkkijono tyhjä
		$virheilmoitukset = self::tyhja_merkkijono( $kentta, $mj );
		if( count( $virheilmoitukset ) > 0 ) {
			return $virheilmoitukset;
		}

		// \A tarkoittaa merkkijonon alkua, \z sen loppua. Merkkijonon alun
		// ja lopun välissä on siis vähintään kaksi ja enintään 20 kirjainta
		// tai väliviivaa. En käytä lyhyttä merkintää [A-Ö], koska merkinnän
		// tulkinta on käsittääkseni herkkä lokalisointikohtaisille eroille.
		// sl = säännöllinen lauseke
		$sl = '/\A[ABCDEFGHIJKLMNOPQRSTUVWXYZÅÄÖabcdefghijklmnopqrstuvwxyzåäö-]{2,20}\z/';
		if( strlen( $mj ) < 2 || strlen( $mj ) > 20 ) {
			$virheilmoitukset[] = $kentta . 'Liian lyhyt (< 2) tai liian pitkä (> 20)';
		}
		// Paluuarvo nolla tarkoittaa "no match". Tässä siis verrataan
		// merkkijonoa säännölliseen lausekkeeseen.
		else if( preg_match( $sl, $mj ) == 0 ) {
			$virheilmoitukset[] = $kentta . 'Ei ole tulkittavissa sanana tai nimenä';
		}

		// Katsotaan, alkaako tai päättyykö merkkijono väliviivaan
		if( $mj[ 0 ] == '-' || $mj[ strlen( $mj ) - 1 ] == '-' ) {
			$virheilmoitukset[] = $kentta . 'Alkaa tai päättyy väliviivaan';
		}

		// Väliviivoja saa olla sanassa korkeintaan yksi
		if( self::merkkilaskuri( $mj, '-' ) > 1 ) {
			$virheilmoitukset[] = $kentta . 'Väliviivoja saa olla korkeintaan yksi';
		}

		return $virheilmoitukset;
	}

	// Laskee, kuinka monta kertaa merkki $merkki esiintyy merkkijonossa $mj
	public static function merkkilaskuri( $mj, $merkki ) {
		$pituus = strlen( $mj );
		$lkm = 0;

		for( $i = 0; $i < $pituus; $i++ ) {
			if( $mj[ $i ] == $merkki ) {
				$lkm++;
			}
		}

		return $lkm;
	}

	// $kentta ilmaisee virheilmoitukseen lomakkeen kentän nimen,
	// esim. "Etunimi: "
	public static function merkkijono_on_erisnimi( $kentta, $mj ) {
		$virheet = self::merkkijono_on_sana( $kentta, $mj );

		// Jos merkkijono ei ole sana, se ei myöskään voi olla erisnimi
		if( count( $virheet ) > 0 ) {
			return $virheet;
		}

		// Merkki ^ tarkoittaa rivin alkua, $ rivin loppua. Erisnimi (rivi)
		// alkaa yhdellä isolla kirjaimella, jota seuraa nolla tai useampi
		// pieni kirjain tai väliviiva, jota seuraa nolla tai yksi väliviiva
		// ja iso kirjain (esim. "-M"), jota seuraa yksi tai useampi pieni
		// kirjain. Hieman monimutkaista ehkä, mutta tällä tavalla voidaan
		// matchata sekä "Jussi" että "Anna-Maija".
		$sl = '/^[ABCDEFGHIJKLMNOPQRSTUVWXYZÅÄÖ][abcdefghijklmnopqrstuvwxyzåäö]*'
		. '(-[ABCDEFGHIJKLMNOPQRSTUVWXYZÅÄÖ])?[abcdefghijklmnopqrstuvwxyzåäö]+$/';

		// Paluuarvo nolla tarkoittaa "no match". Tässä siis verrataan
		// merkkijonoa säännölliseen lausekkeeseen.
		if( preg_match( $sl, $mj ) == 0 ) {
			$virheet[] = $kentta . 'Tarkista nimesi oikeinkirjoitus (esim. Pekka)';
		}

		return $virheet;
	}

	public static function tyhja_merkkijono( $mj ) { // mj, merkkijono
		if( $mj == null || $mj == '' ) {
			return true;
		}

		return false;
	}
}
