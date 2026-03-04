# PFA — Application Web de Gestion de Covoiturage

---

# 1️⃣ VISION GLOBALE & ARCHITECTURE

## 📌 Nom du projet

**Application Web de Gestion de Covoiturage**
*(Inspiration :* BlaBlaCar*)*

## 🎯 Cible

* Étudiants
* Employés
* Personnes souhaitant partager les frais de transport

## 🏗️ Architecture Logicielle

Architecture **MVC simplifiée** :

* **Modèle (Model)** → Gestion base de données
* **Vue (View)** → Interface utilisateur
* **Contrôleur (Controller)** → Logique métier

## 💻 Stack Technique

* Frontend : **HTML5 / CSS3 / JavaScript**
* Backend : **PHP (PDO)**
* Base de données : **MySQL**
* Serveur local : **XAMPP / WAMP**

---

# 2️⃣ PÉRIMÈTRE FONCTIONNEL

## 🔐 MODULE 1 — Authentification & Sécurité

### Fonctionnalités

* Inscription
* Connexion
* Déconnexion
* Gestion des rôles :

  * Admin
  * Conducteur
  * Passager

### Sécurité

* `password_hash()` pour les mots de passe
* `password_verify()`
* Requêtes préparées PDO
* Gestion des sessions avec `$_SESSION`

---

## 🚗 MODULE 2 — Gestion des Trajets

### Conducteur

* Publier un trajet
* Modifier un trajet
* Supprimer un trajet

### Passager

* Rechercher un trajet par :

  * Ville de départ
  * Ville d’arrivée
  * Date

---

## 📅 MODULE 3 — Réservations (Cœur du système)

### Passager

* Envoyer une demande de réservation

### Conducteur

* Accepter ou refuser une demande

### Logique métier

* Décrémentation automatique des `places_restantes`
* Vérification qu’il reste des places
* Gestion des statuts :

  * En attente
  * Acceptée
  * Refusée

---

## ⭐ MODULE 4 — Avis (Bonus)

* Note de 1 à 5
* Commentaire
* Affichage moyenne des notes conducteur

---

## 💬 MODULE 5 — Messagerie (Bonus)

* Chat interne conducteur ↔ passager
* Historique des messages
* Sécurité accès (uniquement participants)

---

## 🛠️ MODULE 6 — Administration

### Dashboard

* Nombre total utilisateurs
* Nombre trajets
* Nombre réservations
* Statistiques simples

### Modération

* Supprimer utilisateur
* Supprimer trajet
* Bloquer compte

---

# 3️⃣ PLANNING DE RÉALISATION (7 SEMAINES)

| Semaine | Phase            | Objectifs                            |
| ------- | ---------------- | ------------------------------------ |
| 1       | Conception & DB  | Finalisation SQL, UML, MVC structure |
| 2       | Authentification | Connexion PDO, inscription, sessions |
| 3       | Gestion trajets  | CRUD complet + moteur recherche      |
| 4       | Réservations     | Logique métier + gestion places      |
| 5       | Bonus & Admin    | Avis + messagerie + dashboard        |
| 6       | Tests & Rapport  | Correction bugs + rédaction          |
| 7       | Soutenance       | Slides + répétition démo             |

---

# 4️⃣ PLAN DU RAPPORT DE PFA

---

## 🔵 INTRODUCTION

* Contexte du covoiturage
* Problématique
* Objectifs du projet

---

## 🔵 CHAPITRE 1 — ANALYSE

### 1.1 Étude de l’existant

Comparaison avec BlaBlaCar

### 1.2 Cahier des charges

* Fonctionnel
* Non fonctionnel

---

## 🔵 CHAPITRE 2 — CONCEPTION

### 2.1 Diagramme de Cas d’Utilisation

### 2.2 Diagramme de Classes

### 2.3 Diagramme de Séquence (Réservation)

### 2.4 Modèle Conceptuel de Données (MCD)

### 2.5 Schéma Relationnel

---

## 🔵 CHAPITRE 3 — RÉALISATION & CHOIX TECHNIQUES

### 3.1 Justification des choix

#### Pourquoi PHP ?

* Simple
* Large communauté
* Adapté aux projets académiques

#### Pourquoi MySQL ?

* Relationnel
* Gestion contraintes
* Fiable

#### Pourquoi MVC ?

* Séparation des responsabilités
* Code maintenable
* Projet évolutif

---

### 3.2 Implémentation

* Authentification
* CRUD trajets
* Réservations
* Sécurité
* Captures d’écran

---

## 🔵 CHAPITRE 4 — TESTS & VALIDATION

### Scénarios de test

* Inscription valide
* Mauvais mot de passe
* Réservation sans place
* Tentative accès non autorisé
* Concurrence réservation

---

## 🔵 CONCLUSION

### Résumé

Ce qui a été réalisé

### Limites

* Pas de paiement en ligne
* Pas de géolocalisation temps réel
* Pas de notifications email

### Perspectives

* API mobile
* Paiement sécurisé
* Notifications push

---

# 5️⃣ PLAN DE SOUTENANCE (10–15 MINUTES)

---

## 🎤 Slide 1 — Page de garde

Titre, nom, année universitaire

## 🎤 Slide 2 — Contexte & Objectifs

## 🎤 Slide 3 — Choix Techniques

* PHP
* MySQL
* MVC

## 🎤 Slide 4 — Conception UML

(Diagramme de classes ou séquence)

## 🎤 Slide 5 — Base de données

Contraintes + relations

## 🎤 Slide 6 — DÉMONSTRATION LIVE

* Créer trajet
* Réserver avec autre compte
* Montrer décrémentation place

## 🎤 Slide 7 — Difficultés & Limites

## 🎤 Slide 8 — Conclusion & Questions

---

# 6️⃣ ANTISÈCHE — QUESTIONS PIÈGES DU JURY

---

### ❓ Pourquoi MVC ?

> Pour séparer la logique métier, l’accès aux données et l’interface utilisateur.
> Cela rend le code maintenable et évolutif.

---

### ❓ Comment avez-vous sécurisé l’application ?

* PDO + requêtes préparées
* `password_hash()`
* Vérification des sessions
* Validation des inputs

---

### ❓ Que se passe-t-il si deux passagers réservent la dernière place en même temps ?

Problème de **race condition**.

Solution implémentée :

* Contrainte CHECK (places_restantes >= 0)

Solution professionnelle avancée :

```sql
SELECT places_restantes 
FROM trajets 
WHERE id = ? 
FOR UPDATE;
```

→ Utilisation de transaction SQL avec verrouillage.

---

# 🎯 Conclusion Finale

Ce projet démontre :

* Maîtrise du développement web complet
* Compréhension base de données relationnelle
* Application d’architecture MVC
* Gestion de sécurité web
* Implémentation logique métier réelle

