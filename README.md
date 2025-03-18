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
&nbsp;  
&nbsp;  
&nbsp;


---

# Conception du projet

- Choix du nom : LibraNova

## Pre-requis

- PHP 8.3 ou supérieur
- Composer
- SGBDR (MySQL, MariaDB, PostgreSQL ou SQLite)
- symfony-cli


## Diagramme des cas d'utilisations : 
| En tant que | Je veux | Afin de |
| --- | --- | --- |
| Visiteur | contacter les responsables du site | demander de l'aide |
| Visiteur | visiter le site | découvrir celui-ci |
| Visiteur | voir la liste des livres | découvir le choix existant |
| Visiteur | voir les détails d'un livre | d'en apprendre plus sur le livre |
| Visiteur | rechercher un livre | savoir s'il est disponible |
| Visiteur | créer un compte | pour emprunter et consulter les livres |
| Utilisateur | me connecter | d'accéder à toutes les fonctionnalités du site |
| Utilisateur | accéder à mon profil | de modifier/supprimer celui-ci et voir mon historique de login et d'emprunts |
| Utilisateur | voir ma liste de favoris | de les gérer (visiter la page d'un favoris ou le retirer de la liste) |
| Utilisateur | mettre en favoris un livre | le sauvegarder et le retrouver plus facilement, être notifié quand celui-ci est disponible |
| Utilisateur | emprunter un livre (max 5) | le lire |
| Administrateur | me connecter | gérer la BDD |
| Administrateur | gérer la BDD | d'ajouter/modifier/supprimer des tables, users, livres, des tags, des emprunts |


## Modèle de Base de Données

### User
| Champ              | Type                |
|--------------------|---------------------|
| id                 | int (PK)            |
| username           | varchar(100)        |
| email              | varchar(255) (à ne pas créer)         |
| password           | varchar(255) (à ne pas créer)         |
| roles              | array (ROLE_USER, ROLE_ADMIN) (à ne pas créer)             |
| rented_novels_count| int (0-5)           |
| is_adult           | boolean             |
| ref                | varchar(255)        |
| is_verified        | boolean (à ne pas créer)         |
| is_terms           | boolean             |
| is_gpdr            | boolean             |
| novels             | collection (ManyToMany avec Novel)              |


### Novel
| Champ             | Type                |
|-------------------|---------------------|
| id                | int (PK)            |
| name              | varchar(255)        |
| author            | varchar(255)        |
| abstract          | text                |
| is_published      | boolean             |
| released_at       | date, nullable      |
| created_at        | datetime immutable  |
| updated_at        | datetime immutable, nullable |
| likes             | collection (ManyToMany avec User)                 |
| pic               | varchar(255)        |
| file              | varchar(255)        |
| slug              | varchar(255)        |
| ref               | varchar(255)        |
| isbn              | varchar(255)        |
| is_for_adult      | boolean             |

### Tag
| Champ           | Type               |
|-----------------|--------------------|
| id              | int (PK)           |
| name            | varchar(100)       |
| description     | text               |

### Renting_History
| Champ           | Type               |
|-----------------|--------------------|
| id              | int (PK)           |
| user_id         | int (FK -> User, ManyToOne)   |
| novel_id        | int (FK -> Novel, ManyToOne)  |
| start           | datetime immutable |
| end             | datetime immutable |
| last_page       | string, nullable        |
| updated_at      | datetime immutable, nullable |

### Login_History
| Champ        | Type                  |
|--------------|-----------------------|
| id           | int (PK)              |
| user_id      | int (FK -> User, ManyToOne)      |
| login_date   | datetime immutable    |
| ip_address   | varchar(255)          |
| device       | varchar(255)          |
| os           | varchar(255)          |
| browser      | varchar(255)          |

### Novel_Tag (Table de Jointure)
| Champ       | Type                  |
|-------------|-----------------------|
| id_novel    | int (FK -> Novel)     |
| id_tag      | int (FK -> Tag)       |

### User_Likes_Novel (Table de Jointure)
| Champ     | Type                  |
|-----------|-----------------------|
| id_user   | int (FK -> User)      |
| id_novel  | int (FK -> Novel)     |

#### Relations
- **User** peut emprunter plusieurs **Novel** (relation avec `Renting_History`).
- **User** peut aimer plusieurs **Novel** (relation `User_Likes_Novel`).
- **User** a un historique de connexion (**Login_History**).
- **Novel** peut avoir plusieurs **Tags** (relation `Novel_Tag`).
- **Novel** peut être emprunté par plusieurs **User** (relation avec `Renting_History`).

### Roles des utilisateurs 
- ROLE_USER : rôle de base d'un utilisateur connecté
- ROLE_VERIFIED : rôle obtenu si email vérifié
- ROLE_ADULT : rôle si utilisateur a déclaré être majeur
- ROLE_ADMIN : rôle de l'administrateur

## Controllers
- RegistrationController
- SecurityController
- UserController
- NovelController
- PageController


## URLS
- / : page d'acceuil
- /livres : page affichant tous les livres et un formulaire de tri
- /livres/ref : page affichant les détails d'un livre
- /livres/ref/lecture : page affichant le pdf du livre
- /inscription : page affichant le formulaire d'inscription
- /connexion : page affichant le formulaire de connexion
- /profil : page de profil de l'utilisateur connecté : 
  - permettant de modifier les informations de son compte
  - permettant de supprimer le compte
  - de se rediriger vers les fonctionnalités liées à l'utilisateur (favoris)
- /profil/favoris : pour voir tous les favoris avec un filtre pour n'afficher que ceux qui sont dispo ou inversement
- /profil/emprunts : pour voir tous les livres empruntés en cours
- /profil/historique-emprunts : pour voir tous les livres empruntés depuis l'inscription
- /contact : page avec formulaire de contact
- /rgpd 
- /cgu 
- /mentions-legales 


# Réalisation du projet : 

## Créer l'architecture
```bash
symfony new libraNova --webapp
```

---

## Ajout des dépendances

- Faker
```bash
composer require fakerphp/faker --dev 
```

- Fixtures
```bash
composer require orm-fixtures --dev 
```

- Verificateur d'email
```bash
composer require symfonycasts/verify-email-bundle
```

- Icônes
```bash
composer require symfony/ux-icons  
```

- Paginateur
```bash
composer require knplabs/knp-paginator-bundle
```

- Detecteur d'appareil
```bash
composer require matomo/device-detector 
```

- Symfony UX Autocomplete
```bash
composer require symfony/ux-autocomplete  
```

- Affichage du mot de passe
```bash
composer require symfony/ux-toggle-password
```

- Composants twig
```bash
composer require symfony/ux-twig-component
```

- Upload de fichier (pas sûr si besoin car utilisation de easyadmin)
```bash
composer require symfony/ux-dropzone
```

- Tailwind css
```bash
composer require symfonycasts/tailwind-bundle
```
puis
```bash
symfony console tailwind:init
symfony console tailwind:build --watch
```
---

## Créer une BDD

Metre à jour le ficher `.env` avec les informations de connexion à la BDD.

Exemple : 

```
DATABASE_URL="mysql://root:@localhost:3307/libraNova?serverVersion=8.0.32&charset=utf8mb4"
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

## Lancer l'application

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

## Accéder à l'application

http://localhost:8000 ou http://127.0.0.1:8000

Pour travailler en local avec une configuration qui se rapporche au mieux de la production, nous pouvons installer un certificat SSL en local.

```bash
symfony server:ca:install
```

---

## Création des controllers, formulaires et des templates

### Création Controller et template pour la connexion
```bash
 symfony console make:security:form-login 
```

### Création Controller et template pour l'inscription
```bash
 symfony console make:registration-form 
```

### Création Controller et template pour les pages
```bash
 symfony console make:controller PageController 
```
Puis dans le dossier templates/page, créer les fichiers twig pour la page contact, rgpd, cgu et mentions légales. Fichier twig pour la vue de la page d'acceuil est créée avec la commande. 

### Création service de recherche
- Créer un dossier Service dans le dossier src, puis un fichier SearchService.php
- Activer le service dans le fichier services.yaml
- Importer le service dans le NovelRepository

### Création Controller et template pour les livres
```bash
 symfony console make:crud 
```
- Supprimer les éléments inutiles (templates, form, et routes create, delete, update). 
- Modifier les routes index et show (pour afficher tous les livres avec fonctionnalité de recherche).
- Créer les routes pour :
  - emprunter un livre,
  - rendre un livre,
  - mettre en favoris un livre,
  - retirer le favoris d'un livre, 
  - afficher le pdf d'un livre

### Création Controller et template pour les users
```bash
 symfony console make:crud 
```
- Supprimer les éléments inutiles (templates, form, et routes index, create et update). 
- Modifier la route show pour afficher les infos et permettre la modification des informations de l'utilisateur.
- Créer une route pour voir les favoris avec filtres pour ne voir que ceux qui sont disponibles, ceux qui viennent d'être disponibles.
- Dans le NovelRepository, créer une fonction pour récupérer les favoris avec possibilité de filtrage (donc création d'un formulaire en plus)
- Créer les routes pour voir les emprunts actuels, l'historique de tous les emprunts et l'historique de connexion
- Dans le RentingHistoryRepository, créer une fonction pour pouvoir récupérer que les emprunts en cours
  
---

## Sécuriser les entités et les formulaires
Ajouter les contraintes pour chaques propriétés des entités et pour les champs des formulaires

---

## Enregistrement des connexions
Création d'un dossier EventListener dans src, puis d'un fichier LoginSuccessListener.php
Ajout de l'écouteur dans services.yaml : 
```bash
    App\EventListener\LoginSuccessListener:
        tags:
            - { name: kernel.event_listener, event: security.authentication.success, method: onLoginSuccess }
```

---

## Créer et lancer les fixtures
- Modifier le fichier AppFixtures et créer si besoin d'autres fichiers fixtures selon les entités voulues.
- Lancer les fixtures avec la commande : 
```bash
 symfony console d:f:l -n  
```

---

## Installation de easyAdmin

 - Installation du bundle
```bash
 composer require easycorp/easyadmin-bundle
 ```
 - Création du DashboardController
```bash
 symfony console make:admin:dashboard
```
Où l'on personnalise le chemin

On créer un dossier templates/admin où l'on met le dashboard.html.twig,
on y met l'extend @EasyAdmin/page/content.html.twig

 - Création des CRUD controllers de la page admin
```bash
symfony console make:admin:crud
```
A partir du terminal on créer User
                              Novel
                              LoginHistory
                              RentingHistory
                              Tag

 - On va sur routes.yaml
on y ajoute le admin_dashboard avec son chemin et son controller : 
```bash
admin_dashboard:
    path: '/admin'
    controller: 'App\Controller\Admin\DashboardController::index'
```

 - On nettoi le cache après ça
```bash
symfony console cache:clear
```
Puis on rappel les entité lié au CRUD Controller en les appelant grâce au " MenuItem::LinkToDashboard "

 - Personalisation de Entité affiché
Personnalisation de chaque edit grâce au CrudController et aux Entités
  Dans le CrudController on personnalise grâce a:
    .function configureCrud(Crud $crud): Crud  ---> Personnalise l'affichage
    .function configureFields(string $pageName): iterable  ---> Configure la structure et les fonctionnements
    .function configureActions(Actions $actions): Actions  ---> Represente le petit menu au bout de la ligen qui permet l'edit

 - Ajout de VichUploader pour la gestion des pdf
Modification du fichier vich_uploader.yaml pour créer le mapping

 - Création de la possibilité d'ajouter des images
 Il a fallu créer une une gestion pour afficher d'abord le fichier téléchargé

 - Ajout du suivi de location

## 🧰 Technologies Utilisées

- Symfony
- PHP
- TailwindCSS
- UX Turbo
- Doctrine
- FakerPHP
&nbsp;  
&nbsp;  
&nbsp;

---

# 🚀 Installation

- Cloner le projet sur github : 
```bash
git clone https://github.com/Jensone/todoz-sf.git
```
- ouvrir le dossier dans un vscode
- Supprimer le dépôt distant
- taper dans le terminal :
```bash
composer install
symfony serve -d
```