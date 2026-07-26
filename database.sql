-- Active: 1782565226152@@127.0.0.1@5432@gestion_commerciale_binomephp@public
-- =========================================================================
-- SCRIPT DE CRÉATION DE LA BASE DE DONNÉES COMPLET (POSTGRESQL)
-- =========================================================================

DROP TABLE IF EXISTS produit_commande CASCADE;
DROP TABLE IF EXISTS paiement CASCADE;
DROP TABLE IF EXISTS facture CASCADE;
DROP TABLE IF EXISTS commande CASCADE;
DROP TABLE IF EXISTS produit CASCADE;
DROP TABLE IF EXISTS categorie CASCADE;
DROP TABLE IF EXISTS utilisateur CASCADE;
DROP TABLE IF EXISTS client CASCADE;

DROP TYPE IF EXISTS type_statut_paiement CASCADE;
DROP TYPE IF EXISTS type_statut_produit CASCADE;

-- 1. Énumérations
CREATE TYPE type_statut_paiement AS ENUM ('partiellement_payee', 'totalement_payee', 'non payee');
CREATE TYPE type_statut_produit AS ENUM ('disponible', 'rupture');

-- 2. Tables (dans l'ordre des dépendances)

CREATE TABLE utilisateur (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    telephone VARCHAR(20) UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'client'
        CHECK (role IN ('client', 'gestionnaire'))
);

CREATE TABLE categorie (
    id SERIAL PRIMARY KEY,
    libelle VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE produit (
    id SERIAL PRIMARY KEY,
    reference VARCHAR(50) NOT NULL UNIQUE,
    libelle VARCHAR(150) NOT NULL,
    qte_stock INT NOT NULL DEFAULT 0,
    prix_unitaire NUMERIC(10, 2) NOT NULL,
    statut type_statut_produit NOT NULL DEFAULT 'disponible',
    categorie_id INT NOT NULL REFERENCES categorie(id) ON DELETE RESTRICT
);

CREATE TABLE commande (
    id SERIAL PRIMARY KEY,
    numero VARCHAR(50) NOT NULL UNIQUE,
    date DATE NOT NULL DEFAULT CURRENT_DATE,
    montant_total NUMERIC(10, 2) NOT NULL DEFAULT 0.00,
    validee BOOLEAN NOT NULL DEFAULT FALSE,
    client_id INT NOT NULL REFERENCES utilisateur(id) ON DELETE RESTRICT
);

CREATE TABLE facture (
    id SERIAL PRIMARY KEY,
    numero VARCHAR(50) NOT NULL UNIQUE,
    date DATE NOT NULL DEFAULT CURRENT_DATE,
    montant NUMERIC(10, 2) NOT NULL,
    commande_id INT NOT NULL UNIQUE REFERENCES commande(id) ON DELETE CASCADE
);

CREATE TABLE paiement (
    id SERIAL PRIMARY KEY,
    numero VARCHAR(50) NOT NULL UNIQUE,
    montant_verse NUMERIC(10, 2) NOT NULL,
    date DATE NOT NULL DEFAULT CURRENT_DATE,
    statut type_statut_paiement NOT NULL DEFAULT 'non payee',
    facture_id INT NOT NULL REFERENCES facture(id) ON DELETE CASCADE
);

-- produit_commande : table de jointure commande <-> produit (anciennement ligne_commande).
-- Le prix est figé au moment de la commande.
CREATE TABLE produit_commande (
    id SERIAL PRIMARY KEY,
    quantite INT NOT NULL CHECK (quantite > 0),
    prix NUMERIC(10, 2) NOT NULL,
    commande_id INT NOT NULL REFERENCES commande(id) ON DELETE CASCADE,
    produit_id INT NOT NULL REFERENCES produit(id) ON DELETE RESTRICT
);

-- =========================================================================
-- 3. JEU DE DONNÉES DE TEST
-- =========================================================================

INSERT INTO utilisateur (nom, prenom, telephone, email, password, role)
VALUES ('Diop', 'Sokhna', '771234567', 'sokhna.diop@exemple.sn', 'passer123', 'client');

INSERT INTO utilisateur (nom, prenom, telephone, email, password, role)
VALUES ('Sow', 'Fatou', NULL, 'gestionnaire@exemple.sn', 'passer123', 'gestionnaire');

INSERT INTO categorie (libelle) VALUES ('Informatique');

INSERT INTO produit (reference, libelle, qte_stock, prix_unitaire, statut, categorie_id)
VALUES ('PROD-000001', 'Souris', 50, 5000.00, 'disponible', 1);

INSERT INTO commande (numero, date, montant_total, validee, client_id)
VALUES ('C001', '2026-08-20', 20000.00, TRUE, 1);

INSERT INTO produit_commande (quantite, prix, commande_id, produit_id)
VALUES (10, 5000.00, 1, 1);