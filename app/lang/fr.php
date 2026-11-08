<?php

declare(strict_types=1);

defined('FOOD_APP') || exit;

/** French catalog: English source text => French. {name} placeholders must be kept. */
return [
    // layout, navigation, auth
    'Food stock' => 'Stock alimentaire',
    'Main navigation' => 'Navigation principale',
    'Language' => 'Langue',
    'Stock' => 'Stock',
    'Locations' => 'Emplacements',
    'Containers' => 'Contenants',
    'Log out' => 'Se déconnecter',
    'Sign in' => 'Se connecter',
    'Sign in to continue' => 'Connectez-vous pour continuer',
    'E-mail' => 'Adresse e-mail',
    'Password' => 'Mot de passe',
    'Invalid e-mail or password.' => 'Adresse e-mail ou mot de passe incorrect.',
    'Too many login attempts. Please wait {minutes} minutes and try again.'
        => 'Trop de tentatives de connexion. Patientez {minutes} minutes puis réessayez.',

    // page titles
    'Stock entry' => 'Ligne de stock',
    'Add food' => 'Ajouter des aliments',
    'Error' => 'Erreur',

    // home
    'Search a product' => 'Rechercher un produit',
    'Search a product…' => 'Rechercher un produit…',
    'All locations' => 'Tous les emplacements',
    'Filter' => 'Filtrer',
    'Reset' => 'Réinitialiser',
    'There is no location yet.' => 'Aucun emplacement pour l’instant.',
    'Create a location' => 'Créer un emplacement',
    'No stock matches.' => 'Aucun stock ne correspond.',
    'Add here' => 'Ajouter ici',
    'Product' => 'Produit',
    'Packaging' => 'Conditionnement',
    'Quantity' => 'Quantité',
    'Stored on' => 'Stocké le',
    'Expires on' => 'Périme le',
    'Actions' => 'Actions',
    'Manage' => 'Gérer',
    'Other packaging' => 'Autre conditionnement',

    // add food / stock entry
    'Back to stock' => 'Retour au stock',
    'Pick an existing product or type a new name.' => 'Choisissez un produit existant ou saisissez un nouveau nom.',
    'Location' => 'Emplacement',
    'Choose…' => 'Choisir…',
    'Container' => 'Contenant',
    'New container type' => 'Nouveau type de contenant',
    'Amount per package' => 'Contenance par emballage',
    'Unit' => 'Unité',
    'kg and l: up to 3 decimals. g, ml and pieces: whole numbers.'
        => 'kg et l : jusqu’à 3 décimales. g, ml et pièces : nombres entiers.',
    'Quantity (number of packages)' => 'Quantité (nombre d’emballages)',
    'Storage date' => 'Date de stockage',
    'Expiry date (optional)' => 'Date de péremption (facultative)',
    'Add to stock' => 'Ajouter au stock',
    'Cancel' => 'Annuler',
    'Remove full packages' => 'Retirer des emballages entiers',
    'Number of packages' => 'Nombre d’emballages',
    'Remove' => 'Retirer',
    'Remove part of a package' => 'Retirer une partie d’un emballage',
    'Amount to remove ({unit})' => 'Quantité à retirer ({unit})',
    'Available: {amount}.' => 'Disponible : {amount}.',
    'Whole packages are used first, then one package is opened.'
        => 'Les emballages entiers sont utilisés d’abord, puis un emballage est entamé.',
    'Move to another location' => 'Déplacer vers un autre emplacement',
    'Create another location first.' => 'Créez d’abord un autre emplacement.',
    'Destination' => 'Destination',
    'Move' => 'Déplacer',
    'Change packaging or quantity' => 'Modifier le conditionnement ou la quantité',
    'Save' => 'Enregistrer',

    // locations / containers
    'New location' => 'Nouvel emplacement',
    'Add' => 'Ajouter',
    'Name' => 'Nom',
    'Rename' => 'Renommer',
    'Delete' => 'Supprimer',
    'Stock entries: {count}' => 'Lignes de stock : {count}',
    'Not empty' => 'Non vide',
    'In use' => 'Utilisé',
    'There is no container type yet.' => 'Aucun type de contenant pour l’instant.',

    // error page
    'Something went wrong' => 'Une erreur est survenue',
    'Back to home' => 'Retour à l’accueil',

    // flash messages
    'Food added to stock.' => 'Aliments ajoutés au stock.',
    'Stock entry updated.' => 'Ligne de stock modifiée.',
    'Packages removed.' => 'Emballages retirés.',
    'Amount removed.' => 'Quantité retirée.',
    'Packages moved.' => 'Emballages déplacés.',
    'Location added.' => 'Emplacement ajouté.',
    'Location renamed.' => 'Emplacement renommé.',
    'Location deleted.' => 'Emplacement supprimé.',
    'Container type added.' => 'Type de contenant ajouté.',
    'Container type renamed.' => 'Type de contenant renommé.',
    'Container type deleted.' => 'Type de contenant supprimé.',

    // errors
    'Invalid request.' => 'Requête invalide.',
    'Method not allowed.' => 'Méthode non autorisée.',
    'Your session has expired or the request is invalid. Please reload the page and try again.'
        => 'Votre session a expiré ou la requête est invalide. Rechargez la page puis réessayez.',
    'The data was changed by someone else in the meantime. Please try again.'
        => 'Les données ont été modifiées par quelqu’un d’autre entre-temps. Veuillez réessayer.',
    'Please enter a name.' => 'Veuillez saisir un nom.',
    'The name is invalid (maximum {max} characters, no control characters).'
        => 'Le nom est invalide ({max} caractères maximum, sans caractères de contrôle).',
    'Enter a whole number of packages between 1 and {max}.'
        => 'Saisissez un nombre entier d’emballages entre 1 et {max}.',
    'Please enter the storage date.' => 'Veuillez saisir la date de stockage.',
    'Please enter a valid date.' => 'Veuillez saisir une date valide.',
    'Please choose a unit.' => 'Veuillez choisir une unité.',
    'Enter a positive amount with up to 3 decimals.' => 'Saisissez une quantité positive avec 3 décimales au plus.',
    'Enter a positive whole number.' => 'Saisissez un nombre entier positif.',
    'The amount must be greater than zero.' => 'La quantité doit être supérieure à zéro.',
    'Please choose a location.' => 'Veuillez choisir un emplacement.',
    'Please choose a different location.' => 'Veuillez choisir un autre emplacement.',
    'Please choose a container.' => 'Veuillez choisir un contenant.',
    'A location with this name already exists.' => 'Un emplacement porte déjà ce nom.',
    'This location no longer exists.' => 'Cet emplacement n’existe plus.',
    'This location still contains stock entries and cannot be deleted.'
        => 'Cet emplacement contient encore des lignes de stock et ne peut pas être supprimé.',
    'This container type already exists.' => 'Ce type de contenant existe déjà.',
    'This container type no longer exists.' => 'Ce type de contenant n’existe plus.',
    'This container type is used by stock entries and cannot be deleted.'
        => 'Ce type de contenant est utilisé par des lignes de stock et ne peut pas être supprimé.',
    'This stock entry no longer exists.' => 'Cette ligne de stock n’existe plus.',
    'Only {count} package(s) available.' => 'Seulement {count} emballage(s) disponible(s).',
    'You cannot remove more than the {amount} available.'
        => 'Vous ne pouvez pas retirer plus que les {amount} disponibles.',

    // formats
    '{container} of {amount}' => '{container} de {amount}',
    'piece' => 'pièce',
    'pieces' => 'pièces',
    'Expired' => 'Périmé',
    'Expires soon' => 'Bientôt périmé',
    '{month} {day}, {year}' => '{day} {month} {year}',
    'Jan' => 'janv.', 'Feb' => 'févr.', 'Mar' => 'mars', 'Apr' => 'avr.',
    'May' => 'mai', 'Jun' => 'juin', 'Jul' => 'juil.', 'Aug' => 'août',
    'Sep' => 'sept.', 'Oct' => 'oct.', 'Nov' => 'nov.', 'Dec' => 'déc.',
];
