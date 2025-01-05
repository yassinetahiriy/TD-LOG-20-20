# Gestion des absences

## Description

Ce projet est une application web développée en PHP pour gérer les absences au sein d'une école. Elle offre une gestion centralisée des utilisateurs, des salles, des séances et des groupes TD, avec des fonctionnalités avancées pour les administrateurs, professeurs et étudiants. Cette application garantit un suivi précis et une validation collaborative des présences.

## Fonctionnalités

### Authentification

- Système de connexion sécurisé pour les administrateurs, professeurs et étudiants.

### Gestion par l'administrateur

L'administrateur dispose des fonctionnalités suivantes :

- **Gestion des utilisateurs** :
  - Création, modification et suppression des comptes des professeurs et étudiants.
  - Attribution des étudiants à leurs groupes TD respectifs.
- **Gestion des salles** :
  - Création des salles avec deux formats possibles de disposition des tables :
    - **Salles régulières**.
    - **Salles informatiques**.
- **Création des séances** :
  - Définir la date et l'heure de la séance.
  - Assigner une salle (régulière ou informatique) pour la séance.
  - Associer un professeur responsable pour la séance.
  - Lier la séance au groupe TD concerné.
- **Consultation des rapports d'absences** :
  - Visualisation du nombre total et du taux d'absences pour chaque étudiant.
  - Génération de rapports détaillés sur les présences et absences, filtrables par étudiant.

### Gestion par les professeurs

- **Gestion des séances programmées** :
  - Affichage des séances programmées pour chaque professeur.
  - Possibilité de déclencher une séance, permettant aux étudiants de marquer leur présence.
- **Gestion des présences** :
  - Consultation des présences enregistrées en temps réel.
  - Retrait manuel d'un étudiant de la liste si le professeur constate qu'il n'est pas en classe.
- **Validation collaborative des présences** :
  - Lancement d'une session de validation où chaque étudiant doit confirmer la présence de 4 collègues choisis aléatoirement.
  - Fermeture de la session d'absence une fois la validation terminée.
  - - **Consultation** :
    - - Accès à l'historique d'absences pendant leurs séances.

### Gestion par les étudiants

- **Marquage de présence** :
  - Possibilité de marquer leur présence via l'application lorsque la séance est active.
- **Validation collaborative** :
  - Confirmation de la présence de 4 collègues lors de la session de validation.
- **Consultation** :
  - Accès à leur propre historique de présences et absences.

### Rapports

- Génération de rapports détaillés sur les présences et absences des étudiants, accessibles par les administrateurs.


## Installation avec Docker

1. **Installer Docker**   Assurez-vous que Docker et Docker Compose sont installés sur votre machine.

2. **Cloner le dépôt**   Exécutez les commandes suivantes dans un terminal :

   ```bash
   git clone https://github.com/yassinetahiriy/TD-LOG-20-20.git
   cd TD-LOG-20-20/gestion_presence_docker
   ```

3. **Démarrer les conteneurs**   Dans le répertoire `gestion_presence_docker`, lancez Docker Compose :

   ```bash
   docker-compose up -d
   ```

   Le fichier `docker-compose.yml` configure un serveur web avec PHP et une base de données MySQL.

4. **Importer la base de données**

   - Connectez-vous au conteneur MySQL ou utilisez un outil comme phpMyAdmin.
   - Importez le fichier SQL `gestion_presence (1).sql` :
     ```bash
     mysql -u root -p gestion_presence < chemin/vers/gestion_presence\ \(1\).sql
     ```

5. **Accéder à l'application**   Ouvrez votre navigateur et rendez-vous à l'adresse suivante :

   ```
   http://localhost:8080
   ```

6. **Arrêter les conteneurs**   Pour arrêter les conteneurs, exécutez la commande suivante :

   ```bash
   docker-compose down
   ```

## Installation manuelle (sans Docker)

1. **Cloner le dépôt**   Exécutez les commandes suivantes dans un terminal :

   ```bash
   git clone https://github.com/yassinetahiriy/TD-LOG-20-20.git
   ```

2. **Configurer la base de données**

   - Assurez-vous que MySQL ou MariaDB est installé sur votre machine.
   - Importez le fichier SQL fourni :
     ```bash
     mysql -u root -p gestion_presence < gestion_presence\ \(1\).sql
     ```

3. **Configurer l'application**

   - Modifiez les informations de connexion à la base de données dans le fichier `config/session_config.php`.

4. **Lancer le serveur local**

   - Placez le projet dans le répertoire de votre serveur local (ex. `htdocs` pour XAMPP) et accédez à :
     ```
     http://localhost/TD-LOG-20-20/
     ```
