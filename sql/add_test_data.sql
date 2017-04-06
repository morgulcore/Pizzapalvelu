insert into Kayttaja values
	-- ( 'admin', 'henrik1975', 1 ), -- Ensimmäisenä ylläpidon käyttäjätunnus
	( 'mruusu', 'tsoha2017', 1 ), -- Loput käyttäjätunnukset liittyvät asiakkaisiin
	( 'jporkkana', 'tsoha2017', 0 ),
	( 'skurpitsa', 'tsoha2017', 0 ),
	( 'nvadelma', 'tsoha2017', 1 ),
	( 'therne', 'tsoha2017', 0 ),
	( 'tmansikka', 'tsoha2017', 0 ),
	( 'smansikka', 'tsoha2017', 0 ),
	( 'mmansikka', 'tsoha2017', 0 ),
	( 'sruusu', 'tsoha2017', 0 ),
	( 'pnauris', 'tsoha2017', 0 );

insert into Asiakas ( ktunnus, etunimi, sukunimi, puhelinnumero, sahkopostiosoite )
	values
    ( 'mruusu', 'Minna', 'Ruusu', null, null ),
    ( 'jporkkana', 'Juuso', 'Porkkana', '040 123 4567', null ),
    ( 'skurpitsa', 'Santeri', 'Kurpitsa', null, 'santeri.kurpitsa@virasto.fi' ),
    ( 'nvadelma', 'Niko', 'Vadelma', null, 'vattu@pikaposti.fi' ),
    ( 'therne', 'Tauno', 'Herne', null, null ),
    ( 'tmansikka', 'Tero', 'Mansikka', '040 987 6543', 'tero.mansikka@mansikka.fi' ),
    ( 'smansikka', 'Saara', 'Mansikka', null, 'saara.mansikka@mansikka.fi' ),
    ( 'mmansikka', 'Minttu', 'Mansikka', null, 'minttu.mansikka@mansikka.fi' ),
    ( 'sruusu', 'Sini', 'Ruusu', null, 'ruusunen@kohtalo.fi' ),
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

	-- Kentät: tuotekategoria, yohintakerroin, ongelmahintakerroin
insert into Hintamuunnos values
	( 'pizza', 1.2, 1.4 ),
	( 'vegaanipizza', 1.3, 1.4 ),
	( 'virvoitusjuoma', 1.0, 1.4 ),
	( 'olut', 1.0, 2.0 ),
	( 'muu', 1.0, 1.4 );

insert into Tuotetyyppi ( tuotekategoria, tuotenimi, tuotekuvaus ) values
	( 'pizza', 'Carnivore',
		'Tosimiehen pizza! Syötyäsi tämän pääset ohitusleikkaukseen!' ),
	( 'vegaanipizza', 'Herbivore', 'Nälkäisen ituhipin valinta' );

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
	( 1, 'iso', 7.80 ),
	( 2, 'pieni', 5.10 );

insert into Tilattu_tuote ( tilaus_id, tuotetyyppi_id, tuoteversio, lukumaara )
	values
	( 1, 1, 'iso', 1 ),
	( 1, 2, 'pieni', 2 );

-- 'valkosipuli', 'oregano', 'chili'
-- lisuke_id, kuvaus_lisukkeesta
insert into Lisuke values
	( 'valkosipuli', 'Laadukas luomuvalkosipulimurska lisukkeeksi pizzoihin' ),
	( 'oregano', 'Perinteinen pizzamauste' ),
	( 'chili', 'Lisäpotkua pizzaan tuoreella chilimurskalla' );

-- lisuke_id, tilaus_id, tuotelaskuri
/*
insert into mm_Lisuke_Tilattu_tuote values
	( 'valkosipuli', 1, 1 ),
	( 'chili', 1, 1 ),
	( 'chili', 1, 2 );
*/
