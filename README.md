# Gestion des absences

## Description

Ce projet est une application web développée en PHP pour gérer les absences au sein d'une école. Il offre une gestion centralisée des utilisateurs, des salles, des séances et des groupes TD, avec des fonctionnalités avancées pour les administrateurs, professeurs et étudiants. Cette application garantit un suivi précis et une validation collaborative des présences.

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

### Gestion par les étudiants
- **Marquage de présence** :
  - Possibilité de marquer leur présence via l'application lorsque la séance est active.
- **Validation collaborative** :
  - Confirmation de la présence de 4 collègues lors de la session de validation.
- **Consultation** :
  - Accès à leur propre historique de présences et absences.

### Rapports
- Génération de rapports détaillés sur les présences et absences des étudiants, accessibles par les administrateurs.


## Prérequis

- **Serveur web** : Apache, Nginx ou tout autre serveur compatible.
- **PHP** : Version 7.4 ou supérieure.
- **Base de données** : MySQL ou MariaDB.

## Installation

1. **Cloner le dépôt** :

   ```bash
   git clone https://github.com/yassinetahiriy/TD-LOG-20-20.git
