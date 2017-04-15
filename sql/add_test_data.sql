insert into Asiakas values
	( 'admin', true, 'Tsoh4', 'Gizza', 'Admin', '050 123 1198', 'admin@gizza.fi' ),
    ( 'mruusu', false, 'Tsoh4', 'Minna', 'Ruusu', null, null ),
    ( 'jporkkana', false, 'Tsoh4', 'Juuso', 'Porkkana', '040 123 4567', null ),
    ( 'skurpitsa', false, 'Tsoh4', 'Santeri', 'Kurpitsa', null, 'santeri.kurpitsa@virasto.fi' ),
    ( 'nvadelma', false, 'Tsoh4', 'Niko', 'Vadelma', null, 'vattu@pikaposti.fi' ),
    ( 'therne', false, 'Tsoh4', 'Tauno', 'Herne', null, null ),
    ( 'tmansikka', false, 'Tsoh4', 'Tero', 'Mansikka', '040 321 6543', 'tero.mansikka@mansikka.fi' ),
    ( 'smansikka', false, 'Tsoh4', 'Saara', 'Mansikka', null, 'saara.mansikka@mansikka.fi' ),
    ( 'mmansikka', false, 'Tsoh4', 'Minttu', 'Mansikka', null, 'minttu.mansikka@mansikka.fi' ),
    ( 'sruusu', false, 'Tsoh4', 'Sini', 'Ruusu', null, 'ruusunen@kohtalo.fi' ),
    ( 'pnauris', false, 'Tsoh4','Pekka', 'Nauris', '050 123 1983', null );

insert into Osoite ( lahiosoite, postinumero, postitoimipaikka ) values
	( 'Savirinne 17', '29094', 'Tuonela' ),						-- 1
	( 'Nokkosentie 6 B 15', '29818', 'Tuonela' ),				-- 2
	( 'Metsätie 87', '01642', 'Synkkälä' ),						-- 3
	( 'Omenapolku 7 A', '29396', 'Tuonela' ),					-- 4
	( 'Pihlajakatu 21 C 10', '29396', 'Tuonela' ),				-- 5
	( 'Sitruunakuja 7', '29145', 'Tuonela' ),					-- 6
	( 'Kadotuksentie 13', '06660', 'Gehenna' ),					-- 7
	( 'Korpipolku 3', '01642', 'Synkkälä' ),					-- 8
	( 'Kurpitsankantajankatu 33 B 15', '29400', 'Tuonela' ),	-- 9
	( 'Kurpitsankantajankatu 45 F 6', '29400', 'Tuonela' );		-- 10

insert into mm_Asiakas_Osoite values
	( 'mruusu', 5 ),	-- Minna Ruusu, Pihlajakatu 21 C 10
	( 'therne', 7 ),	-- Tauno Herne, Kadotuksentie 13
	( 'tmansikka', 1 ),	-- Tero Mansikka, Savirinne 17
	( 'tmansikka', 9 ),	-- Tero Mansikka, Kurpitsankantajankatu 33 B 15
	( 'smansikka', 1 ),	-- Saara Mansikka, Savirinne 17
	( 'mmansikka', 1 ),	-- Minttu Mansikka, Savirinne 17
	( 'jporkkana', 2 ), -- Juuso Porkkana, Nokkosentie 6 B 15
	( 'jporkkana', 6 ); -- Juuso Porkkana, Sitruunakuja 7

insert into Tilaus ( ktunnus, ts_tilauksen_teko, ts_tak_toivottu, ts_tak_toteutunut, osoite_id )
	values
	( 'tmansikka', timestamp '2017-01-19 10:23:54', null, timestamp '2017-01-19 11:05:37', 9 ),
	( 'therne', timestamp '2017-01-20 12:01:02', null, null, 7 ),
	( 'tmansikka', timestamp '2017-03-25 23:15:46', null, null, 1 ),
	( 'therne', timestamp '2017-01-22 14:37:16', null, timestamp '2017-01-22 15:32:07', 7 ),
	( 'mruusu', timestamp '2017-02-14 10:02:01', null, null, 5 ),
	( 'jporkkana', timestamp '2017-03-25 13:12:51', timestamp '2017-04-01 00:00:00', null, 2 );

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
