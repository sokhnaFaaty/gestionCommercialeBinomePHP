-- =========================================================================
-- SCRIPT DE CRÉATION DE LA BASE DE DONNÉES COMPLET (POSTGRESQL)
-- =========================================================================

-- Suppression des tables si elles existent déjà (pour réinitialiser proprement)
DROP TABLE IF EXISTS ligne_commande CASCADE;
DROP TABLE IF EXISTS paiement CASCADE;
DROP TABLE IF EXISTS facture CASCADE;
DROP TABLE IF EXISTS commande CASCADE;
DROP TABLE IF EXISTS produit CASCADE;
DROP TABLE IF EXISTS client CASCADE;

DROP TYPE IF EXISTS type_statut_paiement CASCADE;
DROP TYPE IF EXISTS type_statut_produit CASCADE;

-- 1. Création des énumérations (ENUMS)
CREATE TYPE type_statut_paiement AS ENUM ('partiellement_payee', 'totalement_payee', 'non payee');
CREATE TYPE type_statut_produit AS ENUM ('disponible', 'rupture');

-- 2. Création des tables (dans l'ordre strict des dépendances)

-- Table client (Doit être créée en premier)
-- email / password / role servent à l'authentification (voir helpers auth() et hasRole()).
-- TODO (branche auth) : le mot de passe est stocké en clair pour l'instant, à hacher.
CREATE TABLE client (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    telephone VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'client'
);

-- Table produit (Doit être créée en deuxième)
CREATE TABLE produit (
    id SERIAL PRIMARY KEY,
    libelle VARCHAR(150) NOT NULL,
    qte_stock INT NOT NULL DEFAULT 0,
    prix_unitaire NUMERIC(10, 2) NOT NULL,
    statut type_statut_produit NOT NULL DEFAULT 'disponible'
);

-- Table commande (Dépend de client)
CREATE TABLE commande (
    id SERIAL PRIMARY KEY,
    numero VARCHAR(50) NOT NULL UNIQUE,
    date DATE NOT NULL DEFAULT CURRENT_DATE,
    montant_total NUMERIC(10, 2) NOT NULL DEFAULT 0.00,
    validee BOOLEAN NOT NULL DEFAULT FALSE,
    client_id INT NOT NULL REFERENCES client(id) ON DELETE RESTRICT
);

-- Table facture (Dépend de commande)
CREATE TABLE facture (
    id SERIAL PRIMARY KEY,
    numero VARCHAR(50) NOT NULL UNIQUE,
    date DATE NOT NULL DEFAULT CURRENT_DATE,
    montant NUMERIC(10, 2) NOT NULL,
    commande_id INT NOT NULL UNIQUE REFERENCES commande(id) ON DELETE CASCADE
);

-- Table paiement (Dépend de facture)
CREATE TABLE paiement (
    id SERIAL PRIMARY KEY,
    numero VARCHAR(50) NOT NULL UNIQUE,
    montant_verse NUMERIC(10, 2) NOT NULL,
    date DATE NOT NULL DEFAULT CURRENT_DATE,
    statut type_statut_paiement NOT NULL DEFAULT 'non payee',
    facture_id INT NOT NULL REFERENCES facture(id) ON DELETE CASCADE
);

-- Table ligne_commande (Dépend de commande et produit)
CREATE TABLE ligne_commande (
    id SERIAL PRIMARY KEY,
    quantite INT NOT NULL CHECK (quantite > 0),
    prix NUMERIC(10, 2) NOT NULL,
    commande_id INT NOT NULL REFERENCES commande(id) ON DELETE CASCADE,
    produit_id INT NOT NULL REFERENCES produit(id) ON DELETE RESTRICT
);

-- =========================================================================
-- 3. JEU DE DONNÉES DE TEST AUTOMATIQUE (1, 2, 3...)
-- =========================================================================

-- Insertion d'un client (Prendra l'ID 1)
INSERT INTO client (nom, prenom, telephone, email, password, role)
VALUES ('Diop', 'Sokhna', '771234567', 'sokhna.diop@exemple.sn', 'passer123', 'client');

-- Insertion d'un produit (Prendra l'ID 1)
INSERT INTO produit (libelle, qte_stock, prix_unitaire, statut) 
VALUES ('Souris', 50, 5000.00, 'disponible');

-- Insertion d'une commande liée au client 1 (Prendra l'ID 1)
INSERT INTO commande (numero, date, montant_total, validee, client_id) 
VALUES ('C001', '2026-08-20', 20000.00, TRUE, 1);

-- Insertion d'une ligne de commande liée à la commande 1 et au produit 1 (Prendra l'ID 1)
INSERT INTO ligne_commande (quantite, prix, commande_id, produit_id) 
VALUES (10, 5000.00, 1, 1);
