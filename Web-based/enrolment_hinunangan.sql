/*
SQLyog Ultimate v13.1.9 (64 bit)
MySQL - 8.0.42 : Database - enrolment_hinunangan
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
-- CREATE DATABASE /*!32312 IF NOT EXISTS*/`enrolment_hinunangan` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
CREATE DATABASE IF NOT EXISTS `enrolment_hinunangan` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE `enrolment_hinunangan`;

/*Table structure for table `course` */

DROP TABLE IF EXISTS `course`;

CREATE TABLE `course` (
  `id` varchar(45) DEFAULT NULL,
  `course_title` varchar(100) DEFAULT NULL,
  `accro` varchar(45) DEFAULT NULL,
  `lvl` varchar(45) DEFAULT NULL,
  `pri` int unsigned NOT NULL AUTO_INCREMENT,
  `Department` int DEFAULT NULL,
  `YearGranted` int DEFAULT NULL,
  `AuthorityNumber` varchar(150) DEFAULT NULL,
  `isActive` int DEFAULT '0' COMMENT '0-Active 1-InActive',
  `authorizedby` varchar(10) DEFAULT NULL,
  `InitCode` varchar(5) DEFAULT '',
  `showInAdmission` int DEFAULT '0',
  `Window` int DEFAULT '0',
  `ProgramCode` varchar(50) DEFAULT '',
  `old_AuthorityNumber` varchar(150) DEFAULT NULL,
  `yr_granted` int DEFAULT NULL,
  `LMS_category_idnumber` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`pri`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb3;

/*Data for the table `course` */

insert  into `course`(`id`,`course_title`,`accro`,`lvl`,`pri`,`Department`,`YearGranted`,`AuthorityNumber`,`isActive`,`authorizedby`,`InitCode`,`showInAdmission`,`Window`,`ProgramCode`,`old_AuthorityNumber`,`yr_granted`,`LMS_category_idnumber`) values 
('2013050153508','Bachelor of Science in Information Technology','BSIT','Under Graduate',1,6,2007,'Board of Regents Resolution No. 16',0,'BR','ICT',1,0,'464108',NULL,NULL,NULL);

/*Table structure for table `department` */

DROP TABLE IF EXISTS `department`;

CREATE TABLE `department` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Description` varchar(60) DEFAULT '',
  `DepartmentHead` int DEFAULT '0',
  `DepartmentName` varchar(100) DEFAULT '',
  `Designation` varchar(20) DEFAULT '',
  `Active` int DEFAULT '0',
  `AllowOnlineEnrolment` int DEFAULT '0',
  `RestrictIP` int DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1;

/*Data for the table `department` */

insert  into `department`(`id`,`Description`,`DepartmentHead`,`DepartmentName`,`Designation`,`Active`,`AllowOnlineEnrolment`,`RestrictIP`) values 
(1,'For Staff of SLSU CAFES Accessing Enrollment System',100,'Staff','Head, CISA',0,1,0),
(2,'Agricultural Sciences',671222028,'BSA','Program Chair',0,1,0),
(3,'AgriBusiness and Agricultural Entrepreneurship',671222028,'BSAB','Program Chair',0,1,0),
(4,'Environmental Sciences',671222028,'BSES','Program Chair',0,1,0),
(5,'College of Teacher Education',270916005,'BSEd','Program Chair',0,1,0),
(6,'Information Technology',671222028,'BSIT','Program Chair',0,1,0),
(7,'Dept of Technology and Livelihood Education',270916005,'TLE','Program Chair',0,1,0),
(8,'Academic Affairs, Research and Innovations',671222028,'AARi','Assistant Director',0,1,0),
(9,'Agricultural Technology',671222028,'BAT','Program Chair',0,1,0),
(10,'College of Agriculture Food and Environmental Sciences',671222028,'CAFES','Program Chair',0,1,0),
(11,'Clearance-BSEd',270916005,'Clearance-BSEd','Program Chair',0,1,0),
(12,'Clearance-BTLEd',29070207,'Clearance-BTLEd','Program Chair',0,1,0),
(13,'Clearance-BAT',141,'Clearance-BAT','Program Chair',0,1,0),
(14,'Clearance-BSIT',123456,'Clearance-BSIT','Program Chair',0,1,0),
(15,'Clearance-BSAB',94,'Clearance-BSAB','Program Chair',0,1,0),
(16,'Clearance-BSA',671222094,'Clearance-BSA','Program Chair',0,1,0),
(17,'Clearance-BSES',187,'Clearance-BSES','Program Chair',0,1,0);

/*Table structure for table `listgradmain` */

DROP TABLE IF EXISTS `listgradmain`;

CREATE TABLE `listgradmain` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `SchoolYear` int NOT NULL DEFAULT '0',
  `Semester` int NOT NULL DEFAULT '0',
  `BORDate` date NOT NULL,
  `BORNumber` int NOT NULL DEFAULT '0',
  `DateOfGraduation` date NOT NULL,
  `AddedBy` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `listgradmain` */

insert  into `listgradmain`(`id`,`SchoolYear`,`Semester`,`BORDate`,`BORNumber`,`DateOfGraduation`,`AddedBy`,`created_at`,`updated_at`) values 
(1,2024,2,'2025-04-29',30,'2025-06-13','jonathan',NULL,NULL),
(2,2018,2,'2019-04-20',3,'2019-05-29','jonathan',NULL,NULL);

/*Table structure for table `listgradsub` */

DROP TABLE IF EXISTS `listgradsub`;

CREATE TABLE `listgradsub` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `StudentNo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `ORNo` int NOT NULL DEFAULT '0',
  `ORDate` date DEFAULT NULL,
  `LatinHonor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `MainID` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `CertORNo` int DEFAULT '0',
  `CertORDate` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=261 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `listgradsub` */

insert  into `listgradsub`(`id`,`StudentNo`,`ORNo`,`ORDate`,`LatinHonor`,`MainID`,`created_at`,`updated_at`,`CertORNo`,`CertORDate`) values 
(1,'2210013-1',8708780,'2025-05-27','',1,'2025-05-28 11:56:40','2025-05-28 11:56:40',0,NULL);

/*Table structure for table `major` */

DROP TABLE IF EXISTS `major`;

CREATE TABLE `major` (
  `id` varchar(45) NOT NULL,
  `course_major` varchar(45) NOT NULL,
  `isActive` int DEFAULT '0',
  `CourseID` varchar(30) DEFAULT '',
  `showInAdmission` int DEFAULT '0',
  `uid` int NOT NULL AUTO_INCREMENT,
  `YearGranted` int DEFAULT NULL,
  `AuthorityNumber` varchar(150) DEFAULT NULL,
  `isspecialize` int DEFAULT '0',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb3;

/*Data for the table `major` */

insert  into `major`(`id`,`course_major`,`isActive`,`CourseID`,`showInAdmission`,`uid`,`YearGranted`,`AuthorityNumber`,`isspecialize`) values 
('2013050153553','Mathematics',0,'2013050153533',0,1,NULL,NULL,0),
('2013050153557','Biological Science',0,'',1,2,NULL,NULL,0),
('2013050153600','English',0,'2013050153533',1,3,NULL,NULL,0),
('20180530131425','Crop Science',0,'20180530131414',0,4,NULL,NULL,0),
('20180530131430','Animal Science',0,'20180530131414',0,5,NULL,NULL,0),
('20180601112046','Agri-Fishery Arts',0,'20180601112013',0,6,NULL,NULL,0),
('20180601112102','Information Communication Technology',0,'20180601112013',0,7,NULL,NULL,0),
('2018112892930','Science',0,'2013050153533',0,8,NULL,NULL,0),
('20190505113108','General',0,'20180530131414',0,9,NULL,NULL,0),
('0','',0,'',0,10,NULL,NULL,0),
('2024052990729','Programming',0,'2013050153508',0,11,NULL,NULL,0),
('20241128142734','Horticulture',0,'20180530131414',0,12,NULL,NULL,0);

/*Table structure for table `registration` */

DROP TABLE IF EXISTS `registration`;

CREATE TABLE `registration` (
  `RegistrationID` varchar(50) DEFAULT NULL,
  `StudentNo` varchar(45) DEFAULT NULL,
  `SchoolLevel` varchar(45) DEFAULT NULL,
  `Period` varchar(45) DEFAULT NULL,
  `SchoolYear` varchar(45) DEFAULT NULL,
  `Semester` int DEFAULT NULL,
  `StudentYear` varchar(45) DEFAULT NULL,
  `Section` varchar(45) DEFAULT NULL,
  `Course` varchar(45) DEFAULT NULL,
  `Major` varchar(45) DEFAULT NULL,
  `UnitCost` varchar(45) DEFAULT NULL,
  `Scholar` varchar(45) DEFAULT NULL,
  `Adjust` varchar(45) DEFAULT NULL,
  `ditCheck` varchar(30) DEFAULT NULL,
  `finalize` int DEFAULT NULL,
  `sumUnits` int DEFAULT NULL,
  `assessprint` int DEFAULT NULL,
  `StudentStatus` varchar(50) DEFAULT NULL,
  `cur_num` int DEFAULT NULL,
  `id` int NOT NULL AUTO_INCREMENT,
  `Source` int DEFAULT '0',
  `Note` varchar(100) DEFAULT '',
  `Balance` double(15,2) DEFAULT '0.00',
  `isCleared` int DEFAULT '1',
  `EnrollingOfficer` varchar(50) DEFAULT '',
  `DateEnrolled` date DEFAULT '2009-01-01',
  `DisapproveDeptReason` varchar(250) DEFAULT '',
  `TES` int DEFAULT '0',
  `TESDate` date DEFAULT NULL,
  `TESBy` varchar(50) NOT NULL DEFAULT '',
  `TESReason` varchar(250) DEFAULT '',
  `Cashier` varchar(50) DEFAULT '',
  `CashierDate` date DEFAULT NULL,
  `DateEncoded` date DEFAULT NULL,
  `EncodedBy` varchar(50) DEFAULT '',
  `DateValidated` date DEFAULT NULL,
  `ValidatedBy` varchar(50) DEFAULT '',
  `TimeEncoded` varchar(50) DEFAULT '0',
  `TimeEnrolled` varchar(50) DEFAULT '0',
  `TimeTES` varchar(50) DEFAULT '0',
  `TimeValidated` varchar(50) DEFAULT '0',
  `TimeCashier` varchar(15) DEFAULT '0',
  `WhereEnrolled` varchar(20) NOT NULL DEFAULT '',
  `isUnifastPaid` int DEFAULT '0',
  `ByPassAssess` int DEFAULT '0',
  `ByPassAutoAssess` int DEFAULT '0',
  `AdditionalIns` text,
  `forAR` double(15,2) DEFAULT NULL,
  PRIMARY KEY (`id`,`TESBy`),
  KEY `StudentNo_index` (`StudentNo`)
) ENGINE=InnoDB AUTO_INCREMENT=23813 DEFAULT CHARSET=utf8mb3;

/*Data for the table `registration` */

insert  into `registration`(`RegistrationID`,`StudentNo`,`SchoolLevel`,`Period`,`SchoolYear`,`Semester`,`StudentYear`,`Section`,`Course`,`Major`,`UnitCost`,`Scholar`,`Adjust`,`ditCheck`,`finalize`,`sumUnits`,`assessprint`,`StudentStatus`,`cur_num`,`id`,`Source`,`Note`,`Balance`,`isCleared`,`EnrollingOfficer`,`DateEnrolled`,`DisapproveDeptReason`,`TES`,`TESDate`,`TESBy`,`TESReason`,`Cashier`,`CashierDate`,`DateEncoded`,`EncodedBy`,`DateValidated`,`ValidatedBy`,`TimeEncoded`,`TimeEnrolled`,`TimeTES`,`TimeValidated`,`TimeCashier`,`WhereEnrolled`,`isUnifastPaid`,`ByPassAssess`,`ByPassAutoAssess`,`AdditionalIns`,`forAR`) values 
('202207211102382210013-1','2210013-1','Under Graduate',NULL,'2022',1,'1','B','2013050153508','0','150.00',NULL,NULL,NULL,1,NULL,NULL,'New',NULL,15363,0,'',0.00,1,'ruther','2022-07-21','',1,'2022-07-21','fhe_leslie','','',NULL,NULL,'','2022-07-21','Titeet1986','11:02:38','11:02:38','11:11:06','11:15:53','0','',1,0,0,NULL,NULL),
('202301091129262210013-1','2210013-1','Under Graduate',NULL,'2022',2,'1','B','2013050153508','0','150.00','',NULL,NULL,1,NULL,NULL,'Continuing',NULL,16822,0,'',0.00,1,'ruther','2023-01-09','',1,'2023-01-09','fhe_leslie','','',NULL,NULL,'','2023-01-10','amalia','11:29:26','11:29:26','11:35:43','08:49:12','0','',1,0,0,NULL,NULL),
('202307061025222210013-1','2210013-1','Under Graduate',NULL,'2023',1,'2','B','2013050153508','0','150.00',NULL,NULL,NULL,1,NULL,NULL,'Continuing',NULL,17995,0,'',0.00,1,'ruther','2023-07-06','',1,'2023-07-06','fhe_leslie','','',NULL,NULL,'','2023-07-06','Titeet1986','10:25:22','10:25:22','10:29:21','10:52:00','0','',0,0,0,NULL,NULL),
('202401081033142210013-1','2210013-1','Under Graduate',NULL,'2023',2,'2','B','2013050153508','0','150.00',NULL,NULL,NULL,1,NULL,NULL,'Continuing',NULL,19833,0,'',0.00,1,'ruther','2024-01-08','',1,'2024-01-08','fhe_leslie','','',NULL,NULL,'','2024-01-18','amalia','10:33:15','10:33:15','11:16:46','16:42:46','0','',0,0,0,NULL,NULL),
('202407230959292210013-1','2210013-1','Under Graduate',NULL,'2024',1,'3','B','2013050153508','0','150.00',NULL,NULL,NULL,1,NULL,NULL,'Continuing',2021,21588,0,'',0.00,1,'dept_mondani','2024-07-23','',1,'2024-07-23','fhe_leslie','','',NULL,NULL,'','2024-07-23','cjsinahon','09:59:29','09:59:29','10:04:21','10:08:04','0','',0,0,0,NULL,NULL),
('202501061753462210013-1','2210013-1','Under Graduate','','2024',2,'3','B','2013050153508','0','150.00','0',NULL,NULL,1,NULL,NULL,'Continuing',2021,22738,0,'',0.00,1,'ruther','2025-01-13','',1,'2025-01-14','leslieannecaberte','','Jest','2025-01-14','2025-01-06','2210013-1','2025-01-16','cjsinahon','17:53:46','10:52:54','13:15:56','10:18:13','13:23:27','online',0,0,0,NULL,NULL);

/*Table structure for table `students` */

DROP TABLE IF EXISTS `students`;

CREATE TABLE `students` (
  `StudentNo` varchar(20) NOT NULL,
  `LastName` varchar(45) DEFAULT NULL,
  `FirstName` varchar(45) DEFAULT NULL,
  `MiddleName` varchar(45) DEFAULT NULL,
  `Sex` varchar(5) DEFAULT NULL,
  `Course` varchar(45) DEFAULT NULL,
  `StudentYear` varchar(5) DEFAULT NULL,
  `Section` varchar(5) DEFAULT NULL,
  `BirthDate` varchar(45) DEFAULT NULL,
  `BirthPlace` text,
  `AdmissionDate` varchar(45) DEFAULT NULL,
  `major` varchar(45) DEFAULT NULL,
  `temp_id` varchar(45) DEFAULT NULL,
  `status` varchar(45) DEFAULT NULL,
  `elem_school` varchar(100) DEFAULT NULL,
  `elem_add` varchar(100) DEFAULT NULL,
  `elem_year` varchar(5) DEFAULT NULL,
  `high_school` varchar(100) DEFAULT NULL,
  `high_add` varchar(100) DEFAULT NULL,
  `high_year` varchar(5) DEFAULT NULL,
  `col_school` varchar(100) DEFAULT NULL,
  `col_add` varchar(100) DEFAULT NULL,
  `col_year` varchar(5) DEFAULT NULL,
  `father_name` varchar(45) DEFAULT NULL,
  `father_occu` varchar(45) DEFAULT NULL,
  `mother_name` varchar(45) DEFAULT NULL,
  `mother_occu` varchar(45) DEFAULT NULL,
  `emer_name` varchar(45) DEFAULT NULL,
  `emer_relation` varchar(45) DEFAULT NULL,
  `emer_zip` varchar(45) DEFAULT NULL,
  `emer_street` varchar(45) DEFAULT NULL,
  `emer_city` varchar(45) DEFAULT NULL,
  `emer_province` varchar(45) DEFAULT NULL,
  `nationality` varchar(45) DEFAULT NULL,
  `civil_status` varchar(45) DEFAULT NULL,
  `religion` varchar(70) DEFAULT '',
  `p_no` varchar(45) DEFAULT NULL,
  `p_zip` varchar(45) DEFAULT NULL,
  `p_municipality` varchar(45) DEFAULT NULL,
  `p_street` varchar(45) DEFAULT NULL,
  `p_province` varchar(45) DEFAULT NULL,
  `remarks` text,
  `notes` blob,
  `bor` varchar(200) DEFAULT NULL,
  `grad` varchar(50) DEFAULT NULL,
  `pass1` varchar(25) DEFAULT NULL,
  `datelastused` datetime DEFAULT NULL,
  `pass` varchar(25) DEFAULT NULL,
  `emer_contact` varchar(12) DEFAULT NULL,
  `sendSMS` varchar(15) DEFAULT 'sent',
  `txtBlast` varchar(15) DEFAULT 'sent',
  `ContactNo` varchar(15) DEFAULT NULL,
  `inetuser` varchar(25) DEFAULT NULL,
  `inetpass` varchar(25) DEFAULT NULL,
  `entrance` int NOT NULL DEFAULT '0',
  `exam_take_no` int NOT NULL DEFAULT '0',
  `CExam` varchar(50) DEFAULT '',
  `POD` varchar(50) DEFAULT '',
  `FOD` varchar(50) DEFAULT '',
  `CRemarks` varchar(10) DEFAULT '',
  `PRemarks` varchar(10) DEFAULT '',
  `FRemarks` varchar(10) DEFAULT '',
  `Welcome` varchar(15) DEFAULT '',
  `latitude` varchar(20) DEFAULT '',
  `longitude` varchar(20) DEFAULT '',
  `name_ext` varchar(10) DEFAULT NULL,
  `icafe` binary(1) DEFAULT '0',
  `anual_income` int DEFAULT '1',
  `Credential` varchar(150) DEFAULT '',
  `notes2` blob,
  `notuse` int DEFAULT '0' COMMENT '1-Inactive',
  `cur_num` int DEFAULT '0',
  `tmpContactNo` varchar(15) DEFAULT '',
  `tmpCode` varchar(15) DEFAULT '',
  `email` varchar(100) DEFAULT NULL,
  `AcademicYear` varchar(45) DEFAULT NULL,
  `Semester` int DEFAULT NULL,
  `mmaidenfname` varchar(100) DEFAULT NULL,
  `mmaidenmname` varchar(100) DEFAULT NULL,
  `mmaidenlname` varchar(100) DEFAULT NULL,
  `ffname` varchar(100) DEFAULT NULL,
  `fmname` varchar(100) DEFAULT NULL,
  `flname` varchar(100) DEFAULT NULL,
  `lrn` varchar(20) DEFAULT NULL,
  `dswd_id` varchar(30) DEFAULT NULL,
  `CardNo` varchar(50) DEFAULT '',
  `AccessGroup` varchar(15) DEFAULT '',
  `NSTPSerial` varchar(25) DEFAULT '',
  `dswdincom` varchar(50) DEFAULT '0',
  `Disability` varchar(50) DEFAULT '',
  `Picture` varchar(100) DEFAULT '',
  `NetCategory` int DEFAULT '0',
  `fb_account` text,
  `1NetCategory` int DEFAULT '0',
  `ContactNoChange` int DEFAULT '0',
  `ExamineeNumber` varchar(25) DEFAULT '',
  `Rebuild` int DEFAULT '1',
  `ResetPassword` varchar(250) DEFAULT '',
  `Consent` int DEFAULT '0',
  `DateSigned` date DEFAULT NULL,
  `ConsentMother` varchar(50) DEFAULT '',
  `ConsentFather` varchar(50) DEFAULT '',
  `ConsentGuardian` varchar(50) DEFAULT '',
  `ConsentOthers` varchar(50) DEFAULT '',
  `ConsentRelation` varchar(50) DEFAULT '',
  `AdmissionTime` varchar(15) DEFAULT '',
  `AdmittedBy` varchar(50) DEFAULT '',
  `TourHome` int DEFAULT '0',
  `TourErolment` int DEFAULT '0',
  `OSASEvaluatedBy` varchar(150) DEFAULT '',
  `OSASEvaluatedDate` date DEFAULT NULL,
  `OSASEvaluatedStart` varchar(15) DEFAULT '',
  `OSASEvaluatedEnd` varchar(15) DEFAULT '',
  `CompleteEForm` int DEFAULT '0',
  `ElemType` varchar(20) DEFAULT '',
  `SecondaryType` varchar(20) DEFAULT '',
  `SHSType` varchar(20) DEFAULT '',
  `CollegeType` varchar(20) DEFAULT '',
  `YearLevelAdviser` int DEFAULT '0',
  `bordate` date DEFAULT NULL,
  PRIMARY KEY (`StudentNo`),
  KEY `studentNo_index` (`StudentNo`),
  KEY `notuse` (`notuse`),
  KEY `CardNo` (`CardNo`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

/*Data for the table `students` */

insert  into `students`(`StudentNo`,`LastName`,`FirstName`,`MiddleName`,`Sex`,`Course`,`StudentYear`,`Section`,`BirthDate`,`BirthPlace`,`AdmissionDate`,`major`,`temp_id`,`status`,`elem_school`,`elem_add`,`elem_year`,`high_school`,`high_add`,`high_year`,`col_school`,`col_add`,`col_year`,`father_name`,`father_occu`,`mother_name`,`mother_occu`,`emer_name`,`emer_relation`,`emer_zip`,`emer_street`,`emer_city`,`emer_province`,`nationality`,`civil_status`,`religion`,`p_no`,`p_zip`,`p_municipality`,`p_street`,`p_province`,`remarks`,`notes`,`bor`,`grad`,`pass1`,`datelastused`,`pass`,`emer_contact`,`sendSMS`,`txtBlast`,`ContactNo`,`inetuser`,`inetpass`,`entrance`,`exam_take_no`,`CExam`,`POD`,`FOD`,`CRemarks`,`PRemarks`,`FRemarks`,`Welcome`,`latitude`,`longitude`,`name_ext`,`icafe`,`anual_income`,`Credential`,`notes2`,`notuse`,`cur_num`,`tmpContactNo`,`tmpCode`,`email`,`AcademicYear`,`Semester`,`mmaidenfname`,`mmaidenmname`,`mmaidenlname`,`ffname`,`fmname`,`flname`,`lrn`,`dswd_id`,`CardNo`,`AccessGroup`,`NSTPSerial`,`dswdincom`,`Disability`,`Picture`,`NetCategory`,`fb_account`,`1NetCategory`,`ContactNoChange`,`ExamineeNumber`,`Rebuild`,`ResetPassword`,`Consent`,`DateSigned`,`ConsentMother`,`ConsentFather`,`ConsentGuardian`,`ConsentOthers`,`ConsentRelation`,`AdmissionTime`,`AdmittedBy`,`TourHome`,`TourErolment`,`OSASEvaluatedBy`,`OSASEvaluatedDate`,`OSASEvaluatedStart`,`OSASEvaluatedEnd`,`CompleteEForm`,`ElemType`,`SecondaryType`,`SHSType`,`CollegeType`,`YearLevelAdviser`,`bordate`) values 
('2210013-1','BALBUENA','JHOVAN','PEREZ','M','2013050153508',NULL,NULL,'01 27 2004','Laguma, Silago, Southern Leyte','June 24, 2022','0','2210013',NULL,'Laguma Elementary School','Laguma, Silago, Southern Leyte','2016','','','','','','',NULL,'Farmer',NULL,'Housewife','Carlito A. Balbuena','Father','6607','Laguma','Silago','Southern Leyte','Filipino','Single','Roman Catholic',NULL,'6607','SILAGO','Laguma','SOUTHERN LEYTE','FOR OWWA Scholarship Application','','','',NULL,NULL,'405108','','sent','sent','09633316076',NULL,NULL,0,0,'','','','','','','','','',NULL,'0',1,'Form 138, Good Moral & PSA',NULL,0,2021,'','','jhovanbalbuena27@gmail.com',NULL,NULL,'Elizabeth','Labrador','Perez','Carlito','Austero','Balbuena','122056090007','','','','','36000','','e0a58871ade0fb38526316a577336b40fcd18f62.jpg',2,NULL,0,0,'',1,'',1,'2022-08-20','Elizabeth Perez','Carlito Balbuena','','Carlito A. Balbuena','Father','10:19:46','beverlyjane',0,0,'Nova Marie O. Maranguit','2022-06-24','09:02','10:05',0,'','','','',-1,NULL);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
