DROP DATABASE IF EXISTS polaznik07_zavrsni;
CREATE DATABASE polaznik07_zavrsni CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
use polaznik07_zavrsni;

create table room (
id int not null primary key auto_increment,
description text not null,
category int not null,
status boolean default false
)engine=InnoDB;

create table category (
id int not null primary key auto_increment,
name varchar(100) not null
)engine=InnoDB;

create table subcategory (
id int not null primary key auto_increment,
name varchar(100),
category int not null
)engine=InnoDB;

create table reservation (
id int not null primary key auto_increment,
room int not null,
datefrom varchar(100) not null,
dateto varchar(100) not null,
email varchar(100) not null,
status boolean default false
)engine=InnoDB;



alter table room add foreign key (category) references category(id);

alter table subcategory add foreign key (category) references category(id);

alter table reservation add foreign key (room) references room(id);

