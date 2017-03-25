--Kommentoin nämä create table -lauseet vähän myöhemmin jahka kerkeän

create table Kayttaja(
	ktunnus varchar(20) primary key,
	--Jos password on NULL, käyttäjätiliä ei voi käyttää
	--järjestelmään kirjautumiseen
	salasana varchar(50),
	--Käyttäjätunnuksen tyyppi määrää siihen liittyvät oikeudet.
	--Asiakkaiden käyttäjätunnusten tyyppi on aina 0. Jos käyttäjätunnuksen
	--tyyppi on enemmän kuin 0, sillä voi suorittaa järjestelmän ylläpitoon
	--liittyviä toimenpiteitä.
	tyyppi integer not null
);

create table Asiakas(
	asiakas_id serial primary key,
	ktunnus varchar(20) references Kayttaja( ktunnus ),
	etunimi varchar(50) not null,
	sukunimi varchar(50) not null,
	puhelinnumero varchar(20),
	sahkopostiosoite varchar(50)
);

create table Osoite(
	osoite_id serial primary key,
	lahiosoite varchar(50) not null,
	postinumero varchar(10) not null,
	postitoimipaikka varchar(50) not null,
	unique( lahiosoite, postinumero, postitoimipaikka )
);

create table mm_Asiakas_Osoite(
	asiakas_id integer references Asiakas( asiakas_id ),
	osoite_id integer references Osoite( osoite_id ),
	primary key( asiakas_id, osoite_id )
);

create table Tilaus(
	tilaus_id serial primary key,
	asiakas_id integer references Asiakas( asiakas_id ),
	ts_tilauksen_teko timestamp not null, -- Ajankohta, jolloin tilaus tehtiin
	unique( asiakas_id, ts_tilauksen_teko ),
	-- Lyhenne tak = toimitusajankohta. Jos kentän ts_tak_toivottu arvona
	-- on NULL, tarkoittaa se asiakkaan toivomusta siitä, että tilaus
	-- toimitettaisiin hänelle mahdollisimman pian.
	ts_tak_toivottu timestamp, -- Toivottu toimitusajankohta
	ts_tak_toteutunut timestamp default null, -- Toteutunut toimitusajankohta
	osoite_id integer references Osoite( osoite_id )
);

create type Ongelma_enum as enum( 'violence', 'customer_not_found', 'no_payment');
create table Ongelma(
	tilaus_id integer references Tilaus( tilaus_id ),
	ongelman_tyyppi Ongelma_enum not null,
	ts_ongelma timestamp not null,
	ongelman_kuvaus varchar(2000),
	primary key( tilaus_id, ongelman_tyyppi )
);
