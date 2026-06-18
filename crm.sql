-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 18, 2026 at 12:03 PM
-- Server version: 8.4.7
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `crm`
--

-- --------------------------------------------------------

--
-- Table structure for table `klanten`
--

DROP TABLE IF EXISTS `klanten`;
CREATE TABLE IF NOT EXISTS `klanten` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bedrijfsnaam` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contactpersoon` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adres` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `klanten`
--

INSERT INTO `klanten` (`id`, `bedrijfsnaam`, `contactpersoon`, `adres`, `email`) VALUES
(1, 'Acme Corporation', 'Peter de Groot', 'Hoofdstraat 12, 3511 AA Utrecht', 'facturatie@acme.com'),
(2, 'Rabobank Nederland', 'Annelies de Vries', 'Croeselaan 18, 3521 CB Utrecht', 'facturatie@rabobank.nl'),
(3, 'Albert Heijn B.V.', 'Bas de Jong', 'Provincialeweg 11, 1506 MA Zaandam', 'it-support@ah.nl'),
(4, 'Coolblue', 'Mariska Verhoeven', 'Weena 690, 3012 CN Rotterdam', 'finance@coolblue.nl'),
(5, 'NS (Nederlandse Spoorwegen)', 'Jeroen Hendriks', 'Laan van Puntenburg 100, 3511 ER Utrecht', 'infra-projects@ns.nl'),
(6, 'IT Solutions', 'Nino Slaats', 'Stationsplein 45, 6041 GN Roermond', 'nino.slaats@student.gildeopleidingen.nl');

-- --------------------------------------------------------

--
-- Table structure for table `medewerkers`
--

DROP TABLE IF EXISTS `medewerkers`;
CREATE TABLE IF NOT EXISTS `medewerkers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `naam` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `wachtwoord` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rol` enum('Medewerker','Verkoopmedewerker','Afdelingshoofd') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `medewerkers`
--

INSERT INTO `medewerkers` (`id`, `naam`, `email`, `wachtwoord`, `rol`) VALUES
(1, 'Mark de Vries', 'mark@gilde.nl', '$2y$10$2HrKuQ4eQvxaPXaBVc0L8e7aTjEzfsHMFLBZAlxxf37/O4sMBw85e', 'Medewerker'),
(2, 'Sarah Tanger', 'sarah@gilde.nl', '$2y$10$2HrKuQ4eQvxaPXaBVc0L8e7aTjEzfsHMFLBZAlxxf37/O4sMBw85e', 'Verkoopmedewerker'),
(3, 'Jan Hoofd', 'jan@gilde.nl', '$2y$10$2HrKuQ4eQvxaPXaBVc0L8e7aTjEzfsHMFLBZAlxxf37/O4sMBw85e', 'Afdelingshoofd'),
(4, 'Kevin de Bruijn', 'kevin@gilde.nl', '$2y$10$2HrKuQ4eQvxaPXaBVc0L8e7aTjEzfsHMFLBZAlxxf37/O4sMBw85e', 'Medewerker'),
(5, 'Ingrid Jansen', 'ingrid@gilde.nl', '$2y$10$2HrKuQ4eQvxaPXaBVc0L8e7aTjEzfsHMFLBZAlxxf37/O4sMBw85e', 'Afdelingshoofd');

-- --------------------------------------------------------

--
-- Table structure for table `opdrachten`
--

DROP TABLE IF EXISTS `opdrachten`;
CREATE TABLE IF NOT EXISTS `opdrachten` (
  `id` int NOT NULL AUTO_INCREMENT,
  `klant_id` int DEFAULT NULL,
  `naam` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `startdatum` date DEFAULT NULL,
  `einddatum` date DEFAULT NULL,
  `status` enum('Actief','Voldaan') COLLATE utf8mb4_unicode_ci DEFAULT 'Actief',
  `uurprijs` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `klant_id` (`klant_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `opdrachten`
--

INSERT INTO `opdrachten` (`id`, `klant_id`, `naam`, `startdatum`, `einddatum`, `status`, `uurprijs`) VALUES
(1, 1, 'Ontwikkeling Klantenportaal', '2026-06-12', NULL, 'Actief', 95.00),
(2, 2, 'Migratie Cloud Platform', '2026-06-01', NULL, 'Actief', 120.00),
(3, 2, 'Beveiligingsaudit Q1', '2026-05-01', '2026-06-10', 'Voldaan', 150.00),
(4, 3, 'Zelfscan App Optimalisatie', '2026-06-01', NULL, 'Actief', 110.00),
(5, 4, 'Klantenservice Chatbot V2', '2026-05-15', NULL, 'Actief', 125.00),
(6, 4, 'Server Migratie Black Friday', '2026-04-01', '2026-05-01', 'Voldaan', 140.00),
(7, 5, 'API Koppeling Dienstregeling', '2026-06-05', NULL, 'Actief', 135.00),
(8, 6, 'CRM Systeem Optimalisatie', '2026-06-12', NULL, 'Actief', 115.00);

-- --------------------------------------------------------

--
-- Table structure for table `werkzaamheden`
--

DROP TABLE IF EXISTS `werkzaamheden`;
CREATE TABLE IF NOT EXISTS `werkzaamheden` (
  `id` int NOT NULL AUTO_INCREMENT,
  `medewerker_id` int DEFAULT NULL,
  `opdracht_id` int DEFAULT NULL,
  `datum` date NOT NULL,
  `aantal_uren` decimal(5,2) NOT NULL,
  `omschrijving` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `medewerker_id` (`medewerker_id`),
  KEY `opdracht_id` (`opdracht_id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `werkzaamheden`
--

INSERT INTO `werkzaamheden` (`id`, `medewerker_id`, `opdracht_id`, `datum`, `aantal_uren`, `omschrijving`) VALUES
(1, 1, 1, '2026-06-12', 4.00, 'Dashboard layout afgerond en getest'),
(2, 4, 2, '2026-06-11', 8.00, 'API koppelingen omgezet naar Azure en endpoints succesvol getest.'),
(3, 1, 1, '2026-06-12', 5.50, 'Feedback verwerkt van de product owner en de CSS styling aangescherpt.'),
(4, 4, 1, '2026-06-12', 3.25, 'Database migratiescripts geschreven en SQL queries geoptimaliseerd.'),
(5, 1, 4, '2026-06-10', 6.50, 'UI-bugs opgelost in het afrekenscherm van de zelfscan interface.'),
(6, 1, 7, '2026-06-11', 4.00, 'JSON-parser geschreven voor realtime treindata verwerking.'),
(7, 4, 5, '2026-06-09', 7.50, 'Machine learning intents getraind voor de automatische retourverwerking.'),
(8, 4, 5, '2026-06-11', 5.00, 'Webhooks gekoppeld tussen het CRM en de frontend chat-widget.'),
(9, NULL, 8, '2026-06-12', 14.25, 'Automatische PDF-generatie en facturatiemodule gebouwd en getest.');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
