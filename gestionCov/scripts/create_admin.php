<?php
/**
 * Create a faculty/administration admin account.
 * Usage:
 *   php gestionCov/scripts/create_admin.php --nom="Admin" --prenom="Faculte" --email="admin@sesame.com.tn" --password="secret123"
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Ce script doit etre lance uniquement en ligne de commande.');
}

define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/models/User.php';

function cli_prompt(string $label): string
{
    if (function_exists('readline')) {
        $value = readline($label);
        return $value === false ? '' : $value;
    }

    echo $label;
    $value = fgets(STDIN);
    return $value === false ? '' : $value;
}

function is_sesame_email(string $email): bool
{
    $email = strtolower(trim($email));

    return filter_var($email, FILTER_VALIDATE_EMAIL)
        && str_ends_with($email, '@sesame.com.tn');
}

$options = getopt('', ['nom::', 'prenom::', 'email::', 'password::']);

$nom = trim($options['nom'] ?? cli_prompt('Nom admin: '));
$prenom = trim($options['prenom'] ?? cli_prompt('Prénom admin: '));
$email = strtolower(trim($options['email'] ?? cli_prompt('Email admin @sesame.com.tn: ')));
$password = (string) ($options['password'] ?? cli_prompt('Mot de passe admin (saisie visible): '));

$errors = [];
if ($nom === '') {
    $errors[] = 'Le nom est obligatoire.';
}
if ($prenom === '') {
    $errors[] = 'Le prenom est obligatoire.';
}
if (!is_sesame_email($email)) {
    $errors[] = 'Email invalide : utilisez une adresse @sesame.com.tn.';
}
if (strlen($password) < 8) {
    $errors[] = 'Le mot de passe doit contenir au moins 8 caracteres.';
}

if ($errors) {
    foreach ($errors as $error) {
        fwrite(STDERR, "- $error" . PHP_EOL);
    }
    exit(1);
}

$userModel = new User();

if ($userModel->emailExists($email)) {
    fwrite(STDERR, "Un compte existe deja avec cet email." . PHP_EOL);
    exit(1);
}

$id = $userModel->create($nom, $prenom, $email, $password, '', 'admin');

if (!$id) {
    fwrite(STDERR, "Creation impossible." . PHP_EOL);
    exit(1);
}

echo "Compte administration cree avec l'id {$id}." . PHP_EOL;
