-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: expense_management
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

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
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `type` enum('income','expense') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (3,'Other Income','income','2025-07-01 22:03:51'),(5,'Utilities','expense','2025-07-01 22:03:51'),(6,'Marketing','expense','2025-07-01 22:03:51'),(7,'Travel','expense','2025-07-01 22:03:51'),(8,'Equipment','expense','2025-07-01 22:03:51'),(9,'Maintenance','expense','2025-07-01 22:03:51'),(10,'Other Expenses','expense','2025-07-01 22:03:51'),(11,'Salaries','expense','2025-07-21 13:54:41'),(12,'food','expense','2025-10-02 06:28:17'),(13,'rent','expense','2025-10-03 06:57:59');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `monthly_salary` decimal(10,2) NOT NULL,
  `hire_date` date DEFAULT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_id` (`employee_id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
INSERT INTO `employees` VALUES (25,'MW-952083','Fikirte Eshetu','Agedi','fikirte@mulewave.com','+251913001494','Human resource',20000.00,'2025-10-15',NULL,'active','2025-07-25 12:31:48'),(26,'MW-618279','wondifraw Eshetu','Agedi','wondifraw@mulewave.com','+251910622942','Driver',20000.00,'2025-03-19',NULL,'active','2025-07-25 12:35:09'),(27,'MW-355935','Gelilia Belachew','Tessema','gelilia.belachew@mulewave.com','0929116213','software engineer',20000.00,'2025-10-15',NULL,'active','2025-10-10 07:22:12'),(28,'MW-489107','Birhanu Baynesagn','Alene','birhanu.baynesagn@mulewave.com','0941909521','Software Engineer',20000.00,'2025-10-15',NULL,'active','2025-10-10 07:28:27'),(29,'MW-515649','Masresha','Yayeh','masresha@mulewave.com','0928730333','Cybersecurity Specialist / Analyst',30000.00,'2025-05-12',NULL,'active','2025-10-10 07:41:38'),(30,'MW-445850','Estifanos Girma','Wedaseneh','estifanos.girma@mulewave.com','0931386887','Software Engineer',15000.00,'2025-10-15',NULL,'active','2025-10-10 07:44:48'),(31,'MW-821370','Emebet Bekele','Kebede','emebet.bekele@mulewave.com','0913775616','Software developer',30000.00,'2025-08-07',NULL,'active','2025-10-10 08:32:18'),(32,'MW-548647','Haymanot Tesfay','Ejegu','haymanot.tesfay@mulewave.com','0904139200','Internship',10000.00,'2025-03-03',NULL,'active','2025-10-10 08:40:39'),(33,'MW-782016','Yordanos Tadesse','Kebede','yordanos.tadesse@mulewave.com','0983039819','Internship',10000.00,'2025-03-03',NULL,'active','2025-10-10 08:45:23'),(34,'MW-983720','Venos Hailemeskel','Kidanewold','venos.hailemeskel@mulewave.com','0937609277','software developer',15000.00,'2025-10-15',NULL,'active','2025-10-10 08:55:52');
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `migration_name` varchar(255) NOT NULL,
  `executed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'001_create_migrations_table.sql','2025-07-21 09:36:17'),(2,'002_add_attachment_path_to_transactions.sql','2025-07-21 09:36:17'),(3,'003_update_payment_date_precision.sql','2025-07-21 13:22:46'),(4,'004_add_attachment_path_to_employees.sql','2025-07-24 13:05:57');
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `salary_payments`
--

DROP TABLE IF EXISTS `salary_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salary_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `month` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` datetime(6) DEFAULT NULL,
  `status` enum('pending','paid') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_employee_month_year` (`employee_id`,`month`,`year`),
  CONSTRAINT `salary_payments_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salary_payments`
--

LOCK TABLES `salary_payments` WRITE;
/*!40000 ALTER TABLE `salary_payments` DISABLE KEYS */;
INSERT INTO `salary_payments` VALUES (6,25,9,2025,30000.00,NULL,'pending','','2025-10-10 06:52:03'),(7,26,9,2025,20000.00,NULL,'pending','','2025-10-10 06:52:36'),(8,28,9,2025,30000.00,NULL,'pending','Minimize the borrowed amount to 5,000','2025-10-10 09:04:34'),(9,31,9,2025,30000.00,NULL,'pending','','2025-10-10 09:05:10'),(10,30,9,2025,20000.00,NULL,'pending','','2025-10-10 09:05:25'),(12,27,9,2025,30000.00,NULL,'pending','','2025-10-10 09:07:18'),(13,32,9,2025,10000.00,NULL,'pending','','2025-10-10 09:07:36'),(14,29,9,2025,30000.00,NULL,'pending','','2025-10-10 09:07:59'),(15,34,9,2025,30000.00,NULL,'pending','','2025-10-10 09:08:12'),(17,33,9,2025,10000.00,NULL,'pending','','2025-10-10 09:09:59');
/*!40000 ALTER TABLE `salary_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('income','expense') NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `transaction_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `attachment_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` VALUES (4,'expense',8,12000.00,'house hold equipment(12 dish, tea glass, coffee cup, pasta maker, set bowl)','2025-03-25','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-07-25 09:06:09','2025-07-25 09:06:29',NULL),(5,'expense',8,2000.00,'maereg purchased pinch ???','2025-03-25','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-07-25 09:10:48','2025-07-25 09:11:05',NULL),(6,'expense',8,578300.00,'LG Equipment\'s (oven, refrigerator, washing machine TV)','2025-05-02','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-07-25 09:44:34','2025-07-25 09:44:34','uploads/tx_6883520219d171.51951722_20250725_122536[1].jpg'),(7,'expense',8,76650.00,'oven with transport cost','2025-04-28','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-07-25 09:54:55','2025-07-25 09:54:55','uploads/tx_6883546f5eb9a9.04961080_20250725_124735[1].jpg'),(8,'expense',8,50000.00,'deluxe group table','2025-04-29','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-07-25 10:12:25','2025-07-25 10:12:25','uploads/tx_68835889928cb1.76485454_20250725_130749[1].jpg'),(9,'expense',NULL,62000.00,'curtain(2/3) payment','2025-05-15','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-07-25 11:25:13','2025-07-25 11:25:13','uploads/tx_68836999146c24.14965743_cbe_receiptFT25135MCV6V_(1)[1].pdf'),(10,'expense',NULL,11150.00,'1/4 Curtin','2025-05-17','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-07-25 11:37:46','2025-07-25 11:37:46','uploads/tx_68836c8a4efa82.01555573_Megareja_2_payment[1].pdf'),(11,'expense',NULL,36000.00,'conference table','2025-07-31','mule Wave Technology - Addis Ababa ETHIOPIA','2025-07-31 08:40:10','2025-07-31 08:40:10','uploads/tx_688b2bea2a7cf1.56513809_photo_2025-07-31_11-36-17.jpg'),(12,'expense',10,3150.00,'gas','2025-08-04','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-08-04 09:21:29','2025-08-04 09:21:29','uploads/tx_68907b991d8197.95078761_20250804_121601[1].jpg'),(13,'income',NULL,200000.00,'general purpose','2025-02-14','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-02 06:36:09','2025-10-02 06:36:09',NULL),(14,'income',NULL,150000.00,'General purpose','2025-04-08','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-02 06:39:53','2025-10-02 06:39:53',NULL),(15,'income',NULL,26000.00,'General purpose','2025-04-15','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-02 06:41:15','2025-10-02 06:41:31',NULL),(16,'income',NULL,700000.00,'General purpose','2025-04-19','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-02 06:42:44','2025-10-02 06:42:44',NULL),(17,'income',NULL,150000.00,'General purpose','2025-04-28','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-02 06:53:55','2025-10-02 06:53:55',NULL),(18,'income',NULL,1000000.00,'General purpose','2025-05-06','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-02 06:55:29','2025-10-02 07:07:27',NULL),(19,'income',NULL,100000.00,'General purpose','2025-06-06','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-02 06:58:54','2025-10-02 06:58:54',NULL),(20,'income',NULL,100000.00,'General purpose','2025-06-10','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-02 07:00:10','2025-10-02 07:00:10',NULL),(21,'income',NULL,2200000.00,'General purpose','2025-07-04','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-02 07:02:33','2025-10-02 07:02:33',NULL),(22,'income',NULL,160000.00,'general purpose(Negat transfer money)','2025-08-09','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-02 07:05:16','2025-10-02 07:05:16',NULL),(23,'expense',NULL,2000.00,'wosen cloth (qumta)','2025-10-02','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-02 07:17:48','2025-10-02 07:39:11','uploads/tx_68de271cdf9746.52331996_1.pdf'),(24,'expense',8,150000.00,'dinning table','2025-06-03','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-02 08:09:06','2025-10-02 08:09:06','uploads/tx_68de3322b1d084.33824359_20251002_110028[1].jpg'),(25,'expense',8,42000.00,'bed','2025-10-02','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-02 08:21:00','2025-10-02 08:21:00','uploads/tx_68de35ec920074.30759211_20251002_111110.jpg'),(26,'expense',8,33000.00,'L-shape office table','2025-10-02','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-02 08:24:22','2025-10-02 08:24:22','uploads/tx_68de36b61a75d7.48845616_20251002_112133[1].jpg'),(27,'expense',8,20000.00,'stabilizer (two pc)','2025-10-02','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-02 08:26:42','2025-10-02 08:26:42','uploads/tx_68de37421559b3.40590297_Stabilizer.pdf'),(28,'expense',8,19500.00,'router table','2025-05-19','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-02 08:38:19','2025-10-02 08:38:19','uploads/tx_68de39fb866be9.47056989_20251002_113352[1].jpg'),(29,'expense',NULL,17700.00,'house equipment with cash and transfer','2025-05-14','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-02 08:42:00','2025-10-02 08:42:00','uploads/tx_68de3ad813c155.21109222_House equipment payment.pdf'),(30,'expense',8,7330.00,'Ironing and liquid soap','2025-05-05','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-02 09:06:40','2025-10-02 09:06:40','uploads/tx_68de40a0353786.58151512_20251002_120250[1].jpg'),(31,'expense',8,5000.00,'Tv stand','2025-10-02','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-02 09:08:40','2025-10-02 09:08:40','uploads/tx_68de4118a3bf50.73284142_Tv stand payment.pdf'),(32,'expense',5,5000.00,'2 body soap(2000+3000 birr)','2025-05-21','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-02 09:29:29','2025-10-02 09:29:29','uploads/tx_68de45f9900465.00734406_wosen soap (attached).pdf'),(33,'expense',5,4700.00,'cleaning material','2025-05-22','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-02 09:30:30','2025-10-02 09:30:30',NULL),(34,'expense',NULL,2750.00,'electric tool ( two bulb)','2025-05-21','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-02 12:09:32','2025-10-02 12:09:32','uploads/tx_68de6b7c5bf7c9.36582821_20251002_150502[1].jpg'),(35,'expense',NULL,2500.00,'ye injera mesob','2025-05-26','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-02 12:29:09','2025-10-02 12:29:09',NULL),(36,'expense',8,2200.00,'milk boiler','2025-05-23','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-02 12:34:08','2025-10-02 12:35:18','uploads/tx_68de71402452f4.43338045_milk_boiler[1].pdf'),(37,'expense',5,2100.00,'white plaster or chair plaster','2025-06-06','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-02 13:14:23','2025-10-02 13:14:23','uploads/tx_68de7aafd1a1f9.59378978_white plaster 2_merged.pdf'),(38,'expense',NULL,2500.00,'shell(cylinder) regulator','2025-05-12','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-02 13:19:19','2025-10-02 13:19:19','uploads/tx_68de7bd75c3fc1.33717329_20251002_161440[1].jpg'),(39,'expense',13,99000.00,'house rent','2025-03-04','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-03 06:59:49','2025-10-03 06:59:49','uploads/tx_68df74658cf142.30691848_20251003_095106[1].jpg'),(40,'expense',11,95000.00,'staff salary(7 STAFF)','2025-04-08','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-03 07:04:00','2025-10-03 07:13:05',NULL),(41,'expense',11,18000.00,'Gelila AND KIDIST salary','2025-04-04','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-03 07:14:34','2025-10-03 07:14:34',NULL),(42,'expense',11,127500.00,'staff salary','2025-05-06','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-03 07:25:45','2025-10-03 07:25:45',NULL),(43,'expense',11,160000.00,'staff salary','2025-06-05','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-03 07:29:23','2025-10-03 07:29:23',NULL),(44,'expense',13,48000.00,'car rent','2025-06-11','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-03 08:12:58','2025-10-03 08:12:58','uploads/tx_68df858ab8fcc6.64955952_Car rent.pdf'),(45,'expense',13,110000.00,'house rent','2025-04-14','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-03 08:26:28','2025-10-03 08:26:28','uploads/tx_68df88b45bfdd7.24867798_20251003_112130[1].jpg'),(46,'expense',5,3400.00,'wifi end payment','2025-03-25','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-03 08:42:44','2025-10-03 08:44:24','uploads/tx_68df8c8404d4b8.88007954_Wi-Fi payment.pdf'),(47,'expense',10,2000.00,'wifi worker tip','2025-03-27','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-03 08:44:01','2025-10-03 08:44:01',NULL),(48,'expense',5,46300.00,'Wi-Fi April','2025-05-14','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-03 08:46:14','2025-10-03 08:46:14','uploads/tx_68df8d5658bbc9.61436856_Wi-Fi April payment.pdf'),(49,'expense',5,54000.00,'Safaricom Wi-Fi first installation payment','2025-05-07','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-03 08:51:48','2025-10-03 08:51:48','uploads/tx_68df8ea4e1ac74.97014791_20251003_114823[1].jpg'),(50,'expense',5,46300.00,'monthly Wi-Fi payment','2025-04-17','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-03 08:59:55','2025-10-03 08:59:55','uploads/tx_68df908baa6dc4.85408439_Wifi payment March.pdf'),(51,'expense',5,14700.00,'unlimited Fikirte yearly package','2025-05-05','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-03 11:09:44','2025-10-03 11:09:44','uploads/tx_68dfaef88e50d5.78124207_20251003_140516[1].jpg'),(52,'expense',12,30000.00,'food cost(February and march)','2025-03-01','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-03 11:17:19','2025-10-03 11:20:07','uploads/tx_68dfb0bf4b2740.98082667_Food2_Food3_Food4_merged.pdf'),(53,'expense',12,7600.00,'teff payment with cost of worker','2025-04-01','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-03 11:19:11','2025-10-03 11:19:11','uploads/tx_68dfb12faddde1.44047738_Teff payment.pdf'),(54,'expense',12,10000.00,'food cost','2025-05-15','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-03 11:21:51','2025-10-03 11:21:51','uploads/tx_68dfb1cf4f4114.58527904_Food 5.pdf'),(55,'expense',12,30350.00,'food cost','2025-05-30','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-03 11:28:33','2025-10-03 11:30:14','uploads/tx_68dfb361e8d2a4.13149435_Food 5_Food_Food5_merged.pdf'),(56,'expense',7,47500.00,'Fuel cost (April and may)','2025-05-30','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-03 12:24:46','2025-10-03 12:24:46','uploads/tx_68dfc08eaa88f4.79413825_Fuel 04-06-2025_merged_compressed.pdf'),(57,'expense',10,800.00,'lebes masqacha','2025-03-30','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-07 07:28:36','2025-10-07 07:28:36','uploads/tx_68e4c124d44b35.66025202_Lebes maskaca.pdf'),(58,'expense',10,15000.00,'not retried money on wosen','2025-03-28','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-07 07:31:01','2025-10-07 07:31:01',NULL),(59,'expense',10,11000.00,'dawit invition','2025-05-02','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-07 07:32:18','2025-10-07 07:32:18',NULL),(60,'expense',8,1040.00,'utility material(socket and hose)','2025-05-12','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-07 07:40:37','2025-10-07 07:40:37','uploads/tx_68e4c3f587fd94.93498065_20251007_103604[1].jpg'),(61,'expense',10,1000.00,'parking money','2025-04-01','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-07 07:41:45','2025-10-07 07:41:45',NULL),(62,'expense',10,6000.00,'worker tip for different purpose','2025-05-30','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-07 07:43:36','2025-10-07 07:43:36',NULL),(63,'expense',10,6500.00,'yarid tip for selling and washing machine','2025-04-16','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-07 08:03:39','2025-10-07 08:03:39',NULL),(64,'expense',10,2730.00,'wifi cable   mentainance','2025-05-16','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-07 08:05:37','2025-10-07 08:05:37',NULL),(65,'expense',10,2000.00,'tip for table handling underground to up floor transfer money','2025-05-31','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-07 08:07:37','2025-10-07 08:07:37',NULL),(66,'expense',10,2500.00,'monitor wood(Wosen office)','2025-10-07','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-07 08:08:42','2025-10-07 08:08:42',NULL),(67,'expense',10,114925.00,'Home improvement(additional cost like tej,qibe and others)','2025-05-22','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-07 09:35:06','2025-10-07 09:35:06','uploads/tx_68e4deca05ccf7.90518102_Beg megza_merged.pdf'),(69,'expense',10,6000.00,'transport decor material and bed','2025-05-21','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-07 11:16:59','2025-10-07 11:16:59',NULL),(70,'expense',8,30000.00,'inside door','2025-06-07','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-07 11:18:11','2025-10-07 11:18:11','uploads/tx_68e4f6f3cb8d29.78416125_Door payment.pdf'),(71,'expense',5,900.00,'white plaster','2025-06-10','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-07 11:21:07','2025-10-07 11:21:07','uploads/tx_68e4f7a375ad64.23853538_White plaster 3.pdf'),(72,'expense',8,2770.00,'soft paper &Detergent','2025-06-17','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-08 08:41:37','2025-10-08 08:41:37',NULL),(73,'expense',8,3585.00,'Sanatory Equipment','2025-10-08','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-08 08:43:08','2025-10-08 08:43:08',NULL),(74,'expense',8,2105.00,'Sanitary Equipment','2025-06-17','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-08 08:44:30','2025-10-08 08:44:30',NULL),(75,'expense',7,48000.00,'car rent','2025-06-11','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-08 08:47:27','2025-10-08 08:47:27','uploads/tx_68e6251f2873f3.54671411_Car rent.pdf'),(76,'expense',8,6760.00,'1/2 payment of one sofa with transport','2025-09-28','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-08 09:19:00','2025-10-08 09:19:00','uploads/tx_68e62c840896a9.42774609_20251008_121305[1].jpg'),(77,'expense',5,3434.00,'sanitary materials(vim, detergent etc)','2025-10-07','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-08 09:34:27','2025-10-08 09:34:27','uploads/tx_68e63023460b78.77773749_Sanitory_7-10-2025[1].pdf'),(78,'expense',13,48000.00,'car rent','2025-09-15','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-09 06:24:02','2025-10-09 06:24:02','uploads/tx_68e75502ca7f78.83628847_Car_rent_sep[1].pdf'),(79,'expense',13,8000.00,'car rent pigmy','2025-09-25','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-09 06:31:43','2025-10-09 06:31:43','uploads/tx_68e756cf52f209.83249007_Car_rent_pagmie[1].pdf'),(81,'expense',7,20000.00,'September fuel cost','2025-09-30','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-09 11:57:09','2025-10-09 11:57:09','uploads/tx_68e7a31571ee49.86565373_pdf24_merged (1).pdf'),(82,'expense',12,6000.00,'10kg coffee','2025-10-09','6kg coffee use for office and 4kg buy for staff member','2025-10-09 12:00:30','2025-10-09 12:00:30','uploads/tx_68e7a3de61d228.19741636_Coffee[1].pdf'),(83,'expense',12,17850.00,'food cost','2025-10-06','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-09 12:09:39','2025-10-09 12:09:39','uploads/tx_68e7a603cd36c5.50143076_Teff_sep[1].pdf'),(84,'expense',12,3450.00,'water','2025-09-23','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-09 12:13:05','2025-10-09 12:13:05','uploads/tx_68e7a6d1e50097.52773373_Water_sep[1].pdf'),(85,'expense',12,10000.00,'food cost','2025-09-22','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-09 12:14:43','2025-10-09 12:14:43','uploads/tx_68e7a733f0b609.07458464_Food_12-1-2018[1].pdf'),(86,'expense',10,1300.00,'maereg  electric cost','2025-09-30','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-09 12:22:34','2025-10-09 12:22:34','uploads/tx_68e7a90ae05fa3.12520862_Eletric[1].pdf'),(87,'expense',5,20000.00,'September Safaricom wife payment','2025-10-08','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-09 12:25:29','2025-10-09 12:25:29','uploads/tx_68e7a9b9c45b93.14621755_Fikirte_(1)-1[1].pdf'),(88,'expense',9,5000.00,'door maintenance','2025-09-25','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-09 12:28:10','2025-10-09 12:28:10','uploads/tx_68e7aa5aeb6430.41698214_Door_sep[1].pdf'),(89,'expense',11,6000.00,'August salary','2025-09-30','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-09 12:36:36','2025-10-09 12:36:36','uploads/tx_68e7ac545650d2.33595768_Webit_salary[1].pdf'),(90,'expense',10,3076.00,'governmental service  payment(Kirkos sub city) hard copy, office payment etc','2025-10-02','','2025-10-09 12:41:28','2025-10-09 12:41:28',NULL),(91,'expense',8,800.00,'sealer','2025-10-09','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-09 12:54:34','2025-10-09 12:54:34','uploads/tx_68e7b08ace5997.93557482_Mahetem[1].pdf'),(92,'expense',12,12160.00,'September sheep cost','2025-10-09','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-09 13:03:18','2025-10-09 13:03:18','uploads/tx_68e7b2966bce79.02638422_Sep_beg_yetgezabet[1].pdf'),(93,'expense',12,700.00,'telba and fish','2025-10-06','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-10 09:14:27','2025-10-10 09:14:27',NULL),(94,'expense',10,15000.00,'initial deposit for new account at Ahudue Bank (cloth passbook)','2025-10-01','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-10 09:17:37','2025-10-10 09:17:37',NULL),(96,'expense',7,760.00,'sofa delivery pickup and tip','2025-10-10','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-10 09:26:45','2025-10-10 09:26:45','uploads/tx_68e8d155bc8eb3.12218397_Sofa_delivery[1].pdf'),(97,'expense',5,1500.00,'birhanu wifi','2025-10-10','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-13 06:20:26','2025-10-13 06:20:26','uploads/tx_68ec9a2a5c45c3.81614378_Birhanu_wifi_sep[1].pdf'),(98,'expense',7,1150.00,'ride transport','2025-10-12','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-13 06:35:46','2025-10-13 06:35:46','uploads/tx_68ec9dc2852fe4.38641336_Ride 1_Ride 2_merged.pdf'),(99,'expense',10,30000.00,'Birhanu borrowed a sum of money. For medication purpose','2025-09-19','Mule Wave Technology - Addis Ababa ETHIOPIA','2025-10-13 06:55:14','2025-10-13 06:55:14','uploads/tx_68eca252796436.88062257_Borrowing_money[1].pdf');
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','admin','admin@company.com','Administrator','2025-07-01 22:03:51');
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

-- Dump completed on 2025-10-15 14:40:59
