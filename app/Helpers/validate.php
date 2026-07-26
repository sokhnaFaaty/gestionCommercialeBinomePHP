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

function validDataProduit(array $data): array
{

    $errors = [];

    if (empty($data["reference"])) {
        $errors["reference"] = "Veuillez remplir la référence";
    }

    if (empty($data["libelle"])) {
        $errors["libelle"] = "Veuillez remplir le libellé";
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
