<?php

class Kayttaja extends BaseModel {
	// Attribuutit
	public $ktunnus, $salasana, $tyyppi;

	// Konstruktori
	public function __construct( $attributes ) {
		parent::__construct( $attributes );
	}

	public static function authenticate( $ktunnus, $salasana ) {
		$query = DB::connection()->prepare(
			'select * from Kayttaja where ktunnus = :ktunnus and salasana = :salasana limit 1' );
		$query->execute( array( 'ktunnus' => $ktunnus, 'salasana' => $salasana ) );
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
}
