# Cahier des charges  

## Projet : Site de Gestion des Bibliothèques Numériques  

### 1. Contexte et Objectifs  
Le projet vise à créer une application web permettant aux utilisateurs d'accéder à une bibliothèque numérique, d’emprunter des livres et de gérer leur compte utilisateur.  
L’objectif est de faciliter l’accès aux ressources littéraires et d'optimiser la gestion des prêts et réservations.  

### 2. Présentation du Projet  
L'application doit permettre aux utilisateurs de :  
- Consulter un catalogue de livres numériques.  
- Emprunter et restituer des livres en ligne.  
- Rechercher et filtrer les livres par auteur, genre ou popularité.  
- Gérer leur historique d’emprunts et réservations.  

Les administrateurs auront un tableau de bord pour :  
- Gérer les utilisateurs.  
- Ajouter/modifier des livres.  
- Superviser les emprunts.  

### 3. Fonctionnalités  

#### 3.1 Gestion des Livres  
- **Ajout/Suppression/Modification** : Les administrateurs peuvent gérer la base de données des livres.  
- **Consultation** : Les utilisateurs peuvent voir les fiches détaillées des livres disponibles.  
- **Disponibilité en temps réel** : Indication si un livre est actuellement disponible en prêt.  
- **Emprunt** :  
  - L’utilisateur pourra demander l’emprunt de jusqu’à 5 livres.  
  - L’administrateur déterminera s’il donne l’accès au livre ou non.  
  - Lorsqu’un emprunt à été accepté, l’utilisateur pourra visionner le livre de manière non copiable (lecture en PDF en ligne par exemple).  
  - L’historique des emprunts sera visible par les utilisateurs.  

#### 3.2 Rôles et Permissions  
- **Utilisateur** :  
  - Création d’un compte pour accéder aux emprunts.  
  - Consultation du catalogue et détails des livres.  
  - Emprunt et restitution des ouvrages numériques.  
- **Administrateur** :  
  - Gestion du catalogue de livres.  
  - Gestion des utilisateurs et des emprunts.  
  - Suivi des emprunts en retard et envoi de rappels.  

#### 3.3 Recherche et Filtres Avancés  
- **Recherche par** :  
  - Titre / Auteur  
  - Genre  
  - Popularité  

### 4. Exigences Techniques  

#### 4.1 Interface Utilisateur  
- **Utilisateur** : Interface ergonomique et intuitive permettant de consulter les livres.  
- **Administrateur** : Tableau de bord de gestion.  

#### 4.2 Gestion de Version avec Git  
- Le projet devra être versionné et partagé sur une plateforme de gestion de version telle que GitHub.  
- Les membres de l'équipe doivent s'assurer de commit leurs changements régulièrement et de respecter les bonnes pratiques de Git :  
  - Messages de commit clairs.  
  - Branche pour chaque fonctionnalité.  
  - Pull request pour les révisions.  
- Une documentation expliquant le processus de développement avec Git et les conventions utilisées doit être incluse.  

#### 4.3 Technologies et Conception  
- **Back-end** : Symfony (exigé).  
- **Front-end** : Design minimaliste.  
- **UML** : Diagramme UML validé avant développement.  

### 5. Critères de Validation  
- **Fonctionnalités complètes** : Implémentation conforme aux attentes.  
- **Suivi des emprunts** : Fonctionnalité fiable.  

### 6. Conditions de Réalisation  
- **Équipe de Développement** :  
  - Ce projet sera réalisé en groupe.  
  - Les membres de l'équipe devront collaborer pour respecter les délais et garantir l'alignement sur les objectifs.  
- **Présentation Finale** :  
  - La présentation doit inclure :  
    - Le diagramme UML.  
    - Un tableau de réalisation.  
    - Une démonstration des fonctionnalités du site via un support de présentation.  
  - Le temps de parole doit être partagé équitablement entre chaque membre du groupe.  

### 7. Livrables  
- **Support de présentation**  
- **Code Source** :  
  - Ensemble des fichiers de code et de configuration nécessaires au bon fonctionnement du site.  
- **Documentation** :  
  - Guide utilisateur.  
  - Guide d'installation pour l'administrateur.  
  - Documentation du processus Git.  

#### Bonus : Hébergement en ligne  
- En option, l’équipe pourra choisir d’héberger l’application web sur un serveur en ligne.  
- Les critères pour l’hébergement incluront :  
  - Disponibilité.  
  - Sécurité.  
  - Performance.  
- Un guide pour l’installation et la configuration sur l’hôte choisi devra être fourni en supplément.  


# Début du projet

- Choix du nom : LibraNova

## Pre-requis

- PHP 8.3 ou supérieur
- Composer
- SGBDR (MySQL, MariaDB, PostgreSQL ou SQLite)
- symfony-cli

## JOUR 1
- diagrammes de classe (UML) et cas d'utilisations : fait
- idée de design : fait
- Se mettre d'accord sur api ou non : non
- faire le résumé pour Raphaël : fait
- création du projet sur github : fait
- ajouter collaborateurs : fait
- voir comment réaliser les fonctionnalités essentielles : moitier fait

### Diagramme des cas d'utilisations : 
| En tant que | Je veux | Afin de |
| --- | --- | --- |
| Visiteur | contacter les responsables du site | demander de l'aide |
| Visiteur | visiter le site | découvrir celui-ci |
| Visiteur | voir la liste des livres | découvir le choix existant |
| Visiteur | voir les détails d'un livre | d'en apprendre plus sur le livre |
| Visiteur | rechercher un livre | savoir s'il est disponible |
| Visiteur | créer un compte | pour emprunter et consulter les livres |
| Utilisateur | me connecter | d'accéder  |
| Utilisateur | Renseigner des informations | de créer un CV |
| Utilisateur | Télécharger mes CV | de les conserver |


### Création du projet : 
commande : symfony new libraNova --webapp

---

### Créer un BDD

Metre à jour le ficher `.env` avec les informations de connexion à la BDD.

Exemple : 

```
DATABASE_URL="mysql://root:@localhost:3306/libraNova?serverVersion=10.11.2-MariaDB&charset=utf8mb4"
```

Puis dans le terminal, grâce à symfony-cli, créer la BDD :

```bash
# Créer la BDD
symfony console doctrine:database:create
```

```bash
# Créer le ficher de migration
symfony console make:migration
```

```bash
# Exécuter les migrations
symfony console doctrine:migrations:migrate
```

---

### Lancer l'application

```bash
# Lancer l'application
symfony server:start

# ou
symfony serve

# ou sans les logs (non recommandé pour Windows)
symfony server:start -d

# stopper un serveur en cours d'exécution
symfony server:stop
```

---

### Accéder à l'application

http://localhost:8000 ou http://127.0.0.1:8000

Pour travailler en local avec une configuration qui se rapporche au mieux de la production, nous pouvons installer un certificat SSL en local.

```bash
symfony server:ca:install
```

---

# Installation du projet 
- Cloner le projet sur github
- ouvrir le dossier dans un vscode
- Supprimer le dépôt distant
- taper dans le terminal : composer install
- 