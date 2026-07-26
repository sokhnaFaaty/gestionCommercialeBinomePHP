-- =========================================================================
-- MIGRATION : ajout de email / password / role à la table client
-- -------------------------------------------------------------------------
-- À exécuter UNIQUEMENT si la base existe déjà avec des données.
-- Sur une base neuve, database.sql contient déjà ces colonnes.
--
-- On ajoute d'abord les colonnes en NULL autorisé, on remplit les lignes
-- existantes, puis on pose les contraintes NOT NULL et UNIQUE.
-- =========================================================================

ALTER TABLE client ADD COLUMN IF NOT EXISTS email    VARCHAR(150);
ALTER TABLE client ADD COLUMN IF NOT EXISTS password VARCHAR(255);
ALTER TABLE client ADD COLUMN IF NOT EXISTS role     VARCHAR(20) NOT NULL DEFAULT 'client';

-- Valeurs par défaut pour les clients déjà enregistrés.
UPDATE client
SET email = lower(prenom || '.' || nom || '@exemple.sn')
WHERE email IS NULL;

UPDATE client
SET password = 'passer123'
WHERE password IS NULL;

ALTER TABLE client ALTER COLUMN email    SET NOT NULL;
ALTER TABLE client ALTER COLUMN password SET NOT NULL;

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint WHERE conname = 'client_email_key'
    ) THEN
        ALTER TABLE client ADD CONSTRAINT client_email_key UNIQUE (email);
    END IF;
END $$;
