-- phpMyAdmin SQL Dump
-- version 5.2. 
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 11 juin 2026 à 20:51
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
-- Base de données : `gestion_cov`
--

-- --------------------------------------------------------

--
-- Structure de la table `app_settings`
--

CREATE TABLE `app_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` varchar(255) NOT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `app_settings`
--

INSERT INTO `app_settings` (`setting_key`, `setting_value`, `updated_at`) VALUES
('points_par_trajet', '10', '2026-06-08 21:22:52'),
('points_seuil_remise_sesame', '100', '2026-06-08 21:22:52'),
('prix_par_km', '259.000', '2026-06-08 22:04:33');

-- --------------------------------------------------------

--
-- Structure de la table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 3, 'user_login', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-29 21:07:39'),
(2, 3, 'user_logout', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-29 21:08:08'),
(3, 1, 'user_login', 'utilisateur', 1, '{\"role\":\"admin\",\"email\":\"admin@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-29 21:08:55'),
(4, 1, 'user_logout', 'utilisateur', 1, '{\"role\":\"admin\",\"email\":\"admin@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-29 21:10:39'),
(5, 3, 'user_login', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-29 21:11:05'),
(6, 3, 'user_logout', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-29 21:14:14'),
(7, 3, 'user_login', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-29 21:14:27'),
(8, 3, 'user_logout', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-29 21:15:02'),
(9, 2, 'user_login', 'utilisateur', 2, '{\"role\":\"etudiant\",\"email\":\"chiheb@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-29 21:17:26'),
(10, 2, 'user_logout', 'utilisateur', 2, '{\"role\":\"etudiant\",\"email\":\"chiheb@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-29 21:17:45'),
(11, 3, 'user_login', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-29 21:18:08'),
(12, 3, 'user_logout', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-29 21:19:00'),
(13, 3, 'user_login', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-29 21:19:28'),
(14, 3, 'user_logout', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-29 21:26:47'),
(15, 3, 'user_login', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-29 21:27:01'),
(16, 3, 'user_login', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '2026-04-29 22:18:48'),
(17, 3, 'user_login', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 22:26:53'),
(18, 3, 'trajet_created', 'trajet', 1, '{\"conducteur_id\":3,\"ville_depart\":\"sidi thabet\",\"ville_arrivee\":\"Sesame\",\"date_depart\":\"2026-04-30\",\"heure_depart\":\"08:00\",\"distance_km\":22.52,\"duree_minutes\":27,\"prix_par_km\":1,\"prix\":22.52,\"point_lat\":36.934338546423284,\"point_lng\":10.042701918864752,\"route_provider\":\"osrm\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 22:29:46'),
(19, 3, 'user_logout', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 22:29:55'),
(20, 2, 'user_login', 'utilisateur', 2, '{\"role\":\"etudiant\",\"email\":\"chiheb@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 22:31:25'),
(21, 2, 'reservation_created', 'reservation', 1, '{\"trajet_id\":1,\"passager_id\":2,\"conducteur_id\":3,\"statut\":\"en_attente\",\"prix_par_passager\":19.76,\"reservation_point_type\":\"prise_en_charge\",\"reservation_distance_km\":19.76}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 22:32:19'),
(22, 2, 'user_logout', 'utilisateur', 2, '{\"role\":\"etudiant\",\"email\":\"chiheb@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 22:32:33'),
(23, 3, 'user_login', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 22:32:36'),
(24, 3, 'reservation_confirmed', 'reservation', 1, '{\"reservation_id\":1,\"trajet_id\":1,\"passager_id\":2,\"previous_status\":\"en_attente\",\"new_status\":\"confirmee\",\"conducteur_id\":3}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 22:32:53'),
(25, 3, 'user_logout', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 22:32:57'),
(26, 1, 'user_login', 'utilisateur', 1, '{\"role\":\"admin\",\"email\":\"admin@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 22:33:16'),
(27, 3, 'user_login', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 23:58:45'),
(28, 3, 'user_logout', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 23:59:08'),
(29, 2, 'user_login', 'utilisateur', 2, '{\"role\":\"etudiant\",\"email\":\"chiheb@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-29 23:59:29'),
(30, 3, 'user_login', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 10:52:27'),
(31, 3, 'user_logout', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 10:52:42'),
(32, 3, 'user_login', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 10:52:47'),
(33, 3, 'user_logout', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 10:53:11'),
(34, 1, 'user_login', 'utilisateur', 1, '{\"role\":\"admin\",\"email\":\"admin@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 10:53:18'),
(35, 1, 'user_logout', 'utilisateur', 1, '{\"role\":\"admin\",\"email\":\"admin@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 10:57:10'),
(36, 3, 'user_login', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 10:57:14'),
(37, 3, 'trajet_created', 'trajet', 2, '{\"conducteur_id\":3,\"ville_depart\":\"sidi thabet\",\"ville_arrivee\":\"Sesame\",\"date_depart\":\"2026-10-10\",\"heure_depart\":\"10:00\",\"distance_km\":7.6,\"duree_minutes\":8,\"prix_par_km\":1,\"prix\":7.6,\"point_lat\":36.88282676491516,\"point_lng\":10.171645479236867,\"route_provider\":\"osrm\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 10:59:13'),
(38, 3, 'user_logout', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 10:59:17'),
(39, 2, 'user_login', 'utilisateur', 2, '{\"role\":\"etudiant\",\"email\":\"chiheb@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 10:59:44'),
(40, 2, 'reservation_created', 'reservation', 2, '{\"trajet_id\":2,\"passager_id\":2,\"conducteur_id\":3,\"statut\":\"en_attente\",\"prix_par_passager\":4.85,\"reservation_point_type\":\"prise_en_charge\",\"reservation_distance_km\":4.85}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 11:00:30'),
(41, 2, 'user_logout', 'utilisateur', 2, '{\"role\":\"etudiant\",\"email\":\"chiheb@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 11:00:40'),
(42, 3, 'user_login', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '2026-04-30 11:00:51'),
(43, 3, 'user_login', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-06 22:18:31'),
(44, 3, 'user_logout', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-06 22:19:06'),
(45, 2, 'user_login', 'utilisateur', 2, '{\"role\":\"etudiant\",\"email\":\"chiheb@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-06 22:19:15'),
(46, 2, 'user_logout', 'utilisateur', 2, '{\"role\":\"etudiant\",\"email\":\"chiheb@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-06 22:19:36'),
(47, 2, 'user_login', 'utilisateur', 2, '{\"role\":\"etudiant\",\"email\":\"chiheb@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-06 22:19:38'),
(48, 2, 'user_logout', 'utilisateur', 2, '{\"role\":\"etudiant\",\"email\":\"chiheb@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-06 22:19:41'),
(49, 1, 'user_login', 'utilisateur', 1, '{\"role\":\"admin\",\"email\":\"admin@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-06 22:19:47'),
(50, 1, 'user_logout', 'utilisateur', 1, '{\"role\":\"admin\",\"email\":\"admin@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-06 22:22:41'),
(51, 3, 'user_login', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', '2026-06-06 22:22:54'),
(52, 1, 'user_login', 'utilisateur', 1, '{\"role\":\"admin\",\"email\":\"admin@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 19:45:50'),
(53, 1, 'user_logout', 'utilisateur', 1, '{\"role\":\"admin\",\"email\":\"admin@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 21:17:38'),
(54, 2, 'user_login', 'utilisateur', 2, '{\"role\":\"etudiant\",\"email\":\"chiheb@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 21:19:31'),
(55, 2, 'user_logout', 'utilisateur', 2, '{\"role\":\"etudiant\",\"email\":\"chiheb@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 21:19:45'),
(56, 3, 'user_login', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 21:20:02'),
(57, 3, 'user_logout', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 21:30:12'),
(58, 3, 'user_login', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 21:42:13'),
(59, 3, 'user_logout', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 21:45:14'),
(60, 3, 'user_login', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 21:48:41'),
(61, 3, 'user_logout', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 21:48:47'),
(62, 3, 'user_login', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 21:55:50'),
(63, 3, 'reservation_confirmed', 'reservation', 2, '{\"reservation_id\":2,\"trajet_id\":2,\"passager_id\":2,\"previous_status\":\"en_attente\",\"new_status\":\"confirmee\",\"conducteur_id\":3}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 21:56:37'),
(64, 3, 'user_logout', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 21:56:52'),
(65, 2, 'user_login', 'utilisateur', 2, '{\"role\":\"etudiant\",\"email\":\"chiheb@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 21:57:07'),
(66, 2, 'user_logout', 'utilisateur', 2, '{\"role\":\"etudiant\",\"email\":\"chiheb@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 21:57:26'),
(67, 1, 'user_login', 'utilisateur', 1, '{\"role\":\"admin\",\"email\":\"admin@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 21:57:43'),
(68, 1, 'settings_updated', 'app_setting', NULL, '{\"setting_key\":\"prix_par_km\",\"new_value\":250}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 22:04:27'),
(69, 1, 'settings_updated', 'app_setting', NULL, '{\"setting_key\":\"prix_par_km\",\"new_value\":259}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 22:04:33'),
(70, 1, 'user_logout', 'utilisateur', 1, '{\"role\":\"admin\",\"email\":\"admin@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 22:04:45'),
(71, 2, 'user_login', 'utilisateur', 2, '{\"role\":\"etudiant\",\"email\":\"chiheb@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 22:04:52'),
(72, 2, 'user_logout', 'utilisateur', 2, '{\"role\":\"etudiant\",\"email\":\"chiheb@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 22:04:59'),
(73, 3, 'user_login', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 22:05:11'),
(74, 3, 'user_logout', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 22:05:22'),
(75, 1, 'user_login', 'utilisateur', 1, '{\"role\":\"admin\",\"email\":\"admin@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 22:11:28'),
(76, 1, 'user_logout', 'utilisateur', 1, '{\"role\":\"admin\",\"email\":\"admin@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 22:56:10'),
(77, 2, 'user_login', 'utilisateur', 2, '{\"role\":\"etudiant\",\"email\":\"chiheb@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 22:58:07'),
(78, 2, 'user_logout', 'utilisateur', 2, '{\"role\":\"etudiant\",\"email\":\"chiheb@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 22:58:17'),
(79, 3, 'user_login', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 22:58:29'),
(80, 3, 'user_logout', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 22:58:57'),
(81, 1, 'user_login', 'utilisateur', 1, '{\"role\":\"admin\",\"email\":\"admin@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 22:59:08'),
(82, 1, 'user_logout', 'utilisateur', 1, '{\"role\":\"admin\",\"email\":\"admin@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 23:00:59'),
(83, 3, 'user_login', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 23:01:09'),
(84, 3, 'user_logout', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 23:01:52'),
(85, 1, 'user_login', 'utilisateur', 1, '{\"role\":\"admin\",\"email\":\"admin@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 23:04:03'),
(86, 1, 'user_logout', 'utilisateur', 1, '{\"role\":\"admin\",\"email\":\"admin@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 23:05:18'),
(87, 1, 'user_login', 'utilisateur', 1, '{\"role\":\"admin\",\"email\":\"admin@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 23:15:09'),
(88, 1, 'user_logout', 'utilisateur', 1, '{\"role\":\"admin\",\"email\":\"admin@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 23:15:39'),
(89, 3, 'user_login', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 23:42:09'),
(90, 3, 'user_logout', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 23:42:26'),
(91, 1, 'user_login', 'utilisateur', 1, '{\"role\":\"admin\",\"email\":\"admin@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 23:42:32'),
(92, 1, 'account_deactivated', 'utilisateur', 2, '{\"target_role\":\"etudiant\",\"previous_status\":\"actif\",\"new_status\":\"desactive\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 23:42:53'),
(93, 1, 'account_activated', 'utilisateur', 2, '{\"target_role\":\"etudiant\",\"previous_status\":\"desactive\",\"new_status\":\"actif\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 23:42:54'),
(94, 1, 'user_logout', 'utilisateur', 1, '{\"role\":\"admin\",\"email\":\"admin@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-09 00:03:45'),
(95, 2, 'user_login', 'utilisateur', 2, '{\"role\":\"etudiant\",\"email\":\"chiheb@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-09 00:36:45'),
(96, 2, 'user_logout', 'utilisateur', 2, '{\"role\":\"etudiant\",\"email\":\"chiheb@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-09 00:37:06'),
(97, 3, 'user_login', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-09 00:37:20'),
(98, 3, 'user_logout', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-09 01:07:30'),
(99, 1, 'user_login', 'utilisateur', 1, '{\"role\":\"admin\",\"email\":\"admin@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-09 01:23:09'),
(100, 1, 'user_logout', 'utilisateur', 1, '{\"role\":\"admin\",\"email\":\"admin@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-09 01:24:45'),
(101, 2, 'user_login', 'utilisateur', 2, '{\"role\":\"etudiant\",\"email\":\"chiheb@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-09 01:24:50'),
(102, 2, 'user_logout', 'utilisateur', 2, '{\"role\":\"etudiant\",\"email\":\"chiheb@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-09 01:25:25'),
(103, 3, 'user_login', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-09 01:25:30'),
(104, 3, 'user_logout', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-09 01:25:50'),
(105, 2, 'user_login', 'utilisateur', 2, '{\"role\":\"etudiant\",\"email\":\"chiheb@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-09 01:25:56'),
(106, 2, 'user_logout', 'utilisateur', 2, '{\"role\":\"etudiant\",\"email\":\"chiheb@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-09 01:26:34'),
(107, 3, 'user_login', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-09 01:26:41'),
(108, 3, 'trajet_created', 'trajet', 3, '{\"conducteur_id\":3,\"ville_depart\":\"ariana\",\"ville_arrivee\":\"Sesame\",\"date_depart\":\"2026-06-11\",\"heure_depart\":\"10:28\",\"distance_km\":4.97,\"duree_minutes\":6,\"prix_par_km\":259,\"prix\":1287.23,\"point_lat\":36.86618004759711,\"point_lng\":10.208358764648438,\"route_provider\":\"osrm\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-09 01:28:40'),
(109, 3, 'user_logout', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-09 01:29:05'),
(110, 2, 'user_login', 'utilisateur', 2, '{\"role\":\"etudiant\",\"email\":\"chiheb@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-09 01:29:23'),
(111, 2, 'message_sent', 'message', 1, '{\"message_id\":1,\"expediteur_id\":2,\"destinataire_id\":3,\"trajet_id\":null}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-09 01:29:41'),
(112, 2, 'user_logout', 'utilisateur', 2, '{\"role\":\"etudiant\",\"email\":\"chiheb@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-09 01:31:07'),
(113, 3, 'user_login', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-09 01:31:15'),
(114, 3, 'user_logout', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-09 01:53:47'),
(115, 2, 'user_login', 'utilisateur', 2, '{\"role\":\"etudiant\",\"email\":\"chiheb@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-09 01:53:56'),
(116, 3, 'user_login', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-11 19:39:43'),
(117, 3, 'user_logout', 'utilisateur', 3, '{\"role\":\"conducteur\",\"email\":\"oussema@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-11 19:40:02'),
(118, 2, 'user_login', 'utilisateur', 2, '{\"role\":\"etudiant\",\"email\":\"chiheb@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-11 19:40:08'),
(119, 2, 'user_logout', 'utilisateur', 2, '{\"role\":\"etudiant\",\"email\":\"chiheb@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-11 19:40:14'),
(120, 1, 'user_login', 'utilisateur', 1, '{\"role\":\"admin\",\"email\":\"admin@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-11 19:40:28'),
(121, 1, 'user_logout', 'utilisateur', 1, '{\"role\":\"admin\",\"email\":\"admin@sesame.com.tn\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-06-11 19:40:49');

-- --------------------------------------------------------

--
-- Structure de la table `avis`
--

CREATE TABLE `avis` (
  `id` int(11) NOT NULL,
  `trajet_id` int(11) NOT NULL,
  `passager_id` int(11) NOT NULL,
  `note` int(11) NOT NULL CHECK (`note` >= 1 and `note` <= 5),
  `commentaire` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `expediteur_id` int(11) NOT NULL,
  `destinataire_id` int(11) NOT NULL,
  `trajet_id` int(11) DEFAULT NULL,
  `contenu` text NOT NULL,
  `lu` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `messages`
--

INSERT INTO `messages` (`id`, `expediteur_id`, `destinataire_id`, `trajet_id`, `contenu`, `lu`, `created_at`, `updated_at`) VALUES
(1, 2, 3, NULL, 'SLM', 1, '2026-06-09 01:29:41', '2026-06-09 01:31:22');

-- --------------------------------------------------------

--
-- Structure de la table `points_history`
--

CREATE TABLE `points_history` (
  `id` int(11) NOT NULL,
  `conducteur_id` int(11) NOT NULL,
  `reservation_id` int(11) DEFAULT NULL,
  `points_gained` int(11) NOT NULL,
  `reason` varchar(100) NOT NULL COMMENT 'ex: trajet_complete, bonus, etc',
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `trajet_id` int(11) NOT NULL,
  `passager_id` int(11) NOT NULL,
  `statut` enum('en_attente','confirmee','annulee','refusee') DEFAULT 'en_attente',
  `prix_snapshot` decimal(5,2) DEFAULT NULL,
  `confirmed_at` datetime DEFAULT NULL,
  `refused_at` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `payment_status` enum('non_applicable','declare_paye') DEFAULT 'non_applicable',
  `paid_amount` decimal(6,2) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `reservation_point_lat` decimal(10,7) DEFAULT NULL,
  `reservation_point_lng` decimal(10,7) DEFAULT NULL,
  `reservation_point_type` enum('prise_en_charge','depose') DEFAULT NULL,
  `reservation_distance_km` decimal(7,2) DEFAULT NULL,
  `reservation_duree_minutes` int(11) DEFAULT NULL,
  `reservation_price` decimal(6,2) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `points_earned` int(11) DEFAULT NULL COMMENT 'Points gagn├®s par le conducteur lors de la compl├®tion du trajet'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `reservations`
--

INSERT INTO `reservations` (`id`, `trajet_id`, `passager_id`, `statut`, `prix_snapshot`, `confirmed_at`, `refused_at`, `cancelled_at`, `payment_status`, `paid_amount`, `paid_at`, `reservation_point_lat`, `reservation_point_lng`, `reservation_point_type`, `reservation_distance_km`, `reservation_duree_minutes`, `reservation_price`, `created_at`, `updated_at`, `points_earned`) VALUES
(1, 1, 2, 'confirmee', 19.76, '2026-04-29 22:32:53', NULL, NULL, 'non_applicable', NULL, NULL, 36.9129513, 10.0564272, 'prise_en_charge', 19.76, 24, 19.76, '2026-04-29 22:32:19', '2026-04-29 22:32:53', NULL),
(2, 2, 2, 'confirmee', 4.85, '2026-06-08 21:56:37', NULL, NULL, 'non_applicable', NULL, NULL, 36.8696424, 10.1930337, 'prise_en_charge', 4.85, 5, 4.85, '2026-04-30 11:00:30', '2026-06-08 21:56:37', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `trajets`
--

CREATE TABLE `trajets` (
  `id` int(11) NOT NULL,
  `conducteur_id` int(11) NOT NULL,
  `ville_depart` varchar(100) NOT NULL,
  `ville_arrivee` varchar(100) NOT NULL,
  `date_depart` date NOT NULL,
  `heure_depart` time NOT NULL,
  `distance_km` decimal(7,2) DEFAULT NULL,
  `duree_minutes` int(11) DEFAULT NULL,
  `prix_par_km` decimal(6,3) DEFAULT 1.000,
  `point_lat` decimal(10,7) DEFAULT NULL,
  `point_lng` decimal(10,7) DEFAULT NULL,
  `route_geometry` longtext DEFAULT NULL,
  `route_provider` varchar(50) DEFAULT 'osrm',
  `route_calculated_at` datetime DEFAULT NULL,
  `prix` decimal(5,2) NOT NULL,
  `places_total` int(11) NOT NULL CHECK (`places_total` > 0),
  `places_restantes` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `statut_trajet` enum('publie','termine','annule') DEFAULT 'publie',
  `completed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `trajets`
--

INSERT INTO `trajets` (`id`, `conducteur_id`, `ville_depart`, `ville_arrivee`, `date_depart`, `heure_depart`, `distance_km`, `duree_minutes`, `prix_par_km`, `point_lat`, `point_lng`, `route_geometry`, `route_provider`, `route_calculated_at`, `prix`, `places_total`, `places_restantes`, `description`, `statut_trajet`, `completed_at`, `created_at`, `updated_at`) VALUES
(1, 3, 'sidi thabet', 'Sesame', '2026-04-30', '08:00:00', 22.52, 27, 1.000, 36.9343385, 10.0427019, '{\"coordinates\":[[10.042916,36.934357],[10.043104,36.93292],[10.043349,36.931046],[10.043475,36.930295],[10.043724,36.929376],[10.043929,36.928621],[10.044438,36.926764],[10.044633,36.926079],[10.04489,36.925176],[10.045254,36.923904],[10.045348,36.923732],[10.045739,36.923267],[10.046108,36.922925],[10.047351,36.921725],[10.048411,36.920715],[10.049502,36.919654],[10.050673,36.918532],[10.054446,36.914913],[10.054574,36.914785],[10.05472,36.914649],[10.054746,36.914626],[10.055979,36.913449],[10.056257,36.913161],[10.056425,36.912951],[10.056521,36.912966],[10.056937,36.913039],[10.057672,36.913167],[10.058221,36.913271],[10.058786,36.913379],[10.059257,36.913475],[10.059755,36.913573],[10.060278,36.913675],[10.062009,36.914028],[10.062152,36.914059],[10.062257,36.914085],[10.062397,36.914124],[10.062696,36.914207],[10.065331,36.914981],[10.065999,36.915164],[10.066655,36.915323],[10.067258,36.915489],[10.067874,36.915699],[10.068356,36.915905],[10.068681,36.916044],[10.069078,36.916212],[10.069426,36.916372],[10.069667,36.916491],[10.069905,36.916627],[10.070021,36.916695],[10.070194,36.916789],[10.070571,36.91699],[10.07104,36.91722],[10.071867,36.917616],[10.072111,36.917754],[10.072411,36.917921],[10.072468,36.917953],[10.07272,36.918094],[10.073079,36.918292],[10.073471,36.918485],[10.073902,36.918677],[10.074212,36.918803],[10.075089,36.91913],[10.076218,36.919495],[10.076984,36.919752],[10.077877,36.920025],[10.078226,36.920129],[10.079452,36.920496],[10.080143,36.920698],[10.080517,36.920798],[10.080833,36.92088],[10.081115,36.920938],[10.081415,36.920981],[10.081689,36.920995],[10.081903,36.920996],[10.082028,36.920998],[10.082158,36.920994],[10.082272,36.920989],[10.082385,36.920975],[10.082834,36.920896],[10.08358,36.920743],[10.083821,36.920697],[10.084675,36.920503],[10.085791,36.920264],[10.085895,36.920236],[10.086078,36.920197],[10.086245,36.920159],[10.086535,36.920097],[10.086912,36.920006],[10.08751,36.919844],[10.087794,36.919736],[10.08815,36.919562],[10.088389,36.919405],[10.089228,36.918816],[10.08936,36.918724],[10.08956,36.918589],[10.089599,36.918564],[10.089995,36.918389],[10.090703,36.918114],[10.090887,36.918044],[10.091054,36.917978],[10.091255,36.917896],[10.091731,36.917694],[10.091962,36.917591],[10.092178,36.917507],[10.092819,36.91723],[10.093212,36.91704],[10.093484,36.916908],[10.094139,36.916591],[10.094438,36.916438],[10.09474,36.916306],[10.094897,36.91624],[10.095093,36.916156],[10.0954,36.916052],[10.095693,36.91597],[10.096123,36.915853],[10.0965,36.915751],[10.096823,36.915663],[10.097118,36.915576],[10.097372,36.915489],[10.098106,36.915222],[10.098895,36.914908],[10.099126,36.914791],[10.09934,36.914679],[10.099656,36.91449],[10.100009,36.914277],[10.100412,36.914035],[10.100778,36.913815],[10.101407,36.913436],[10.102149,36.91299],[10.102497,36.912762],[10.102916,36.912478],[10.103242,36.912263],[10.103314,36.912205],[10.103408,36.912141],[10.10346,36.912107],[10.103643,36.911996],[10.103658,36.911972],[10.103683,36.911954],[10.103716,36.911944],[10.103751,36.911945],[10.103783,36.911957],[10.103807,36.911977],[10.103819,36.912003],[10.104018,36.912159],[10.104118,36.91224],[10.104365,36.91239],[10.104624,36.91255],[10.105037,36.912799],[10.105245,36.912928],[10.105409,36.913017],[10.105569,36.913088],[10.105689,36.913125],[10.1058,36.91315],[10.105851,36.913161],[10.106014,36.913184],[10.106103,36.913192],[10.106203,36.913201],[10.106362,36.913206],[10.10653,36.913204],[10.106598,36.913066],[10.10714,36.911891],[10.107207,36.911719],[10.107238,36.911618],[10.10726,36.911526],[10.10729,36.911365],[10.107333,36.911125],[10.107371,36.910848],[10.107433,36.910458],[10.107472,36.9102],[10.107489,36.910098],[10.107513,36.910008],[10.107543,36.909907],[10.107595,36.909784],[10.10773,36.909428],[10.1078,36.909256],[10.107887,36.909043],[10.107978,36.908834],[10.108036,36.908699],[10.108133,36.908471],[10.108244,36.908218],[10.108333,36.908034],[10.108495,36.907732],[10.108798,36.907187],[10.108932,36.906939],[10.109088,36.906686],[10.109298,36.906345],[10.109649,36.905726],[10.109892,36.905272],[10.110048,36.905028],[10.110241,36.904682],[10.110315,36.90455],[10.110522,36.904203],[10.110745,36.903873],[10.111037,36.903461],[10.111201,36.903229],[10.111543,36.902761],[10.111867,36.902325],[10.112092,36.90202],[10.112323,36.901745],[10.112575,36.901466],[10.112722,36.901299],[10.112907,36.901113],[10.113142,36.900864],[10.113261,36.900727],[10.113364,36.900598],[10.113499,36.900396],[10.113604,36.900172],[10.113778,36.899733],[10.113893,36.899432],[10.114041,36.89915],[10.114221,36.898884],[10.114435,36.898598],[10.114679,36.898327],[10.114818,36.898186],[10.11496,36.898054],[10.115065,36.897968],[10.115239,36.897851],[10.115467,36.897718],[10.115811,36.897552],[10.116135,36.897367],[10.116248,36.897285],[10.116333,36.897197],[10.116397,36.897088],[10.11642,36.897039],[10.116458,36.896996],[10.116507,36.896962],[10.116578,36.896934],[10.116656,36.896924],[10.116735,36.896933],[10.116807,36.896959],[10.116867,36.897],[10.116902,36.897042],[10.117097,36.897185],[10.117315,36.897305],[10.117549,36.897411],[10.117807,36.897503],[10.118027,36.89756],[10.11824,36.897589],[10.118554,36.897601],[10.118787,36.897588],[10.119007,36.897564],[10.119221,36.897521],[10.119421,36.897462],[10.119631,36.897379],[10.120455,36.896993],[10.120918,36.896854],[10.12153,36.896551],[10.121748,36.896442],[10.122837,36.895915],[10.123696,36.895512],[10.124221,36.89526],[10.125964,36.894419],[10.126517,36.89414],[10.126989,36.893923],[10.127514,36.893695],[10.128418,36.893314],[10.129293,36.892986],[10.129699,36.892838],[10.130424,36.892598],[10.131247,36.892311],[10.132035,36.892047],[10.132428,36.891935],[10.132765,36.891852],[10.132946,36.891821],[10.133105,36.891798],[10.133488,36.891769],[10.13383,36.891745],[10.134102,36.891741],[10.134381,36.891747],[10.134687,36.891757],[10.134978,36.89178],[10.135258,36.891813],[10.135678,36.891866],[10.136638,36.891996],[10.137283,36.892072],[10.137758,36.892117],[10.138673,36.892223],[10.138867,36.892246],[10.140738,36.892466],[10.141511,36.892553],[10.142226,36.892632],[10.143189,36.892728],[10.143824,36.892794],[10.145438,36.892956],[10.145657,36.892977],[10.146174,36.893001],[10.146563,36.892996],[10.14694,36.892981],[10.147266,36.89296],[10.14747,36.892941],[10.147691,36.89291],[10.148159,36.892809],[10.148745,36.892655],[10.149261,36.892487],[10.149876,36.892218],[10.150963,36.891666],[10.151539,36.89137],[10.152116,36.891104],[10.15251,36.890967],[10.152998,36.890827],[10.153371,36.890743],[10.153698,36.89069],[10.154015,36.890662],[10.154543,36.890642],[10.154905,36.890646],[10.15519,36.890658],[10.155556,36.890684],[10.155943,36.890727],[10.156529,36.89079],[10.157154,36.890828],[10.157582,36.890836],[10.158111,36.890801],[10.158401,36.890764],[10.158709,36.890714],[10.159316,36.89062],[10.159698,36.890561],[10.160949,36.890359],[10.16144,36.890266],[10.161981,36.890129],[10.162205,36.890052],[10.162427,36.889963],[10.162621,36.889884],[10.162992,36.889697],[10.163264,36.889546],[10.164245,36.888904],[10.165572,36.888031],[10.167141,36.887006],[10.168203,36.886289],[10.16929,36.885562],[10.170362,36.884846],[10.171405,36.884171],[10.17456,36.882087],[10.174621,36.882047],[10.175581,36.881444],[10.176361,36.880962],[10.178056,36.879866],[10.17949,36.878895],[10.179718,36.878736],[10.182393,36.876955],[10.18403,36.875882],[10.185778,36.874734],[10.187417,36.873584],[10.187675,36.873403],[10.187794,36.87332],[10.187895,36.873251],[10.189,36.872476],[10.189502,36.872088],[10.191918,36.870417],[10.193867,36.869064],[10.194739,36.868461],[10.196384,36.867288],[10.197453,36.866539],[10.198496,36.865779],[10.198636,36.865684],[10.198718,36.865625],[10.199294,36.865217],[10.199762,36.864866],[10.200004,36.864658],[10.200275,36.864423],[10.200539,36.864169],[10.200833,36.863826],[10.201008,36.863602],[10.201122,36.863454],[10.201354,36.863116],[10.201584,36.862763],[10.201844,36.862284],[10.20189,36.862194],[10.202044,36.86184],[10.20212,36.86166],[10.20228,36.861217],[10.202359,36.860988],[10.202436,36.860764],[10.202553,36.860359],[10.202652,36.859926],[10.202688,36.859626],[10.202748,36.858985],[10.202746,36.85857],[10.202732,36.857797],[10.202689,36.857242],[10.202646,36.856658],[10.202618,36.856275],[10.202612,36.856116],[10.20258,36.855501],[10.202564,36.855278],[10.202544,36.854888],[10.202533,36.854614],[10.202471,36.854015],[10.202465,36.853579],[10.202419,36.853053],[10.202007,36.846895],[10.201818,36.844871],[10.201805,36.844736],[10.201743,36.843909],[10.201595,36.842606],[10.201437,36.841079],[10.201404,36.840977],[10.20139,36.840818],[10.201321,36.840703],[10.201256,36.840607],[10.201183,36.840539],[10.201044,36.840442],[10.200664,36.840238],[10.200525,36.84018],[10.200386,36.840149],[10.200285,36.840149],[10.200133,36.840185],[10.200015,36.840228],[10.199912,36.840232],[10.199803,36.840203],[10.199717,36.840148],[10.199668,36.840093],[10.199648,36.840042],[10.199715,36.839904],[10.199776,36.839826],[10.199872,36.839732],[10.200127,36.839537],[10.20039,36.839328],[10.200597,36.839163],[10.200823,36.839068],[10.201017,36.838987],[10.201724,36.838674],[10.201883,36.838596],[10.2021,36.838489],[10.202201,36.838431],[10.202304,36.838353],[10.202412,36.838262],[10.202552,36.838119],[10.202752,36.837931],[10.202787,36.837917],[10.202828,36.837913],[10.202878,36.837915],[10.202909,36.837921],[10.202952,36.837933],[10.203032,36.838029],[10.203424,36.838502],[10.203603,36.838675],[10.204066,36.839121],[10.204337,36.839381],[10.204388,36.83944],[10.205151,36.840137],[10.205765,36.84069],[10.205794,36.840663],[10.205831,36.840647],[10.205871,36.840642],[10.205912,36.840647],[10.205947,36.840663],[10.205975,36.840689],[10.205985,36.840705],[10.205993,36.84074],[10.205989,36.840755],[10.205985,36.840774],[10.205949,36.840815],[10.205913,36.840832],[10.205871,36.840837],[10.205828,36.840831],[10.20509,36.841882],[10.204893,36.842162],[10.205537,36.842454],[10.205717,36.842535],[10.205642,36.842634],[10.205572,36.842664],[10.205572,36.842664]],\"type\":\"LineString\"}', 'osrm', '2026-04-29 22:29:46', 22.52, 4, 3, '', 'publie', NULL, '2026-04-29 22:29:46', '2026-04-29 22:32:19'),
(2, 3, 'sidi thabet', 'Sesame', '2026-10-10', '10:00:00', 7.60, 8, 1.000, 36.8828268, 10.1716455, '{\"coordinates\":[[10.171762,36.882914],[10.172176,36.882558],[10.172272,36.882476],[10.172278,36.882463],[10.172277,36.882447],[10.172255,36.882427],[10.172232,36.882414],[10.171581,36.882061],[10.171189,36.881865],[10.170815,36.881673],[10.170742,36.881619],[10.17109,36.881334],[10.17119,36.881264],[10.171308,36.881196],[10.17163,36.881033],[10.171841,36.880956],[10.17208,36.88088],[10.172427,36.880799],[10.172558,36.880778],[10.172897,36.880725],[10.173175,36.880671],[10.173458,36.880603],[10.173695,36.88054],[10.173949,36.880458],[10.174166,36.880381],[10.174305,36.880307],[10.174462,36.880211],[10.175202,36.879763],[10.175422,36.879618],[10.175838,36.879335],[10.176852,36.878669],[10.176944,36.878608],[10.177042,36.878551],[10.17721,36.878479],[10.177374,36.878445],[10.177636,36.878437],[10.177783,36.878454],[10.177916,36.878476],[10.17798,36.878457],[10.178067,36.878459],[10.178145,36.878488],[10.178202,36.87854],[10.178226,36.878595],[10.178224,36.878654],[10.178288,36.878754],[10.178437,36.878919],[10.178622,36.87908],[10.178784,36.879138],[10.178853,36.879135],[10.178949,36.8791],[10.179084,36.879033],[10.180352,36.8782],[10.182393,36.876955],[10.18403,36.875882],[10.185778,36.874734],[10.187417,36.873584],[10.187675,36.873403],[10.187794,36.87332],[10.187895,36.873251],[10.189,36.872476],[10.189502,36.872088],[10.191918,36.870417],[10.193867,36.869064],[10.194739,36.868461],[10.196384,36.867288],[10.197453,36.866539],[10.198496,36.865779],[10.198636,36.865684],[10.198718,36.865625],[10.199294,36.865217],[10.199762,36.864866],[10.200004,36.864658],[10.200275,36.864423],[10.200539,36.864169],[10.200833,36.863826],[10.201008,36.863602],[10.201122,36.863454],[10.201354,36.863116],[10.201584,36.862763],[10.201844,36.862284],[10.20189,36.862194],[10.202044,36.86184],[10.20212,36.86166],[10.20228,36.861217],[10.202359,36.860988],[10.202436,36.860764],[10.202553,36.860359],[10.202652,36.859926],[10.202688,36.859626],[10.202748,36.858985],[10.202746,36.85857],[10.202732,36.857797],[10.202689,36.857242],[10.202646,36.856658],[10.202618,36.856275],[10.202612,36.856116],[10.20258,36.855501],[10.202564,36.855278],[10.202544,36.854888],[10.202533,36.854614],[10.202471,36.854015],[10.202465,36.853579],[10.202419,36.853053],[10.202007,36.846895],[10.201818,36.844871],[10.201805,36.844736],[10.201743,36.843909],[10.201595,36.842606],[10.201437,36.841079],[10.201404,36.840977],[10.20139,36.840818],[10.201321,36.840703],[10.201256,36.840607],[10.201183,36.840539],[10.201044,36.840442],[10.200664,36.840238],[10.200525,36.84018],[10.200386,36.840149],[10.200285,36.840149],[10.200133,36.840185],[10.200015,36.840228],[10.199912,36.840232],[10.199803,36.840203],[10.199717,36.840148],[10.199668,36.840093],[10.199648,36.840042],[10.199715,36.839904],[10.199776,36.839826],[10.199872,36.839732],[10.200127,36.839537],[10.20039,36.839328],[10.200597,36.839163],[10.200823,36.839068],[10.201017,36.838987],[10.201724,36.838674],[10.201883,36.838596],[10.2021,36.838489],[10.202201,36.838431],[10.202304,36.838353],[10.202412,36.838262],[10.202552,36.838119],[10.202752,36.837931],[10.202787,36.837917],[10.202828,36.837913],[10.202878,36.837915],[10.202909,36.837921],[10.202952,36.837933],[10.203032,36.838029],[10.203424,36.838502],[10.203603,36.838675],[10.204066,36.839121],[10.204337,36.839381],[10.204388,36.83944],[10.205151,36.840137],[10.205765,36.84069],[10.205794,36.840663],[10.205831,36.840647],[10.205871,36.840642],[10.205912,36.840647],[10.205947,36.840663],[10.205975,36.840689],[10.205985,36.840705],[10.205993,36.84074],[10.205989,36.840755],[10.205985,36.840774],[10.205949,36.840815],[10.205913,36.840832],[10.205871,36.840837],[10.205828,36.840831],[10.20509,36.841882],[10.204893,36.842162],[10.205537,36.842454],[10.205717,36.842535],[10.205642,36.842634],[10.205572,36.842664],[10.205572,36.842664]],\"type\":\"LineString\"}', 'osrm', '2026-04-30 10:59:13', 7.60, 1, 0, '', 'publie', NULL, '2026-04-30 10:59:13', '2026-04-30 11:00:30'),
(3, 3, 'ariana', 'Sesame', '2026-06-11', '10:28:00', 4.97, 6, 259.000, 36.8661800, 10.2083588, '{\"coordinates\":[[10.208563,36.866278],[10.20848,36.866388],[10.208457,36.866408],[10.208428,36.866424],[10.20823,36.86648],[10.208198,36.86649],[10.208176,36.866503],[10.208154,36.866525],[10.207944,36.866798],[10.207948,36.866827],[10.207977,36.866854],[10.208248,36.86699],[10.207772,36.86758],[10.207408,36.867406],[10.207125,36.867278],[10.206354,36.866941],[10.205961,36.866782],[10.205707,36.866684],[10.205445,36.866583],[10.205089,36.866438],[10.204439,36.866168],[10.203445,36.86575],[10.203385,36.865721],[10.203021,36.865573],[10.202466,36.865353],[10.201786,36.865074],[10.201578,36.864985],[10.201424,36.864917],[10.20102,36.86476],[10.200855,36.864695],[10.200669,36.864616],[10.200568,36.864606],[10.200468,36.8646],[10.20044,36.864614],[10.200409,36.864625],[10.200321,36.864638],[10.200232,36.864627],[10.200153,36.864592],[10.200093,36.864538],[10.20006,36.864471],[10.200056,36.864399],[10.200061,36.864379],[10.200093,36.864318],[10.200147,36.864267],[10.200218,36.864233],[10.200278,36.86422],[10.200382,36.86413],[10.20074,36.863766],[10.201046,36.863411],[10.201269,36.8631],[10.201489,36.862763],[10.201844,36.862284],[10.20189,36.862194],[10.202044,36.86184],[10.20212,36.86166],[10.20228,36.861217],[10.202359,36.860988],[10.202436,36.860764],[10.202553,36.860359],[10.202652,36.859926],[10.202688,36.859626],[10.202748,36.858985],[10.202746,36.85857],[10.202732,36.857797],[10.202689,36.857242],[10.202646,36.856658],[10.202618,36.856275],[10.202612,36.856116],[10.20258,36.855501],[10.202564,36.855278],[10.202544,36.854888],[10.202533,36.854614],[10.202471,36.854015],[10.202465,36.853579],[10.202419,36.853053],[10.202007,36.846895],[10.201818,36.844871],[10.201805,36.844736],[10.201743,36.843909],[10.201595,36.842606],[10.201437,36.841079],[10.201404,36.840977],[10.20139,36.840818],[10.201321,36.840703],[10.201256,36.840607],[10.201183,36.840539],[10.201044,36.840442],[10.200664,36.840238],[10.200525,36.84018],[10.200386,36.840149],[10.200285,36.840149],[10.200133,36.840185],[10.200015,36.840228],[10.199912,36.840232],[10.199803,36.840203],[10.199717,36.840148],[10.199668,36.840093],[10.199648,36.840042],[10.199715,36.839904],[10.199776,36.839826],[10.199872,36.839732],[10.200127,36.839537],[10.20039,36.839328],[10.200597,36.839163],[10.200823,36.839068],[10.201017,36.838987],[10.201724,36.838674],[10.201883,36.838596],[10.2021,36.838489],[10.202201,36.838431],[10.202304,36.838353],[10.202412,36.838262],[10.202552,36.838119],[10.202752,36.837931],[10.202787,36.837917],[10.202828,36.837913],[10.202878,36.837915],[10.202909,36.837921],[10.202952,36.837933],[10.203032,36.838029],[10.203424,36.838502],[10.203603,36.838675],[10.204066,36.839121],[10.204337,36.839381],[10.204388,36.83944],[10.205151,36.840137],[10.205765,36.84069],[10.205794,36.840663],[10.205831,36.840647],[10.205871,36.840642],[10.205912,36.840647],[10.205947,36.840663],[10.205975,36.840689],[10.205985,36.840705],[10.205993,36.84074],[10.205989,36.840755],[10.205985,36.840774],[10.205949,36.840815],[10.205913,36.840832],[10.205871,36.840837],[10.205828,36.840831],[10.20509,36.841882],[10.204893,36.842162],[10.205537,36.842454],[10.205717,36.842535],[10.205642,36.842634],[10.205572,36.842664],[10.205572,36.842664]],\"type\":\"LineString\"}', 'osrm', '2026-06-09 02:28:40', 999.99, 4, 4, 'Y', 'publie', NULL, '2026-06-09 01:28:40', '2026-06-09 01:28:40');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `role` enum('admin','conducteur','etudiant','professeur') DEFAULT 'etudiant',
  `statut_compte` enum('actif','en_attente','refuse','desactive') DEFAULT 'actif',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `points_total` int(11) DEFAULT 0 COMMENT 'Total de points accumul├®s',
  `eligibilite_remise_sesame_at` datetime DEFAULT NULL COMMENT 'Date d''acc├¿s ├á la remise SESAME (100+ points)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `nom`, `prenom`, `email`, `mot_de_passe`, `telephone`, `role`, `statut_compte`, `created_at`, `updated_at`, `points_total`, `eligibilite_remise_sesame_at`) VALUES
(1, 'Admin', 'Faculte', 'admin@sesame.com.tn', '$2y$12$XgqYpGUbslxwcgrm5/VJm.FIcMb/WdE8eOyE7m6Wpez3d96ftS1wa', '00000000', 'admin', 'actif', '2026-04-28 22:32:23', '2026-04-28 22:32:23', 0, NULL),
(2, 'Chiheb', 'selmi', 'chiheb@sesame.com.tn', '$2y$10$55FP6UcBq7w1REcgEaruyeiXj1xLIsnSY4R/D1SnbUMBtAZBsfn92', '63152761', 'etudiant', 'actif', '2026-04-28 22:34:06', '2026-06-08 23:42:54', 0, NULL),
(3, 'oussema', 'hadded', 'oussema@sesame.com.tn', '$2y$10$V3j/LpwMQCSLkENMf2dxee6uwsUmDg/i9nvo2XFVVjFYO5ElYIBPe', '52042600', 'conducteur', 'actif', '2026-04-28 22:34:54', '2026-04-28 22:35:55', 0, NULL);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `app_settings`
--
ALTER TABLE `app_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Index pour la table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_user` (`user_id`),
  ADD KEY `idx_audit_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_audit_created_at` (`created_at`);

--
-- Index pour la table `avis`
--
ALTER TABLE `avis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_avis_trajet` (`trajet_id`),
  ADD KEY `idx_avis_passager` (`passager_id`);

--
-- Index pour la table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_message_destinataire` (`destinataire_id`),
  ADD KEY `fk_message_trajet` (`trajet_id`),
  ADD KEY `idx_messages_conversation` (`expediteur_id`,`destinataire_id`);

--
-- Index pour la table `points_history`
--
ALTER TABLE `points_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_conducteur` (`conducteur_id`),
  ADD KEY `idx_reservation` (`reservation_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Index pour la table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_reservation` (`trajet_id`,`passager_id`),
  ADD KEY `idx_reservation_trajet` (`trajet_id`),
  ADD KEY `idx_reservation_passager` (`passager_id`);

--
-- Index pour la table `trajets`
--
ALTER TABLE `trajets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_recherche_trajet` (`ville_depart`,`ville_arrivee`,`date_depart`),
  ADD KEY `idx_trajet_conducteur` (`conducteur_id`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_points_total` (`points_total`),
  ADD KEY `idx_eligibilite_sesame` (`eligibilite_remise_sesame_at`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=122;

--
-- AUTO_INCREMENT pour la table `avis`
--
ALTER TABLE `avis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `points_history`
--
ALTER TABLE `points_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `trajets`
--
ALTER TABLE `trajets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `avis`
--
ALTER TABLE `avis`
  ADD CONSTRAINT `fk_avis_passager` FOREIGN KEY (`passager_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_avis_trajet` FOREIGN KEY (`trajet_id`) REFERENCES `trajets` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_message_destinataire` FOREIGN KEY (`destinataire_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_message_expediteur` FOREIGN KEY (`expediteur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_message_trajet` FOREIGN KEY (`trajet_id`) REFERENCES `trajets` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `points_history`
--
ALTER TABLE `points_history`
  ADD CONSTRAINT `fk_points_history_conducteur` FOREIGN KEY (`conducteur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_points_history_reservation` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `fk_reservation_passager` FOREIGN KEY (`passager_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reservation_trajet` FOREIGN KEY (`trajet_id`) REFERENCES `trajets` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `trajets`
--
ALTER TABLE `trajets`
  ADD CONSTRAINT `fk_trajet_conducteur` FOREIGN KEY (`conducteur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
