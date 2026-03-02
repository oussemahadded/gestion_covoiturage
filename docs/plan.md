# 🏆 PLAN MAÎTRE : PFA - Application Web de Gestion de Covoiturage

## 🎯 1. VISION GLOBALE & ARCHITECTURE

* **Nom du projet :** Application Web de Gestion de Covoiturage (Type BlaBlaCar).
* **Cible :** Étudiants / Employés cherchant à partager des frais de transport.
* **Architecture logicielle :** **MVC** (Modèle - Vue - Contrôleur) simplifié.
* **Stack Technique :**
* **Frontend :** HTML5, CSS3 (ou Bootstrap/Tailwind pour aller plus vite), JavaScript (vanilla).
* **Backend :** PHP (orienté objet de préférence, avec `PDO`).
* **Base de données :** MySQL.
* **Environnement local :** XAMPP / WAMP.



---

## 🚀 2. PÉRIMÈTRE FONCTIONNEL (Les 6 Modules)

* **🔐 MODULE 1 : Authentification & Sécurité**
* Inscription / Connexion / Déconnexion.
* Gestion des rôles (Admin, Conducteur, Passager) via `$_SESSION`.
* *Sécurité absolue :* Utilisation de `password_hash()` et requêtes préparées (`PDO`).


* **🚗 MODULE 2 : Gestion des Trajets**
* **Conducteur :** Publier (CRUD : Créer, Lire, Mettre à jour, Supprimer ses propres trajets).
* **Passager :** Rechercher un trajet (par ville de départ, d'arrivée, et date).


* **📅 MODULE 3 : Réservations (Le cœur du système)**
* Passager : Demander une réservation.
* Conducteur : Accepter ou Refuser la demande (changement de `statut`).
* *Logique métier :* Décrémenter `places_restantes` uniquement si la réservation est confirmée.


* **⭐ MODULE 4 : Avis (Bonus)**
* Laisser une note (1 à 5) et un commentaire après un trajet terminé.
* Calcul et affichage de la moyenne du conducteur.


* **💬 MODULE 5 : Messagerie (Bonus)**
* Chat interne entre un passager et un conducteur lié à un trajet précis.


* **🛠️ MODULE 6 : Administration**
* Dashboard global (Statistiques : total utilisateurs, trajets, réservations).
* Modération : Pouvoir supprimer un compte ou un trajet inapproprié.



---

## 📅 3. PLANNING DE RÉALISATION (7 Semaines)

| Période | Phase du projet | Objectifs concrets |
| --- | --- | --- |
| **Semaine 1** | **Conception & DB** | Valider le script SQL (Fait ✅), dessiner les diagrammes UML (Cas d'utilisation, Classes, Séquence), préparer l'arborescence MVC des dossiers. |
| **Semaine 2** | **Socle & Auth** | Fichier de connexion DB (`PDO`), Formulaires Inscription/Connexion, Gestion des sessions PHP. |
| **Semaine 3** | **Trajets (CRUD)** | Formulaire d'ajout de trajet pour le conducteur, Page de recherche et d'affichage pour les passagers. |
| **Semaine 4** | **Réservations** | Logique de demande de place, acceptation par le conducteur, mise à jour dynamique des places restantes. |
| **Semaine 5** | **Bonus & Admin** | Intégration des avis, du mini-chat, et création du tableau de bord Administrateur. |
| **Semaine 6** | **Rapport & Tests** | Chasser les bugs (tests d'injections SQL, tests de double réservation), rédaction du rapport complet. |
| **Semaine 7** | **Soutenance** | Préparation des slides, répétition de la démonstration en direct (Démo). |

---

## 📘 4. PLAN DU RAPPORT DE PFA (Livrable Écrit)

* **INTRODUCTION :** Contexte du covoiturage, Problématique, Objectifs.
* **CHAPITRE 1 : ANALYSE**
* Étude de l'existant (comparaison avec BlaBlaCar).
* Cahier des charges fonctionnel et non fonctionnel (Sécurité, responsive).


* **CHAPITRE 2 : CONCEPTION (Très important pour le jury)**
* Diagramme de cas d’utilisation (Acteurs et actions).
* Diagramme de classes (Structure objet/données).
* Diagramme de séquence (Focus sur le flux de réservation).
* Explication de l'architecture MVC choisie.


* **CHAPITRE 3 : RÉALISATION**
* Technologies utilisées (PHP, MySQL, etc.).
* Présentation de la Base de Données (MCD ou Schéma Relationnel).
* *Captures d'écran* de l'application avec explications des bouts de code clés (ex: connexion PDO).


* **CHAPITRE 4 : TESTS & VALIDATION**
* Scénarios de tests validés (Ex: "Un passager ne peut pas réserver s'il y a 0 place").


* **CONCLUSION :** Bilan personnel, difficultés rencontrées, perspectives d'évolution (Paiement en ligne, appli mobile).

---

## 🎤 5. PLAN DE LA SOUTENANCE (10-15 Minutes)

* **Slide 1 :** Page de garde (Titre, Nom, Année).
* **Slide 2 :** Contexte et Objectifs (Pourquoi ce projet ?).
* **Slide 3 :** Architecture technique (Logos PHP, MySQL, MVC).
* **Slide 4 :** Conception (Afficher le Diagramme de Cas d'Utilisation ou de Classes).
* **Slide 5 :** Modèle de données (Le schéma relationnel global).
* **Slide 6 : DÉMONSTRATION EN DIRECT (Le moment de briller ✨)**
* *Scénario :* Connecter un Conducteur -> Créer un trajet -> Connecter un Passager -> Réserver -> Montrer la place qui diminue.


* **Slide 7 :** Bilan & Améliorations futures.
* **Slide 8 :** Remerciements et Questions.

---

## 🧠 6. ANTISÈCHE : LES QUESTIONS PIÈGES DU JURY

Prépare-toi à répondre tac-au-tac à ces questions classiques en 2ème année :

1. **Pourquoi avoir choisi l'architecture MVC ?**
* *Réponse :* "Pour séparer la logique d'accès aux données (Modèle), l'interface utilisateur (Vue) et la logique de traitement (Contrôleur). Cela rend le code plus propre, maintenable et évolutif."


2. **Comment avez-vous sécurisé les mots de passe ?**
* *Réponse :* "Les mots de passe ne sont jamais stockés en clair. J'ai utilisé la fonction native `password_hash()` de PHP qui génère un sel aléatoire (bcrypt), et `password_verify()` pour la connexion."


3. **Comment êtes-vous protégé contre les injections SQL ?**
* *Réponse :* "J'ai systématiquement utilisé l'extension `PDO` avec des **requêtes préparées** (`prepare()` puis `execute()`). Les variables utilisateurs ne sont jamais concaténées directement dans la requête SQL."


4. **Que se passe-t-il si 2 passagers cliquent sur 'Réserver' en même temps pour la dernière place ?**
* *Réponse :* "J'ai mis une contrainte `CHECK (places_restantes >= 0)` dans la base de données. Même si le code PHP laisse passer la requête simultanée, MySQL bloquera la deuxième transaction, garantissant l'intégrité des données."



