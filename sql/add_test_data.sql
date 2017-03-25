insert into Kayttaja values
	( 'mruusu', 'tsoha2017', 0 ),
	( 'jporkkana', 'tsoha2017', 0 ),
	( 'skurpitsa', 'tsoha2017', 0 ),
	( 'nvadelma', 'tsoha2017', 0 ),
	( 'therne', 'tsoha2017', 0 ),
	( 'tmansikka', 'tsoha2017', 0 ),
	( 'smansikka', 'tsoha2017', 0 ),
	( 'mmansikka', 'tsoha2017', 0 ),
	( 'sruusu', 'tsoha2017', 0 ),
	( 'pnauris', 'tsoha2017', 0 ),
	( 'nobody', null, 0 );

insert into Asiakas ( ktunnus, etunimi, sukunimi, puhelinnumero, sahkopostiosoite )
	values
    ( 'mruusu', 'Minna', 'Ruusu', null, null ),
    ( 'jporkkana', 'Juuso', 'Porkkana', '040 123 4567', null ),
    ( 'skurpitsa', 'Santeri', 'Kurpitsa', null, null ),
    ( 'nvadelma', 'Niko', 'Vadelma', null, null ),
    ( 'therne', 'Tauno', 'Herne', null, null ),
    ( 'tmansikka', 'Tero', 'Mansikka', null, 'tero.mansikka@mansikka.fi' ),
    ( 'smansikka', 'Saara', 'Mansikka', null, null ),
    ( 'mmansikka', 'Minttu', 'Mansikka', null, null ),
    ( 'sruusu', 'Sini', 'Ruusu', null, null ),
    ( 'pnauris', 'Pekka', 'Nauris', null, null );

insert into Osoite ( lahiosoite, postinumero, postitoimipaikka ) values
	( 'Savirinne 17', '29094', 'Tuonela' ),
	-- Seuraava ei onnistuisi tai ainakaan sen ei pitäisi onnistua. Osoitteiden
	-- pitää nimittäin olla uniikkeja (unique constraint).
	-- ( 'Savirinne 17', '29094', 'Tuonela' ),
	( 'Nokkosentie 6 B 15', '29818', 'Tuonela' ),
	( 'Metsätie 87', '01642', 'Synkkälä' ),
	( 'Omenapolku 7 A', '29396', 'Tuonela' ),
	( 'Pihlajakatu 21 C 10', '29396', 'Tuonela' ),
	( 'Sitruunakuja 7', '29145', 'Tuonela' ),
	( 'Kadotuksentie 13', '06660', 'Gehenna' ),
	( 'Korpipolku 3', '01642', 'Synkkälä' ),
	( 'Kurpitsankantajankatu 33 B 15', '29400', 'Tuonela' ),
	( 'Kurpitsankantajankatu 45 F 6', '29400', 'Tuonela' );

insert into mm_Asiakas_Osoite values
	(1,5), -- Minna Ruusu, Pihlajakatu 21 C 10
	(5,7), -- Tauno Herne, Kadotuksentie 13
	(6,1), -- Tero Mansikka, Savirinne 17
	(6,9), -- Tero Mansikka, Kurpitsankantajankatu 33 B 15
	(7,1), -- Saara Mansikka, Savirinne 17
	(8,1); -- Minttu Mansikka, Savirinne 17

insert into Tilaus ( asiakas_id, ts_tilauksen_teko, ts_tak_toivottu, ts_tak_toteutunut, osoite_id )
	values
	( 6, timestamp '2017-01-19 10:23:54', null, timestamp '2017-01-19 11:05:37', 9 ),
	( 5, timestamp '2017-01-20 12:01:02', null, null, 7 ),
	( 6, timestamp '2017-03-25 23:15:46', null, null, 1 ),
	( 5, timestamp '2017-01-22 14:37:16', null, timestamp '2017-01-22 15:32:07', 7 ),
	( 1, timestamp '2017-02-14 10:02:01', null, null, 5 );

--tilaus_id, ongelman_tyyppi, ts_ongelma, ongelman_kuvaus
insert into Ongelma values
	( 2, 'no_payment', timestamp '2017-01-20 12:40:41',
		'Asiakas sieppasi pizzan ja pakeni' ),
	( 2, 'violence', timestamp '2017-01-22 15:33:16',
		'Maksettuaan tilauksen asiakas löi tilauksen toimittajaa' ),
	( 5, 'customer_not_found', timestamp '2017-02-14 10:45:41', null );

insert into Tuotetyyppi ( tuotekategoria, tuotenimi, tuotekuvaus ) values
	( 'pizza', 'Carnivore',
		'Tosimiehen pizza! Syötyäsi tämän joudut ohitusleikkaukseen!' ),
	( 'vegaanipizza', 'Herbivore', 'Ituhipin valinta.' );

insert into Tuote values
	-- "Values of the numeric, int, and bigint data types can be cast to money.
	-- Conversion from the real and double precision data types can be done by
	-- casting to numeric first."
	-- https://www.postgresql.org/docs/9.1/static/datatype-money.html
	-- On siis tarpeen castata liukulukuarvo monivaiheisesti moneyksi.
	-- On syytä muistaa sekin, että "oikeassa" eli tuotantokäyttöön
	-- tarkoitetussa järjestelmässä hintoja ei pidä esittää liukulukuina,
	-- koska tästä voi seurata pyöristysvirheitä.
	( 1, 'pieni', 4.50 ),
	( 1, 'iso', 7.80 );
