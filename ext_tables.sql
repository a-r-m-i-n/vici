CREATE TABLE tx_vici_table
(
    label varchar(15) DEFAULT '' NOT NULL,
    title varchar(255) DEFAULT '' NOT NULL
);

CREATE TABLE tx_vici_table_column
(
    title varchar(255) DEFAULT '' NOT NULL,
    description text,
    placeholder text
);

CREATE TABLE tx_vici_table_column_item
(
    name varchar(255) DEFAULT '' NOT NULL
);


CREATE TABLE tx_vici_translations
(
    identifier varchar(255) DEFAULT '' NOT NULL,
    language varchar(7) DEFAULT '' NOT NULL,
    translation text,
    PRIMARY KEY (identifier, language)
);
