# CHAYA3NI

CHAYA3NI est une application PHP/MySQL MVC de covoiturage autour de la communauté Sesame.

## Modèle de rôles

Les rôles de compte sont :

- `admin` : faculté / administration
- `conducteur` : compte conducteur qui propose et gère des trajets
- `etudiant` : étudiant passager
- `professeur` : professeur passager

`passager` n'est plus un rôle de compte. Il est remplacé par `etudiant` et `professeur`.
`conducteur` reste un rôle de compte.

## Inscription publique

L'inscription publique accepte uniquement :

- `conducteur`
- `etudiant`
- `professeur`

Le rôle `admin` n'est jamais disponible depuis le formulaire public.

Validation email côté serveur (`AuthController`) :

- normalisation : `strtolower(trim($email))`
- pour `etudiant` et `professeur` :
  `filter_var(..., FILTER_VALIDATE_EMAIL)` + `str_ends_with($email, '@sesame.com.tn')`
- pour `conducteur` :
  `filter_var(..., FILTER_VALIDATE_EMAIL)` uniquement

## Création du compte admin (CLI)

```bash
php gestionCov/scripts/create_admin.php --nom="Admin" --prenom="Faculte" --email="admin@sesame.com.tn" --password="motdepassefort"
```

Le script ne fonctionne qu'en CLI et crée un compte `admin` avec mot de passe hashé.

## Migration base de données

Cas A : ancienne base avec `admin/conducteur/passager`

```sql
UPDATE utilisateurs SET role = 'etudiant' WHERE role = 'passager';

ALTER TABLE utilisateurs
    MODIFY role ENUM('admin', 'conducteur', 'etudiant', 'professeur') DEFAULT 'etudiant';
```

Cas B : base déjà migrée par erreur vers `admin/etudiant/professeur`

```sql
ALTER TABLE utilisateurs
    MODIFY role ENUM('admin', 'conducteur', 'etudiant', 'professeur') DEFAULT 'etudiant';

-- Restaurer manuellement les vrais conducteurs :
UPDATE utilisateurs SET role = 'conducteur'
WHERE email IN ('driver1@sesame.com.tn', 'driver2@sesame.com.tn');
```
