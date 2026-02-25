/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.14-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: db    Database: desparking
-- ------------------------------------------------------
-- Server version	10.11.14-MariaDB-ubu2204-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_carpark_id` int(11) NOT NULL,
  `booking_user_id` int(11) NOT NULL,
  `booking_name` varchar(50) NOT NULL,
  `booking_start` datetime NOT NULL,
  `booking_end` datetime NOT NULL,
  PRIMARY KEY (`booking_id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookings`
--

LOCK TABLES `bookings` WRITE;
/*!40000 ALTER TABLE `bookings` DISABLE KEYS */;
INSERT INTO `bookings` VALUES
(35,103,1,'Henry','2026-02-05 13:00:00','2026-02-05 13:50:00'),
(36,103,1,'Henry barnes','2026-03-04 12:00:00','2026-03-04 13:15:00');
/*!40000 ALTER TABLE `bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carparks`
--

DROP TABLE IF EXISTS `carparks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `carparks` (
  `carpark_id` int(11) NOT NULL AUTO_INCREMENT,
  `carpark_owner` int(11) NOT NULL,
  `carpark_name` varchar(100) NOT NULL,
  `carpark_description` varchar(255) NOT NULL,
  `carpark_capacity` int(11) NOT NULL DEFAULT 1,
  `carpark_address` text NOT NULL,
  `carpark_lng` double NOT NULL,
  `carpark_lat` double NOT NULL,
  `carpark_type` enum('bookable','affiliate') NOT NULL,
  `carpark_affiliate_url` varchar(255) DEFAULT NULL,
  `carpark_features` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  PRIMARY KEY (`carpark_id`)
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carparks`
--

LOCK TABLES `carparks` WRITE;
/*!40000 ALTER TABLE `carparks` DISABLE KEYS */;
INSERT INTO `carparks` VALUES
(1,0,'Test','This is a description',1,'',1.30011,52.637924,'bookable','','{\r\n  \"Hello\": \"World\"\r\n}'),
(2,0,'Test 2','This is also a description',1,'',1.275952,52.641886,'affiliate','https://example.com','{\r\n  \"Hello\": \"World\"\r\n}'),
(53,0,'Westfield Stratford Parking','Large multi-storey near Westfield Stratford.',1,'',-0.0059,51.544,'bookable',NULL,'CCTV, Covered, EV Charging'),
(54,0,'London Bridge Car Park','Secure parking right beside London Bridge.',1,'',-0.0888,51.5066,'affiliate','https://partner.com/london-bridge','Secure, Outdoor'),
(55,0,'Canary Wharf Car Park','Underground parking in business district.',1,'',-0.0222,51.5054,'bookable',NULL,'Covered, CCTV'),
(56,0,'Victoria Station Parking','Busy central car park ideal for commuters.',1,'',-0.1433,51.4952,'affiliate','https://partner.com/victoria','Lighting, Covered'),
(57,0,'Camden Market Parking','Parking close to Camden Market.',1,'',-0.1406,51.539,'bookable',NULL,'Outdoor, Secure'),
(58,0,'Arndale Centre Parking','Central Manchester shopping district parking.',1,'',-2.242,53.483,'bookable',NULL,'Covered, CCTV'),
(59,0,'Old Trafford Stadium Parking','Matchday parking near the stadium.',1,'',-2.2913,53.4631,'affiliate','https://partner.com/old-trafford','Outdoor'),
(60,0,'MediaCityUK Parking','Parking close to BBC and MediaCity.',1,'',-2.3026,53.474,'bookable',NULL,'CCTV, Indoor'),
(61,0,'Manchester Piccadilly Parking','Central station parking.',1,'',-2.2317,53.4775,'affiliate','https://partner.com/piccadilly','Lighting'),
(62,0,'Etihad Campus Parking','Parking near Etihad Stadium.',1,'',-2.2003,53.4851,'bookable',NULL,'Outdoor'),
(63,0,'New Street Station Parking','Next to Birmingham New Street.',1,'',-1.899,52.4778,'affiliate','https://partner.com/new-street','Covered'),
(64,0,'Broad Street Parking','Nightlife district parking.',1,'',-1.915,52.4769,'bookable',NULL,'CCTV'),
(65,0,'Jewellery Quarter Parking','Historic area parking.',1,'',-1.9129,52.4893,'bookable',NULL,'Outdoor'),
(66,0,'Birmingham Airport Parking','Airport parking with shuttle.',1,'',-1.731,52.452,'affiliate','https://partner.com/bham-airport','Secure'),
(67,0,'ICC Birmingham Parking','Parking for events & conferences.',1,'',-1.9121,52.4789,'bookable',NULL,'Covered, CCTV'),
(68,0,'Liverpool Central Parking','Parking near Central Station.',1,'',-2.9814,53.4051,'bookable',NULL,'Outdoor'),
(69,0,'Albert Dock Parking','Tourist attraction parking.',1,'',-2.9925,53.401,'affiliate','https://partner.com/albert-dock','Lighting'),
(70,0,'Anfield Stadium Parking','Matchday stadium parking.',1,'',-2.9608,53.4308,'bookable',NULL,'Outdoor'),
(71,0,'Liverpool University Parking','Campus visitor parking.',1,'',-2.962,53.4065,'bookable',NULL,'CCTV'),
(72,0,'King’s Dock Parking','Large waterfront car park.',1,'',-2.992,53.397,'affiliate','https://partner.com/kings-dock','Covered'),
(73,0,'Leeds City Parking','Parking beside Leeds station.',1,'',-1.547,53.7941,'bookable',NULL,'Covered'),
(74,0,'Elland Road Parking','Football stadium parking.',1,'',-1.5721,53.7777,'affiliate','https://partner.com/elland-road','Outdoor'),
(75,0,'Leeds Dock Parking','Waterside parking.',1,'',-1.5315,53.791,'bookable',NULL,'CCTV'),
(76,0,'Leeds Arena Parking','Event venue parking.',1,'',-1.5331,53.8034,'affiliate','https://partner.com/leeds-arena','Lighting'),
(77,0,'Kirkstall Road Parking','Budget parking outside centre.',1,'',-1.575,53.8062,'bookable',NULL,'Outdoor'),
(78,0,'Newcastle Central Parking','City centre secure car park.',1,'',-1.6156,54.969,'affiliate','https://partner.com/newcastle-central','Covered'),
(79,0,'St James Park Parking','Football stadium parking.',1,'',-1.6215,54.9756,'bookable',NULL,'Outdoor'),
(80,0,'Quayside Parking','Scenic riverside parking.',1,'',-1.6068,54.9682,'bookable',NULL,'Lighting'),
(81,0,'Metrocentre Retail Parking','Large free parking at shopping centre.',1,'',-1.676,54.9585,'affiliate','https://partner.com/metrocentre','Outdoor'),
(82,0,'O2 Academy Parking','Event venue car park.',1,'',-1.613,54.9733,'bookable',NULL,'Indoor'),
(83,0,'Temple Meads Parking','Station parking.',1,'',-2.5804,51.4494,'affiliate','https://partner.com/temple-meads','Covered'),
(84,0,'Harbourside Parking','Parking near the harbour.',1,'',-2.6023,51.4491,'bookable',NULL,'Outdoor'),
(85,0,'Cabot Circus North Parking','Additional Cabot Circus facility.',1,'',-2.5846,51.4575,'bookable',NULL,'CCTV'),
(86,0,'Bristol Airport Parking','Airport shuttle parking.',1,'',-2.7087,51.3827,'affiliate','https://partner.com/bristol-airport','Secure'),
(87,0,'Clifton Village Parking','Parking in historic Clifton.',1,'',-2.62,51.455,'bookable',NULL,'Outdoor'),
(88,0,'St David’s Centre Parking','Main Cardiff shopping parking.',1,'',-3.1732,51.4808,'bookable',NULL,'Covered'),
(89,0,'Cardiff Stadium Parking','Event day parking.',1,'',-3.267,51.4725,'affiliate','https://partner.com/cardiff-stadium','Outdoor'),
(90,0,'Cardiff Bay Parking','Waterfront attractions parking.',1,'',-3.1686,51.4647,'bookable',NULL,'Outdoor'),
(91,0,'Queen Street Parking','City shopping district.',1,'',-3.1705,51.4812,'bookable',NULL,'Lighting'),
(92,0,'Cardiff University Parking','Campus visitor parking.',1,'',-3.1781,51.4898,'affiliate','https://partner.com/cardiff-uni','Lighting'),
(93,0,'Princes Street Parking','Shopping district parking.',1,'',-3.196,55.9526,'affiliate','https://partner.com/princes-street','Covered'),
(95,0,'Haymarket Station Parking','Railway station parking.',1,'',-3.2187,55.9453,'bookable',NULL,'CCTV'),
(98,0,'Riverside Retail Parking','Parking at Riverside leisure area.',1,'',1.3112,52.6247,'bookable',NULL,'Outdoor'),
(99,0,'Norwich Train Station Parking','Ideal for commuters.',1,'',1.3068,52.6262,'affiliate','https://partner.com/norwich-station','Covered'),
(100,0,'Chapelfield Shopping Parking','Multi-storey next to centre.',1,'101 Norwich Lane',1.295,52.624,'bookable',NULL,'Indoor'),
(101,0,'UEA Campus Parking','Parking for university visitors.',1,'',1.24,52.6212,'affiliate','https://partner.com/uea','Outdoor'),
(102,0,'Norwich Airport Parking','Airport parking with shuttle.',1,'',1.2793,52.6758,'bookable',NULL,'Secure'),
(103,3,'Henrys House','Henrys house parking',1,'32 Vane Close',1.3622697,52.6323883,'bookable','','CCTV'),
(104,3,'Fleetwood Drive','fleetwood drive parking space',1,'2 Fleetwood Drive, Norwich, NR7 0RT, United Kingdom',1.36636,52.63959,'bookable','','CCTV');
/*!40000 ALTER TABLE `carparks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `stripe_payment_intent_id` varchar(255) NOT NULL,
  `stripe_customer_id` varchar(255) DEFAULT NULL,
  `amount` int(11) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'gbp',
  `type` enum('initial','adjustment','refund') NOT NULL,
  `status` enum('pending','succeeded','failed','refunded','partial_refund') NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES
(1,35,1,'pi_3Swpu1LPm1JfR71q1uUngu5Q',NULL,4500,'gbp','initial','succeeded','2026-02-03 20:08:52'),
(23,35,1,'pi_3Swpu1LPm1JfR71q1uUngu5Q',NULL,500,'gbp','refund','succeeded','2026-02-03 22:11:45'),
(24,36,1,'pi_3SzdI6LPm1JfR71q0qEEr2JV',NULL,375,'gbp','initial','succeeded','2026-02-11 13:17:17'),
(25,36,1,'pi_3SzdIvLPm1JfR71q0sQkRA1W',NULL,3000,'gbp','initial','succeeded','2026-02-11 13:17:45'),
(26,36,1,'pi_3SzdIvLPm1JfR71q0sQkRA1W',NULL,1500,'gbp','refund','succeeded','2026-02-11 13:18:28');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rates`
--

DROP TABLE IF EXISTS `rates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `rates` (
  `rate_id` int(11) NOT NULL AUTO_INCREMENT,
  `carpark_id` int(11) NOT NULL,
  `duration_minutes` int(11) NOT NULL,
  `price` decimal(10,0) NOT NULL,
  PRIMARY KEY (`rate_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rates`
--

LOCK TABLES `rates` WRITE;
/*!40000 ALTER TABLE `rates` DISABLE KEYS */;
INSERT INTO `rates` VALUES
(1,1,1,100),
(2,1,1,100),
(4,103,1,100),
(5,104,1,500000),
(6,103,60,375);
/*!40000 ALTER TABLE `rates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(50) NOT NULL,
  `user_email` varchar(50) NOT NULL,
  `user_password_hash` longtext NOT NULL,
  `user_is_admin` int(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'Henry','henrytbarnes07@gmail.com','$2y$10$x6mTyL7KVsTgtkqHtoDnKeN7x2.moWFeDOvSDcIcJSn3ZZ/8FZAtW',1),
(2,'jeff','jeff@gmail.com','$2y$10$2erOZ1YSGvrGWknLkIHIYuHLDGjju4QUsKlOuYRkUI6pX6/0YiIT.',0),
(3,'Bob','bob@gmail.com','$2y$10$znzweyXS2IU3epq07ogxneU6rsNxuQ0/81/x6E8nLFB/qYkZhhDLS',0);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-25 11:00:56
