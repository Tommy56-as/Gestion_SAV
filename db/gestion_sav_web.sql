-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 18 sep. 2025 à 20:12
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `gestion_sav`
--

-- --------------------------------------------------------

--
-- Structure de la table `approvisionnement`
--

CREATE TABLE `approvisionnement` (
  `idApp` int(11) NOT NULL,
  `produit` varchar(100) NOT NULL,
  `caracteristique` varchar(100) NOT NULL,
  `quantite_stock` varchar(100) DEFAULT NULL,
  `quantite_app` varchar(100) DEFAULT NULL,
  `Prix_total` double DEFAULT NULL,
  `fournisseur` varchar(100) DEFAULT NULL,
  `date_app` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `approvisionnement`
--

INSERT INTO `approvisionnement` (`idApp`, `produit`, `caracteristique`, `quantite_stock`, `quantite_app`, `Prix_total`, `fournisseur`, `date_app`) VALUES
(1, 'Laptop hp Corei5 6gen', 'Hdd500go/Ram8go/dd2go/cpu2GHz', '10', '5', 1000000, 'Waffo john', '2025-07-25'),
(2, 'Laptop hp Corei5 6gen', 'Hdd500go/Ram8go/dd2go/cpu2GHz', '0', '10', 1000000, 'Waffo john', '2025-08-28');

-- --------------------------------------------------------

--
-- Structure de la table `fournisseur`
--

CREATE TABLE `fournisseur` (
  `idfour` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `telephone` varchar(100) NOT NULL,
  `adresse` varchar(100) NOT NULL,
  `produit_livré` int(11) DEFAULT NULL,
  `statut` tinyint(1) DEFAULT NULL,
  `Created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `fournisseur`
--

INSERT INTO `fournisseur` (`idfour`, `nom`, `prenom`, `telephone`, `adresse`, `produit_livré`, `statut`, `Created_at`) VALUES
(1, 'Waffo', 'john', '658688907', 'paris', 1, 0, '2025-07-25 07:11:27');

-- --------------------------------------------------------

--
-- Structure de la table `historiques`
--

CREATE TABLE `historiques` (
  `id` int(11) NOT NULL,
  `utilisateur` varchar(80) DEFAULT NULL,
  `operation_effectuée` varchar(255) DEFAULT NULL,
  `date_action` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `historiques`
--

INSERT INTO `historiques` (`id`, `utilisateur`, `operation_effectuée`, `date_action`) VALUES
(1, 'admin', 'connexion de l\'utilisateur admin', '2025-07-24 22:09:36'),
(2, 'admin', 'connexion de l\'utilisateur admin', '2025-07-24 22:32:01'),
(3, 'admin', 'connexion de l\'utilisateur admin', '2025-07-25 06:41:09'),
(4, 'admin', 'connexion de l\'utilisateur admin', '2025-07-25 06:45:08'),
(5, 'admin', 'connexion de l\'utilisateur admin', '2025-07-25 06:48:00'),
(6, 'Ajout de l\'utilisateur raoul', 'admin', '2025-07-25 06:49:19'),
(7, 'admin', 'deconnnexion de l\'utilisateur ', '2025-07-25 06:49:42'),
(8, 'raoul', 'connexion de l\'utilisateur raoul', '2025-07-25 06:50:52'),
(9, 'raoul', 'connexion de l\'utilisateur raoul', '2025-07-25 06:57:32'),
(10, 'raoul', 'vente du produit Laptop hp Corei5 6gen\nPaulau clientPaul', '2025-07-25 07:01:34'),
(11, 'raoul', 'vente du produit Laptop hp Corei5 6gen\nPaulau clientPaul', '2025-07-25 07:01:37'),
(12, 'raoul', 'connexion de l\'utilisateur raoul', '2025-07-25 07:04:56'),
(13, 'raoul', 'connexion de l\'utilisateur raoul', '2025-07-25 07:08:04'),
(14, 'raoul', 'enregistrement du fournisseur Waffo john', '2025-07-25 07:11:27'),
(15, 'raoul', 'connexion de l\'utilisateur raoul', '2025-07-25 07:13:45'),
(16, 'raoul', 'connexion de l\'utilisateur raoul', '2025-07-25 07:18:08'),
(17, 'raoul', 'connexion de l\'utilisateur raoul', '2025-07-25 07:23:00'),
(18, 'raoul', 'Entrée de 5 Laptop hp Corei5 6gen\n avec Hdd500go/Ram8go/dd2go/cpu2GHz', '2025-07-25 07:24:14'),
(19, 'raoul', 'deconnnexion de l\'utilisateur ', '2025-07-25 07:25:41'),
(20, 'raoul', 'connexion de l\'utilisateur raoul', '2025-07-25 07:29:21'),
(21, 'raoul', 'connexion de l\'utilisateur raoul', '2025-07-25 07:36:59'),
(22, 'raoul', 'connexion de l\'utilisateur Raoul', '2025-07-25 07:41:59'),
(23, 'raoul', 'connexion de l\'utilisateur Administrateur:raoul', '2025-07-25 07:53:58'),
(24, 'raoul', 'connexion Administrateur: raoul', '2025-07-25 08:04:09'),
(25, 'raoul', 'connexion Administrateur: raoul', '2025-07-25 11:19:31'),
(26, 'raoul', 'deconnnexion de l\'utilisateur ', '2025-07-25 11:26:56'),
(27, 'raoul', 'connexion Administrateur: raoul', '2025-07-25 12:14:05'),
(28, 'raoul', 'connexion Administrateur: raoul', '2025-07-25 13:07:16'),
(29, 'raoul', 'connexion Administrateur: raoul', '2025-07-25 13:33:06'),
(30, 'raoul', 'connexion Administrateur: raoul', '2025-07-25 13:40:32'),
(31, 'raoul', 'connexion Administrateur: raoul', '2025-07-25 13:54:17'),
(32, 'raoul', 'connexion Administrateur: raoul', '2025-07-25 13:56:50'),
(33, 'raoul', 'deconnnexion de l\'utilisateur ', '2025-07-25 13:58:19'),
(34, 'raoul', 'connexion Administrateur: raoul', '2025-07-25 13:59:35'),
(35, 'raoul', 'vente du produit Laptop hp Corei5 6gen\n+au client atagana', '2025-07-25 14:00:34'),
(36, 'raoul', 'deconnnexion de l\'utilisateur ', '2025-07-25 14:01:44'),
(37, 'raoul', 'connexion Administrateur: raoul', '2025-07-25 14:06:12'),
(38, 'raoul', 'connexion Administrateur: raoul', '2025-07-25 14:13:52'),
(39, 'raoul', 'vente du produit Laptop hp Corei5 6gen\n+au client atagana', '2025-07-25 14:14:43'),
(40, 'raoul', 'vente du produit Laptop hp Corei5 6gen\n+au client Paul', '2025-07-25 14:15:17'),
(41, 'raoul', 'deconnnexion de l\'utilisateur ', '2025-07-25 14:17:57'),
(42, 'raoul', 'connexion Administrateur: raoul', '2025-07-25 18:11:06'),
(43, 'raoul', 'connexion Administrateur: raoul', '2025-07-26 05:37:06'),
(44, 'raoul', 'Ajout de l\'utilisateur Technicien:tech1', '2025-07-26 05:40:27'),
(45, 'tech1', 'connexion Technicien: tech1', '2025-07-26 05:43:06'),
(46, 'tech1', 'connexion Technicien: tech1', '2025-07-26 05:45:43'),
(47, 'tech1', 'ajout de la piece Disque dur', '2025-07-26 05:46:10'),
(48, 'raoul', 'connexion Administrateur: raoul', '2025-07-26 06:14:39'),
(49, 'raoul', 'connexion Administrateur: raoul', '2025-07-26 06:33:35'),
(50, 'raoul', 'connexion Administrateur: raoul', '2025-07-26 06:36:23'),
(51, 'raoul', 'connexion Administrateur: raoul', '2025-07-26 06:44:21'),
(52, 'raoul', 'Reparation en en cours pour le client arthur', '2025-07-26 06:46:12'),
(53, 'raoul', 'connexion Administrateur: raoul', '2025-07-26 06:52:18'),
(54, 'raoul', 'connexion Administrateur: raoul', '2025-07-26 06:57:56'),
(55, 'raoul', 'Reparation en en cours pour le client paul', '2025-07-26 06:58:34'),
(56, 'raoul', 'connexion Administrateur: raoul', '2025-07-26 07:02:12'),
(57, 'raoul', 'connexion Administrateur: raoul', '2025-07-26 07:07:14'),
(58, 'raoul', 'vente du produit Laptop hp Corei5 6gen\n+au client Paul', '2025-07-26 07:07:49'),
(59, 'raoul', 'deconnnexion de l\'utilisateur ', '2025-07-26 07:08:24'),
(60, 'raoul', 'connexion Administrateur: raoul', '2025-07-26 07:18:05'),
(61, 'raoul', 'connexion Administrateur: raoul', '2025-07-26 07:20:35'),
(62, 'raoul', 'connexion Administrateur: raoul', '2025-07-26 13:40:22'),
(63, 'raoul', 'ajout de la piece ram2go', '2025-07-26 13:41:14'),
(64, 'raoul', 'Reparation en terminée pour le client paul', '2025-07-26 13:43:32'),
(65, 'raoul', 'connexion Administrateur: raoul', '2025-07-26 13:57:26'),
(66, 'raoul', 'connexion Administrateur: raoul', '2025-08-21 14:42:54'),
(67, 'raoul', 'vente du produit laptop corei7\n+au client Paul', '2025-08-21 14:44:36'),
(68, 'raoul', 'vente du produit Laptop hp Corei5 6gen\n+au client Paul', '2025-08-21 14:46:17'),
(69, 'raoul', 'connexion Administrateur: raoul', '2025-08-21 14:48:48'),
(70, 'raoul', 'connexion Administrateur: raoul', '2025-08-22 12:08:06'),
(71, 'raoul', 'vente du produit laptop corei7\n+au client Paul', '2025-08-22 12:08:59'),
(72, 'raoul', 'connexion Administrateur: raoul', '2025-08-26 10:44:41'),
(73, 'raoul', 'Entrée de 10 Laptop hp Corei5 6gen\n avec Hdd500go/Ram8go/dd2go/cpu2GHz', '2025-08-26 10:45:48'),
(74, 'raoul', 'deconnnexion de l\'utilisateur ', '2025-08-26 10:52:06'),
(75, 'raoul', 'connexion Administrateur: raoul', '2025-08-26 10:53:48'),
(76, 'raoul', 'deconnnexion de l\'utilisateur ', '2025-08-26 10:54:28'),
(77, 'raoul', 'connexion Administrateur: raoul', '2025-08-26 10:56:14'),
(78, 'raoul', 'Reparation mise à jour en terminée pour le client paul', '2025-08-26 10:56:27'),
(79, 'raoul', 'Reparation mise à jour en terminée pour le client arthur', '2025-08-26 10:56:52'),
(80, 'raoul', 'Reparation mise à jour en terminée pour le client paul', '2025-08-26 10:57:00'),
(81, 'raoul', 'Reparation mise à jour en terminée pour le client arthur', '2025-08-26 10:57:55'),
(82, 'raoul', 'deconnnexion de l\'utilisateur ', '2025-08-26 10:58:18'),
(83, 'raoul', 'connexion Administrateur: raoul', '2025-08-26 11:00:30'),
(84, 'raoul', 'Reparation mise à jour en terminée pour le client arthur', '2025-08-26 11:01:03'),
(85, 'raoul', 'deconnnexion de l\'utilisateur ', '2025-08-26 11:01:31'),
(86, 'raoul', 'connexion Administrateur: raoul', '2025-08-26 11:04:10'),
(87, 'raoul', 'Reparation en en attente pour le client paul', '2025-08-26 11:04:44'),
(88, 'raoul', 'deconnnexion de l\'utilisateur ', '2025-08-26 11:05:33'),
(89, 'raoul', 'connexion Administrateur: raoul', '2025-08-26 11:10:42'),
(90, 'raoul', 'Reparation mise à jour en terminée pour le client arthur', '2025-08-26 11:11:36'),
(91, 'raoul', 'connexion Administrateur: raoul', '2025-08-27 11:43:51'),
(92, 'raoul', 'connexion Administrateur: raoul', '2025-08-27 11:53:06'),
(93, 'raoul', 'connexion Administrateur: raoul', '2025-08-27 11:55:08'),
(94, 'raoul', 'connexion Administrateur: raoul', '2025-08-27 12:10:05'),
(95, 'raoul', 'ajout de 20 ram laptopde 8 Go', '2025-08-27 12:11:10'),
(96, 'raoul', 'deconnnexion de l\'utilisateur ', '2025-08-27 12:11:49'),
(97, 'raoul', 'connexion Administrateur: raoul', '2025-08-27 12:25:15'),
(98, 'raoul', 'Reparation mise à jour en en attente pour le client paul', '2025-08-27 12:28:31'),
(99, 'raoul', 'deconnnexion de l\'utilisateur ', '2025-08-27 12:30:59'),
(100, 'raoul', 'connexion Administrateur: raoul', '2025-08-27 12:38:00'),
(101, 'raoul', 'connexion Administrateur: raoul', '2025-08-27 12:42:03'),
(102, 'raoul', 'deconnnexion de l\'utilisateur ', '2025-08-27 12:43:20'),
(103, 'raoul', 'connexion Administrateur: raoul', '2025-08-27 12:47:36'),
(104, 'raoul', 'Reparation mise à jour en terminée pour le client arthur', '2025-08-27 12:48:39'),
(105, 'raoul', 'deconnnexion de l\'utilisateur ', '2025-08-27 12:51:50'),
(106, 'raoul', 'connexion Administrateur: raoul', '2025-08-27 13:02:18'),
(107, 'raoul', 'deconnnexion de l\'utilisateur ', '2025-08-27 13:05:57'),
(108, 'raoul', 'connexion Administrateur: raoul', '2025-08-27 13:11:45'),
(109, 'raoul', 'deconnnexion de l\'utilisateur ', '2025-08-27 13:12:17'),
(110, 'raoul', 'connexion Administrateur: raoul', '2025-08-27 13:13:16'),
(111, 'raoul', 'deconnnexion de l\'utilisateur ', '2025-08-27 13:20:17'),
(112, 'raoul', 'connexion Administrateur: raoul', '2025-08-27 13:24:05'),
(113, 'raoul', 'Reparation en en attente pour le client paul', '2025-08-27 13:25:14'),
(114, 'raoul', 'Reparation mise à jour en en cours pour le client paul', '2025-08-27 13:26:43'),
(115, 'raoul', 'deconnnexion de l\'utilisateur ', '2025-08-27 13:29:30'),
(116, 'raoul', 'connexion Administrateur: raoul', '2025-08-27 14:05:53'),
(117, 'raoul', 'Ajout de l\'utilisateur Caissier:laura+\"mot de passe +\"1111', '2025-08-27 14:07:31'),
(118, 'raoul', 'deconnnexion de l\'utilisateur ', '2025-08-27 14:08:53'),
(119, 'raoul', 'connexion Administrateur: raoul', '2025-08-27 14:09:09'),
(120, 'raoul', 'Ajout de l\'utilisateur Caissier:laure+\"mot de passe +\"$2a$10$YsDkl7qDmzpx5QCAC7Bf7uzRNzsLGMODFBRKKNulDcSh/BA8CMc6u', '2025-08-27 14:10:02'),
(121, 'raoul', 'deconnnexion de l\'utilisateur ', '2025-08-27 14:10:18'),
(122, 'raoul', 'connexion Administrateur: raoul', '2025-08-27 14:14:16'),
(123, 'raoul', 'deconnnexion de l\'utilisateur ', '2025-08-27 14:15:44'),
(124, 'raoul', 'connexion Administrateur: raoul', '2025-08-27 14:17:24'),
(125, 'raoul', 'deconnnexion de l\'utilisateur ', '2025-08-27 14:22:19'),
(126, 'raoul', 'connexion Administrateur: raoul', '2025-08-27 14:25:11'),
(127, 'raoul', 'connexion Administrateur: raoul', '2025-08-27 14:27:11'),
(128, 'raoul', 'connexion Administrateur: raoul', '2025-08-27 14:28:36'),
(129, 'raoul', 'connexion Administrateur: raoul', '2025-08-27 14:41:58'),
(130, 'raoul', 'Reparation en en attente pour le client johm', '2025-08-27 14:44:15'),
(131, 'raoul', 'deconnnexion de l\'utilisateur ', '2025-08-27 14:49:28'),
(132, 'raoul', 'connexion Administrateur: raoul', '2025-08-27 14:49:54'),
(133, 'raoul', 'Reparation mise à jour en en cours pour le client johm', '2025-08-27 14:50:26'),
(134, 'raoul', 'connexion Administrateur: raoul', '2025-08-27 14:57:18'),
(135, 'raoul', 'Reparation mise à jour en en cours pour le client paul', '2025-08-27 14:57:54'),
(136, 'raoul', 'Reparation mise à jour en terminée pour le client paul', '2025-08-27 14:58:18'),
(137, 'raoul', 'Reparation mise à jour en terminée pour le client paul', '2025-08-27 14:58:36'),
(138, 'raoul', 'Reparation mise à jour en en attente pour le client paul', '2025-08-27 14:59:06'),
(139, 'raoul', 'deconnnexion de l\'utilisateur ', '2025-08-27 14:59:34'),
(140, 'raoul', 'connexion Administrateur: raoul', '2025-08-27 15:02:15'),
(141, 'raoul', 'connexion Administrateur: raoul', '2025-08-27 19:10:24'),
(142, 'raoul', 'deconnnexion de l\'utilisateur ', '2025-08-27 19:10:43'),
(143, 'raoul', 'connexion Administrateur: raoul', '2025-08-31 15:37:55'),
(144, 'raoul', 'vente du produit Laptop hp Corei5 6gen\n+au client Paul', '2025-08-31 15:39:26'),
(145, 'raoul', 'connexion Administrateur: raoul', '2025-08-31 15:44:30'),
(146, 'raoul', 'connexion Administrateur: raoul', '2025-08-31 15:54:45'),
(147, 'raoul', 'deconnnexion de l\'utilisateur ', '2025-08-31 15:55:04'),
(148, 'raoul', 'connexion Administrateur: raoul', '2025-09-01 08:28:29'),
(149, 'raoul', 'deconnnexion de l\'utilisateur ', '2025-09-01 08:29:25'),
(150, 'raoul', 'connexion Administrateur: raoul', '2025-09-01 08:33:58'),
(151, 'raoul', 'deconnnexion de l\'utilisateur ', '2025-09-01 08:37:27'),
(152, 'raoul', 'connexion Administrateur: raoul', '2025-09-01 08:37:43'),
(153, 'raoul', 'Reparation mise à jour en en cours pour le client paul', '2025-09-01 08:39:52'),
(154, 'raoul', 'deconnnexion de l\'utilisateur ', '2025-09-01 08:45:44'),
(155, 'raoul', 'connexion Administrateur: raoul', '2025-09-04 11:45:29');

-- --------------------------------------------------------

--
-- Structure de la table `lignevente`
--

CREATE TABLE `lignevente` (
  `idvente` int(11) NOT NULL,
  `prixTotal` double DEFAULT NULL,
  `prixRecu` double DEFAULT NULL,
  `remboursement` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `lignevente`
--

INSERT INTO `lignevente` (`idvente`, `prixTotal`, `prixRecu`, `remboursement`) VALUES
(1, 750000, 1000000, 350000),
(2, 750000, 1000000, 350000),
(3, 300000, 400000, 100000),
(4, 300000, 400000, 100000),
(5, 750000, 1000000, 350000),
(6, 750000, 1000000, 350000),
(7, 750000, 1000000, 250000),
(8, 150000, 1000000, 850000),
(9, 150000, 1000000, 850000),
(10, 750000, 1000000, 250000);

-- --------------------------------------------------------

--
-- Structure de la table `piece`
--

CREATE TABLE `piece` (
  `idpiece` int(11) NOT NULL,
  `designation` varchar(100) NOT NULL,
  `quantite` varchar(100) DEFAULT NULL,
  `quantite_min` varchar(100) DEFAULT NULL,
  `prixUnitaire` varchar(100) DEFAULT NULL,
  `Created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `piece`
--

INSERT INTO `piece` (`idpiece`, `designation`, `quantite`, `quantite_min`, `prixUnitaire`, `Created_at`) VALUES
(1, 'Disque dur', '10', '5', '10000', '2025-07-26 05:46:10'),
(2, 'ram2go', '5', '3', '1500', '2025-07-26 13:41:14');

-- --------------------------------------------------------

--
-- Structure de la table `produit`
--

CREATE TABLE `produit` (
  `idproduit` int(11) NOT NULL,
  `designation` varchar(100) NOT NULL,
  `caracteristique` varchar(100) NOT NULL,
  `quantite` varchar(100) NOT NULL,
  `quantite_min` varchar(100) NOT NULL,
  `prixUnitaire` double DEFAULT NULL,
  `categorie` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `Created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `produit`
--

INSERT INTO `produit` (`idproduit`, `designation`, `caracteristique`, `quantite`, `quantite_min`, `prixUnitaire`, `categorie`, `Created_at`) VALUES
(1, 'Laptop hp Corei5 6gen', 'Hdd500go/Ram8go/dd2go/cpu2GHz', '5', '5', 150000, 'ordinateur', '2025-07-25 07:00:10'),
(2, 'laptop corei7', '500Go/2g0ram/2goDD/2ghz', '14', '10', 150000, 'ordinateur', '2025-07-26 13:59:09'),
(3, 'disque dur HHD', '128 Go', '7', '5', 4000, 'equipement', '2025-08-27 11:59:13'),
(4, 'ram desktop', '4 Go', '15', '5', 5000, 'equipement', '2025-08-27 12:00:01'),
(5, 'ram laptop', '8 Go', '20', '5', 10000, 'equipement', '2025-08-27 12:11:10');

-- --------------------------------------------------------

--
-- Structure de la table `reclamation`
--

CREATE TABLE `reclamation` (
  `id` int(11) NOT NULL,
  `client` varchar(100) DEFAULT NULL,
  `produit` varchar(100) NOT NULL,
  `caracteristique` varchar(100) NOT NULL,
  `motif` text DEFAULT NULL,
  `diagnostic` text DEFAULT NULL,
  `garantie` date DEFAULT NULL,
  `date_retoue` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `reparation`
--

CREATE TABLE `reparation` (
  `idrep` int(11) NOT NULL,
  `technicien` varchar(100) DEFAULT NULL,
  `nomClient` varchar(100) NOT NULL,
  `telephone` varchar(100) NOT NULL,
  `appareil` varchar(100) NOT NULL,
  `diagnostic` text DEFAULT NULL,
  `solution` text DEFAULT NULL,
  `statut` varchar(100) DEFAULT NULL,
  `piece_utilisée` varchar(100) DEFAULT NULL,
  `quantite` varchar(100) DEFAULT NULL,
  `prixUnitaire` varchar(100) DEFAULT NULL,
  `prixTotal` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `reparation`
--

INSERT INTO `reparation` (`idrep`, `technicien`, `nomClient`, `telephone`, `appareil`, `diagnostic`, `solution`, `statut`, `piece_utilisée`, `quantite`, `prixUnitaire`, `prixTotal`) VALUES
(1, 'tech1', 'arthur', '658688910', 'hp core i5', 'disque detruit', 'remplacer ', 'terminée', 'disque dur HHD', '1', '10000.0', 12000),
(2, 'tech1', 'paul', '658688911', 'hp core i7', 'disque detruit', 'remplacer ', 'terminée', 'disque dur HHD', '7', '4000.0', 50000),
(3, 'tech1', 'paul', '6585487845', 'laptop', 'ram endommage', 'ram remplaer avec une ram2go', 'terminée', 'ram laptop', '1', '10000.0', 20123),
(4, 'tech1', 'paul', '6585487845', 'laptop', 'ram endommage', 'ram remplaer avec une ram2go', 'en cours', 'ram desktop', '1', '5000.0', 5500),
(5, 'tech1', 'paul', '658688910', 'hp core i5', 'disque detruit', 'remplacer ', 'en cours', 'disque dur HHD', '0', '4000.0', 42000),
(6, 'tech1', 'johm', '6558741012', 'laptop lenovo/25684652', 'ram endommage', 'changer ram', 'en cours', 'disque dur HHD', '2', '4000.0', 9000);

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `idUser` int(11) NOT NULL,
  `Nom_Utilisateur` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `TypeDeCompte` varchar(100) NOT NULL,
  `MotDePasse` varchar(100) NOT NULL,
  `NomComplet` varchar(100) NOT NULL,
  `Telephone` varchar(100) NOT NULL,
  `Adresse` varchar(100) NOT NULL,
  `Statut` tinyint(1) DEFAULT 0,
  `Created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_otp` varchar(6) DEFAULT NULL,
  `otp_expiration` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`idUser`, `Nom_Utilisateur`, `Email`, `TypeDeCompte`, `MotDePasse`, `NomComplet`, `Telephone`, `Adresse`, `Statut`, `Created_at`, `reset_otp`, `otp_expiration`) VALUES
(1, 'raoul', 'raoulloic610@gmail.com', 'Administrateur', '$2a$10$T7EHhYwxrwobRrXJnRJHceZpg6XD43bIdk5wHW9UMP3J4k6ENZbnG', 'tsague kougang raoul loic', '658688907', 'cite cicam', 0, '2025-07-25 06:49:19', '174781', '2025-07-26 02:48:00'),
(2, 'tech1', 'technicien@gmail.com', 'Technicien', '$2a$10$20poKUvlbDC7ndNnCqgysORSVjMu6ppst58ARzWSW8TGT/KDv6R1i', 'tom jean', '659770286', 'makepe', 0, '2025-07-26 05:40:27', NULL, NULL),
(3, 'laura', 'laura@gmail.com', 'Caissier', '$2a$10$M4.OaY5vX7b6mb7ZZOTcQ.bUmyd5BiCNUPuetZLYhrC9xEHJRz1we', 'nguickeu laura', '678124578', 'quartier 8 bis', 0, '2025-08-27 14:07:30', NULL, NULL),
(4, 'laure', 'laure@gmail.com', 'Caissier', '$2a$10$YsDkl7qDmzpx5QCAC7Bf7uzRNzsLGMODFBRKKNulDcSh/BA8CMc6u', 'nguickeu laure', '678124578', 'quartier 8 ', 0, '2025-08-27 14:10:02', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `vente`
--

CREATE TABLE `vente` (
  `idvente` int(11) NOT NULL,
  `client` varchar(100) NOT NULL,
  `produit` varchar(100) NOT NULL,
  `caracteristique` varchar(100) NOT NULL,
  `numero_serie` varchar(100) DEFAULT NULL,
  `prix_unitaire` double DEFAULT NULL,
  `quantite` varchar(100) NOT NULL,
  `fin_garantie` date DEFAULT NULL,
  `date_vente` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `vente`
--

INSERT INTO `vente` (`idvente`, `client`, `produit`, `caracteristique`, `numero_serie`, `prix_unitaire`, `quantite`, `fin_garantie`, `date_vente`) VALUES
(1, 'Paul', 'Laptop hp Corei5 6gen', 'Hdd500go/Ram8go/dd2go/cpu2GHz', '2546897ASRS', 150000, '5', '2025-07-27', '2025-07-25'),
(2, 'Paul', 'Laptop hp Corei5 6gen', 'Hdd500go/Ram8go/dd2go/cpu2GHz', '2546897ASRS', 150000, '5', '2025-07-27', '2025-07-25'),
(3, 'atagana', 'Laptop hp Corei5 6gen', 'Hdd500go/Ram8go/dd2go/cpu2GHz', 'QYU56821WD', 150000, '2', '2026-07-05', '2025-07-25'),
(4, 'atagana', 'Laptop hp Corei5 6gen', 'Hdd500go/Ram8go/dd2go/cpu2GHz', 'QYU56821WD', 150000, '2', '2026-07-05', '2025-07-26'),
(5, 'Paul', 'Laptop hp Corei5 6gen', 'Hdd500go/Ram8go/dd2go/cpu2GHz', '2546897ASRS', 150000, '5', '2025-07-27', '2025-08-04'),
(6, 'Paul', 'Laptop hp Corei5 6gen', 'Hdd500go/Ram8go/dd2go/cpu2GHz', '2546897ASRS', 150000, '5', '2025-07-27', '2025-08-04'),
(7, 'Paul', 'laptop corei7', '500Go/2g0ram/2goDD/2ghz', '2546897ASRS', 150000, '5', '2025-07-27', '2025-07-25'),
(8, 'Paul', 'Laptop hp Corei5 6gen', 'Hdd500go/Ram8go/dd2go/cpu2GHz', '2546897ASRS', 150000, '1', '2025-07-27', '2025-08-20'),
(9, 'Paul', 'laptop corei7', '500Go/2g0ram/2goDD/2ghz', '2546897ASRS', 150000, '1', '2025-07-27', '2025-08-22'),
(10, 'Paul', 'Laptop hp Corei5 6gen', 'Hdd500go/Ram8go/dd2go/cpu2GHz', '2546897ASRS', 150000, '5', '2025-07-27', '2025-08-31');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `approvisionnement`
--
ALTER TABLE `approvisionnement`
  ADD PRIMARY KEY (`idApp`);

--
-- Index pour la table `fournisseur`
--
ALTER TABLE `fournisseur`
  ADD PRIMARY KEY (`idfour`),
  ADD KEY `produit_livré` (`produit_livré`);

--
-- Index pour la table `historiques`
--
ALTER TABLE `historiques`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `lignevente`
--
ALTER TABLE `lignevente`
  ADD PRIMARY KEY (`idvente`);

--
-- Index pour la table `piece`
--
ALTER TABLE `piece`
  ADD PRIMARY KEY (`idpiece`);

--
-- Index pour la table `produit`
--
ALTER TABLE `produit`
  ADD PRIMARY KEY (`idproduit`);

--
-- Index pour la table `reclamation`
--
ALTER TABLE `reclamation`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `reparation`
--
ALTER TABLE `reparation`
  ADD PRIMARY KEY (`idrep`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`idUser`),
  ADD UNIQUE KEY `Nom_Utilisateur` (`Nom_Utilisateur`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Index pour la table `vente`
--
ALTER TABLE `vente`
  ADD PRIMARY KEY (`idvente`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `approvisionnement`
--
ALTER TABLE `approvisionnement`
  MODIFY `idApp` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `fournisseur`
--
ALTER TABLE `fournisseur`
  MODIFY `idfour` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `historiques`
--
ALTER TABLE `historiques`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=156;

--
-- AUTO_INCREMENT pour la table `lignevente`
--
ALTER TABLE `lignevente`
  MODIFY `idvente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `piece`
--
ALTER TABLE `piece`
  MODIFY `idpiece` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `produit`
--
ALTER TABLE `produit`
  MODIFY `idproduit` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `reclamation`
--
ALTER TABLE `reclamation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `reparation`
--
ALTER TABLE `reparation`
  MODIFY `idrep` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `idUser` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `vente`
--
ALTER TABLE `vente`
  MODIFY `idvente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `fournisseur`
--
ALTER TABLE `fournisseur`
  ADD CONSTRAINT `fournisseur_ibfk_1` FOREIGN KEY (`produit_livré`) REFERENCES `produit` (`idproduit`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
