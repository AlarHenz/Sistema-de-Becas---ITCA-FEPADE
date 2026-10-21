/*
SQLyog Ultimate v13.1.1 (32 bit)
MySQL - 10.4.32-MariaDB : Database - sistemaglobal5.1
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`sistemaglobal5.1` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `sistemaglobal5.1`;

/*Table structure for table `alumno` */

DROP TABLE IF EXISTS `alumno`;

CREATE TABLE `alumno` (
  `idalumno` int(11) NOT NULL AUTO_INCREMENT,
  `carnet` varchar(6) NOT NULL,
  `nombre` varchar(20) NOT NULL,
  `apellido` varchar(20) NOT NULL,
  `telefono` varchar(9) NOT NULL,
  `sexo` varchar(1) NOT NULL,
  `foto` varchar(50) NOT NULL,
  `email` varchar(150) NOT NULL,
  `estadoAlumno` int(2) NOT NULL,
  `yearingreso` int(4) DEFAULT NULL,
  `beca` int(2) NOT NULL,
  `tipobeca` varchar(250) NOT NULL,
  `id_carrera` int(11) NOT NULL,
  PRIMARY KEY (`idalumno`)
) ENGINE=MyISAM AUTO_INCREMENT=2719 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `alumno` */

insert  into `alumno`(`idalumno`,`carnet`,`nombre`,`apellido`,`telefono`,`sexo`,`foto`,`email`,`estadoAlumno`,`yearingreso`,`beca`,`tipobeca`,`id_carrera`) values 
(2235,'068524','Bryan Eduardo','Vega Aguilar','','M','foto/068524.jpg','bryan.vega24@itca.edu.sv',1,2024,1,'BANCO CENTRAL DE RESERVA',9),
(2251,'073624','Bayron Ronaldo','Zeceña Moran','','M','foto/073624.jpg','bayron.zecena24@itca.edu.sv',1,2024,1,'Club Rotario Santa Ana',9);

/*Table structure for table `aula` */

DROP TABLE IF EXISTS `aula`;

CREATE TABLE `aula` (
  `id_aula` int(11) NOT NULL AUTO_INCREMENT,
  `aula` varchar(25) DEFAULT NULL,
  `tipoa` varchar(30) DEFAULT NULL COMMENT 'Ej: Laboratorio, Oficina, Aula',
  `descripcion` varchar(30) DEFAULT NULL,
  `ubicacion` varchar(30) DEFAULT NULL COMMENT 'Edificio, piso, etc',
  `estado` int(1) DEFAULT 1,
  PRIMARY KEY (`id_aula`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

/*Data for the table `aula` */

insert  into `aula`(`id_aula`,`aula`,`tipoa`,`descripcion`,`ubicacion`,`estado`) values 
(1,'LAB-101','Laboratorio','Lab. de Computación','Edificio A, Piso 1',1),
(2,'COORD-201','Oficina','Oficina Coordinador','Edificio B, Piso 2',1),
(3,'BODEGA-01','Bodega','Bodega General','Edificio C',1),
(4,'OFICINA-301','Oficina','Oficina Profesores','Edificio A, Piso 3',1);

/*Table structure for table `carrera` */

DROP TABLE IF EXISTS `carrera`;

CREATE TABLE `carrera` (
  `idcarrera` int(11) NOT NULL AUTO_INCREMENT,
  `carrera` varchar(200) DEFAULT NULL,
  `iddepto` int(11) DEFAULT NULL,
  `corto` varchar(60) DEFAULT NULL,
  `estadocarrera` varchar(10) DEFAULT 'A',
  `duracion` int(11) DEFAULT NULL,
  `turno_estudio` varchar(50) DEFAULT 'dia',
  `costo_matricula` decimal(10,2) DEFAULT NULL,
  `matriculas` int(11) DEFAULT NULL,
  `cuota_mensual` decimal(10,2) DEFAULT NULL,
  `cuotas_anuales` int(11) DEFAULT NULL,
  `costo_laboratorios` decimal(10,2) DEFAULT NULL,
  `otros_costos` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`idcarrera`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

/*Data for the table `carrera` */

insert  into `carrera`(`idcarrera`,`carrera`,`iddepto`,`corto`,`estadocarrera`,`duracion`,`turno_estudio`,`costo_matricula`,`matriculas`,`cuota_mensual`,`cuotas_anuales`,`costo_laboratorios`,`otros_costos`) values 
(3,'Técnico en Ingeniería de Desarrollo de Software',1,NULL,'I',2,'diurno',NULL,NULL,NULL,NULL,100.00,8.00),
(5,'Técnico en Gestión Tecnológica del Patrimonio Cultural',5,NULL,'A',2,'diurno',NULL,NULL,NULL,NULL,100.00,8.00),
(6,'Grupo OIM',7,NULL,'A',2,'diurno',NULL,NULL,NULL,NULL,100.00,8.00),
(7,'Técnico en Hardware Computacional',2,NULL,'A',2,'diurno',NULL,NULL,NULL,NULL,100.00,8.00),
(9,'Técnico en Ingeniería de Desarrollo de Software',1,NULL,'A',2,'diurno',NULL,NULL,NULL,NULL,100.00,8.00),
(11,'Técnico en Ingeniería Eléctrica',2,NULL,'A',2,'diurno',NULL,NULL,NULL,NULL,100.00,8.00);

/*Table structure for table `departamento` */

DROP TABLE IF EXISTS `departamento`;

CREATE TABLE `departamento` (
  `id_departamento` int(11) NOT NULL AUTO_INCREMENT,
  `departamento` varchar(200) DEFAULT NULL,
  `estado` int(1) NOT NULL DEFAULT 1 COMMENT '1=activo 0=inactivo',
  PRIMARY KEY (`id_departamento`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AVG_ROW_LENGTH=24 ROW_FORMAT=DYNAMIC;

/*Data for the table `departamento` */

insert  into `departamento`(`id_departamento`,`departamento`,`estado`) values 
(1,'Computación',1),
(2,'Eléctrica',1),
(3,'Area básica',1),
(4,'Administración',1),
(5,'Patrimonio',1),
(7,'Servicio Desarrollo prof.',1),
(8,'OIM',1);

/*Table structure for table `docente` */

DROP TABLE IF EXISTS `docente`;

CREATE TABLE `docente` (
  `id_docente` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `carnet` char(20) NOT NULL,
  `nom_usuario` char(30) NOT NULL,
  `ape_usuario` char(30) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `estado` char(20) NOT NULL,
  `id_depto` int(11) DEFAULT NULL,
  `esadminpermisos` int(1) NOT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_docente`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `docente` */

insert  into `docente`(`id_docente`,`carnet`,`nom_usuario`,`ape_usuario`,`email`,`estado`,`id_depto`,`esadminpermisos`,`tipo`) values 
(1,'DOC001','Carlos','Martínez','melvin.bran24@itca.edu.sv','Activo',5,3,'1'),
(2,'DOC002','María','González','melvin.bran24@itca.edu.sv','Activo',2,2,'2'),
(3,'DIR001','Roberto','Hernández',NULL,'Activo',2,1,'3'),
(4,'COORD001','Ana','López','melvin.bran24@itca.edu.sv','Activo',2,2,'4'),
(5,'BIEN001','Luis','Ramírez','melvin.bran24@itca.edu.sv','Activo',4,3,'5'),
(6,'DOC003','Patricia','Torres',NULL,'Activo',2,4,'6');

/*Table structure for table `grupo` */

DROP TABLE IF EXISTS `grupo`;

CREATE TABLE `grupo` (
  `id_grupo` int(10) NOT NULL AUTO_INCREMENT,
  `grupo` varchar(20) NOT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `idcarrera` int(11) DEFAULT NULL,
  `year` int(4) DEFAULT NULL,
  `ciclo` varchar(6) DEFAULT NULL,
  `estado` varchar(15) DEFAULT 'Habilitado',
  PRIMARY KEY (`id_grupo`)
) ENGINE=MyISAM AUTO_INCREMENT=434 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AVG_ROW_LENGTH=20 ROW_FORMAT=DYNAMIC;

/*Data for the table `grupo` */

insert  into `grupo`(`id_grupo`,`grupo`,`tipo`,`idcarrera`,`year`,`ciclo`,`estado`) values 
(1,'HW11','U',7,2023,'I','Habilitado'),
(2,'HW11','A',7,2023,'I','Habilitado'),
(3,'HW11','B',7,2023,'I','Habilitado'),
(4,'HW31','U',7,2023,'III','Habilitado'),
(5,'HW31','A',7,2023,'III','Habilitado'),
(6,'SOFT31','U',9,2023,'III','Habilitado'),
(7,'SOFT31','A',9,2023,'III','Habilitado'),
(8,'SOFT31','B',9,2023,'III','Habilitado'),
(9,'SOFT32','U',9,2023,'III','Habilitado'),
(10,'SOFT32','A',9,2023,'III','Habilitado'),
(11,'SOFT32','B',9,2023,'III','Habilitado'),
(12,'SOFT11','U',9,2023,'I','Habilitado'),
(13,'SOFT11','A',9,2023,'I','Habilitado'),
(14,'SOFT11','B',9,2023,'I','Habilitado'),
(15,'SOFT12','U',9,2023,'I','Habilitado'),
(16,'SOFT12','A',9,2023,'I','Habilitado'),
(17,'SOFT12','B',9,2023,'I','Habilitado'),
(18,'ELEC11','U',3,2023,'I','Habilitado'),
(19,'ELEC11','A',3,2023,'I','Habilitado'),
(20,'ELEC11','B',3,2023,'I','Habilitado'),
(21,'ELEC12','U',3,2023,'I','Habilitado'),
(22,'ELEC12','A',3,2023,'I','Habilitado'),
(23,'ELEC12','B',3,2023,'I','Habilitado'),
(24,'ELEC13','U',3,2023,'I','Habilitado'),
(25,'ELEC13','A',3,2023,'I','Habilitado'),
(26,'ELEC13','B',3,2023,'I','Habilitado'),
(27,'ELEC31','U',3,2023,'III','Habilitado'),
(28,'ELEC31','A',3,2023,'III','Habilitado'),
(29,'ELEC31','B',3,2023,'III','Habilitado'),
(30,'ELEC32','U',3,2023,'III','Habilitado'),
(31,'ELEC32','A',3,2023,'III','Habilitado'),
(32,'ELEC32','B',3,2023,'III','Habilitado'),
(33,'SOFT31','C',9,2023,'III','Habilitado'),
(34,'SOFT13','U',9,2023,'I','Habilitado'),
(35,'SOFT13','A',9,2023,'I','Habilitado'),
(36,'SOFT13','B',9,2023,'I','Habilitado'),
(37,'ELEC14','U',3,2023,'I','Habilitado'),
(38,'ELEC14','A',3,2023,'I','Habilitado'),
(39,'ELEC14','B',3,2023,'I','Habilitado'),
(114,'PAT11','C',5,2023,'I','Habilitado'),
(113,'PAT11','B',5,2023,'I','Habilitado'),
(112,'PAT11','A',5,2023,'I','Habilitado'),
(110,'PAT31','B',5,2023,'I','Habilitado'),
(111,'PAT11','U',5,2023,'I','Habilitado'),
(108,'PAT31','U',5,2023,'I','Habilitado'),
(109,'PAT31','A',5,2023,'I','Habilitado'),
(49,'Sist41','U',2,2023,'IV','Habilitado'),
(50,'Sist41','A',2,2023,'IV','Habilitado'),
(51,'Sist41','B',2,2023,'IV','Habilitado'),
(52,'Sist41','C',2,2023,'IV','Habilitado'),
(53,'HW41','U',7,2023,'IV','Habilitado'),
(54,'HW41','A',7,2023,'IV','Habilitado'),
(55,'HW21','U',7,2023,'II','Habilitado'),
(56,'HW21','A',7,2023,'II','Habilitado'),
(57,'HW21','B',7,2023,'II','Habilitado'),
(58,'ELEC21','U',3,2023,'II','Habilitado'),
(59,'ELEC21','A',3,2023,'II','Habilitado'),
(60,'ELEC21','B',3,2023,'II','Habilitado'),
(61,'ELEC21','C',3,2023,'II','Habilitado'),
(62,'ELEC22','U',3,2023,'II','Habilitado'),
(63,'ELEC22','A',3,2023,'II','Habilitado'),
(64,'ELEC22','B',3,2023,'II','Habilitado'),
(65,'ELEC22','C',3,2023,'II','Habilitado'),
(66,'ELEC23','U',3,2023,'II','Habilitado'),
(67,'ELEC23','A',3,2023,'II','Habilitado'),
(68,'ELEC23','B',3,2023,'II','Habilitado'),
(69,'ELEC41','U',3,2023,'IV','Habilitado'),
(70,'ELEC41','A',3,2023,'IV','Habilitado'),
(71,'ELEC41','B',3,2023,'IV','Habilitado'),
(72,'ELEC42','U',3,2023,'IV','Habilitado'),
(73,'ELEC42','A',3,2023,'IV','Habilitado'),
(74,'ELEC42','B',3,2023,'IV','Habilitado'),
(75,'SOFT21','U',9,2023,'II','Habilitado'),
(76,'SOFT21','A',9,2023,'II','Habilitado'),
(77,'SOFT21','B',9,2023,'II','Habilitado'),
(78,'SOFT21','C',9,2023,'II','Habilitado'),
(79,'SOFT22','U',9,2023,'II','Habilitado'),
(80,'SOFT22','A',9,2023,'II','Habilitado'),
(81,'SOFT22','B',9,2023,'II','Habilitado'),
(82,'SOFT22','C',9,2023,'II','Habilitado'),
(83,'OIM','U',6,2023,'III','Habilitado'),
(116,'HW31','B',7,2023,'III','Habilitado'),
(115,'PAT32','U',5,2023,'I','Habilitado'),
(91,'insaforp','U',6,2023,'II','Habilitado'),
(117,'PAT31','U',5,2023,'III','Habilitado'),
(118,'PAT31','A',5,2023,'III','Habilitado'),
(119,'PAT31','B',5,2023,'III','Habilitado'),
(120,'PAT32','U',5,2023,'III','Habilitado'),
(121,'ELEC33','U',3,2023,'III','Habilitado'),
(122,'ELEC33','A',3,2023,'III','Habilitado'),
(123,'ELEC33','B',3,2023,'III','Habilitado'),
(124,'ELEC24','U',3,2023,'II','Habilitado'),
(125,'ELEC24','A',3,2023,'II','Habilitado'),
(126,'ELEC24','B',3,2023,'II','Habilitado'),
(127,'HW41','B',7,2023,'IV','Habilitado'),
(128,'SOFT41','U',9,2023,'IV','Habilitado'),
(129,'SOFT41','A',9,2023,'IV','Habilitado'),
(130,'SOFT41','B',9,2023,'IV','Habilitado'),
(131,'SOFT42','U',9,2023,'IV','Habilitado'),
(132,'SOFT42','A',9,2023,'IV','Habilitado'),
(133,'SOFT42','B',9,2023,'IV','Habilitado'),
(134,'SOFT23','U',9,2023,'II','Habilitado'),
(135,'SOFT23','A',9,2023,'II','Habilitado'),
(136,'SOFT23','B',9,2023,'II','Habilitado'),
(137,'PAT21','U',5,2023,'II','Habilitado'),
(138,'PAT21','A',5,2023,'II','Habilitado'),
(139,'PAT21','B',5,2023,'II','Habilitado'),
(140,'PAT41','U',5,2023,'II','Habilitado'),
(141,'PAT41','A',5,2023,'II','Habilitado'),
(142,'PAT41','B',5,2023,'II','Habilitado'),
(143,'PAT42','U',5,2023,'II','Habilitado'),
(144,'PAT41','U',5,2023,'IV','Habilitado'),
(145,'PAT41','A',5,2023,'IV','Habilitado'),
(146,'PAT41','B',5,2023,'IV','Habilitado'),
(147,'PAT42','U',5,2023,'IV','Habilitado'),
(148,'ELEC43','U',3,2023,'IV','Habilitado'),
(149,'ELEC43','A',3,2023,'IV','Habilitado'),
(150,'ELEC43','B',3,2023,'IV','Habilitado'),
(151,'SOFT42','C',9,2023,'IV','Habilitado'),
(152,'SIST42','U',2,2023,'IV','Habilitado'),
(153,'SIST42','A',2,2023,'IV','Habilitado'),
(154,'SIST42','U',9,2023,'IV','Habilitado'),
(155,'SIST42','A',9,2023,'IV','Habilitado'),
(156,'HW11','U',7,2024,'I','Habilitado'),
(157,'HW11','A',7,2024,'I','Habilitado'),
(158,'HW11','B',7,2024,'I','Habilitado'),
(159,'HW31','U',7,2024,'III','Habilitado'),
(160,'HW31','A',7,2024,'III','Habilitado'),
(161,'HW31','B',7,2024,'III','Habilitado'),
(162,'SOFT11','U',9,2024,'I','Habilitado'),
(163,'SOFT11','A',9,2024,'I','Habilitado'),
(164,'SOFT11','B',9,2024,'I','Habilitado'),
(165,'SOFT12','U',9,2024,'I','Habilitado'),
(166,'SOFT12','A',9,2024,'I','Habilitado'),
(167,'SOFT12','B',9,2024,'I','Habilitado'),
(168,'SOFT13','U',9,2024,'I','Habilitado'),
(169,'SOFT13','A',9,2024,'I','Habilitado'),
(170,'SOFT13','B',9,2024,'I','Habilitado'),
(171,'ELEC11','U',3,2024,'I','Habilitado'),
(172,'ELEC11','A',3,2024,'I','Habilitado'),
(173,'ELEC11','B',3,2024,'I','Habilitado'),
(174,'ELEC12','U',3,2024,'I','Habilitado'),
(175,'ELEC12','A',3,2024,'I','Habilitado'),
(176,'ELEC12','B',3,2024,'I','Habilitado'),
(177,'ELEC13','U',3,2024,'I','Habilitado'),
(178,'ELEC13','A',3,2024,'I','Habilitado'),
(179,'ELEC13','B',3,2024,'I','Habilitado'),
(180,'ELEC14','U',3,2024,'I','Habilitado'),
(181,'ELEC14','A',3,2024,'I','Habilitado'),
(182,'ELEC14','B',3,2024,'I','Habilitado'),
(183,'Soft31','U',9,2024,'III','Habilitado'),
(184,'Soft31','A',9,2024,'III','Habilitado'),
(185,'Soft31','B',9,2024,'III','Habilitado'),
(186,'Soft31','C',9,2024,'III','Habilitado'),
(187,'Soft32','U',9,2024,'III','Habilitado'),
(188,'Soft32','A',9,2024,'III','Habilitado'),
(189,'Soft32','B',9,2024,'III','Habilitado'),
(190,'Elec31','U',3,2024,'III','Habilitado'),
(191,'Elec31','A',3,2024,'III','Habilitado'),
(192,'Elec31','B',3,2024,'III','Habilitado'),
(193,'Elec32','U',3,2024,'III','Habilitado'),
(194,'Elec32','A',3,2024,'III','Habilitado'),
(195,'Elec32','B',3,2024,'III','Habilitado'),
(196,'Elec33','U',3,2024,'III','Habilitado'),
(197,'Elec33','A',3,2024,'III','Habilitado'),
(198,'Elec33','B',3,2024,'III','Habilitado'),
(199,'ELEC21','U',3,2024,'II','Habilitado'),
(200,'ELEC21','A',3,2024,'II','Habilitado'),
(201,'ELEC21','B',3,2024,'II','Habilitado'),
(202,'ELEC22','U',3,2024,'II','Habilitado'),
(203,'ELEC22','A',3,2024,'II','Habilitado'),
(204,'ELEC22','B',3,2024,'II','Habilitado'),
(205,'ELEC23','U',3,2024,'II','Habilitado'),
(206,'ELEC23','A',3,2024,'II','Habilitado'),
(207,'ELEC23','B',3,2024,'II','Habilitado'),
(208,'ELEC41','U',3,2024,'IV','Habilitado'),
(209,'ELEC41','A',3,2024,'IV','Habilitado'),
(210,'ELEC41','B',3,2024,'IV','Habilitado'),
(211,'ELEC42','U',3,2024,'IV','Habilitado'),
(212,'ELEC42','A',3,2024,'IV','Habilitado'),
(213,'ELEC42','B',3,2024,'IV','Habilitado'),
(214,'ELEC43','U',3,2024,'IV','Habilitado'),
(215,'ELEC43','A',3,2024,'IV','Habilitado'),
(216,'ELEC43','B',3,2024,'IV','Habilitado'),
(217,'PAT 21','U',5,2024,'II','Habilitado'),
(218,'PAT 21','A',5,2024,'II','Habilitado'),
(219,'PAT 21','B',5,2024,'II','Habilitado'),
(220,'PAT 41','U',5,2024,'IV','Habilitado'),
(221,'PAT 41','A',5,2024,'IV','Habilitado'),
(222,'PAT 41','B',5,2024,'IV','Habilitado'),
(223,'HW21','U',7,2024,'II','Habilitado'),
(224,'HW21','A',7,2024,'II','Habilitado'),
(225,'HW21','B',7,2024,'II','Habilitado'),
(226,'HW41','U',7,2024,'IV','Habilitado'),
(227,'HW41','A',7,2024,'IV','Habilitado'),
(228,'HW41','B',7,2024,'IV','Habilitado'),
(229,'SOFT41','U',9,2024,'IV','Habilitado'),
(230,'SOFT41','A',9,2024,'IV','Habilitado'),
(231,'SOFT41','B',9,2024,'IV','Habilitado'),
(232,'SOFT41','C',9,2024,'IV','Habilitado'),
(233,'SOFT42','U',9,2024,'IV','Habilitado'),
(234,'SOFT42','A',9,2024,'IV','Habilitado'),
(235,'SOFT42','B',9,2024,'IV','Habilitado'),
(236,'SOFT21','U',9,2024,'II','Habilitado'),
(237,'SOFT21','A',9,2024,'II','Habilitado'),
(238,'SOFT21','B',9,2024,'II','Habilitado'),
(239,'SOFT22','U',9,2024,'II','Habilitado'),
(240,'SOFT22','A',9,2024,'II','Habilitado'),
(241,'SOFT22','B',9,2024,'II','Habilitado'),
(242,'ELEC23','U',9,2024,'II','Habilitado'),
(243,'ELEC23','A',9,2024,'II','Habilitado'),
(244,'ELEC23','B',9,2024,'II','Habilitado'),
(245,'SOFT23','U',9,2024,'II','Habilitado'),
(246,'SOFT23','A',9,2024,'II','Habilitado'),
(247,'SOFT23','B',9,2024,'II','Habilitado'),
(330,'SOFT12','B',3,2025,'I','Habilitado'),
(329,'SOFT12','A',3,2025,'I','Habilitado'),
(328,'SOFT12','U',3,2025,'I','Habilitado'),
(327,'SOFT12','B',3,2025,'I','Habilitado'),
(326,'SOFT12','A',3,2025,'I','Habilitado'),
(325,'SOFT12','U',3,2025,'I','Habilitado'),
(324,'SOFT11','B',3,2025,'I','Habilitado'),
(323,'SOFT11','A',3,2025,'I','Habilitado'),
(322,'SOFT11','U',3,2025,'I','Habilitado'),
(321,'SOFT32','B',3,2025,'III','Habilitado'),
(320,'SOFT32','A',3,2025,'III','Habilitado'),
(319,'SOFT32','U',3,2025,'III','Habilitado'),
(318,'SOFT31','C',3,2025,'III','Habilitado'),
(317,'SOFT31','B',3,2025,'III','Habilitado'),
(316,'SOFT31','A',3,2025,'III','Habilitado'),
(315,'SOFT31','U',3,2025,'III','Habilitado'),
(314,'HW31','B',7,2025,'III','Habilitado'),
(313,'HW31','A',7,2025,'III','Habilitado'),
(312,'HW31','U',7,2025,'III','Habilitado'),
(311,'HW11','B',7,2025,'I','Habilitado'),
(310,'HW11','A',7,2025,'I','Habilitado'),
(309,'HW11','U',7,2025,'I','Habilitado'),
(308,'ELEC32','B',11,2025,'III','Habilitado'),
(307,'ELEC32','A',11,2025,'III','Habilitado'),
(306,'ELEC32','U',11,2025,'III','Habilitado'),
(305,'ELEC31','C',11,2025,'III','Habilitado'),
(304,'ELEC31','B',11,2025,'III','Habilitado'),
(303,'ELEC31','A',11,2025,'III','Habilitado'),
(302,'ELEC31','U',11,2025,'III','Habilitado'),
(301,'ELEC13','B',3,2025,'I','Habilitado'),
(300,'ELEC13','A',3,2025,'I','Habilitado'),
(299,'ELEC13','U',3,2025,'I','Habilitado'),
(298,'ELEC12','B',11,2025,'I','Habilitado'),
(297,'ELEC12','A',11,2025,'I','Habilitado'),
(296,'ELEC12','U',11,2025,'I','Habilitado'),
(295,'ELEC11','B',11,2025,'I','Habilitado'),
(294,'ELEC11','A',11,2025,'I','Habilitado'),
(293,'ELEC11','U',11,2025,'I','Habilitado'),
(331,'SOFT11','U',3,2025,'I','Habilitado'),
(332,'SOFT11','A',3,2025,'I','Habilitado'),
(333,'SOFT11','B',3,2025,'I','Habilitado'),
(334,'SOFT11','U',9,2025,'I','Habilitado'),
(335,'SOFT11','A',9,2025,'I','Habilitado'),
(336,'SOFT11','B',9,2025,'I','Habilitado'),
(337,'SOFT12','U',9,2025,'I','Habilitado'),
(338,'SOFT12','A',9,2025,'I','Habilitado'),
(339,'SOFT12','B',9,2025,'I','Habilitado'),
(432,'SOFT42','C',9,2025,'IV','Habilitado'),
(431,'SOFT42','B',9,2025,'IV','Habilitado'),
(430,'SOFT42','A',9,2025,'IV','Habilitado'),
(343,'SOFT31','U',9,2025,'I','Habilitado'),
(344,'SOFT31','A',9,2025,'I','Habilitado'),
(345,'SOFT31','B',9,2025,'I','Habilitado'),
(346,'SOFT31','C',9,2025,'I','Habilitado'),
(347,'SOFT32','U',9,2025,'I','Habilitado'),
(348,'SOFT32','A',9,2025,'I','Habilitado'),
(349,'SOFT32','B',9,2025,'I','Habilitado'),
(350,'SOFT31','U',9,2025,'III','Habilitado'),
(351,'SOFT31','A',9,2025,'III','Habilitado'),
(352,'SOFT31','B',9,2025,'III','Habilitado'),
(353,'SOFT31','C',9,2025,'III','Habilitado'),
(354,'SOFT32','U',9,2025,'III','Habilitado'),
(355,'SOFT32','A',9,2025,'III','Habilitado'),
(356,'SOFT32','B',9,2025,'III','Habilitado'),
(357,'ELEC13','U',11,2025,'I','Habilitado'),
(358,'ELEC13','A',11,2025,'I','Habilitado'),
(359,'ELEC13','B',11,2025,'I','Habilitado'),
(360,'PAT31','U',5,2025,'III','Habilitado'),
(361,'PAT31','A',5,2025,'III','Habilitado'),
(429,'SOFT42','U',9,2025,'IV','Habilitado'),
(428,'SOFT41','B',9,2025,'IV','Habilitado'),
(427,'SOFT41','A',9,2025,'IV','Habilitado'),
(426,'SOFT41','U',9,2025,'IV','Habilitado'),
(425,'HW41','A',7,2025,'IV','Habilitado'),
(424,'HW41','U',7,2025,'IV','Habilitado'),
(423,'ELEC42','B',11,2025,'IV','Habilitado'),
(422,'ELEC42','A',11,2025,'IV','Habilitado'),
(421,'ELEC42','U',11,2025,'IV','Habilitado'),
(420,'ELEC41','B',11,2025,'IV','Habilitado'),
(419,'ELEC41','A',11,2025,'IV','Habilitado'),
(418,'ELEC41','U',11,2025,'IV','Habilitado'),
(417,'SOFT22','B',9,2025,'II','Habilitado'),
(416,'SOFT22','A',9,2025,'II','Habilitado'),
(415,'SOFT22','U',9,2025,'II','Habilitado'),
(414,'SOFT21','B',9,2025,'II','Habilitado'),
(413,'SOFT21','A',9,2025,'II','Habilitado'),
(412,'SOFT21','U',9,2025,'II','Habilitado'),
(411,'HW21','A',7,2025,'II','Habilitado'),
(410,'HW21','U',7,2025,'II','Habilitado'),
(409,'ELEC23','B',11,2025,'II','Habilitado'),
(408,'ELEC23','A',11,2025,'II','Habilitado'),
(407,'ELEC23','U',11,2025,'II','Habilitado'),
(406,'ELEC22','B',11,2025,'II','Habilitado'),
(405,'ELEC22','A',11,2025,'II','Habilitado'),
(404,'ELEC22','U',11,2025,'II','Habilitado'),
(403,'ELEC21','B',11,2025,'II','Habilitado'),
(402,'ELEC21','A',11,2025,'II','Habilitado'),
(401,'ELEC21','U',11,2025,'II','Habilitado'),
(433,'PAT41','U',5,2025,'IV','Habilitado');

/*Table structure for table `materia` */

DROP TABLE IF EXISTS `materia`;

CREATE TABLE `materia` (
  `id_materia` int(10) NOT NULL AUTO_INCREMENT,
  `materia` varchar(200) NOT NULL,
  `id_departamento` int(60) DEFAULT NULL,
  `curricula` varchar(4) DEFAULT NULL,
  `idcarrera` int(11) DEFAULT NULL,
  `ciclo` varchar(6) DEFAULT NULL COMMENT 'ya no se usa',
  `uv` varchar(2) DEFAULT NULL,
  `ciclomateria` varchar(6) DEFAULT NULL,
  `codigomateria` varchar(15) DEFAULT NULL,
  `horasteoricas` int(4) DEFAULT NULL,
  `horaspracticas` int(4) DEFAULT NULL,
  `correlativo` int(4) DEFAULT NULL,
  `ciclonumero` int(2) DEFAULT NULL,
  `estado` int(1) DEFAULT 1 COMMENT 'estado=1 materia activada',
  `esmateriabasica` int(1) DEFAULT 1 COMMENT '0=si_es_basica  1=no_es_basica',
  `nombre_corto` varchar(150) DEFAULT 'nd' COMMENT 'nd=no definido',
  PRIMARY KEY (`id_materia`)
) ENGINE=MyISAM AUTO_INCREMENT=473 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AVG_ROW_LENGTH=38 ROW_FORMAT=DYNAMIC;

/*Data for the table `materia` */

insert  into `materia`(`id_materia`,`materia`,`id_departamento`,`curricula`,`idcarrera`,`ciclo`,`uv`,`ciclomateria`,`codigomateria`,`horasteoricas`,`horaspracticas`,`correlativo`,`ciclonumero`,`estado`,`esmateriabasica`,`nombre_corto`) values 
(1,'ConfiguraciÃ³n de redes informaticas',1,'6',2,'VII','5','IV','COM75',30,70,16,0,1,1,NULL),
(2,'AdministraciÃ³n de Transacciones Comerciales por Medios ElectrÃ³nicos',1,'5',2,'VII','4','IV','COMP011904',20,60,19,0,1,1,NULL),
(3,'Desarrollo de Programas Multiplataforma ',1,'5',2,'VII','5','IV','COMP012005',30,70,20,0,1,1,NULL),
(5,'ConfiguraciÃ³n de Sistemas Operativos',1,'5',2,'VII','5','IV','COMP012105',30,70,21,0,1,1,NULL),
(15,'PrevenciÃ³n de Accidentes y Enfermedades Ocupacionales',1,'6',2,'VII','2','IV','BAS297',16,24,21,0,1,0,NULL),
(7,'InglÃ©s Intermedio II',1,'5',2,'VII','3','IV','COMP012203',30,30,22,0,1,0,NULL),
(8,'ConfiguraciÃ³n y AdministraciÃ³n de Sistemas Operativos',1,'6',2,'VII','5','IV','COM84',30,70,18,0,1,1,NULL),
(9,'PrevenciÃ³n de Accidentes y Enfermedades Ocupacionales',1,'5',2,'VII','2','IV','COMP012302',20,20,23,0,1,0,NULL),
(10,'AdministraciÃ³n de Herramientas web',1,'6',2,'VII','4','IV','COM85',16,64,19,0,1,1,NULL),
(11,'Desarrollo de LÃ³gica de ProgramaciÃ³n',1,'5',2,'I','5','I','COMP010105',30,70,1,0,1,1,NULL),
(12,'DirecciÃ³n del Comportamiento Humano en el Ambiente Laboral',1,'6',2,'VII','2','IV','BAS296',16,24,20,0,1,1,NULL),
(17,'AnÃ¡lisis y DiseÃ±o de Sistemas',1,'5',2,'I','5','I','COMP010205',30,70,2,0,1,1,NULL),
(19,'Desarrollo de Aplicaciones Usando TecnologÃ­as Emergentes',1,'6',2,'VII','5','IV','COM83',30,70,17,0,1,1,NULL),
(20,'InstalaciÃ³n y ConfiguraciÃ³n Software y Hardware',1,'5',2,'III','4','IV','COMP010403',20,60,3,0,1,1,NULL),
(21,'Desarrollo de LÃ³gica de ProgramaciÃ³n',1,'6',2,'I','5','I','COM01',30,70,1,0,1,1,NULL),
(24,'GestiÃ³n de Proyectos de Desarrollo de Software',1,'6',2,'I','4','I','COM72',16,64,2,0,1,0,NULL),
(23,'InglÃ©s BÃ¡sico I',1,'5',2,'I','3','I','COMP010403',30,30,4,0,1,0,NULL),
(25,'DiseÃ±o de PÃ¡ginas Web',1,'6',2,'I','4','I','COM73',16,64,3,0,1,1,NULL),
(26,'DiseÃ±o del Plan de Negocio',1,'5',2,'I','3','I','COMP010503',30,30,5,0,1,1,NULL),
(27,'Ingles BÃ¡sico 1',1,'6',2,'I','3','I','BAS42',30,30,4,0,1,0,NULL),
(28,'ComunicaciÃ³n Oral y Escrita',1,'5',2,'I','2','I','COMP010602',20,20,6,0,1,0,NULL),
(29,'MatemÃ¡tica',1,'6',2,'I','4','I','BAS132',32,48,4,0,1,0,NULL),
(30,'Desarrollo de Aplicaciones Cliente Servidor',1,'5',2,'III','5','II','COMP010705',30,70,7,0,1,1,NULL),
(31,'DiseÃ±o de PÃ¡ginas Web',1,'5',2,'III','5','II','COMP010804',20,60,8,0,1,1,NULL),
(32,'DiseÃ±o de Base de Datos',1,'5',2,'III','5','II','COMP010905',30,70,9,0,1,1,NULL),
(33,'Desarrollo de Aplicaciones de Escritorio',1,'6',2,'III','5','II','COM80',30,70,6,0,1,1,NULL),
(39,'Ingles BÃ¡sico ll',1,'6',2,'III','3','II','BAS43',30,30,9,0,1,0,NULL),
(36,'Analisis y DiseÃ±o de Sistemas',1,'6',2,'III','5','II','COM02',30,70,7,0,1,1,NULL),
(38,'DiseÃ±o de Bases de Datos',1,'6',2,'III','5','II','COM06',30,70,8,0,1,1,NULL),
(40,'FormulaciÃ³n y EvaluaciÃ³n de Planes de Negocios',1,'6',2,'III','4','II','BAS209',32,48,10,0,1,1,NULL),
(41,'InstalaciÃ³n y ConfiguraciÃ³n de Software y Hardware ',1,'6',2,'V','4','III','COM77',16,64,11,0,1,1,NULL),
(42,'AplicaciÃ³n de Metodologias Agiles y Testeo de Software',1,'6',2,'V','5','III','COM82',30,70,12,0,1,1,NULL),
(43,'Desarrollo de Aplicaciones Para La Web',1,'6',2,'V','5','III','COM07',30,70,13,0,1,1,NULL),
(44,'ComunicaciÃ³n Oral y Escrita',1,'6',2,'V','2','III','BAS298',16,24,14,0,1,0,NULL),
(45,'FÃ­sica',1,'6',2,'V','3','III','BAS40',30,30,15,0,1,0,NULL),
(46,'InglÃ©s BÃ¡sico II',1,'5',2,'III','3','II','COMP011003',30,30,10,0,1,0,NULL),
(47,'Ejecucion del Plan de Negocio',1,'5',2,'III','3','II','COMO011103',30,30,11,0,1,1,NULL),
(48,'MatemÃ¡tica',1,'5',2,'III','4','II','COMP011204',30,30,12,0,1,0,NULL),
(49,'Desarrollo de Aplicaciones para la Web',1,'5',2,'V','5','III','COMP011305',30,70,13,0,1,1,NULL),
(50,'SelecciÃ³n de TÃ©cnicas de IngenierÃ­a de Software',1,'5',2,'V','5','III','COMP011405',30,70,14,0,1,1,NULL),
(51,'DiseÃ±o de Redes InformÃ¡ticas',1,'5',2,'V','5','III','COMP011505',30,70,15,0,1,1,NULL),
(228,'Configuración de Redes Informáticas',1,'17',9,'II','4','III','COM111',16,64,9,NULL,1,1,'Conf. de redes'),
(53,'DirecciÃ³n del Comportamiento Humano en el Ambiente Laboral',1,'5',2,'V','2','III','COMP011702',20,20,17,0,1,0,NULL),
(54,'FÃ­sica',1,'5',2,'V','3','III','COMP011803',30,30,18,0,1,0,NULL),
(55,'Mantenimiento Preventivo en Equipos de Computo',2,'7',4,'I','4','I','ELE113',32,48,1,0,1,0,NULL),
(56,'PrevenciÃ³n de Accidentes y Enfermedades Ocupacionales',2,'1000',4,'V','2','I','BAS166',40,0,2,0,1,0,NULL),
(57,'Desarrollo de Procesos AlgÃ©braicos y CÃ¡lculos de Ãreas de PÃ©rimetros y Volumenes   ',2,'7',4,'I','3','I','BAS02',60,0,3,0,1,0,NULL),
(58,'InstalaciÃ³n, ConfiguraciÃ³n y ActualizaciÃ³n  de Sistemas Operativos',2,'7',4,'I','4','I','ELE114',24,56,4,0,1,1,NULL),
(59,'InstalaciÃ³n de Cable Estructurado',2,'7',4,'I','4','I','ELE115',16,64,5,0,1,1,NULL),
(60,'MediciÃ³n de Magnitudes FÃ­sicas',2,'7',4,'I','2','I','BAS121',24,16,6,0,1,0,NULL),
(61,'Desarrollo y AnÃ¡lisis de Funciones AlgÃ©braicas y CÃ³nicas',2,'7',4,'I','3','I','BAS17',60,0,7,0,1,0,NULL),
(62,'ApropiaciÃ³n de Vocabulario en InglÃ©s para Actividades de Esparimiento',2,'7',4,'I','2','I','BAS94',16,24,8,0,1,0,NULL),
(63,'AdquisiciÃ³n de Vocabulario en InglÃ©s Relacionado a Situaciones Sociales ',2,'7',4,'I','2','I','BAS96',16,24,9,0,1,0,NULL),
(64,'InstalaciÃ³n y ConfiguraciÃ³n de Redes Punto a Punto',2,'7',4,'III','4','II','ELE116',24,56,10,0,1,1,NULL),
(65,'AdministraciÃ³n de Sistemas Operativos',2,'7',4,'III','7','II','ELE118',52,88,11,0,1,1,NULL),
(66,'ElaboraciÃ³n de Anteproyectos de InvestigaciÃ³n',2,'7',4,'III','2','II','BAS07',40,0,12,0,1,0,NULL),
(67,'Mantenimiento Correctivo y ActualizaciÃ³n de Componentes de Computadoras',2,'7',4,'III','5','II','ELE117',36,64,14,0,1,1,NULL),
(120,'ContextualizaciÃ³n histÃ³rica y geogrÃ¡fica del patrimonio cultural',5,'9',5,'I','3','I','PAT010303',60,0,3,0,0,1,NULL),
(69,'ConstrucciÃ³n de Expresiones en InglÃ©s sobre Cuestiones Laborales',2,'7',4,'III','2','II','BAS101',16,24,15,0,1,0,NULL),
(70,'ConstrucciÃ³n de Frases en InglÃ©s Sobre Temas de InterÃ©s Particular',2,'7',4,'III','2','II','BAS98',16,24,16,0,1,0,NULL),
(122,'ElaboraciÃ³n de registros de bienes culturales con aplicaciÃ³n de tecnologÃ­as',5,'9',5,'I','4','I','PAT010604',44,36,6,0,0,1,NULL),
(72,'PreparaciÃ³n de Presentaciones Orales y RedacciÃ³n de Informes Escritos',2,'7',4,'III','2','II','BAS212',20,28,18,0,1,0,NULL),
(73,'ConstrucciÃ³n de Circuitos de Control ElectrÃ³nico de Potencia',2,'8',3,'I','3','I','ELE162',8,52,1,0,1,1,NULL),
(74,'Mantenimiento Correctivo en Monitores de Computadoras',2,'7',4,'V','4','III','ELE119',24,56,19,0,1,1,NULL),
(75,'ElaboraciÃ³n de Anteproyectos de InvestigaciÃ³n',2,'8',3,'I','2','I','BAS208',24,16,2,0,1,1,NULL),
(76,'ElaboraciÃ³n de Planos para Sistemas Electricos',2,'8',3,'I','3','I','ELE161',8,52,3,0,1,1,NULL),
(77,'Instalaciones Electricas Internas',2,'8',3,'I','4','I','ELE160',16,64,4,0,1,1,NULL),
(78,'MatemÃ¡tica',2,'8',3,'I','4','I','BAS132',32,48,5,0,1,0,NULL),
(80,'TÃ©cnicas de Taller para la ElaboraciÃ³n de Proyectos',2,'8',3,'I','2','I','ELE163',8,32,6,0,1,1,NULL),
(81,'FormulaciÃ³n y AdministraciÃ³n de Proyectos de Sistemas Electricos',2,'8',3,'III','4','II','ELE165',16,64,7,0,1,1,NULL),
(82,'ConfiguraciÃ³n de Dispositivos de InterconexiÃ³n en Redes de Ãrea Local Basadas En Cobre y TecnologÃ­a InalÃ¡mbrica',2,'7',4,'V','4','III','ELE121',24,56,21,0,1,1,NULL),
(83,'FormulaciÃ³n y EvaluaciÃ³n de Planes de Negocios',2,'8',3,'III','4','II','BAS209',32,48,8,0,1,1,NULL),
(84,'Mantenimiento Correctivo en Impresoras y UPS',2,'7',4,'V','4','III','ELE122',24,56,22,0,1,1,NULL),
(85,'Mantenimiento a Instalaciones Electricas Internas',2,'8',3,'III','3','II','ELE164',8,52,9,0,1,1,NULL),
(86,'GestiÃ³n de Redes de Datos en Ãrea Local',2,'7',4,'V','6','III','ELE123',32,88,23,0,1,1,NULL),
(87,'Mantenimiento a Motores ElÃ©ctricos',2,'8',3,'III','3','II','ELE167',8,52,10,0,1,1,NULL),
(88,'DescripciÃ³n de Situaciones Actuales en InglÃ©s ',2,'7',4,'V','2','III','BAS104',16,24,24,0,1,0,NULL),
(89,'PresentaciÃ³n en InglÃ©s de Temas de InterÃ©s Personal',2,'7',4,'V','2','III','BAS102',16,24,25,0,1,0,NULL),
(90,'Mantenimiento ElÃ©ctronico a Plantas de Emergencia y a Sistemas de RefrigeraciÃ³n',2,'8',3,'III','2','II','ELE166',8,32,11,0,1,1,NULL),
(91,'DirecciÃ³n del Comportamiento Humano en el Ambiente Laboral',2,'7',4,'V','2','III','BAS105',24,24,26,0,1,0,NULL),
(92,'Montaje y Mantenimiento de Sistemas de Puestas a Tierra',2,'8',3,'III','2','II','ELE168',8,32,12,0,1,1,NULL),
(93,'ImplementaciÃ³n de Servicios en Redes de Ãrea Local',2,'7',4,'VII','6','IV','ELE124',40,80,27,0,1,1,NULL),
(94,'AnÃ¡lisis de Eficiencia EnÃ©rgetica en Edificaciones',2,'8',3,'V','3','III','ELE173',8,52,13,0,1,1,NULL),
(95,'ImplementaciÃ³n de Estrategias de Seguridad en Redes de Ãrea Local',2,'7',4,'VII','6','IV','ELE125',32,88,28,0,1,1,NULL),
(96,'ConstrucciÃ³n y Mantenimiento de Lineas de DistrubuciÃ³n de EnergÃ­a ElÃ©ctrica',2,'8',3,'V','3','III','ELE170',8,52,14,0,1,1,NULL),
(97,'FormulaciÃ³n de Planes de Negocio',2,'7',4,'VII','3','IV','BAS145',60,0,29,0,1,0,NULL),
(98,'Dimensionamiento, Montaje y Mantenimiento de Sistemas Fotovoltaicos',2,'8',3,'V','3','III','ELE169',8,52,15,0,1,1,NULL),
(99,'FÃ­sica',2,'8',3,'V','3','III','BAS40',30,30,16,0,1,1,NULL),
(100,'Ingles BÃ¡sico l',2,'8',3,'V','3','III','BAS42',30,30,17,0,1,0,NULL),
(101,'AdministraciÃ³n de Proyectos de TecnologÃ­as de la InformaciÃ³n',2,'7',4,'VII','5','IV','ELE126',40,60,30,0,1,1,NULL),
(102,'Mantenimiento Preventivo a Sistemas Mecatronicos',2,'8',3,'V','2','III','ELE172',8,32,18,0,1,1,NULL),
(103,'Montaje y Mantenimiento de Subestaciones de DistribuciÃ³n',2,'8',3,'V','3','III','ELE171',8,52,19,0,1,1,NULL),
(104,'RenovaciÃ³n de Programas InformÃ¡ticos',2,'7',4,'VII','3','IV','ELE127',16,44,31,0,1,1,NULL),
(105,'Diagnostico de la Calidad de la EnÃ©rgia en Sistemas ElÃ©ctrico',2,'8',3,'VII','3','IV','ELE176',8,52,20,0,1,1,NULL),
(106,'ElaboraciÃ³n de Planes de Negocio',2,'7',4,'VII','3','IV','BAS150',60,0,32,0,1,0,NULL),
(107,'DirecciÃ³n del Comportamiento Humano en el Ambiente Laboral',2,'8',3,'VII','2','IV','BAS210',24,16,21,0,1,1,NULL),
(108,'ConversaciÃ³n de Temas ComÃºnes en InglÃ©s',2,'7',4,'VII','2','IV','BAS107',16,24,33,0,1,0,NULL),
(109,'DiseÃ±o de IluminaciÃ³n de Interiores e InstalaciÃ³n de Sistemas Domoticos',2,'8',3,'VII','3','IV','ELE174',8,52,22,0,1,1,NULL),
(110,'NarraciÃ³n de Experiencias Personales en InglÃ©s',2,'7',4,'VII','2','IV','BAS109',16,24,34,0,1,0,NULL),
(111,'Ingles BÃ¡sico ll',2,'8',3,'VII','3','IV','BAS43',30,30,23,0,1,1,NULL),
(112,'Mantenimiento de Lineas de TransmisiÃ³n de EnÃ©rgia ElÃ©ctrica',2,'8',3,'VII','3','IV','ELE175',8,52,24,0,1,1,NULL),
(113,'Montaje de Componentes en Paneles de Control ElÃ©ctrico',2,'8',3,'VII','2','IV','ELE177',8,32,25,0,1,1,NULL),
(114,'PrevenciÃ³n de Accidentes y Enfermedades Ocupacionales',2,'8',3,'VII','2','IV','BAS99',24,16,26,0,1,1,NULL),
(121,'EdiciÃ³n y RepresentaciÃ³n de bienes culturales con aplicaciones tecnolÃ³gicas',5,NULL,10000,'I','4','I','PAT010704',40,40,7,0,1,1,NULL),
(116,'PrevenciÃ³n de Accidentes y Enfermedades Ocupacionales',2,'7',4,'I','2','I','BAS166',40,0,2,0,1,0,NULL),
(117,'Tratamiento EstÃ¡distico de la InformaciÃ³n',2,'7',4,'III','3','II','BAS10',64,0,13,0,1,0,NULL),
(118,'DescripciÃ³n y CuantificaciÃ³n de FenÃ³menos HidrostÃ¡ticos, TÃ©rmicos y ElÃ©ctricos',2,'7',4,'III','2','II','BAS197',24,16,17,0,1,0,NULL),
(119,'Mantenimiento Correctivo a Sistemas Operativos',2,'7',4,'V','4','III','ELE120',24,56,20,0,1,1,NULL),
(123,'ElaboraciÃ³n y publicaciones de pro. culturales',5,NULL,10000,'I','3','I','4000',60,0,9,0,1,0,NULL),
(124,'Emprenderurismo colaborativo',5,NULL,10000,'I','4','I','PAT010205',80,0,2,0,1,1,NULL),
(125,'OrganizaciÃ³n e investigaciÃ³n del Patrimonio Cultural',5,'9',5,'I','4','I','PAT010404',80,0,4,0,0,1,NULL),
(126,'OrientaciÃ³n de estudiantes al proceso educativo de primer aÃ±o',5,'9',5,'I','1','I','PAT01010',20,0,1,0,0,1,NULL),
(127,'ConservaciÃ³n preventiva de bienes culturales',5,'9',5,'III','7','II','PAT011007',72,72,10,0,0,1,NULL),
(128,'ConversaciÃ³n en lengua nahuat',5,'9',5,'IV','4','II','PAT011104',16,64,11,0,0,1,NULL),
(129,'DiseÃ±o de estrategias de comunicaciÃ³n para la promociÃ³n y preservaciÃ³n del patrimonio cultural',5,'9',5,'VI','4','III','PAT011704',96,0,17,0,0,1,NULL),
(130,'DiseÃ±o de planes de negocio en asociatividad cooperativa',5,'9',5,'III','5','II','PAT010805',100,0,8,0,0,1,NULL),
(131,'OrientaciÃ³n de estudiantes al proceso educativo del segundo 2 aÃ±o',5,'9',5,'V','1','III','PAT011301',24,0,13,0,0,1,NULL),
(132,'OrganizaciÃ³n y elaboraciÃ³n de inventarios de bienes culturales.',5,'9',5,'III','3','II','PAT010903',0,60,9,0,0,1,NULL),
(133,'Puesta en marcha de la microempresa en asosiatividad cooperativa',5,'9',5,'V','8','III','PAT011508',160,0,15,0,0,1,NULL),
(134,'RestauraciÃ³n de bienes culturales',5,'9',5,'VI','7','III','PAT011607',144,0,16,0,0,1,NULL),
(135,'RealizaciÃ³n de diagnÃ³sticos y proyectos culturales comunitarios',5,'9',5,'IV','4','II','PAT011204',40,40,12,0,0,1,NULL),
(136,'ElaboraciÃ³n y publicaciÃ³n de productos culturales',5,'9',5,'II','8','I','PAT010503',30,30,5,0,0,1,NULL),
(137,'Desarrollo de lenguaje tÃ©cnico del patrimonio cultural en idioma inglÃ©s',5,'9',5,'V','6','III','PAT011406',120,0,14,0,0,1,NULL),
(138,'CreaciÃ³n de empresas culturales de beneficio social',5,'9',5,'VIII','7','IV','PAT012107',144,0,21,0,0,1,NULL),
(139,'Desarrollo de estrategias de mercadeo aplicadas al patrimonio cultural',5,'9',5,'VII','6','IV','PAT011806',120,0,18,0,0,1,NULL),
(140,'AdministraciÃ³n  de espacios culturales',5,'9',5,'VIII','8','IV','PAT012006',120,0,20,0,0,1,NULL),
(141,'ProducciÃ³n de Eventos Culturales',5,'9',5,'VII','7','IV','PAT011907',144,0,19,0,0,1,NULL),
(142,'Ingles Basico Entre Semana',7,'1000',6,'I','0','I','IB',20,0,0,0,1,0,NULL),
(143,'Ingles Intermedio Entre Semana',7,NULL,10000,'I','0','I','II',20,0,0,0,1,0,NULL),
(144,'Ingles Avanzado Entre Semana',7,'10',6,'I','0','I','IA',20,0,0,0,1,0,NULL),
(145,'Ingles Talk Entre Semana',7,'1000',6,'I','0','I','IT',20,0,0,0,1,1,NULL),
(225,'Diseño de Bases de Datos Relacionales',1,'17',9,'II','4','III','COM143',16,64,6,NULL,1,1,'no definido aún'),
(226,'Análisis y Diseño de Sistemas',1,'17',9,'II','4','III','COM113',16,64,7,NULL,1,1,'no definido aún'),
(227,'Desarrollo de Aplicaciones de Escritorio',1,'17',9,'II','4','III','COM144',16,64,8,NULL,1,1,'no definido aún'),
(219,'Desarrollo de Lógica de Programación – 2024',1,'17',9,'I','4','I','COM25',16,64,1,NULL,1,1,'DLP'),
(220,'Diseño de Páginas Web – 2024',1,'17',9,'I','4','I','COM73',16,64,2,NULL,1,1,'DPW'),
(221,'Análisis y Diseño de Sistemas-2024',1,'17',9,'I','4','I','COM113',16,64,3,NULL,1,1,'ADS'),
(224,'Diseño de Planes de Negocios en Asociatividad Cooperativa – 2024',1,'17',9,'I','4','I','BAS436',24,56,5,NULL,1,0,'DPDAC'),
(223,'Conversación en Inglés sobre Sistemas Informáticos y Desarrollo de Páginas Web – 2024',1,'17',9,'I','4','I','BAS434',24,56,4,NULL,1,0,'CISSIDP'),
(287,'Puesta en marcha de la microempresa en asociatividad cooperativa',5,'12',5,'I','6','III','PAT0216',120,0,16,NULL,1,1,'no definido aún'),
(288,'Comunicación básica en Inglés de temas cotidianos',5,'12',5,'I','3','III','PAT0217',60,0,17,NULL,1,1,'no definido aún'),
(284,'APLICACIÃ“N DE TÃ‰CNICAS DE ANIMACIÃ“N CULTURAL',5,'12',5,'I','4','II','GTP61',80,0,1,NULL,1,1,'nd'),
(285,'EVOLUCIÃ“N URBANA Y ARQUITECTÃ“NICA DE LOS BIENES CULTURALES INMUEBLES',5,'12',5,'I','3','II','GTP62',60,0,14,NULL,1,1,'no definido aÃºn'),
(230,'Construcción de Frases en Inglés sobre Cuestiones Laborales',1,'17',9,'II','4','III','BAS338',32,48,10,NULL,1,0,'no definido aún'),
(280,'DISEÃ‘O DE PLANES DE NEGOCIO EN ASOCIATIVIDAD COOPERATIVA',5,'12',5,'I','4','II','GTP57',40,40,9,NULL,1,1,'no definido aÃºn'),
(281,'CONTEXTUALIZACIÃ“N HISTÃ“RICA Y GEOGRÃFICA DE LAS EXPRESIONES CULTURALES INMATERIALES',5,'12',5,'I','4','II','GTP58',80,0,10,NULL,1,1,'nd'),
(282,'EXPRESIONES ETNOLINGÃœÃSTICAS ORIGINARIAS',5,'12',5,'I','3','II','GTP59',60,0,11,NULL,1,1,'nd'),
(283,'EDICIÃ“N Y REPRESENTACIÃ“N DE BIENES CULTURALES CON APLICACIÃ“N DE TECNOLOGÃA',5,'12',5,'I','5','II','GTP60',30,70,12,NULL,1,1,'nd'),
(169,'ENSAMBLE Y CONFIGURACIÃ“N DE COMPUTADORAS PERSONALES ',2,'13',7,'I','3','I','ELE279',24,36,1,NULL,1,1,'nd'),
(170,'INSTALACIÃ“N DE MEDIOS Y DISPOSITIVOS PARA REDES LOCALES ',2,'13',7,'I','4','I','ELE280',32,48,2,NULL,1,1,'nd'),
(171,'ANÃLISIS Y MONTAJE DE CIRCUITOS DE CORRIENTE DIRECTA ',2,'13',7,'I','3','I','ELE281',24,36,3,NULL,1,1,'nd'),
(172,'CREACIÃ“N DE ALGORITMOS Y PROGRAMAS COMPUTACIONALES ',2,'13',7,'I','3','I','ELE282',24,36,4,NULL,1,1,'nd'),
(173,'MATEMATICA ',2,'13',7,'I','4','I','BAS132',32,48,5,NULL,1,0,'nd'),
(174,'ELABORACION DE ANTEPROYECTOS DE INVESTIGACIÃ“N',2,'13',7,'I','2','III','BAS208',24,16,6,NULL,1,0,'no definido aÃºn'),
(175,'CONFIGURACIÃ“N Y ADMINISTRACIÃ“N DE SISTEMAS OPERATIVOS ',2,'13',7,'II','3','II','ELE283',20,40,7,NULL,1,1,'nd'),
(176,'CONFIGURACIÃ“N Y ADMINISTRACIÃ“N DE SWITCHES Y ROUTERS PARA REDES LOCALES ',2,'13',7,'II','4','II','ELE284',32,48,8,NULL,1,1,'nd'),
(177,'MONTAJE DE CIRCUITOS ELECTRÃ“NICOS ANALÃ“GICOS ',2,'13',7,'II','3','II','ELE285',24,36,9,NULL,1,1,'nd'),
(178,'DISEÃ‘O Y CONSTRUCCIÃ“N DE CIRCUITOS ELECTRÃ“NICOS DIGITALES ',2,'13',7,'II','3','II','ELE286',24,36,10,NULL,1,1,'nd'),
(179,'DESAROLLO DE HABILIDADES DE FABRICACIÃ“N ELECTRÃ“NICA ',2,'13',7,'II','2','II','ELE287',0,40,11,NULL,1,1,'nd'),
(243,'FORMULACIÃ“N Y EVALUACIÃ“N DE PLANES DE NEGOCIO ',2,'13',7,'II','4','II','BAS209',32,48,12,NULL,1,0,'nd'),
(181,'CONFIGURACIÃ“N Y ADMINISTRACIÃ“N DE SISTEMAS OPERATIVOS DE RED ',2,'13',7,'III','4','III','ELE288',32,48,13,NULL,1,1,'nd'),
(182,'ANÃLISIS Y SOLUCIÃ“N DE FALLAS EN EQUIPOS DE CÃ“MPUTO ',2,'13',7,'III','4','III','ELE289',32,48,14,NULL,1,1,'nd'),
(183,'CREACIÃ“N  DE APLICACIONES ORIENTADAS AL INTERNET ',2,'13',7,'III','4','III','ELE290',32,48,15,NULL,1,1,'nd'),
(184,'CREACIÃ“N DE CIRCUITOS ELECTRÃ“NICOS CON MICROCONTROLADORES ',2,'13',7,'III','4','III','ELE237',32,48,16,NULL,1,1,'nd'),
(185,'PREVENCIÃ“N DE ACCIDENTES Y ENFERMEDADES OCUPACIONALES ',2,'13',7,'III','2','III','BAS99',24,16,17,NULL,1,0,'nd'),
(186,'INGLÃ‰S BÃSICO I',2,'13',7,'III','3','III','BAS42',30,30,18,NULL,1,0,'nd'),
(245,'APLICACIÃ“N DE PRINCIPIOS DE SEGURIDAD INFORMÃTICA ',2,'13',7,'IV','4','IV','ELE291',32,48,19,NULL,1,1,'nd'),
(244,'INGLÃ‰S BÃSICO II ',2,'13',7,'IV','3','IV','BAS43',30,30,24,NULL,1,0,'nd'),
(189,'CONSTRUCCIÃ“N DE SIMULACIÃ“N Y MONTAJE DE DISPOSITOS DE HARDWARE COMPUTACIONAL ',2,'13',7,'IV','4','IV','ELE240',32,48,20,NULL,1,1,'nd'),
(190,'DESARROLLO DE EMPRENDIMIENTO APLICANDO TECNOLOGIAS DE LA INFORMACIÃ“N',2,'13',7,'IV','3','IV','ELE292',24,36,21,NULL,1,1,'nd'),
(191,'ADMINISTRACIÃ“N Y PLANIFICACIÃ“N TÃ‰CNICA ',2,'13',7,'IV','2','IV','ELE243',20,20,23,NULL,1,1,'nd'),
(192,'DIRECCIÃ“N DEL COMPORTAMIENTO HUMANO EN EL AMBIENTE LABORAL ',2,'13',7,'IV','2','IV','BAS210',24,16,23,NULL,1,0,'nd'),
(193,'INGLÃ‰S BÃSICO II ',2,'',7,'IV','3','IV','BAS43',30,30,24,NULL,1,0,'nd'),
(194,'INSTALACIONES ELÃ‰CTRICAS RESIDENCIALES ',2,'16',3,'I','4','I','ELE293',32,48,1,NULL,1,1,'nd'),
(195,'ANÃLISIS DE CIRCUITOS ELÃ‰CTRICOS ',2,'16',3,'I','4','I','ELE294',32,48,2,NULL,1,1,'nd'),
(196,'ELABORACIÃ“N DE ANTEPROYECTOS DE INVESTIGACIÃ“N ',2,'16',3,'I','2','I','BAS208',24,16,3,NULL,1,0,'nd'),
(197,'MATEMATICA ',2,'16',3,'I','2','I','BAS132',32,48,4,NULL,1,0,'nd'),
(198,'INGLÃ‰S BÃSICO I ',2,'16',3,'I','3','I','BAS42',30,30,5,NULL,1,0,'no definido aÃºn'),
(217,'INSTALACIONES ELÃ‰CTRICAS COMERCIALES E INDUSTRIALES ',2,'16',3,'II','4','II','ELE295',32,48,7,NULL,1,1,'nd'),
(200,'ELABORACIÃ“N DE PLANOS PARA PROYECTOS ELÃ‰CTRICOS',2,'16',3,'II','4','II','ELE298',32,48,7,NULL,1,1,'nd'),
(201,'OPERACIÃ“N Y MANTENIMIENTO DE MÃQUINAS DE CONVERSIÃ“N DE ENERGÃA ',2,'16',3,'II','4','II','ELE296',32,48,8,NULL,1,1,'nd'),
(202,'SEGURIDAD ELÃ‰CTRICA ',2,'16',3,'II','3','II','ELE306',24,36,9,NULL,1,1,'nd'),
(203,'Fisica',2,'',3,'II','3','III','BAS40',30,30,10,NULL,1,0,'no definido aún'),
(204,'INGLÃ‰S BÃSICO II ',2,'16',3,'II','3','II','BAS43',30,30,11,NULL,1,0,'nd'),
(205,'TÃ‰CNICAS DE TALLER ',2,'16',3,'III','3','III','ELE299',24,36,12,NULL,1,1,'nd'),
(206,'DIMENSIONAMIENTO, MONTAJE Y MANTENIMIENTO DE SUBESTACIONES Y LÃNEAS DE DISTRIBUCIÃ“N DE ENERGÃA ELÃ‰CTRICA ',2,'16',3,'III','4','III','ELE297',32,48,13,NULL,1,1,'nd'),
(207,'DISEÃ‘O Y MONTAJE DE SISTEMAS DE CONTROL AUTOMÃTICO ',2,'16',3,'III','4','III','ELE307',32,48,14,NULL,1,1,'nd'),
(208,'CONSTRUCCIÃ“N DE CIRCUITOS DE CONTROL ELECTRÃ“NICO DE POTENCIA ',2,'16',3,'III','4','III','ELE300',32,48,15,NULL,1,1,'nd'),
(209,'PREVENCIÃ“N DE ACCIDENTES Y ENFERMEDADES OCUPACIONALES ',2,'16',3,'III','2','III','BAS99',24,16,16,NULL,1,0,'nd'),
(210,'FORMULACIÃ“N Y EVALUACIONES DE PLANES DE NEGOCIOS ',2,'16',3,'III','4','III','BAS209',32,48,17,NULL,1,0,'nd'),
(211,'DISEÃ‘O Y MANTENIMIENTO DE SISTEMA DE ENERGÃA SOLAR FOTOVOLTAICA ',2,'16',3,'IV','4','IV','ELE301',32,48,18,NULL,1,1,'nd'),
(212,'DISEÃ‘O DE ILUMINACIÃ“N DE INTERIORES E INSTALACIÃ“N DE SISTEMAS DOMÃ“TICOS ',2,'16',3,'IV','3','IV','ELE302',24,36,19,NULL,1,1,'nd'),
(213,'ANÃLISIS DE EFICIENCIA Y CALIDAD DE ENERGÃA EN EDIFICACIONES ',2,'16',3,'IV','4','IV','ELE303',32,48,20,NULL,1,1,'nd'),
(214,'FORMULACIÃ“N Y ADMINISTRACIÃ“N DE PROYECTOS DE SISTEMAS ELÃ‰CTRICOS ',2,'16',3,'IV','4','IV','ELE304',32,48,21,NULL,1,1,'nd'),
(215,'PROGRAMACIÃ“N Y MANTENIMIENTO A SISTEMAS MECATRÃ“NICOS ',2,'16',3,'IV','3','IV','ELE305',24,36,22,NULL,1,1,'nd'),
(216,'DIRECCIÃ“N DEL COMPORTAMIENTO HUMANO EN EL AMBIENTE LABORAL ',2,'16',3,'IV','2','IV','BAS210',24,16,23,NULL,1,0,'nd'),
(229,'Comunicación Oral y Escrita',1,'17',9,'II','2','III','BAS298',16,24,11,NULL,1,0,'no definido aún'),
(289,'Difusión de manifestaciones y productos culturales',5,'12',5,'I','5','III','PAT0218',100,0,18,NULL,1,1,'no definido aún'),
(286,'Formulación de proyectos sobre patrimonio cultural',5,'12',5,'I','4','III','PAT0215',80,0,15,NULL,1,1,'no definido aÃºn'),
(276,'REGISTRO DE BIENES CULTURALES CON APLICACIÃ“N DE TECNOLOGÃAS',5,'12',5,'I','5','I','GTP53',30,70,5,NULL,1,1,'nd'),
(277,'ANTEPROYECTOS DE INVESTIGACIÃ“N SOBRE TEMAS RELACIONADOS AL PATRIMONIO CULTURAL',5,'12',5,'I','3','I','GTP54',60,0,6,NULL,1,1,'nd'),
(278,'EVOLUCIÃ“N ARTÃSTICA Y ESTILÃSTICA DE LOS BIENES CULTURALES',5,'12',5,'I','4','I','GTP55',80,0,7,NULL,1,1,'nd'),
(279,'LEGISLACIÃ“N SOBRE PATRIMONIO CULTURAL',5,'12',5,'I','2','II','GTP56',40,0,8,NULL,1,1,'no definido aÃºn'),
(275,'ESTADÃSTICA APLICADA AL PATRIMONIO CULTURAL',5,'12',5,'I','3','I','GTP52',60,0,4,NULL,1,1,'nd'),
(272,'CLASIFICACIÃ“N DE BIENES CULTURALES',5,'12',5,'I','3','I','GTP49',60,0,1,NULL,1,1,'nd'),
(273,'EMPRENDEDURISMO COLABORATIVO',5,'12',5,'I','4','I','GTP50',80,0,2,NULL,1,1,'nd'),
(274,'TÃ‰CNICAS PARA PREPARACIÃ“N DE INFORMES Y REPORTES',5,'12',5,'I','2','I','GTP51',40,0,3,NULL,1,1,'nd'),
(257,'Desarrollo de Aplicaciones Web',1,'17',9,'III','8','I','COM145',16,64,12,NULL,1,1,'no definido aÃºn'),
(258,'Gestión de Herramientas Web',1,'17',9,'III','2','I','COM146',16,64,13,NULL,1,1,'no definido aún'),
(259,'Diseño de Bases de Datos no Relacionales',1,'17',9,'III','6','I','COM147',16,64,14,NULL,1,1,'no definido aún'),
(260,'Configuración de Servidores para Implementación de Sistemas',1,'17',9,'III','3','I','COM148',16,64,15,NULL,1,1,'no definido aún'),
(261,'Prevención de Accidentes y Enfermedades Ocupacionales',1,'17',9,'III','2','I','BAS297',16,24,16,NULL,1,0,'no definido aún'),
(262,'Descripción de Fenómenos Físicos',1,'17',9,'III','4','I','BAS343',32,48,17,NULL,1,0,'no definido aún'),
(265,'Desarrollo de Aplicaciones Móviles',1,'17',9,'IV','4','IV','COM149',16,64,19,NULL,1,1,'no definido aún'),
(266,'Aplicación de Metodologías Ágiles y Control de Calidad del Software',1,'17',9,'IV','4','IV','COM150',16,64,19,NULL,1,1,'no definido aún'),
(271,'Utilización de Frameworks para el Desarrollo de Software',1,'17',9,'IV','4','IV','COM151',16,64,20,NULL,1,1,'no definido aún'),
(268,'Dirección del Comportamiento Humano en el Ambiente Laboral',1,'17',9,'IV','2','IV','BAS296',16,24,21,NULL,1,0,'no definido aún'),
(269,'Desarrollo del Pensamiento Estadístico y Probabilístico',1,'17',9,'IV','4','IV','BAS340',32,48,22,NULL,1,0,'no definido aún'),
(270,'Formulación y Evaluación de Planes de Negocio de Desarrollo de Software',1,'17',9,'IV','4','IV','BAS386',32,48,23,NULL,1,0,'no definido aún'),
(290,'Conversación en lengua Náhuat',5,'12',5,'I','4','III','PAT0219',80,0,19,NULL,1,1,'no definido aún'),
(291,'Comunicación de ideas en idioma Inglés para interactuar con extrnajeros',5,'12',5,'I','3','III','PAT0220',60,0,20,NULL,1,1,'no definido aún'),
(292,'PROCEDIMIENTOS DE CONSERVACIÃ“N Y RESTAURACIÃ“N DE BIENES CULTURALES MUEBLES',5,'12',5,'I','3','IV','GTP69',160,8,21,NULL,1,1,'no definido aÃºn'),
(293,'COMUNICACIÃ“N ORGANIZACIONAL',5,'12',5,'I','3','IV','GTP70',60,0,22,NULL,1,1,'nd'),
(294,'APLICACIÃ“N DEL LENGUAJE TÃ‰CNICO DEL PATRIMONIO CULTURAL EN INGLÃ‰S.',5,'12',5,'I','4','IV','GTP71',80,0,23,NULL,1,1,'nd'),
(295,'DISEÃ‘O DE ESTRATEGIAS DE MERCADEO APLICADAS AL PATRIMONIO CULTURAL',5,'12',5,'I','5','IV','GTP72',100,0,24,NULL,1,1,'nd'),
(296,'PRODUCCIÃ“N DE EVENTOS CULTURALES',5,'12',5,'I','6','IV','GTP73',120,0,25,NULL,1,1,'nd'),
(297,'CREACIÃ“N DE EMPRESAS CULTURALES',5,'12',5,'I','6','III','GTP95',120,0,26,NULL,0,1,'no definido aÃºn'),
(298,'OfimÃ¡tica',7,'18',6,'III','2','III','ofi2022',50,50,0,NULL,1,1,'nd'),
(299,'Curso de INSAFORP Microsoft Power Point',7,'18',6,'III','3','II','01',20,20,1,NULL,1,1,'nd'),
(300,'Electricista Industrial',7,'18',6,'IV','2','II','022',200,200,2,NULL,1,1,'nd'),
(301,'ConfiguraciÃ³n y AdministraciÃ³n de Sistemas  Operativos',2,'19',7,'I','3','I','ELE332',24,36,1,NULL,1,1,'nd'),
(302,'InstalaciÃ³n de Medios y Dispositivos para Redes  Locales',2,'19',7,'I','4','I','ELE280',32,48,2,NULL,1,1,'nd'),
(303,'AnÃ¡lisis y Montaje de Circuitos de Corriente  Directa',2,'19',7,'I','3','I','ELE281',24,36,3,NULL,1,1,'nd'),
(304,'DiseÃ±o de Sistema ElÃ©ctrico en Ambientes  Computacionales',2,'19',7,'I','2','I','ELE333',16,24,4,NULL,1,1,'nd'),
(305,'MatemÃ¡ticas',2,'19',7,'I','4','I','BAS387',32,48,5,NULL,1,0,'nd'),
(306,'PrevenciÃ³n de Accidentes y Enfermedades  Ocupacionales',2,'19',7,'I','2','I','BAS99',24,16,6,NULL,1,0,'nd'),
(307,'Instalaciones ElÃ©ctricas Residenciales',2,'20',3,'I','4','I','ELE293',32,48,1,NULL,1,1,'nd'),
(308,'AnÃ¡lisis de Circuitos ElÃ©ctricos',2,'20',3,'I','4','I','ELE294',32,48,2,NULL,1,1,'nd'),
(309,'Seguridad ElÃ©ctrica',2,'20',3,'I','3','I','ELE306',24,36,3,NULL,1,1,'nd'),
(310,'ElaboraciÃ³n de Anteproyectos de InvestigaciÃ³n',2,'20',3,'I','2','I','BAS208',24,16,4,NULL,1,0,'no definido aÃºn'),
(311,'MatemÃ¡ticas',2,'20',3,'I','4','I','BAS387',32,48,5,NULL,1,0,'no definido aÃºn'),
(312,'InglÃ©s BÃ¡sico I',2,'20',3,'I','3','I','BAS42',30,30,6,NULL,1,0,'no definido aÃºn'),
(313,'Instalaciones ElÃ©ctricas Comerciales e Industriales',2,'20',3,'I','4','II','ELE295',32,48,7,NULL,1,1,'no definido aÃºn'),
(314,'ElaboraciÃ³n de Planos para Proyectos ElÃ©ctricos',2,'20',3,'I','4','II','ELE298',32,48,8,NULL,1,1,'no definido aÃºn'),
(315,'OperaciÃ³n y Mantenimiento de MÃ¡quinas de  ConversiÃ³n de EnergÃ­a',2,'20',3,'I','4','II','ELE296',32,48,9,NULL,1,1,'no definido aÃºn'),
(316,'ConstrucciÃ³n de Circuitos de Control ElectrÃ³nico de Potencia',2,'20',3,'I','4','II','ELE300',32,48,10,NULL,1,1,'no definido aÃºn'),
(317,'FÃ­sica',2,'20',3,'I','3','II','BAS40',30,30,11,NULL,1,0,'no definido aÃºn'),
(318,'InglÃ©s BÃ¡sico II',2,'20',3,'I','3','II','BAS43',30,30,12,NULL,1,0,'no definido aÃºn'),
(319,'TÃ©cnicas de Taller',2,'20',3,'I','3','III','ELE299',24,36,13,NULL,1,1,'no definido aÃºn'),
(320,'ConstrucciÃ³n y Mantenimiento de LÃ­neas de DistribuciÃ³n y Subestaciones de Servicio de  EnergÃ­a ElÃ©ctrica',2,'20',3,'I','4','III','ELE331',32,48,14,NULL,1,1,'no definido aÃºn'),
(321,'DiseÃ±o y Montaje de Sistemas de Control AutomÃ¡tico',2,'20',3,'I','4','III','ELE307',32,48,15,NULL,1,1,'no definido aÃºn'),
(322,'DiseÃ±o de IluminaciÃ³n de Interiores e InstalaciÃ³n de Sistemas DomÃ³ticos',2,'20',3,'I','3','III','ELE302',24,36,16,NULL,1,1,'no definido aÃºn'),
(323,'PrevenciÃ³n de Accidentes y Enfermedades  Ocupacionales',2,'20',3,'I','2','III','BAS99',24,16,17,NULL,1,0,'no definido aÃºn'),
(324,'FormulaciÃ³n y EvaluaciÃ³n de Planes de Negocios',2,'20',3,'I','4','III','BAS388',32,48,18,NULL,1,0,'no definido aÃºn'),
(325,'DiseÃ±o y Mantenimiento de Sistemas de EnergÃ­a Solar Fotovoltaica',2,'20',3,'I','4','IV','ELE301',32,48,19,NULL,1,1,'no definido aÃºn'),
(326,'AnÃ¡lisis de Eficiencia y Calidad de la EnergÃ­a en Edificaciones',2,'20',3,'I','4','IV','ELE303',32,48,20,NULL,1,1,'no definido aÃºn'),
(327,'FormulaciÃ³n y AdministraciÃ³n de Proyectos de Sistemas ElÃ©ctricos',2,'20',3,'I','4','IV','ELE304',32,48,21,NULL,1,1,'no definido aÃºn'),
(328,'ProgramaciÃ³n y Mantenimiento a Sistemas MecatrÃ³nicos',2,'20',3,'I','3','IV','ELE305',24,36,22,NULL,1,1,'no definido aÃºn'),
(329,'DirecciÃ³n del Comportamiento Humano en el Ambiente Laboral',2,'20',3,'I','2','IV','BAS210',24,16,23,NULL,1,0,'no definido aÃºn'),
(330,'Ensamble y ConfiguraciÃ³n de Equipos InformÃ¡ticos',2,'19',7,'I','4','II','ELE334',32,48,7,NULL,1,1,'no definido aÃºn'),
(331,'ConfiguraciÃ³n de Switches y Routers para Redes Locales',2,'19',7,'I','4','II','ELE341',32,48,8,NULL,1,1,'no definido aÃºn'),
(332,'DiseÃ±o y ConstrucciÃ³n de Circuitos ElectrÃ³nicos AnalÃ³gicos',2,'19',7,'I','3','II','ELE335',24,36,9,NULL,1,1,'no definido aÃºn'),
(333,'DiseÃ±o y ConstrucciÃ³n de Circuitos ElectrÃ³nicos Digitales',2,'19',7,'I','3','II','ELE286',24,36,10,NULL,1,1,'no definido aÃºn'),
(334,'CreaciÃ³n de Algoritmos y Programas Computacionales',2,'19',7,'I','3','II','ELE282',24,36,11,NULL,1,1,'no definido aÃºn'),
(335,'InglÃ©s BÃ¡sico I',2,'19',7,'I','3','II','BAS42',30,30,12,NULL,1,0,'no definido aÃºn'),
(336,'Configuración y Administración de Servidores',2,'19',7,'I','4','I','ELE336',32,48,13,NULL,1,1,'no definido aún'),
(337,'AnÃ¡lisis y SoluciÃ³n de Fallas en Equipos de CÃ³mputo',2,'19',7,'I','4','III','ELE289',32,48,14,NULL,1,1,'no definido aÃºn'),
(338,'Creación de Circuitos Electrónicos con Microcontroladores',2,'19',7,'I','4','I','ELE237',32,48,15,NULL,1,1,'no definido aún'),
(339,'Desarrollo de Habilidades de Fabricación Electrónica',2,'19',7,'I','2','I','ELE337',16,24,16,NULL,1,1,'no definido aún'),
(340,'Elaboración de Anteproyectos de Investigación',2,'19',7,'I','2','I','BAS208',24,16,17,NULL,1,0,'no definido aún'),
(341,'InglÃ©s BÃ¡sico II',2,'19',7,'I','3','III','BAS43',30,30,18,NULL,1,0,'no definido aÃºn'),
(342,'AplicaciÃ³n de Principios de Seguridad InformÃ¡tica',2,'19',7,'I','4','IV','ELE291',32,48,19,NULL,1,1,'no definido aÃºn'),
(343,'DiseÃ±o de Aplicaciones de Internet de las Cosas',2,'19',7,'I','4','IV','ELE338',32,48,20,NULL,1,1,'no definido aÃºn'),
(344,'DiseÃ±o y ProgramaciÃ³n de Sistemas Embebidos',2,'19',7,'I','4','IV','ELE339',32,48,21,NULL,1,1,'no definido aÃºn'),
(345,'AdministraciÃ³n y PlanificaciÃ³n TÃ©cnica de Planes de Mantenimiento a Equipo InformÃ¡tico ',2,'19',7,'I','2','IV','ELE340',16,24,22,NULL,1,1,'no definido aÃºn'),
(346,'DirecciÃ³n del Comportamiento Humano en el Ambiente Laboral',2,'19',7,'I','2','IV','BAS210',24,16,23,NULL,1,0,'no definido aÃºn'),
(347,'FormulaciÃ³n y EvaluaciÃ³n de Planes de Negocio',2,'19',7,'I','4','IV','BAS388',32,48,24,NULL,1,0,'no definido aÃºn'),
(348,'2017-Desarrollo de Aplicaciones Usando Tecnologías Emergentes',1,'17',9,'I','5','IV','COM83',30,70,17,NULL,1,1,'no definido aún'),
(349,'Desarrollo de Aplicaciones de Escritorio – 2024',1,'23',9,'II','4','II','COM144',16,64,8,NULL,1,1,'no definido aún'),
(350,'Puesta en Marcha de la Microempresa en Asociada Cooperativa-2024',1,'23',9,'II','4','II','BAS437',24,56,11,NULL,1,1,'no definido aún'),
(351,'Diseño de Bases de Datos Relacionales – 2024',1,'23',9,'II','4','II','COM143',16,64,6,NULL,1,1,'no definido aún'),
(352,'Gestión de Herramientas Web y Contenido Multimedia – 2024',1,'23',9,'II','4','II','COM154',16,64,7,NULL,1,1,'no definido aún'),
(353,'Configuración de Redes Informáticas-2024',1,'23',9,'I','4','II','COM111',16,64,9,NULL,1,1,'no definido aún'),
(354,'Conversaciones en inglés sobre arquitectura de software y bases de datos – 2024',1,'23',9,'I','4','II','BAS435',24,56,10,NULL,1,1,'no definido aún'),
(357,'Instalaciones Eléctricas Residenciales',2,'25',3,'I','4','I','ELEC11425',32,48,1,NULL,1,1,'nd'),
(358,'Análisis de Circuitos Eléctricos',2,'25',3,'I','4','I','ELEC12425',32,48,2,NULL,1,1,'nd'),
(359,'Seguridad Eléctrica',2,'25',3,'I','3','I','ELEC13325',24,36,3,NULL,1,1,'nd'),
(427,'Instalaciones Eléctricas Residenciales',2,'26',11,'I','4','I','ELE293',32,48,1,NULL,1,1,'nd'),
(361,'Matemática Básica',2,'25',3,'I','4','I','ELEC15425',32,48,5,NULL,1,0,'nd'),
(362,'Inglés Básico I',2,'25',3,'I','4','I','ELEC16325',32,48,6,NULL,1,0,'nd'),
(363,'Instalaciones Eléctricas Comerciales e Industriales',2,'25',3,'I','4','II','ELEC17425',32,48,7,NULL,1,1,'nd'),
(364,'Elaboración de Planos para Proyectos Eléctricos',2,'25',3,'I','4','II','ELEC18425',32,48,8,NULL,1,1,'nd'),
(365,'Operación y Mantenimiento de Máquinas de Conversión de Energía',2,'25',3,'I','4','II','ELEC19425',32,48,9,NULL,1,1,'nd'),
(366,'Construcción de Circuitos de Control Electrónico de Potencia ',2,'25',3,'I','4','II','ELEC110425',32,48,10,NULL,1,1,'nd'),
(367,'Física',2,'25',3,'I','3','II','ELEC111325',30,30,11,NULL,1,0,'nd'),
(368,'Inglés Básico II',2,'25',3,'I','4','II','ELEC112325',32,48,12,NULL,1,0,'nd'),
(369,'Técnicas de Taller',2,'25',3,'I','3','III','ELEC113325',24,36,13,NULL,1,1,'nd'),
(370,'Diseño y Mantenimiento de Sistemas de Energía Solar Fotovoltaica',2,'25',3,'I','4','III','ELEC114425',32,48,14,NULL,1,1,'nd'),
(371,'Diseño y Montaje de Sistemas de Control Automático',2,'25',3,'I','4','III','ELEC115425',32,48,15,NULL,1,1,'nd'),
(372,'Redes Eléctricas Inteligentes',2,'25',3,'I','4','III','ELEC116425',32,48,16,NULL,1,1,'nd'),
(373,'Prevención de Accidentes y Enfermedades Ocupacionales',2,'25',3,'I','2','III','ELEC117225',16,24,17,NULL,1,0,'nd'),
(374,'Formulación y Evaluación de Planes de Negocio',2,'25',3,'I','4','III','ELEC118425',32,48,18,NULL,1,0,'nd'),
(375,'Construcción y Mantenimiento de Líneas de Distribucción y Subestaciones de Servicio de Energía Eléctrica',2,'25',3,'I','4','IV','ELEC119425',32,48,19,NULL,1,1,'nd'),
(376,'Análisis de Eficiencia y Calidad de la Energía en Edificaciones',2,'25',3,'I','4','IV','ELEC120425',32,48,20,NULL,1,1,'nd'),
(377,'Formulación y Administración de Proyectos de Sistemas Eléctricos',2,'25',3,'I','4','IV','ELEC121425',32,48,21,NULL,1,1,'nd'),
(378,'Programación y Mantenimiento a Sistemas Mecatrónicos',2,'25',3,'I','3','IV','ELEC122325',24,36,22,NULL,1,1,'nd'),
(379,'Dirección del Comportamiento Humano en el Ambiente Laboral',2,'25',3,'I','3','IV','ELEC123225',30,30,23,NULL,1,0,'nd'),
(383,'Desarrollo de Lógica de Programación',1,'23',9,'I','4','I','COM25',16,64,1,NULL,1,1,'nd'),
(380,'Diseño de Páginas Web',1,'23',9,'I','4','I','COM73',16,64,1,NULL,1,1,'nd'),
(384,'Análisis y Diseño de Sistemas',1,'23',9,'I','4','I','COM113',16,64,2,NULL,1,1,'no definido aún'),
(385,'Conversación en Inglés sobre Sistemas Informáticos y Desarrollo de Páginas Web',1,'23',9,'I','4','I','BAS434',24,56,4,NULL,1,0,'nd'),
(386,'Diseño de Planes de Negocio en Asociatividad Cooperativa',1,'23',9,'I','4','I','BAS436',24,56,5,NULL,1,0,'nd'),
(387,'Desarrollo de Aplicaciones Web',1,'23',9,'I','4','III','COM145',16,64,12,NULL,1,1,'nd'),
(388,'Mantenimiento de Software y Hardware',1,'23',9,'I','4','III','COM132',16,64,13,NULL,1,1,'nd'),
(389,'Diseño de Base de Datos no Relacionales',1,'23',9,'I','4','III','COM147',16,64,14,NULL,1,1,'nd'),
(390,'Configuración de Servidores para Implementación de Sistemas',1,'23',9,'I','4','III','COM148',16,64,15,NULL,1,1,'nd'),
(391,'Desarrollo del Pensamiento Numérico y Algebraico Aplicado a la Informática',1,'23',9,'I','4','III','BAS438',16,64,16,NULL,1,0,'no definido aún'),
(392,'Dirección del Comportamiento Humano en el Ambiente Laboral',1,'23',9,'I','2','III','BAS296',16,24,17,NULL,1,0,'nd'),
(393,'Desarrollo de Aplicaciones Móviles',1,'23',9,'I','4','IV','COM155',16,64,18,NULL,1,1,'nd'),
(394,'Aplicación de Metodologías Águiles y Control de la Calidad del Software',1,'23',9,'I','4','IV','COM150',16,64,19,NULL,1,1,'nd'),
(395,'Utilización de Frameworks para el Desarrollo de Software',1,'23',9,'I','4','IV','COM151',16,64,20,NULL,1,1,'nd'),
(396,'Descripción de Fenómenos Físicos Aplicados a la Computación',1,'23',9,'I','4','IV','BAS439',16,64,21,NULL,1,0,'nd'),
(397,'Desarrollo del Pensamiento Estadístico y Probabilístico',1,'23',9,'I','4','IV','BAS440',16,64,22,NULL,1,0,'nd'),
(398,'Configuración y Administración de Sistemas Operativos',2,'24',7,'I','3','I','ELE390',12,48,1,NULL,1,1,'no definido aún'),
(399,'Instalación de Medios y Dispositivos para Redes Locales',2,'24',7,'I','4','I','ELE280',32,48,2,NULL,1,1,'no definido aún'),
(400,'Análisis y Montaje de Circuitos de Corriente Directa',2,'24',7,'I','3','I','ELE391',12,48,3,NULL,1,1,'no definido aún'),
(401,'Creación de Programas Computacionales',2,'24',7,'I','4','I','ELE392',32,48,4,NULL,1,1,'no definido aún'),
(402,'Matemática Básica',2,'24',7,'I','4','I','BAS458',32,48,5,NULL,1,0,'no definido aún'),
(403,'Comunicación Oral y Escrita',2,'24',7,'I','2','I','BAS298',16,24,6,NULL,1,0,'no definido aún'),
(404,'Inglés Básico I',2,'24',7,'I','4','I','BAS452',32,48,7,NULL,1,0,'no definido aún'),
(405,'Ensamble y Configuración de Equipos Informáticos',2,'24',7,'I','4','II','ELE334',32,48,8,NULL,1,1,'no definido aún'),
(406,'Configuración de Switches y Routers para Redes Locales',2,'24',7,'I','4','II','ELE341',32,48,9,NULL,1,1,'no definido aún'),
(407,'Diseño y Construcción de Circuitos Electrónicos Analógicos',2,'24',7,'I','3','II','ELE393',12,48,10,NULL,1,1,'no definido aún'),
(408,'Diseño y Construcción de Circuitos Electrónicos Digitales',2,'24',7,'I','3','II','ELE394',12,48,11,NULL,1,1,'no definido aún'),
(409,'Desarrollo de Habilidades de Fabricación Electrónica',2,'24',7,'I','2','II','ELE395',10,30,12,NULL,1,1,'no definido aún'),
(410,'Elaboración de Anteproyectos de Investigación',2,'25',3,'I','2','I','ELEC314225',24,16,4,NULL,1,0,'no definido aún'),
(411,'Inglés Básico II',2,'24',7,'I','4','II','BAS453',32,48,14,NULL,1,0,'no definido aún'),
(412,'Configuración y Administración de Servidores',2,'24',7,'I','4','III','ELE336',32,48,15,NULL,1,1,'no definido aún'),
(413,'Diágnostico y Reparación de Computadoras de Escritorio y Móviles',2,'24',7,'I','3','III','ELE396',12,48,16,NULL,1,1,'no definido aún'),
(414,'Creación de Circuitos Electrónicos con Microcontroladores',2,'24',7,'I','4','III','ELE237',32,48,17,NULL,1,1,'no definido aún'),
(415,'Diseño de Sistema Eléctrico en Ambientes Computacionales',2,'24',7,'I','3','III','ELE397',12,48,18,NULL,1,1,'no definido aún'),
(416,'Formulación y Evaluación de Planes de Negocios',2,'24',7,'I','4','III','BAS388',32,48,19,NULL,1,0,'no definido aún'),
(417,'Prevención de Accidentes y Enfermedades Ocupacionales',2,'24',7,'I','2','III','BAS297',16,24,20,NULL,1,0,'no definido aún'),
(418,'Inglés Intermedio I',2,'24',7,'I','4','III','BAS454',32,48,21,NULL,1,0,'no definido aún'),
(419,'Aplicación de Principios de Seguridad Informática',2,'24',7,'I','4','IV','ELE291',32,48,22,NULL,1,1,'no definido aún'),
(420,'Análisis y Solución de Fallas en Equipos Periféricos',2,'24',7,'I','3','IV','ELE398',12,48,23,NULL,1,1,'no definido aún'),
(421,'Diseño de Aplicaciones de Internet de las Cosas',2,'24',7,'I','4','IV','ELE338',32,48,24,NULL,1,1,'no definido aún'),
(422,'Diseño y Programación de Sistemas Embebidos',2,'24',7,'I','4','IV','ELE339',32,48,25,NULL,1,1,'no definido aún'),
(423,'Elaboración de Anteproyectos de Investigación',2,'24',7,'I','2','II','BAS100',16,24,13,NULL,1,0,'no definido aún'),
(424,'Administración y Planificación Técnica de Proyectos',2,'24',7,'I','2','IV','ELE399',16,24,26,NULL,1,0,'no definido aún'),
(425,'Dirección del Comportamiento Humano en el Ambiente Laboral',2,'24',7,'I','3','IV','BAS449',30,30,27,NULL,1,0,'no definido aún'),
(426,'Inglés Intermedio II',2,'24',7,'I','4','IV','BAS455',32,48,28,NULL,1,0,'no definido aún'),
(428,'Análisis de Circuitos Eléctricos',2,'26',11,'I','4','I','ELE294',32,48,2,NULL,1,1,'nd'),
(429,'Seguridad Eléctrica',2,'26',11,'I','3','I','ELE306',24,36,3,NULL,1,1,'nd'),
(430,'Matemáticas',2,'26',11,'I','4','I','BAS387',32,48,5,NULL,1,0,'nd'),
(431,'Elaboración de Anteproyectos de Investigación',2,'26',11,'I','2','I','BAS208',24,16,4,NULL,1,0,'nd'),
(432,'Inglés Básico I',2,'26',11,'I','3','I','BAS42',30,30,6,NULL,1,0,'nd'),
(433,'Instalaciones Eléctricas Comerciales e Industriales',2,'26',11,'I','4','II','ELE295',32,48,7,NULL,1,1,'nd'),
(434,'Elaboración de Planos para Proyectos Eléctricos',2,'26',11,'I','4','II','ELE298',32,48,8,NULL,1,1,'nd'),
(435,'Operación y Mantenimiento de Máquinas de Conversión de Energía',2,'26',11,'I','4','II','ELE296',32,48,9,NULL,1,1,'nd'),
(436,'Construcción de Circuitos de Control Electrónico de Potencia ',2,'26',11,'I','4','II','ELE300',32,48,10,NULL,1,1,'nd'),
(437,'Física',2,'26',11,'I','3','II','BAS40',30,30,11,NULL,1,0,'nd'),
(438,'Técnicas de Taller',2,'26',11,'I','3','III','ELE299',24,36,13,NULL,1,1,'no definido aún'),
(439,'Construcción y Mantenimiento de Líneas de Distribucción y Subestaciones de Servicio de Energía Eléctrica',2,'26',11,'I','4','III','ELE331',32,48,14,NULL,1,1,'nd'),
(440,'Diseño y Montaje de Sistemas de Control Automático',2,'26',11,'I','4','III','ELE307',32,48,15,NULL,1,1,'nd'),
(441,'Diseño de Iluminación de  Interiores e Instalación de Sistemas Domóticos',2,'26',11,'I','3','III','ELE302',24,36,16,NULL,1,1,'nd'),
(442,'Prevención de Accidentes y Enfermedades Ocupacionales',2,'26',11,'I','2','III','BAS99',24,16,17,NULL,1,0,'nd'),
(443,'Formulación y Evaluación de Planes de Negocio',2,'26',11,'I','4','III','BAS388',32,48,18,NULL,1,0,'nd'),
(444,'Diseño y Mantenimiento de Sistemas de Energía Solar Fotovoltaica',2,'26',11,'I','4','IV','ELE301',32,48,19,NULL,1,1,'nd'),
(445,'Análisis de Eficiencia y Calidad de la Energía en Edificaciones',2,'26',11,'I','4','IV','ELE303',32,48,20,NULL,1,1,'nd'),
(446,'Formulación y Administración de Proyectos de Sistemas Eléctricos',2,'26',11,'I','4','IV','ELE304',32,48,21,NULL,1,1,'nd'),
(447,'Programación y Mantenimiento a Sistemas Mecatrónicos',2,'26',11,'I','3','IV','ELE305',24,36,22,NULL,1,1,'nd'),
(448,'Dirección del Comportamiento Humano en el Ambiente Laboral',2,'26',11,'I','2','IV','BAS210',24,16,23,NULL,1,0,'nd'),
(449,'Inglés Básico II',2,'26',11,'I','3','II','BAS43',30,30,12,NULL,1,0,'nd'),
(450,'Instalaciones Eléctricas Residenciales',2,'27',11,'I','4','I','ELE293',32,48,1,NULL,1,1,'no definido aún'),
(451,'Análisis de Circuitos Eléctricos',2,'27',11,'I','4','I','ELE294',32,48,2,NULL,1,1,'no definido aún'),
(452,'Seguridad Eléctrica',2,'27',11,'I','3','I','ELE306',24,36,3,NULL,1,1,'no definido aún'),
(453,'Elaboración de Anteproyectos de Investigación',2,'27',11,'I','2','I','BAS208',24,16,4,NULL,1,0,'no definido aún'),
(454,'Matemática Básica',2,'27',11,'I','4','I','BAS458',32,48,5,NULL,1,0,'no definido aún'),
(455,'Inglés Básico I',2,'27',11,'I','4','I','BAS452',32,48,6,NULL,1,0,'no definido aún'),
(456,'Elaboración de Planos para Proyectos Eléctricos',2,'27',11,'I','4','II','ELE298',32,48,8,NULL,1,1,'no definido aún'),
(457,'Operación y Mantenimiento de Máquinas de Conversión de Energía',2,'27',11,'I','4','II','ELE296',32,48,9,NULL,1,1,'no definido aún'),
(458,'Instalaciones Eléctricas Comerciales e Industriales',2,'27',11,'I','4','II','ELE295',32,48,7,NULL,1,1,'no definido aún'),
(459,'Construcción de Circuitos de Control Electrónico de Potencia ',2,'27',11,'I','4','II','ELE300',32,48,8,NULL,1,1,'no definido aún'),
(460,'Física',2,'27',11,'I','3','II','BAS40',30,30,11,NULL,1,0,'no definido aún'),
(461,'Inglés Básico II',2,'27',11,'I','4','II','BAS453',32,48,12,NULL,1,0,'no definido aún'),
(462,'Técnicas de Taller',2,'27',11,'I','3','III','ELE299',24,36,13,NULL,1,1,'no definido aún'),
(463,'Diseño y Mantenimiento de Sistemas de Energía Solar Fotovoltaica',2,'27',11,'I','4','III','ELE301',32,48,14,NULL,1,1,'no definido aún'),
(464,'Diseño y Montaje de Sistemas de Control Automático',2,'27',11,'I','4','III','ELE307',32,48,15,NULL,1,1,'no definido aún'),
(465,'Redes Eléctricas Inteligentes',2,'27',11,'I','4','III','ELE388',32,48,16,NULL,1,1,'no definido aún'),
(466,'Prevención de Accidentes y Enfermedades Ocupacionales',2,'27',11,'I','2','III','BAS297',16,24,17,NULL,1,0,'no definido aún'),
(467,'Formulación y Evaluación de Planes de Negocios',2,'27',11,'I','4','III','BAS388',32,48,18,NULL,1,0,'no definido aún'),
(468,'Construcción y Mantenimiento de Líneas de Distribucción y Subestaciones de Servicio de Energía Eléctrica',2,'27',11,'I','4','IV','ELE331',32,48,19,NULL,1,1,'no definido aún'),
(469,'Análisis de Eficiencia y Calidad de la Energía en Edificaciones',2,'27',11,'I','4','IV','ELE303',32,48,19,NULL,1,1,'no definido aún'),
(470,'Formulación y Administración de Proyectos de Sistemas Eléctricos',2,'27',11,'I','4','IV','ELE304',32,48,21,NULL,1,1,'no definido aún'),
(471,'Programación y Mantenimiento a Sistemas Mecatrónicos',2,'27',11,'I','3','IV','ELE305',24,36,22,NULL,1,1,'no definido aún'),
(472,'Dirección del Comportamiento Humano en el Ambiente Laboral',2,'27',11,'I','3','IV','BAS449',30,30,23,NULL,1,0,'no definido aún');

/*Table structure for table `sinv_imagenes_bien` */

DROP TABLE IF EXISTS `sinv_imagenes_bien`;

CREATE TABLE `sinv_imagenes_bien` (
  `id_imagen` int(11) NOT NULL AUTO_INCREMENT,
  `id_bien` int(11) NOT NULL,
  `ruta_imagen` varchar(255) NOT NULL,
  `fecha_subida` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_imagen`),
  KEY `idx_imagen_bien` (`id_bien`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `sinv_imagenes_bien` */

/*Table structure for table `sinv_inventario_general` */

DROP TABLE IF EXISTS `sinv_inventario_general`;

CREATE TABLE `sinv_inventario_general` (
  `id_bien` int(11) NOT NULL AUTO_INCREMENT,
  `codigo_interno` varchar(50) DEFAULT NULL COMMENT 'Código interno ITCA',
  `codigo_itca` varchar(50) DEFAULT NULL COMMENT 'Código de activo oficial',
  `codigo_mineducyt` varchar(50) DEFAULT NULL,
  `descripcion` text NOT NULL,
  `marca` varchar(100) DEFAULT NULL,
  `modelo` varchar(100) DEFAULT NULL,
  `serie` varchar(100) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `id_responsable` int(11) DEFAULT NULL COMMENT 'FK a docente.id_docente',
  `id_ubicacion` int(11) DEFAULT NULL COMMENT 'FK a aula.id_aula',
  `responsable_nombre` varchar(150) DEFAULT NULL COMMENT 'Copia del nombre por historial',
  `ubicacion_nombre` varchar(150) DEFAULT NULL COMMENT 'Copia de ubicación por historial',
  `valor_compra` decimal(10,2) DEFAULT NULL,
  `anio_adquisicion` int(4) DEFAULT NULL,
  `estado_bien` varchar(50) DEFAULT 'BUENO' COMMENT 'BUENO, REGULAR, MALO, BAJA',
  `tipo_bien` enum('ACTIVO_FIJO','CONTROL_ADMIN','NO_ACTIVO') DEFAULT 'ACTIVO_FIJO',
  `proyecto_origen` varchar(100) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  PRIMARY KEY (`id_bien`)
) ENGINE=MyISAM AUTO_INCREMENT=39 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

/*Data for the table `sinv_inventario_general` */

insert  into `sinv_inventario_general`(`id_bien`,`codigo_interno`,`codigo_itca`,`codigo_mineducyt`,`descripcion`,`marca`,`modelo`,`serie`,`color`,`id_responsable`,`id_ubicacion`,`responsable_nombre`,`ubicacion_nombre`,`valor_compra`,`anio_adquisicion`,`estado_bien`,`tipo_bien`,`proyecto_origen`,`observaciones`) values 
(1,'COMP-2024-001','ACT-2024-0001','MINED-2024-001','Computadora de escritorio Dell OptiPlex 7090','DELL','OptiPlex 7090','SN7090X2024001','NEGRO',1,1,'Juan Carlos Pérez','Laboratorio de Informática 1',850.00,2024,'BUENO','ACTIVO_FIJO','Proyecto Modernización TIC 2024','Equipo con Windows 11 Pro, 16GB RAM, SSD 512GB'),
(2,'COMP-2024-002','ACT-2024-0002','MINED-2024-002','Laptop HP ProBook 450 G9','HP','ProBook 450 G9','SNHP450G9-2024002','PLATA',2,2,'María González','Aula 201',920.00,2024,'BUENO','ACTIVO_FIJO','Proyecto Modernización TIC 2024','Laptop para docente con Windows 11, 16GB RAM'),
(3,'COMP-2023-045','ACT-2023-0045','MINED-2023-045','Computadora de escritorio Lenovo ThinkCentre M720q','LENOVO','ThinkCentre M720q','SNM720Q2023045','NEGRO',3,2,'Roberto Martínez','Laboratorio de Informática 2',780.00,2023,'BUENO','ACTIVO_FIJO','Dotación Equipos 2023','Core i5, 8GB RAM, HDD 1TB'),
(4,'PROY-2024-010','ACT-2024-0010','MINED-2024-010','Proyector Epson PowerLite L200W','EPSON','PowerLite L200W','SNEPSONL200W010','BLANCO',1,1,'Juan Carlos Pérez','Laboratorio de Informática 1',650.00,2024,'BUENO','ACTIVO_FIJO','Equipamiento Multimedia 2024','Proyector láser WXGA 4200 lúmenes'),
(5,'PROY-2023-025','ACT-2023-0025','MINED-2023-025','Proyector BenQ MH535A','BENQ','MH535A','SNBENQMH535A025','BLANCO',4,3,'Ana Rodríguez','Aula Magna',520.00,2023,'REGULAR','ACTIVO_FIJO','Dotación Equipos 2023','Requiere cambio de lámpara pronto'),
(6,'IMP-2024-008','ACT-2024-0008','MINED-2024-008','Impresora multifuncional HP LaserJet Pro MFP M428fdw','HP','LaserJet Pro MFP M428fdw','SNHPM428FDW008','GRIS',5,4,'Carlos López','Oficina Administrativa',480.00,2024,'BUENO','ACTIVO_FIJO','Renovación Equipos Oficina','Impresora láser con WiFi, escáner y fax'),
(7,'IMP-2023-018','ACT-2023-0018','MINED-2023-018','Impresora HP OfficeJet Pro 9015e','HP','OfficeJet Pro 9015e','SNHP9015E018','NEGRO',6,3,'Laura Hernández','Coordinación Académica',320.00,2023,'BUENO','ACTIVO_FIJO',NULL,'Impresora de tinta con sistema de recarga'),
(8,'MOB-2024-050','ACT-2024-0050',NULL,'Escritorio ejecutivo de madera','MUEBLES EL HOGAR','EXE-100',NULL,'CAOBA',7,1,'Director General','Dirección General',450.00,2024,'BUENO','ACTIVO_FIJO','Mobiliario Oficinas 2024','Escritorio de 1.60m x 0.80m con cajonera'),
(9,'MOB-2023-120','ACT-2023-0120',NULL,'Mesa de reuniones para 8 personas','OFICENTRO','MR-800',NULL,'NEGRO',NULL,4,NULL,'Sala de Reuniones',680.00,2023,'BUENO','ACTIVO_FIJO','Mobiliario Oficinas 2023','Mesa rectangular de 2.40m x 1.20m'),
(10,'MOB-2022-085','ACT-2022-0085',NULL,'Silla ergonómica de oficina','ERGOFLEX','ERGO-PRO-200',NULL,'NEGRO',2,2,'María González','Aula 201',180.00,2022,'REGULAR','ACTIVO_FIJO',NULL,'Requiere ajuste del respaldo'),
(11,'LAB-2024-015','ACT-2024-0015','MINED-2024-015','Osciloscopio digital Tektronix TBS2104','TEKTRONIX','TBS2104','SNTBS2104015','NEGRO/AZUL',8,4,'Ing. Pedro Flores','Laboratorio de Electrónica',1250.00,2024,'BUENO','ACTIVO_FIJO','Equipamiento Laboratorios 2024','Osciloscopio de 4 canales, 100MHz'),
(12,'LAB-2023-032','ACT-2023-0032','MINED-2023-032','Multímetro digital Fluke 87V','FLUKE','87V','SN87V032','AMARILLO/NEGRO',8,4,'Ing. Pedro Flores','Laboratorio de Electrónica',420.00,2023,'BUENO','ACTIVO_FIJO','Equipamiento Laboratorios 2023','Multímetro de precisión industrial'),
(13,'AC-2024-005','ACT-2024-0005',NULL,'Aire acondicionado LG Inverter 18000 BTU','LG','S4-W18KL3AA','SNLGAC18K005','BLANCO',NULL,1,NULL,'Laboratorio de Informática 1',720.00,2024,'BUENO','ACTIVO_FIJO','Climatización 2024','Sistema Split con tecnología inverter'),
(14,'AC-2023-012','ACT-2023-0012',NULL,'Aire acondicionado Samsung 12000 BTU','SAMSUNG','AR12TXHQASINXD','SNSAMAC12K012','BLANCO',NULL,2,NULL,'Aula 201',580.00,2023,'BUENO','ACTIVO_FIJO','Climatización 2023','Mantenimiento preventivo al día'),
(15,'RED-2024-020','ACT-2024-0020','MINED-2024-020','Switch Cisco Catalyst 2960-X 48 puertos','CISCO','WS-C2960X-48TS-L','SNCS2960X020','GRIS',9,3,'Ing. Carlos Ramírez','Sala de Servidores',1850.00,2024,'BUENO','ACTIVO_FIJO','Infraestructura de Red 2024','Switch Gigabit capa 2, 48 puertos'),
(16,'RED-2024-021','ACT-2024-0021','MINED-2024-021','Access Point Ubiquiti UniFi AP AC Pro','UBIQUITI','UAP-AC-PRO','SNUAP-AC-PRO021','BLANCO',9,1,'Ing. Carlos Ramírez','Laboratorio de Informática 1',145.00,2024,'BUENO','ACTIVO_FIJO','Infraestructura de Red 2024','Access point WiFi 5 dual band'),
(17,'AV-2023-008','ACT-2023-0008','MINED-2023-008','Sistema de audio JBL para aula','JBL','EON610','SNJBLEON610008','NEGRO',4,3,'Ana Rodríguez','Aula Magna',680.00,2023,'BUENO','ACTIVO_FIJO','Equipamiento Multimedia 2023','Sistema de parlantes amplificados'),
(18,'AV-2024-012','ACT-2024-0012','MINED-2024-012','Cámara de videoconferencia Logitech Rally','LOGITECH','Rally','SNLOGRALLY012','NEGRO',NULL,4,NULL,'Sala de Reuniones',1350.00,2024,'BUENO','ACTIVO_FIJO','Equipamiento Videoconferencias','Cámara 4K UHD con zoom 15x'),
(19,'CTRL-2024-100',NULL,NULL,'Calculadora científica Casio FX-991EX','CASIO','FX-991EX','SNCASFX991100','NEGRO',2,3,'María González','Aula 201',35.00,2024,'BUENO','CONTROL_ADMIN',NULL,'Para uso en clases de matemáticas'),
(20,'CTRL-2023-085',NULL,NULL,'Ventilador de pedestal','SAMURAI','VS-1620',NULL,'BLANCO',1,1,'Juan Carlos Pérez','Laboratorio de Informática 1',45.00,2023,'REGULAR','CONTROL_ADMIN',NULL,'Requiere limpieza de aspas'),
(21,'COMP-2020-150','ACT-2020-0150','MINED-2020-150','Computadora de escritorio HP Compaq Elite 8300','HP','Compaq Elite 8300','SNHP8300150','NEGRO',NULL,1,NULL,'Bodega',550.00,2020,'MALO','ACTIVO_FIJO',NULL,'Equipo obsoleto, pendiente de baja'),
(22,'PROY-2018-045','ACT-2018-0045','MINED-2018-045','Proyector Epson PowerLite S31+','EPSON','PowerLite S31+','SNEPSONS31045','BLANCO',NULL,2,NULL,'Bodega',380.00,2018,'BAJA','ACTIVO_FIJO',NULL,'Equipo dado de baja por falla irreparable'),
(23,'COMP-2024-075','ACT-2024-0075','MINED-2024-075','Laptop Dell Latitude 5430','DELL','Latitude 5430','SNDELL5430075','GRIS',10,4,'Dra. Patricia Vásquez','Oficina Investigación',1100.00,2024,'BUENO','ACTIVO_FIJO','Proyecto I+D CONACYT 2024','Equipo para investigación científica'),
(24,'LAB-2024-030','ACT-2024-0030','MINED-2024-030','Impresora 3D Creality Ender 3 V2','CREALITY','Ender 3 V2','SNENDER3V2030','NEGRO/NARANJA',11,2,'Ing. Fernando Castro','Laboratorio de Manufactura',280.00,2024,'BUENO','ACTIVO_FIJO','Proyecto Innovación Tecnológica','Impresora 3D FDM, volumen 220x220x250mm'),
(25,'AF-2026-001','ITCA-INV-7845','MIN-EL-99231','Computadora portátil para uso administrativo y académico.','Dell','Eclipse','DL5420-9XK73P','Negro',1,NULL,'Administración',NULL,850.00,2026,'BUENO','ACTIVO_FIJO','Modernización de Laboratorios','Equipo en óptimas condiciones, incluye cargador original.'),
(32,'SA-1150','',NULL,'MICROFONO SISTEMA DE 2 MICROFONOS INALAMBRICO CAP DISTANCIA 30 MTS','RADIO SHACK','3202703','44117','NEGRO',1,NULL,'juan perez','BODEGA DE PATRIMONIO',0.00,2026,'BUENO','ACTIVO_FIJO','',''),
(33,'SA-1353','',NULL,'CONSOLA DE SONIDO CON CABLES','PEAVEY','PV10USB','0ABBC150113','GRIS',1,NULL,'ana perez','BODEGA PATRIMONIO',0.00,2026,'BUENO','ACTIVO_FIJO','',''),
(34,'SA-2942',NULL,'0260-5606-001','COMPRESOR 60,000 BTU R22 ','S/M','S/M','S/S','GRIS',1,NULL,'ana ruiz','ÁREA EXTERNA DE TALLER PLC',720.00,2017,'MALO','ACTIVO_FIJO','CIDE I','SE QUITÓ DE LA BASE, TENÍA CÓDIGO ITCA: 234201669'),
(35,'SA-0406',NULL,'0260-5412-001','PANTALLA LED 55\" NARDT 4K ','LG','55UJ6200','709RMEN4J596','NEGRO',1,NULL,'juan duran','SALA DOC. PATRIMONIO CULTURAL',883.47,2017,'BUENO','ACTIVO_FIJO','CIDE I','SE QUITÓ DE LA BASE, TENÍA CÓDIGO ITCA: 234209511'),
(36,'ssssssssss','ITCA-INV-7845','MIN-EL-99231','lkjhkjhgkjgmn ','Dell','Eclipse','DL5420-9XK73P','Negro',1,NULL,'Administración',NULL,5765.00,2026,'BUENO','ACTIVO_FIJO','Modernización de Laboratorios','jhgfjhgjhgjhghgjh'),
(37,'SA-1150','',NULL,'MICROFONO SISTEMA DE 2 MICROFONOS INALAMBRICO CAP DISTANCIA 30 MTS','RADIO SHACK','3202703','44117','NEGRO',1,NULL,'juan perez','BODEGA DE PATRIMONIO',0.00,2026,'BUENO','ACTIVO_FIJO','',''),
(38,'SA-1353','',NULL,'CONSOLA DE SONIDO CON CABLES','PEAVEY','PV10USB','0ABBC150113','GRIS',1,NULL,'ana perez','BODEGA PATRIMONIO',0.00,2026,'BUENO','ACTIVO_FIJO','','');

/*Table structure for table `sinv_movimientos_cabecera` */

DROP TABLE IF EXISTS `sinv_movimientos_cabecera`;

CREATE TABLE `sinv_movimientos_cabecera` (
  `id_movimiento` int(11) NOT NULL AUTO_INCREMENT,
  `numero_acta` varchar(50) NOT NULL COMMENT 'Ej: SAN00000',
  `fecha` date NOT NULL,
  `tipo_movimiento` varchar(50) NOT NULL COMMENT 'ASIGNACION, TRASLADO, PRESTAMO, BAJA',
  `origen_id_responsable` int(11) DEFAULT NULL COMMENT 'FK a docente.id_docente',
  `origen_id_depto` int(11) DEFAULT NULL COMMENT 'FK a departamento.id_departamento',
  `origen_id_ubicacion` int(11) DEFAULT NULL COMMENT 'FK a aula.id_aula',
  `origen_centro_costo` varchar(50) DEFAULT NULL,
  `origen_firma` tinyint(1) DEFAULT 0,
  `destino_id_responsable` int(11) DEFAULT NULL COMMENT 'FK a docente.id_docente',
  `destino_id_depto` int(11) DEFAULT NULL COMMENT 'FK a departamento.id_departamento',
  `destino_centro_costo` varchar(50) DEFAULT NULL,
  `destino_id_ubicacion` int(11) DEFAULT NULL COMMENT 'FK a aula.id_aula',
  `destino_regional` varchar(50) DEFAULT NULL,
  `destino_firma` tinyint(1) DEFAULT 0,
  `edificio` varchar(100) DEFAULT NULL,
  `observaciones_acta` text DEFAULT NULL,
  `estado` enum('PENDIENTE','FINALIZADO','RECHAZADO') DEFAULT 'FINALIZADO',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_movimiento`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

/*Data for the table `sinv_movimientos_cabecera` */

insert  into `sinv_movimientos_cabecera`(`id_movimiento`,`numero_acta`,`fecha`,`tipo_movimiento`,`origen_id_responsable`,`origen_id_depto`,`origen_id_ubicacion`,`origen_centro_costo`,`origen_firma`,`destino_id_responsable`,`destino_id_depto`,`destino_centro_costo`,`destino_id_ubicacion`,`destino_regional`,`destino_firma`,`edificio`,`observaciones_acta`,`estado`,`created_at`) values 
(1,'ACTA-20260106-1631','2026-01-06','TRASLADO_REPARACION',4,4,4,NULL,0,6,4,NULL,1,'SANTA_ANA',0,'','','FINALIZADO','2026-01-06 09:33:03');

/*Table structure for table `sinv_movimientos_detalle` */

DROP TABLE IF EXISTS `sinv_movimientos_detalle`;

CREATE TABLE `sinv_movimientos_detalle` (
  `id_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `id_movimiento` int(11) NOT NULL COMMENT 'FK a movimientos_cabecera.id_movimiento',
  `id_bien` int(11) NOT NULL COMMENT 'FK a inventario_general.id_bien',
  `seccion_formulario` varchar(20) NOT NULL COMMENT 'ACTIVO_FIJO, CONTROL_ADMIN, NO_ACTIVO',
  PRIMARY KEY (`id_detalle`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

/*Data for the table `sinv_movimientos_detalle` */

/*Table structure for table `sinv_ubicacion` */

DROP TABLE IF EXISTS `sinv_ubicacion`;

CREATE TABLE `sinv_ubicacion` (
  `id_ubicacion` int(11) NOT NULL AUTO_INCREMENT,
  `ubicacion` varchar(150) NOT NULL,
  `tipou` varchar(50) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `zona_id` int(11) DEFAULT NULL,
  `estado` varchar(20) DEFAULT 'Activo',
  PRIMARY KEY (`id_ubicacion`),
  KEY `fk_zona_ubicacion` (`zona_id`),
  CONSTRAINT `fk_zona_ubicacion` FOREIGN KEY (`zona_id`) REFERENCES `sinv_zona` (`id_zona`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `sinv_ubicacion` */

insert  into `sinv_ubicacion`(`id_ubicacion`,`ubicacion`,`tipou`,`descripcion`,`zona_id`,`estado`) values 
(1,'C102','Computo','Computo nuevo',5,'Activo');

/*Table structure for table `sinv_zona` */

DROP TABLE IF EXISTS `sinv_zona`;

CREATE TABLE `sinv_zona` (
  `id_zona` int(11) NOT NULL AUTO_INCREMENT,
  `zona` varchar(100) NOT NULL,
  PRIMARY KEY (`id_zona`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `sinv_zona` */

insert  into `sinv_zona`(`id_zona`,`zona`) values 
(1,'Verde'),
(2,'Parqueo'),
(3,'Bodega'),
(4,'Planta Baja'),
(5,'Segunda Planta'),
(6,'Talleres'),
(7,'Chalet');

/*Table structure for table `solp_correccionnota` */

DROP TABLE IF EXISTS `solp_correccionnota`;

CREATE TABLE `solp_correccionnota` (
  `id_correccion` int(11) NOT NULL AUTO_INCREMENT,
  `id_docente` int(11) NOT NULL,
  `id_materia` int(11) NOT NULL,
  `idalumno` int(11) NOT NULL,
  `id_grupo` int(11) NOT NULL,
  `id_carrera` int(11) NOT NULL,
  `ciclo` varchar(10) NOT NULL,
  `anio` int(4) NOT NULL,
  `promedio_final` decimal(5,2) DEFAULT NULL,
  `promedio_anterior` decimal(5,2) DEFAULT NULL,
  `promedio_corregido` decimal(5,2) DEFAULT NULL,
  `porcentaje` decimal(5,2) DEFAULT NULL,
  `razon_correccion` text NOT NULL,
  `fecha_solicitud` date NOT NULL,
  `aprobacion_coordinador` enum('P','A','R') DEFAULT 'P',
  `id_coordinador` int(11) DEFAULT NULL,
  `fecha_aprobacion_coordinador` datetime DEFAULT NULL,
  PRIMARY KEY (`id_correccion`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `solp_correccionnota` */

insert  into `solp_correccionnota`(`id_correccion`,`id_docente`,`id_materia`,`idalumno`,`id_grupo`,`id_carrera`,`ciclo`,`anio`,`promedio_final`,`promedio_anterior`,`promedio_corregido`,`porcentaje`,`razon_correccion`,`fecha_solicitud`,`aprobacion_coordinador`,`id_coordinador`,`fecha_aprobacion_coordinador`) values 
(1,2,257,2185,151,9,'II',2025,NULL,NULL,NULL,NULL,'correccion notas examenes prueba','2025-12-12','A',4,'2025-12-12 13:35:51'),
(2,2,223,2185,151,9,'II',2025,NULL,NULL,NULL,NULL,'error manual ','2025-12-15','P',NULL,NULL);

/*Table structure for table `solp_correccionnota_detalle` */

DROP TABLE IF EXISTS `solp_correccionnota_detalle`;

CREATE TABLE `solp_correccionnota_detalle` (
  `id_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `id_correccion` int(11) NOT NULL,
  `tipo_evaluacion` enum('T','P') NOT NULL COMMENT 'T = Teórica, P = Práctica',
  `nombre_evaluacion` varchar(100) NOT NULL,
  `nota_anterior` decimal(5,2) NOT NULL,
  `nota_corregida` decimal(5,2) NOT NULL,
  `porcentaje` decimal(5,2) NOT NULL,
  PRIMARY KEY (`id_detalle`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Data for the table `solp_correccionnota_detalle` */

insert  into `solp_correccionnota_detalle`(`id_detalle`,`id_correccion`,`tipo_evaluacion`,`nombre_evaluacion`,`nota_anterior`,`nota_corregida`,`porcentaje`) values 
(1,1,'T','EXAM1',8.00,10.00,20.00),
(2,1,'T','EXAM2',7.00,5.00,30.00),
(3,1,'T','EXAM3',8.00,2.00,10.00),
(4,1,'T','EXAM4',6.00,1.00,50.00),
(5,1,'P','Eval pract1',8.00,5.00,10.00),
(6,1,'P','Eval pract2',10.00,3.00,20.00),
(7,1,'P','Eval pract3',9.00,3.00,30.00),
(8,2,'T','Eval Pract1',5.00,7.00,10.00);

/*Table structure for table `solp_exemdiferido` */

DROP TABLE IF EXISTS `solp_exemdiferido`;

CREATE TABLE `solp_exemdiferido` (
  `id_exemdiferido` int(11) NOT NULL AUTO_INCREMENT,
  `id_materia` int(11) NOT NULL,
  `idalumno` int(11) NOT NULL,
  `id_grupo` int(10) NOT NULL,
  `jornada` varchar(20) DEFAULT NULL,
  `fecha_realizacion` date NOT NULL,
  `motivo_ausencia` text NOT NULL,
  `fecha_emision` date NOT NULL,
  `aprobacion_docente` enum('P','A','D') DEFAULT 'P',
  `aprobacion_coordinador` enum('P','A','D') DEFAULT 'P',
  `aprobacion_director` enum('P','A','D') DEFAULT 'P',
  `id_docente` int(11) unsigned DEFAULT NULL,
  `id_coordinador` int(11) unsigned DEFAULT NULL,
  `id_director` int(11) unsigned DEFAULT NULL,
  `resolucion` text DEFAULT NULL,
  `impreso` enum('S','N') DEFAULT 'N',
  `fecha_impresion` datetime DEFAULT NULL,
  `id_impresora` int(11) unsigned DEFAULT NULL,
  `id_permiso_vinculado` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_exemdiferido`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `solp_exemdiferido` */

insert  into `solp_exemdiferido`(`id_exemdiferido`,`id_materia`,`idalumno`,`id_grupo`,`jornada`,`fecha_realizacion`,`motivo_ausencia`,`fecha_emision`,`aprobacion_docente`,`aprobacion_coordinador`,`aprobacion_director`,`id_docente`,`id_coordinador`,`id_director`,`resolucion`,`impreso`,`fecha_impresion`,`id_impresora`,`id_permiso_vinculado`) values 
(1,393,2185,432,'Vespertino','2025-12-10','fractura de brazos y dolor de cabeza ','2025-12-15','A','A','A',2,4,NULL,'lnaisfndafninaslfneslafneslnflansef\n\nObservaciones Coordinador: USDgfasebfuaksfgeñoausfñuoasegaf','N',NULL,NULL,1),
(2,393,2185,432,'Completa','2025-12-09','fractura de tobillo y dolor de cuerpo','2025-12-23','A','A','A',2,4,NULL,'vabusjkndmlasdfa\n\nObservaciones Coordinador: perfecto lo voy a aprobar','N',NULL,NULL,3),
(3,386,2251,169,'Vespertino','2026-01-27','Prueba de diferidoaaaaaaaaaaaaaaaaaaaaaaaaaaa','2026-01-22','A','P','P',4,NULL,NULL,'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa','N',NULL,NULL,8);

/*Table structure for table `solp_limitepermiso` */

DROP TABLE IF EXISTS `solp_limitepermiso`;

CREATE TABLE `solp_limitepermiso` (
  `id_limitepermiso` int(11) NOT NULL AUTO_INCREMENT,
  `cantidad` int(11) NOT NULL,
  `idalumno` int(11) NOT NULL,
  `limite_maximo` int(11) DEFAULT 3,
  PRIMARY KEY (`id_limitepermiso`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `solp_limitepermiso` */

insert  into `solp_limitepermiso`(`id_limitepermiso`,`cantidad`,`idalumno`,`limite_maximo`) values 
(1,6,2235,6),
(2,6,2251,6),
(3,6,2185,6);

/*Table structure for table `solp_permisoestudiante` */

DROP TABLE IF EXISTS `solp_permisoestudiante`;

CREATE TABLE `solp_permisoestudiante` (
  `id_permisoestudiante` int(11) NOT NULL AUTO_INCREMENT,
  `idalumno` int(11) NOT NULL,
  `idcarrera` int(11) DEFAULT NULL,
  `motivo_ausencia` text NOT NULL,
  `explicacion` text DEFAULT NULL,
  `cantidad_hora` int(11) DEFAULT NULL,
  `hora_inicio` time DEFAULT NULL,
  `hora_fin` time DEFAULT NULL,
  `cantidad_dias` int(11) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `fecha_emision` date NOT NULL,
  `aprobacion_coordinador` enum('P','A','R') DEFAULT 'P',
  `aprobacion_bienestar` enum('P','A','R') DEFAULT 'P',
  `fecha_aprobacion` datetime DEFAULT NULL,
  `id_coordinador` int(11) unsigned DEFAULT NULL,
  `id_bienestar` int(11) unsigned DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `observaciones_bien` varchar(255) DEFAULT NULL,
  `archivo_anexo` varchar(255) DEFAULT NULL,
  `impreso` enum('S','N') DEFAULT 'N',
  `fecha_impresion` datetime DEFAULT NULL,
  `id_impresora` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_permisoestudiante`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `solp_permisoestudiante` */

insert  into `solp_permisoestudiante`(`id_permisoestudiante`,`idalumno`,`idcarrera`,`motivo_ausencia`,`explicacion`,`cantidad_hora`,`hora_inicio`,`hora_fin`,`cantidad_dias`,`fecha_inicio`,`fecha_fin`,`fecha_emision`,`aprobacion_coordinador`,`aprobacion_bienestar`,`fecha_aprobacion`,`id_coordinador`,`id_bienestar`,`observaciones`,`observaciones_bien`,`archivo_anexo`,`impreso`,`fecha_impresion`,`id_impresora`) values 
(1,2185,9,'incapacidad','Fractura de brazo ',NULL,'00:00:00','00:00:00',15,'2025-12-08','2025-12-22','2025-12-15','A','A',NULL,4,1,'bien se lo voy a aprobar','perfecto','assets/anexos/049424/1_2025-12-15.pdf','S','2025-12-15 09:20:47',6),
(2,2185,9,'cita_medica','gripe y tos',2,'09:50:00','12:00:00',NULL,'2025-12-15','0000-00-00','2025-12-15','R','R',NULL,4,NULL,'No me convence y no hay justificacion',NULL,NULL,'N',NULL,NULL),
(3,2185,9,'incapacidad','Fractura de tobillo',NULL,'00:00:00','00:00:00',8,'2025-12-08','2025-12-15','2025-12-15','A','A','2025-12-15 14:21:20',4,1,'Doy mi consentimiento para aprobar este permiso',' Doy mi consentimiento para aprobar este permiso\r\n','assets/anexos/049424/3_2025-12-15.jpg','N',NULL,NULL),
(4,2185,9,'cita_medica',' bngcjyfyufjydftuetdyuiy',NULL,'00:00:00','00:00:00',8,'2025-12-01','2025-12-08','2025-12-15','P','P',NULL,NULL,NULL,NULL,NULL,NULL,'N',NULL,NULL),
(5,2185,9,'cita_medica','bjadsnihfasdhfasbfdawefñoa',NULL,'00:00:00','00:00:00',5,'2025-12-01','2025-12-05','2025-12-15','P','P',NULL,NULL,NULL,NULL,NULL,NULL,'N',NULL,NULL),
(6,2185,9,'cita_medica','vfekasufukeslfiuasgfliase',3,'14:53:00','17:51:00',NULL,'2025-12-05','0000-00-00','2025-12-15','P','P',NULL,NULL,NULL,NULL,NULL,NULL,'N',NULL,NULL),
(7,2185,9,'cita_medica','dafsuidojfoashdfuas',NULL,'00:00:00','00:00:00',8,'2025-12-01','2025-12-08','2025-12-15','P','P',NULL,NULL,NULL,NULL,NULL,NULL,'N',NULL,NULL),
(8,2251,9,'incapacidad','Prueba de inasistencias',NULL,'00:00:00','00:00:00',5,'2026-01-23','2026-01-27','2026-01-22','A','A','2026-01-22 09:02:13',4,5,'ok aprobadooooooo','','assets/anexos/073624/8_2026-01-22.pdf','N',NULL,NULL),
(9,2251,9,'otros','aaaaaaaaaaaaaaaaaaa',NULL,'00:00:00','00:00:00',3,'2026-01-13','2026-01-15','2026-01-22','P','P',NULL,NULL,NULL,NULL,NULL,NULL,'N',NULL,NULL);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
