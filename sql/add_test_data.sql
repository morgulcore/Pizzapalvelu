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
	( 'admin', 6 ),		-- Gizza Admin, Sitruunakuja 7
	( 'admin', 10 ),	-- Gizza Admin, Kurpitsankantajankatu 45 F 6
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

-- Kentät: tuotetyyppi_id, tuotekategoria, tuotenimi, tuotekuvaus, kuva_tuotteesta
insert into Tuotetyyppi values
	( 1, 'pizza', 'Carnivore',
		'Tämä pizza on petojen sukua! Lihansyöjän varma valinta.',
		null ),
	( 2, 'pizza', 'Americano',
		'Ananasta, aurajuustoa, kinkkua – maistuis varmaan sullekin!',
		null ),
	( 3, 'vegaanipizza', 'Herbivore',
		'Maukas herkkupizza vegaaneille',
		null ),
	( 4, 'virvoitusjuoma', 'Sihi-Litku',
		'Keinotekoisen makuinen, voimakkaasti hiilihapotettu virvoitusjuoma',
		null ),
	( 5, 'olut', 'Sport Beer',
		'Kevyt lager-olut meneville, paljon liikkuville ihmisille',
		null ),
	( 6, 'olut', 'Pizza Beer',
		'Tämä olut on pizzan paras kaveri!',
		null ),
	( 7, 'muu', 'Pizzeria Omerta -lippalakki',
		'Jos olet vannoutunut asiakkaamme, voit ilmaista sen tällä lippalakilla :)',
		null );

insert into Tuote values
	( 1, 'iso', 8.90 ),
	( 2, 'tavallinen', 5.50 ),
	( 2, 'iso', 7.90 ),
	( 3, 'tavallinen', 5.50 ),
	( 4, 'tavallinen', 2.10 ),
	( 5, 'tavallinen', 2.60 ),
	( 6, 'pieni', 2.20 ),
	( 6, 'iso', 4.40 ),
	( 7, 'tavallinen', 6.50 );

insert into Tilattu_tuote ( tilaus_id, tuotetyyppi_id, tuoteversio, lukumaara )
	values
	( 1, 1, 'iso', 1 ),
	( 1, 2, 'tavallinen', 2 );

/*
-- 'valkosipuli', 'oregano', 'chili'
-- lisuke_id, kuvaus_lisukkeesta
insert into Lisuke values
	( 'valkosipuli', 'Laadukas luomuvalkosipulimurska lisukkeeksi pizzoihin' ),
	( 'oregano', 'Perinteinen pizzamauste' ),
	( 'chili', 'Lisäpotkua pizzaan tuoreella chilimurskalla' );

-- lisuke_id, tilaus_id, tuotelaskuri
insert into mm_Lisuke_Tilattu_tuote values
	( 'valkosipuli', 1, 1 ),
	( 'chili', 1, 1 ),
	( 'chili', 1, 2 );
*/
