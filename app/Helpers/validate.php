<?php

function validDataClient(array $data): array
{
    $errors = [];
    if (empty($data["nom"])) {
        $errors["nomVide"] = "Veuillez remplir le nom";
    }
    if (empty($data["prenom"])) {
        $errors["prenomVide"] = "Veuillez remplir le prenom";
    }
    if (empty($data["telephone"])) {
        $errors["telephoneVide"] = "Veuillez remplir le telephone";
    }
    if (empty($data["email"])) {
        $errors["email"] = "Veuillez remplir l'email";
    }
    if (empty($data["password"])) {
        $errors["password"] = "Veuillez saisir un mot de passe";
    } elseif (strlen($data["password"]) < 6) {
        $errors["password"] = "Le mot de passe doit contenir au moins 6 caractères";
    }
    return $errors;
}

/**
 * Contrôles de forme du formulaire de connexion.
 *
 * On ne vérifie ici que la présence des champs. Savoir si le couple
 * email / mot de passe est correct demande la base : c'est le rôle de
 * AuthController::authenticate().
 */
function validDataLogin(array $data): array
{
    $errors = [];

    if (empty($data["email"])) {
        $errors["email"] = "Veuillez saisir votre email";
    }

    if (empty($data["password"])) {
        $errors["password"] = "Veuillez saisir votre mot de passe";
    }

    return $errors;
}

function validDataProduit(array $data): array
{

    $errors = [];

    if (empty($data["reference"])) {
        $errors["reference"] = "Veuillez remplir la référence";
    }

    if (empty($data["libelle"])) {
        $errors["libelle"] = "Veuillez remplir le libellé";
    }
 if (empty($data["categorie_id"])) {
        $errors["categorie_id"] = "Veuillez choisir une catégorie";
    }
    if (empty($data["prix"])) {
        $errors["prix"] = "Veuillez remplir le prix";
    } elseif (!is_numeric($data["prix"]) || $data["prix"] <= 0) {
        $errors["prix"] = "Le prix doit être un nombre positif";
    }

    if (empty($data["quantite"])) {
        $errors["quantite"] = "Veuillez remplir la quantité";
    } elseif (!is_numeric($data["quantite"]) || $data["quantite"] < 0) {
        $errors["quantite"] = "La quantité doit être valide";
    }

    return $errors;
}

/**
 * Contrôles de forme du formulaire de commande.
 *
 * $data attend :
 *   'telephone' => string
 *   'lignes'    => [ ['reference' => string, 'quantite' => string], ... ]
 *
 * L'existence du client et des produits est vérifiée dans le controller :
 * elle demande la base de données.
 */
function validDataCommande(array $data): array
{
    $errors = [];

    if (empty($data["telephone"])) {
        $errors["telephone"] = "Veuillez saisir le téléphone du client";
    }

    // Le formulaire ajoute des lignes à la volée : celles restées entièrement
    // vides sont simplement ignorées, elles ne sont pas des erreurs.
    $lignesRemplies = 0;

    foreach ($data["lignes"] ?? [] as $i => $ligne) {
        $reference = trim($ligne["reference"] ?? '');
        $quantite  = trim($ligne["quantite"] ?? '');

        if ($reference === '' && $quantite === '') {
            continue;
        }

        $lignesRemplies++;

        if ($reference === '') {
            $errors["ligne_{$i}_reference"] = "Veuillez saisir la référence";
        }

        if ($quantite === '') {
            $errors["ligne_{$i}_quantite"] = "Veuillez saisir la quantité";
        } elseif (!ctype_digit($quantite) || (int) $quantite < 1) {
            $errors["ligne_{$i}_quantite"] = "La quantité doit être un entier supérieur à 0";
        }
    }

    if ($lignesRemplies === 0) {
        $errors["lignes"] = "Veuillez ajouter au moins un produit";
    }

    return $errors;
}

function validDataCategorie(array $data): array
{
    $errors = [];

    if (empty($data["libelle"])) {
        $errors["libelle"] = "Veuillez remplir le libellé";
    }

    return $errors;
}

function validDataPaiement(array $data): array
{
    $errors = [];

    if ($data['montant_verse'] === '' || $data['montant_verse'] === null) {
        $errors['montant_verse'] = 'Veuillez saisir un montant';
    } elseif (!is_numeric($data['montant_verse']) || (float) $data['montant_verse'] <= 0) {
        $errors['montant_verse'] = 'Le montant doit être un nombre positif';
    }

    return $errors;
}