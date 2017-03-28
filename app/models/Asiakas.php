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
}
