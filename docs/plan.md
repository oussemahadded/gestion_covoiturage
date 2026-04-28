# Plan CHAYA3NI

## Vision

Plateforme de covoiturage autour de la communauté Sesame.

## Rôles de compte

- `admin` : faculté / administration
- `conducteur` : propose et gère des trajets
- `etudiant` : cherche et réserve des trajets
- `professeur` : cherche et réserve des trajets

`passager` n'est plus un rôle de compte. Il est remplacé par `etudiant` et `professeur`.

## Règles d'inscription

- rôles publics autorisés : `conducteur`, `etudiant`, `professeur`
- rôle public interdit : `admin`
- email `etudiant`/`professeur` : `@sesame.com.tn` obligatoire
- email `conducteur` : email valide uniquement

## Flux conducteur

- recherche et consultation
- création, édition, suppression de ses propres trajets
- consultation des demandes reçues
- confirmation/refus des demandes pour ses trajets

## Flux étudiant/professeur

- recherche et consultation des trajets
- réservation de trajets conducteurs
- annulation de ses propres réservations
- dépôt d'avis après réservation confirmée

## Flux administration

- accès au dashboard admin
- gestion utilisateurs et trajets
- pas d'auto-suppression du compte admin
