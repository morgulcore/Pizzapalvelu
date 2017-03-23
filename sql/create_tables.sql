--Kommentoin nämä create table -lauseet vähän myöhemmin jahka kerkeän

create table Asiakas(
	asiakas_id serial primary key,
	ktunnus varchar(50) not null,
	etunimi varchar(50) not null,
	sukunimi varchar(50) not null,
	puhelinnumero varchar(20)
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
	ts_tilauksen_teko timestamp not null,
	asiakas_id integer references Asiakas( asiakas_id ),
	osoite_id integer references Osoite( osoite_id ),
	ts_tilauksen_toimitus timestamp default null,
	primary key( ts_tilauksen_teko, asiakas_id )
);

create type Ongelma_enum as enum( 'violence', 'customer_not_found', 'no_payment');
create table Ongelma(
	ts_tilauksen_teko timestamp not null,
	asiakas_id integer not null,
	foreign key ( ts_tilauksen_teko, asiakas_id )
		references Tilaus( ts_tilauksen_teko, asiakas_id ),
	ongelman_tyyppi Ongelma_enum not null,
	ts_ongelma timestamp not null,
	ongelman_kuvaus varchar(1000),
	primary key( ts_tilauksen_teko, asiakas_id, ongelman_tyyppi )
);

--create table Tilaus(
--	tilaus_id serial primary key,
--	asiakas_id integer references asiakas( asiakas_id ) -- Viiteavain Asiakas-tauluun
--  name varchar(50) NOT NULL,
--  played boolean DEFAULT FALSE,
--  description varchar(400),
--  published DATE,
--  publisher varchar(50),
--  added DATE
--);
