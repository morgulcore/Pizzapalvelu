create table Asiakas(
	ktunnus varchar(20) primary key,
	on_paakayttaja boolean not null,
	salasana varchar(20),
	etunimi varchar(20) not null,
	sukunimi varchar(20) not null,
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
	ktunnus varchar(20) references Asiakas( ktunnus ),
	osoite_id integer references Osoite( osoite_id ),
	primary key( ktunnus, osoite_id )
);

create table Tilaus(
	tilaus_id serial primary key,
	ktunnus varchar(20) references Asiakas( ktunnus ),
	ts_tilauksen_teko timestamp not null, -- Ajankohta, jolloin tilaus tehtiin
	unique( ktunnus, ts_tilauksen_teko ),
	-- Lyhenne tak = toimitusajankohta. Jos kentän ts_tak_toivottu arvona
	-- on NULL, tarkoittaa se asiakkaan toivomusta siitä, että tilaus
	-- toimitettaisiin hänelle mahdollisimman pian.
	ts_tak_toivottu timestamp, -- Toivottu toimitusajankohta
	ts_tak_toteutunut timestamp default null, -- Toteutunut toimitusajankohta
	osoite_id integer references Osoite( osoite_id )
);

create type Ongelma_enum as enum ( 'violence', 'customer_not_found', 'no_payment');
create table Ongelma (
	tilaus_id integer references Tilaus( tilaus_id ),
	ongelman_tyyppi Ongelma_enum not null,
	ts_ongelma timestamp not null, -- Ongelman ilmenemisen ajankohta
	ongelman_kuvaus varchar(2000),
	primary key( tilaus_id, ongelman_tyyppi )
);

-- Määritellään tuotekategorian ilmaisemiseen oma tietotyyppinsä
create type Tuotekategoria_enum as enum(
	'pizza', 'vegaanipizza', 'virvoitusjuoma', 'olut', 'muu' );
-- Tuotteen hinta kerrotaan tästä löytyvillä kertoimilla. Tuotteista voidaan
-- veloittaa enemmän öisin ja silloin, jos tilauksen asiakkaaseen tai
-- osoitteeseen on aiemmin liittynyt ongelmia.
create table Hintamuunnos (
	tuotekategoria Tuotekategoria_enum primary key,
	yohintakerroin numeric(2,1) not null default 1.0, --yö
	ongelmahintakerroin numeric(2,1) not null default 1.0
);

create table Tuotetyyppi (
	tuotetyyppi_id serial primary key,
	-- Olisi fiksumpaa, jos jokainen tuote voisi kuulua useampaan kuin yhteen
	-- tuotekategoriaan (esim. kategoriat pizza ja vegaani). Yksinkertaisuuden
	-- nimissä toteutan tuotekategorian kuitenkin pelkkänä kenttänä.
	-- Huomaa, että tuotekategoria on myös viiteavain tauluun Hintamuunnos.
	tuotekategoria Tuotekategoria_enum references Hintamuunnos( tuotekategoria ),
	tuotenimi varchar(50) not null,
	tuotekuvaus varchar(1000),
	kuva_tuotteesta varchar(80)
		default '~/htdocs/pizzapalvelu/images/default.jpg'
);

-- Jokaisesta tuotetyypistä on yksi tai useampi versio
create type Tuoteversio_enum as enum( 'pieni', 'tavallinen', 'iso' );
create table Tuote ( -- Tuotetta voi ajatella sen tuotetyypin "ilmentymänä"
	tuotetyyppi_id integer references Tuotetyyppi( tuotetyyppi_id ),
	tuoteversio Tuoteversio_enum not null,
	-- Tuotteen hinta (ilman vuorokaudenajasta riippuvaa hintamuunnosta).
	-- Hinta voi olla korkeintaan 9999,99. Desimaaleja on kaksi.
	hinta numeric(6, 2) not null,
	primary key( tuotetyyppi_id, tuoteversio )
);

create table Tilattu_tuote (
	tilaus_id integer references Tilaus( tilaus_id ),
	-- Jokaiselle tilatulle tuotteelle annetaan numero. Numeroin alkaa
	-- ykkösestä. Esim. (1) 2 * Americano (iso), (2) 1 * Coca-Cola (iso),
	-- (3) 1 * Olut (iso).
	tuotelaskuri serial,
	tuotetyyppi_id integer not null,
	tuoteversio Tuoteversio_enum not null,
	lukumaara integer, -- Esim. 2 * Americano (iso)
	foreign key ( tuotetyyppi_id, tuoteversio ) references Tuote (
		tuotetyyppi_id, tuoteversio ),
	primary key ( tilaus_id, tuotelaskuri )
);

/*
create type Lisuke_enum as enum ( 'valkosipuli', 'oregano', 'chili' );
create table Lisuke (
	lisuke_id Lisuke_enum primary key,
	kuvaus_lisukkeesta varchar(500)
);

create table mm_Lisuke_Tilattu_tuote (
	lisuke_id Lisuke_enum references Lisuke( lisuke_id ),
	tilaus_id integer not null,
	tuotelaskuri integer not null,
	foreign key ( tilaus_id, tuotelaskuri ) references Tilattu_tuote(
		tilaus_id, tuotelaskuri ),
	primary key ( lisuke_id, tilaus_id, tuotelaskuri )
);
*/
