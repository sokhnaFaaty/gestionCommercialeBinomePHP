-- =========================================================================
-- MIGRATION : la table client devient utilisateur (clients + gestionnaires)
-- -------------------------------------------------------------------------
-- À exécuter UNIQUEMENT sur une base qui contient déjà des données.
-- Sur une base neuve, database.sql suffit : il crée déjà utilisateur.
--
-- PostgreSQL fait suivre les clés étrangères lors d'un RENAME : la colonne
-- commande.client_id continue de fonctionner et pointe désormais utilisateur.
-- Le script peut être relancé sans erreur.
-- =========================================================================

DO $$
BEGIN
    IF to_regclass('public.client') IS NOT NULL THEN

        -- Colonnes d'authentification, ajoutées en NULL autorisé...
        ALTER TABLE client ADD COLUMN IF NOT EXISTS email    VARCHAR(150);
        ALTER TABLE client ADD COLUMN IF NOT EXISTS password VARCHAR(255);
        ALTER TABLE client ADD COLUMN IF NOT EXISTS role     VARCHAR(20) NOT NULL DEFAULT 'client';

        -- ...puis remplies pour les lignes déjà présentes
        UPDATE client SET email    = lower(prenom || '.' || nom || '@exemple.sn') WHERE email IS NULL;
        UPDATE client SET password = 'passer123'                                  WHERE password IS NULL;

        ALTER TABLE client ALTER COLUMN email    SET NOT NULL;
        ALTER TABLE client ALTER COLUMN password SET NOT NULL;

        -- Un gestionnaire n'a pas de téléphone
        ALTER TABLE client ALTER COLUMN telephone DROP NOT NULL;

        ALTER TABLE client RENAME TO utilisateur;
    END IF;
END $$;

-- Unicité de l'email (le nom de la contrainte dépend de l'historique de la base)
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint
        WHERE conrelid = 'utilisateur'::regclass
          AND contype = 'u'
          AND conname LIKE '%email%'
    ) THEN
        ALTER TABLE utilisateur ADD CONSTRAINT utilisateur_email_key UNIQUE (email);
    END IF;
END $$;

-- Le role ne peut prendre que deux valeurs
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint
        WHERE conrelid = 'utilisateur'::regclass
          AND conname = 'utilisateur_role_check'
    ) THEN
        ALTER TABLE utilisateur
            ADD CONSTRAINT utilisateur_role_check CHECK (role IN ('client', 'gestionnaire'));
    END IF;
END $$;

-- Compte gestionnaire de test
INSERT INTO utilisateur (nom, prenom, telephone, email, password, role)
VALUES ('Sow', 'Fatou', NULL, 'gestionnaire@exemple.sn', 'passer123', 'gestionnaire')
ON CONFLICT (email) DO NOTHING;
