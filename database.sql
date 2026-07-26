-- Script de création de la base de données
-- Vous pouvez l'importer directement dans phpMyAdmin ou via la ligne de commande MySQL :
-- mysql -u root -p < database.sql

CREATE DATABASE IF NOT EXISTS gesclasse_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gesclasse_db;

CREATE TABLE IF NOT EXISTS filieres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS niveaux (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    filiere_id INT NOT NULL,
    niveau_id INT NOT NULL,
    FOREIGN KEY (filiere_id) REFERENCES filieres(id) ON DELETE CASCADE,
    FOREIGN KEY (niveau_id) REFERENCES niveaux(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Insertion de données de test
INSERT INTO filieres (libelle) VALUES 
('Génie Logiciel'), 
('Réseaux et Télécoms'), 
('Data Science');

INSERT INTO niveaux (nom) VALUES 
('Licence 1'), 
('Licence 2'), 
('Licence 3'), 
('Master 1'), 
('Master 2');

INSERT INTO classes (nom, filiere_id, niveau_id) VALUES 
('L2 GL', 1, 2), 
('L3 RT', 2, 3);
