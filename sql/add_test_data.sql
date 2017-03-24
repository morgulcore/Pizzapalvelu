insert into Asiakas ( ktunnus, etunimi, sukunimi, puhelinnumero ) values
    ( 'mruusu', 'Minna', 'Ruusu', null ),
    ( 'jporkkana', 'Juuso', 'Porkkana', '040 123 4567' ),
    ( 'skurpitsa', 'Santeri', 'Kurpitsa', null ),
    ( 'nvadelma', 'Niko', 'Vadelma', null ),
    ( 'therne', 'Tauno', 'Herne', null ),
    ( 'tmansikka', 'Tero', 'Mansikka', null ),
    ( 'smansikka', 'Saara', 'Mansikka', null ),
    ( 'mmansikka', 'Minttu', 'Mansikka', null ),
    ( 'sruusu', 'Sini', 'Ruusu', null ),
    ( 'pnauris', 'Pekka', 'Nauris', null );

insert into Osoite ( lahiosoite, postinumero, postitoimipaikka ) values
	( 'Savirinne 13', '29094', 'Tuonela' ),
	-- Seuraava ei onnistuisi tai ainakaan sen ei pitäisi onnistua. Osoitteiden
	-- pitää nimittäin olla uniikkeja (unique constraint).
	-- ( 'Savirinne 13', '29094', 'Tuonela' ),
	( 'Nokkosentie 6 B 15', '29818', 'Tuonela' );
