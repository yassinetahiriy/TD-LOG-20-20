CREATE DATABASE IF NOT EXISTS gestion_presence;
USE gestion_presence;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : dim. 05 jan. 2025 à 16:41
-- Version du serveur : 10.4.28-MariaDB
-- Version de PHP : 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `gestion_presence`
--

-- --------------------------------------------------------

--
-- Structure de la table `conflits_place`
--

CREATE TABLE `conflits_place` (
  `id` int(11) NOT NULL,
  `id_seance` int(11) NOT NULL,
  `id_place` int(11) NOT NULL,
  `id_etudiant1` int(11) NOT NULL,
  `id_etudiant2` int(11) NOT NULL,
  `status` enum('en_attente','resolu') DEFAULT 'en_attente',
  `resolution_timestamp` timestamp NULL DEFAULT NULL,
  `id_etudiant_confirme` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `groupes_td`
--

CREATE TABLE `groupes_td` (
  `id` int(11) NOT NULL,
  `nom_groupe` varchar(50) NOT NULL,
  `annee_scolaire` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `groupes_td`
--

INSERT INTO `groupes_td` (`id`, `nom_groupe`, `annee_scolaire`) VALUES
(1, 'TD1', '2024-2025'),
(2, 'TD2', '2023-2024'),
(3, 'TD3', '2024-2025');

-- --------------------------------------------------------

--
-- Structure de la table `groupes_td_etudiants`
--

CREATE TABLE `groupes_td_etudiants` (
  `id` int(11) NOT NULL,
  `id_groupe_td` int(11) NOT NULL,
  `id_etudiant` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `groupes_td_etudiants`
--

INSERT INTO `groupes_td_etudiants` (`id`, `id_groupe_td`, `id_etudiant`, `created_at`) VALUES
(20, 3, 18, '2024-11-27 07:52:19'),
(22, 3, 19, '2024-12-24 22:41:32'),
(23, 3, 3, '2024-12-24 22:48:22'),
(24, 3, 21, '2024-12-24 22:48:26'),
(25, 3, 5, '2024-12-24 22:49:20'),
(26, 3, 9, '2024-12-24 22:49:27'),
(27, 3, 1, '2024-12-24 22:49:32'),
(28, 3, 26, '2024-12-24 22:49:36'),
(29, 3, 25, '2024-12-24 22:49:42'),
(30, 3, 14, '2024-12-24 22:49:56'),
(31, 3, 8, '2024-12-24 22:49:59'),
(32, 3, 23, '2024-12-24 22:50:02'),
(33, 3, 22, '2024-12-24 22:50:06'),
(34, 3, 24, '2024-12-24 22:50:09'),
(35, 3, 20, '2024-12-24 22:50:12'),
(36, 3, 4, '2024-12-24 22:50:16'),
(37, 3, 16, '2024-12-24 22:50:19'),
(38, 3, 7, '2024-12-24 22:50:23'),
(40, 3, 2, '2024-12-24 22:50:32'),
(41, 3, 6, '2024-12-24 22:50:35'),
(42, 3, 28, '2024-12-24 22:55:08'),
(43, 3, 27, '2024-12-24 22:55:11'),
(44, 3, 29, '2024-12-24 22:55:15'),
(45, 3, 30, '2024-12-24 22:55:19'),
(47, 3, 15, '2024-12-30 11:04:44');

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `id_seance` int(11) NOT NULL,
  `id_etudiant` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `lu` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `places`
--

CREATE TABLE `places` (
  `id` int(11) NOT NULL,
  `id_salle` int(11) NOT NULL,
  `numero_place` int(11) NOT NULL,
  `position_x` int(11) NOT NULL,
  `position_y` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `presences`
--

CREATE TABLE `presences` (
  `id` int(11) NOT NULL,
  `id_seance` int(11) NOT NULL,
  `id_etudiant` int(11) NOT NULL,
  `numero_place` int(11) NOT NULL,
  `heure_marquage` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('present','absent') NOT NULL DEFAULT 'present'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `presences`
--

INSERT INTO `presences` (`id`, `id_seance`, `id_etudiant`, `numero_place`, `heure_marquage`, `status`) VALUES
(2, 3, 25, 2, '2024-12-24 23:01:46', 'present'),
(3, 3, 20, 9, '2024-12-24 23:11:31', 'present'),
(4, 3, 28, 11, '2024-12-24 23:12:08', 'present'),
(5, 3, 16, 14, '2024-12-24 23:12:31', 'present'),
(6, 3, 24, 23, '2024-12-24 23:13:20', 'present'),
(7, 3, 22, 20, '2024-12-24 23:13:57', 'present'),
(8, 3, 23, 10, '2024-12-24 23:14:24', 'present'),
(9, 4, 25, 1, '2024-12-30 14:36:18', 'present'),
(10, 4, 22, 9, '2024-12-30 14:37:07', 'present'),
(11, 4, 27, 5, '2024-12-30 14:38:09', 'present'),
(12, 4, 20, 14, '2024-12-30 14:38:24', 'present'),
(13, 4, 16, 22, '2024-12-30 14:38:40', 'present'),
(14, 4, 24, 16, '2024-12-30 14:39:02', 'present'),
(15, 4, 23, 19, '2024-12-30 14:39:16', 'present'),
(16, 4, 28, 26, '2024-12-30 14:59:16', 'present'),
(17, 4, 1, 0, '2024-12-30 15:10:55', 'absent'),
(18, 4, 2, 0, '2024-12-30 15:10:55', 'absent'),
(19, 4, 3, 0, '2024-12-30 15:10:55', 'absent'),
(20, 4, 4, 0, '2024-12-30 15:10:55', 'absent'),
(21, 4, 5, 0, '2024-12-30 15:10:55', 'absent'),
(22, 4, 6, 0, '2024-12-30 15:10:55', 'absent'),
(23, 4, 7, 0, '2024-12-30 15:10:55', 'absent'),
(24, 4, 8, 0, '2024-12-30 15:10:55', 'absent'),
(25, 4, 9, 0, '2024-12-30 15:10:55', 'absent'),
(26, 4, 14, 0, '2024-12-30 15:10:55', 'absent'),
(27, 4, 15, 0, '2024-12-30 15:10:55', 'absent'),
(28, 4, 18, 0, '2024-12-30 15:10:55', 'absent'),
(29, 4, 19, 0, '2024-12-30 15:10:55', 'absent'),
(30, 4, 21, 0, '2024-12-30 15:10:55', 'absent'),
(31, 4, 26, 0, '2024-12-30 15:10:55', 'absent'),
(32, 4, 29, 0, '2024-12-30 15:10:55', 'absent'),
(33, 4, 30, 0, '2024-12-30 15:10:55', 'absent'),
(34, 5, 28, 8, '2024-12-30 17:01:59', 'present'),
(35, 5, 20, 15, '2024-12-30 17:02:22', 'present'),
(36, 5, 16, 18, '2024-12-30 17:02:59', 'present'),
(37, 5, 23, 19, '2024-12-30 17:03:13', 'present'),
(38, 5, 24, 6, '2024-12-30 17:03:34', 'present'),
(39, 5, 22, 10, '2024-12-30 17:03:51', 'present'),
(40, 6, 22, 1, '2025-01-04 14:01:21', 'present'),
(41, 6, 16, 4, '2025-01-04 14:01:43', 'present'),
(42, 6, 24, 8, '2025-01-04 14:01:55', 'present'),
(43, 6, 20, 16, '2025-01-04 14:02:16', 'present'),
(44, 6, 23, 11, '2025-01-04 14:02:31', 'present'),
(45, 6, 1, 0, '2025-01-04 14:02:53', 'absent'),
(46, 6, 2, 0, '2025-01-04 14:02:53', 'absent'),
(47, 6, 3, 0, '2025-01-04 14:02:53', 'absent'),
(48, 6, 4, 0, '2025-01-04 14:02:53', 'absent'),
(49, 6, 5, 0, '2025-01-04 14:02:53', 'absent'),
(50, 6, 6, 0, '2025-01-04 14:02:53', 'absent'),
(51, 6, 7, 0, '2025-01-04 14:02:53', 'absent'),
(52, 6, 8, 0, '2025-01-04 14:02:53', 'absent'),
(53, 6, 9, 0, '2025-01-04 14:02:53', 'absent'),
(54, 6, 14, 0, '2025-01-04 14:02:53', 'absent'),
(55, 6, 15, 0, '2025-01-04 14:02:53', 'absent'),
(56, 6, 18, 0, '2025-01-04 14:02:53', 'absent'),
(57, 6, 19, 0, '2025-01-04 14:02:53', 'absent'),
(58, 6, 21, 0, '2025-01-04 14:02:53', 'absent'),
(59, 6, 25, 0, '2025-01-04 14:02:53', 'absent'),
(60, 6, 26, 0, '2025-01-04 14:02:53', 'absent'),
(61, 6, 27, 0, '2025-01-04 14:02:53', 'absent'),
(62, 6, 28, 0, '2025-01-04 14:02:53', 'absent'),
(63, 6, 29, 0, '2025-01-04 14:02:53', 'absent'),
(64, 6, 30, 0, '2025-01-04 14:02:53', 'absent'),
(65, 7, 23, 2, '2025-01-04 14:20:34', 'present'),
(66, 7, 20, 11, '2025-01-04 14:20:54', 'present'),
(67, 7, 27, 17, '2025-01-04 14:21:03', 'present'),
(68, 7, 28, 15, '2025-01-04 14:21:14', 'present'),
(69, 7, 22, 6, '2025-01-04 14:21:25', 'present'),
(70, 7, 25, 21, '2025-01-04 14:21:43', 'present'),
(71, 7, 16, 7, '2025-01-04 14:22:03', 'present'),
(72, 7, 24, 9, '2025-01-04 14:50:09', 'present'),
(73, 7, 1, 0, '2025-01-04 14:50:40', 'absent'),
(74, 7, 2, 0, '2025-01-04 14:50:40', 'absent'),
(75, 7, 3, 0, '2025-01-04 14:50:40', 'absent'),
(76, 7, 4, 0, '2025-01-04 14:50:40', 'absent'),
(77, 7, 5, 0, '2025-01-04 14:50:40', 'absent'),
(78, 7, 6, 0, '2025-01-04 14:50:40', 'absent'),
(79, 7, 7, 0, '2025-01-04 14:50:40', 'absent'),
(80, 7, 8, 0, '2025-01-04 14:50:40', 'absent'),
(81, 7, 9, 0, '2025-01-04 14:50:40', 'absent'),
(82, 7, 14, 0, '2025-01-04 14:50:40', 'absent'),
(83, 7, 15, 0, '2025-01-04 14:50:40', 'absent'),
(84, 7, 18, 0, '2025-01-04 14:50:40', 'absent'),
(85, 7, 19, 0, '2025-01-04 14:50:40', 'absent'),
(86, 7, 21, 0, '2025-01-04 14:50:40', 'absent'),
(87, 7, 26, 0, '2025-01-04 14:50:40', 'absent'),
(88, 7, 29, 0, '2025-01-04 14:50:40', 'absent'),
(89, 7, 30, 0, '2025-01-04 14:50:40', 'absent');

-- --------------------------------------------------------

--
-- Structure de la table `salles`
--

CREATE TABLE `salles` (
  `id` int(11) NOT NULL,
  `nom_salle` varchar(50) NOT NULL,
  `type_salle` enum('binome','groupe') NOT NULL,
  `capacite` int(11) NOT NULL DEFAULT 30
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `salles`
--

INSERT INTO `salles` (`id`, `nom_salle`, `type_salle`, `capacite`) VALUES
(1, 'b003', 'binome', 30),
(2, 'G113', 'groupe', 32),
(3, 'b102', 'binome', 30),
(4, 'E203', 'binome', 30),
(5, 'E201', 'binome', 30),
(6, 'G116', 'binome', 30);

-- --------------------------------------------------------

--
-- Structure de la table `seances`
--

CREATE TABLE `seances` (
  `id` int(11) NOT NULL,
  `date_seance` date NOT NULL,
  `heure_debut` time NOT NULL,
  `heure_fin` time NOT NULL,
  `id_professeur` int(11) NOT NULL,
  `id_salle` int(11) NOT NULL,
  `id_groupe_td` int(11) NOT NULL,
  `statut` enum('programmee','en_cours','terminee') DEFAULT 'programmee',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `validation_active` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `seances`
--

INSERT INTO `seances` (`id`, `date_seance`, `heure_debut`, `heure_fin`, `id_professeur`, `id_salle`, `id_groupe_td`, `statut`, `created_at`, `updated_at`, `validation_active`) VALUES
(1, '2024-11-27', '08:00:00', '10:00:00', 13, 1, 1, 'en_cours', '2024-11-26 19:43:52', '2024-11-27 09:47:34', 0),
(3, '2024-12-25', '00:00:00', '01:00:00', 31, 3, 3, 'en_cours', '2024-12-24 22:57:24', '2024-12-24 23:00:55', 0),
(4, '2024-12-30', '16:00:00', '17:00:00', 31, 5, 3, 'terminee', '2024-12-30 14:34:35', '2024-12-30 15:10:55', 0),
(5, '2024-12-30', '18:00:00', '19:00:00', 31, 1, 3, 'en_cours', '2024-12-30 16:59:56', '2024-12-30 17:01:36', 0),
(6, '2025-01-04', '15:01:00', '16:00:00', 31, 4, 3, 'terminee', '2025-01-04 14:00:54', '2025-01-04 14:02:53', 0),
(7, '2025-01-04', '16:01:00', '16:30:00', 31, 3, 3, 'terminee', '2025-01-04 14:20:10', '2025-01-04 14:50:40', 1);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `user_type` enum('admin','professeur','etudiant') NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `photo_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `user_type`, `nom`, `prenom`, `photo_url`, `created_at`) VALUES
(1, 'anas@gmail.com', '$2y$10$POQ/Gna7e5eLWvR9CQtv9OZrT2Y9i.S949SbIwWdAmBUW1uOFk0Pm', 'anas@gmail.com', 'etudiant', 'dsjh', 'dkjhg', 'uploads/6745e9562c107.jpg', '2024-11-26 15:29:26'),
(2, 'jebli@gmail.com', '$2y$10$QdjqHlh3USuiI3Ev0xETAOYazgIs1qsNYb3hnvep0E9GdfTssAM5G', 'jebli@gmail.com', 'etudiant', 'jebli', 'dkjhg', 'uploads/6745ed2f93456.jpg', '2024-11-26 15:45:51'),
(3, 'abdo@gmail.com', '$2y$10$.U2TULoGGLkjeGRag9vZT.5AXkuVMXe928qwmxT.TdUWHJgj.Hoq6', 'abdo@gmail.com', 'etudiant', 'abdo', 'abdo', 'uploads/6745ed81cdc5a.jpg', '2024-11-26 15:47:13'),
(4, 'oumayma@gmail.com', '$2y$10$e.xQC8ulpbhwGvO2SNjK2u5lGLIkygL2wEy1lVdeX7NsW8FUxDBeq', 'oumayma@gmail.com', 'etudiant', 'oumayma', 'oumayma', 'uploads/6745edd09063d.png', '2024-11-26 15:48:32'),
(5, 'aidi@gmail.com', '$2y$10$oY.IvTw2vO704P2VNXBSIu2S3yDTlsq70B7evGmOrgXaxlQx46b0O', 'aidi@gmail.com', 'etudiant', 'aidi', 'aidi', 'uploads/6745ee0ecde1d.jpg', '2024-11-26 15:49:34'),
(6, 'ghita@gmail.com', '$2y$10$OVVd.21RQK4kdsqd.bapT.bxD182ZgA7OlJlfV5G3qUkhgQo3BhVO', 'ghita@gmail.com', 'etudiant', 'ghita', 'ghita', 'uploads/6745ee4845056.png', '2024-11-26 15:50:32'),
(7, 'meryem@gmail.com', '$2y$10$2jw9B5iIyT8hgX0lOlzYT.0IrN33vRgfDl3bmmUmSbZoEASJOYUxm', 'meryem@gmail.com', 'etudiant', 'meryem', 'meryem', 'uploads/6745ee6f3f993.jpg', '2024-11-26 15:51:11'),
(8, 'taha@gmail.com', '$2y$10$puE7108YxW5EEuhpU/BHb.rza/cE5F0HK5PQ.MKPNesaM0ZtmoCZy', 'taha@gmail.com', 'etudiant', 'taha', 'taha', 'uploads/6745eec440342.jpg', '2024-11-26 15:52:36'),
(9, 'frida@gmail.com', '$2y$10$dW92XR3DPbkIAhO.o9L9eOUJmZAadXT2xQ8Dft34VnGKwyhdenZ4y', 'frida@gmail.com', 'etudiant', 'djskh', 'djskfhg', 'uploads/6745eeeba5320.jpg', '2024-11-26 15:53:15'),
(10, 'taha1@gmail.com', '$2y$10$f2tHMrbTZ3Nm5yiSHzYAPeUx9GAYIDVHYtCon2Lu3KG0FimQfPw3a', 'taha1@gmail.com', 'professeur', 'prof', 'prof', 'uploads/6745ef14d69e7.jpg', '2024-11-26 15:53:56'),
(11, 'tahaprof@gmail.com', '$2y$10$1SsmRC2sp21CMH5.gdU.sePN3IfqdNitDicd1lLK01U75Gid624qe', 'tahaprof@gmail.com', 'professeur', 'pr', 'pr', 'uploads/6745ef6e186fd.jpg', '2024-11-26 15:55:26'),
(12, 'tahaadmin@gmail.com', '$2y$10$Boue3/z4k7U4.OWeQ4KkA.hoiD7PAoe2emHUq3quPq0aQIz2huzfy', 'tahaadmin@gmail.com', 'admin', 'taha', 'admin', 'uploads/6745efa51c7e4.jpg', '2024-11-26 15:56:21'),
(13, 'tahapr@gmail.com', '$2y$10$x8nQRDEaBHjP5qK80XUXeunVvN7a1/38GJYSurpgTNeM2Kp5i6pJK', 'tahapr@gmail.com', 'professeur', 'proffff', 'taha', 'uploads/674620b697c4a.png', '2024-11-26 19:25:42'),
(14, 'yassine@gmail.com', '$2y$10$dMuTVYsGLL7EBVpKI1q.LuX1UB75QeBveUuZMgQRr2S0bpFRzUbma', 'yassine@gmail.com', 'etudiant', 'yassine', 'ettahiri', 'uploads/67462157004ea.png', '2024-11-26 19:28:23'),
(15, 'yass@gmail.com', '$2y$10$JJa0n7wqRKoaiWikNz4k4u6cIzb7834Ayxtfv7RfB4pkPGIFHiG1C', 'yass@gmail.com', 'etudiant', 'jhkbjgh', 'kjhg', 'uploads/67462565aacc3.png', '2024-11-26 19:45:41'),
(16, 'nouhaila@gmail.com', '$2y$10$vLATP2u6Avhryj34E9IpkegU74qqwF1SzDw5.sKvgWFTIn0tjM8rW', 'nouhaila@gmail.com', 'etudiant', 'nn', 'nn', 'uploads/674625cb9bb6e.png', '2024-11-26 19:47:23'),
(17, 'prof2@gmail.com', '$2y$10$QIgK8jJKsMpHOI0N6D51cuEDsCVe3LCZ5tQRkpIkSyzLIFZBHX/jW', 'prof2@gmail.com', 'professeur', 'prof', '2', 'uploads/6746cf0f850f4.png', '2024-11-27 07:49:35'),
(18, 'etudiant1@gmail.com', '$2y$10$R/.22lR2OWOGO8UVdpU1IOoMvJ6reANjyhrmFuAtn9Sf7EVbwYQ8a', 'etudiant1@gmail.com', 'etudiant', 'etudiant', '1', 'uploads/6746cf44015d4.png', '2024-11-27 07:50:28'),
(19, 'kurt@gmail.com', '$2y$10$tqSmVyiTaaSCg7hbP5kBTe.w6nVK5zpIhanURfrSZ.G5mme5uvYd.', 'kurt@gmail.com', 'etudiant', 'kurt', 'kurt', 'uploads/676b388798c48.png', '2024-12-24 22:41:11'),
(20, 'najm@gmail.com', '$2y$10$bUXfrPxNwrb/.5zjnn0XkexzAhpoSUQbLf3.2Ct0iFCQTm7R38Xem', 'najm@gmail.com', 'etudiant', 'najm', 'najm', 'uploads/676b38f745391.png', '2024-12-24 22:43:03'),
(21, 'abdslam@gmail.com', '$2y$10$W5K9dPJRyDAFImWPnZNzgOQbZii2il6VEFNquuj4xIu7a4LUpnSTa', 'abdslam@gmail.com', 'etudiant', 'abdslam', 'abdslam', 'uploads/676b39293dc80.png', '2024-12-24 22:43:53'),
(22, 'sabrine@gmail.com', '$2y$10$KKCpGJaqBFJl4LVxXFjd9uaSf.CnAJwrGH9TdaLsLplCQsHn18KVK', 'sabrine@gmail.com', 'etudiant', 'sabrine', 'sabrine', 'uploads/676b3964691e7.jpeg', '2024-12-24 22:44:52'),
(23, 'soufiane@gmail.com', '$2y$10$bTd2EYecX/gjiCt.RE1E/O87JHjIFiUjhER7D5.CVy5Q8REzynp7y', 'soufiane@gmail.com', 'etudiant', 'soufiane', 'soufiane', 'uploads/676b399ac73e7.png', '2024-12-24 22:45:46'),
(24, 'oussama@gmail.com', '$2y$10$dAbcfPYQAE/wrUPMA2lBn.AQfoSBjYUeiRPdOe3fsS4Y3pTaxxna6', 'oussama@gmail.com', 'etudiant', 'oussama', 'oussama', 'uploads/676b39b7a4555.png', '2024-12-24 22:46:15'),
(25, 'yazid@gmail.com', '$2y$10$k9dklDK0LGUZ9XZSlWR/6ecrmrvkVAlFsR9bvGcrWPFklIcWqri/2', 'yazid@gmail.com', 'etudiant', 'yazid', 'yazid', 'uploads/676b39e760cb9.jpg', '2024-12-24 22:47:03'),
(26, 'faris@gmail.com', '$2y$10$sqGykUoiijTXf.blfOJo4OeELD.AiT6ijyntnSYlV4dlABypL4vny', 'faris@gmail.com', 'etudiant', 'faris', 'faris', 'uploads/676b3a13258c8.png', '2024-12-24 22:47:47'),
(27, 'aantar@gmail.com', '$2y$10$mJDk6QHY0yDi3Ku0xpW2K.mHnnMjdg6bsfVv//NfTcD6HQPNL.0G.', 'aantar@gmail.com', 'etudiant', 'aantar', 'aantar', 'uploads/676b3b067108e.jpg', '2024-12-24 22:51:50'),
(28, 'aabla@gmail.com', '$2y$10$IH2CXaMtAoX4LA8dJwLhsuZ0KFVT5wgB5ke3CfWpDWJueo9tFwdjy', 'aabla@gmail.com', 'etudiant', 'aabla', 'aabla', 'uploads/676b3b3f2e3a6.png', '2024-12-24 22:52:47'),
(29, 'houssein@gmail.com', '$2y$10$lnDIMKoVJgQSDlX/d/NKuuJvaXFhOrFH.gOF74Vu/wO2gxrWu49Xa', 'houssein@gmail.com', 'etudiant', 'houssein', 'housseuin', 'uploads/676b3b7baf396.png', '2024-12-24 22:53:47'),
(30, 'aoidi@gmail.com', '$2y$10$e5oeNp5XzkbYNVDI6KxhEO9ieJLgFWDlxH9MdifVl6LWiMLUhO64.', 'aoidi@gmail.com', 'etudiant', 'youssef', 'aoidi', 'uploads/676b3bc0e1556.png', '2024-12-24 22:54:56'),
(31, 'damil@gmail.com', '$2y$10$5fRdSsWKsKLmPf1rhvJPcuq1ujSOw/VqnuuqEFvsjVnS3.yvejtyy', 'damil@gmail.com', 'professeur', 'damil', 'nourdine', 'uploads/676b3c050c285.png', '2024-12-24 22:56:05');

-- --------------------------------------------------------

--
-- Structure de la table `validations_presence`
--

CREATE TABLE `validations_presence` (
  `id` int(11) NOT NULL,
  `id_presence` int(11) NOT NULL,
  `id_validateur` int(11) NOT NULL,
  `heure_validation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `validations_presence`
--

INSERT INTO `validations_presence` (`id`, `id_presence`, `id_validateur`, `heure_validation`) VALUES
(1, 3, 23, '2024-12-24 23:14:36'),
(2, 5, 23, '2024-12-24 23:14:37'),
(3, 7, 23, '2024-12-24 23:14:37'),
(4, 6, 23, '2024-12-24 23:14:38'),
(5, 4, 23, '2024-12-24 23:15:10'),
(6, 2, 23, '2024-12-24 23:15:11'),
(7, 12, 27, '2024-12-30 14:58:04'),
(8, 10, 27, '2024-12-30 14:58:05'),
(9, 14, 27, '2024-12-30 14:58:06'),
(10, 15, 27, '2024-12-30 14:58:06'),
(11, 13, 28, '2024-12-30 14:59:17'),
(12, 9, 28, '2024-12-30 14:59:17'),
(13, 10, 28, '2024-12-30 14:59:18'),
(14, 12, 28, '2024-12-30 14:59:18'),
(15, 34, 20, '2024-12-30 17:02:28'),
(16, 35, 16, '2024-12-30 17:03:01'),
(17, 34, 16, '2024-12-30 17:03:01'),
(18, 34, 24, '2024-12-30 17:03:35'),
(19, 37, 24, '2024-12-30 17:03:36'),
(20, 36, 24, '2024-12-30 17:03:36'),
(21, 35, 24, '2024-12-30 17:03:37'),
(22, 34, 22, '2024-12-30 17:03:52'),
(23, 35, 22, '2024-12-30 17:03:53'),
(24, 36, 22, '2024-12-30 17:03:53'),
(25, 38, 22, '2024-12-30 17:03:53'),
(26, 40, 16, '2025-01-04 14:01:45'),
(27, 40, 20, '2025-01-04 14:02:17'),
(28, 42, 20, '2025-01-04 14:02:18'),
(29, 41, 20, '2025-01-04 14:02:19');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `conflits_place`
--
ALTER TABLE `conflits_place`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_seance` (`id_seance`),
  ADD KEY `id_place` (`id_place`),
  ADD KEY `id_etudiant1` (`id_etudiant1`),
  ADD KEY `id_etudiant2` (`id_etudiant2`),
  ADD KEY `id_etudiant_confirme` (`id_etudiant_confirme`);

--
-- Index pour la table `groupes_td`
--
ALTER TABLE `groupes_td`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `groupes_td_etudiants`
--
ALTER TABLE `groupes_td_etudiants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_groupe_etudiant` (`id_groupe_td`,`id_etudiant`),
  ADD KEY `id_etudiant` (`id_etudiant`);

--
-- Index pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_seance` (`id_seance`),
  ADD KEY `id_etudiant` (`id_etudiant`);

--
-- Index pour la table `places`
--
ALTER TABLE `places`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_place_salle` (`id_salle`,`numero_place`);

--
-- Index pour la table `presences`
--
ALTER TABLE `presences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_presence_seance` (`id_seance`,`id_etudiant`),
  ADD KEY `id_etudiant` (`id_etudiant`),
  ADD KEY `id_place` (`numero_place`),
  ADD KEY `idx_presences_seance` (`id_seance`);

--
-- Index pour la table `salles`
--
ALTER TABLE `salles`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `seances`
--
ALTER TABLE `seances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_professeur` (`id_professeur`),
  ADD KEY `id_salle` (`id_salle`),
  ADD KEY `id_groupe_td` (`id_groupe_td`),
  ADD KEY `idx_seances_date` (`date_seance`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_type` (`user_type`);

--
-- Index pour la table `validations_presence`
--
ALTER TABLE `validations_presence`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_validation` (`id_presence`,`id_validateur`),
  ADD KEY `id_validateur` (`id_validateur`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `conflits_place`
--
ALTER TABLE `conflits_place`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `groupes_td`
--
ALTER TABLE `groupes_td`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `groupes_td_etudiants`
--
ALTER TABLE `groupes_td_etudiants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT pour la table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `places`
--
ALTER TABLE `places`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `presences`
--
ALTER TABLE `presences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT pour la table `salles`
--
ALTER TABLE `salles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `seances`
--
ALTER TABLE `seances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT pour la table `validations_presence`
--
ALTER TABLE `validations_presence`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `conflits_place`
--
ALTER TABLE `conflits_place`
  ADD CONSTRAINT `conflits_place_ibfk_1` FOREIGN KEY (`id_seance`) REFERENCES `seances` (`id`),
  ADD CONSTRAINT `conflits_place_ibfk_2` FOREIGN KEY (`id_place`) REFERENCES `places` (`id`),
  ADD CONSTRAINT `conflits_place_ibfk_3` FOREIGN KEY (`id_etudiant1`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `conflits_place_ibfk_4` FOREIGN KEY (`id_etudiant2`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `conflits_place_ibfk_5` FOREIGN KEY (`id_etudiant_confirme`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `groupes_td_etudiants`
--
ALTER TABLE `groupes_td_etudiants`
  ADD CONSTRAINT `groupes_td_etudiants_ibfk_1` FOREIGN KEY (`id_groupe_td`) REFERENCES `groupes_td` (`id`),
  ADD CONSTRAINT `groupes_td_etudiants_ibfk_2` FOREIGN KEY (`id_etudiant`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`id_seance`) REFERENCES `seances` (`id`),
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`id_etudiant`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `places`
--
ALTER TABLE `places`
  ADD CONSTRAINT `places_ibfk_1` FOREIGN KEY (`id_salle`) REFERENCES `salles` (`id`);

--
-- Contraintes pour la table `presences`
--
ALTER TABLE `presences`
  ADD CONSTRAINT `presences_ibfk_1` FOREIGN KEY (`id_seance`) REFERENCES `seances` (`id`),
  ADD CONSTRAINT `presences_ibfk_2` FOREIGN KEY (`id_etudiant`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `seances`
--
ALTER TABLE `seances`
  ADD CONSTRAINT `seances_ibfk_1` FOREIGN KEY (`id_professeur`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `seances_ibfk_2` FOREIGN KEY (`id_salle`) REFERENCES `salles` (`id`),
  ADD CONSTRAINT `seances_ibfk_3` FOREIGN KEY (`id_groupe_td`) REFERENCES `groupes_td` (`id`);

--
-- Contraintes pour la table `validations_presence`
--
ALTER TABLE `validations_presence`
  ADD CONSTRAINT `validations_presence_ibfk_1` FOREIGN KEY (`id_presence`) REFERENCES `presences` (`id`),
  ADD CONSTRAINT `validations_presence_ibfk_2` FOREIGN KEY (`id_validateur`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
