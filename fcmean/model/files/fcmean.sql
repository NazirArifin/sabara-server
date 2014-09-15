-- phpMyAdmin SQL Dump
-- version 3.4.5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 07, 2014 at 03:21 PM
-- Server version: 5.5.16
-- PHP Version: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `fcmean`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE IF NOT EXISTS `admin` (
  `ID_ADMIN` smallint(6) NOT NULL AUTO_INCREMENT,
  `NAMA_ADMIN` varchar(30) DEFAULT NULL,
  `USERNAME_ADMIN` varchar(20) DEFAULT NULL,
  `PASSWORD_ADMIN` varchar(40) DEFAULT NULL,
  `STATUS_ADMIN` char(1) DEFAULT NULL,
  PRIMARY KEY (`ID_ADMIN`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`ID_ADMIN`, `NAMA_ADMIN`, `USERNAME_ADMIN`, `PASSWORD_ADMIN`, `STATUS_ADMIN`) VALUES
(1, 'administrator', 'admin', 'fcNOjt/Wbe6EM', '1');

-- --------------------------------------------------------

--
-- Table structure for table `angkatan`
--

CREATE TABLE IF NOT EXISTS `angkatan` (
  `ID_ANGKATAN` smallint(6) NOT NULL AUTO_INCREMENT,
  `NAMA_ANGKATAN` varchar(20) DEFAULT NULL,
  `STATUS_ANGKATAN` char(1) DEFAULT NULL,
  PRIMARY KEY (`ID_ANGKATAN`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `angkatan`
--

INSERT INTO `angkatan` (`ID_ANGKATAN`, `NAMA_ANGKATAN`, `STATUS_ANGKATAN`) VALUES
(1, '2014/2015', '1');

-- --------------------------------------------------------

--
-- Table structure for table `hasil`
--

CREATE TABLE IF NOT EXISTS `hasil` (
  `ID_HASIL` int(11) NOT NULL AUTO_INCREMENT,
  `INFO_HASIL` text NOT NULL,
  `DATA_HASIL` text,
  PRIMARY KEY (`ID_HASIL`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `jurusan`
--

CREATE TABLE IF NOT EXISTS `jurusan` (
  `ID_JURUSAN` smallint(6) NOT NULL AUTO_INCREMENT,
  `NAMA_JURUSAN` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`ID_JURUSAN`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `jurusan`
--

INSERT INTO `jurusan` (`ID_JURUSAN`, `NAMA_JURUSAN`) VALUES
(1, 'IPA'),
(2, 'IPS');

-- --------------------------------------------------------

--
-- Table structure for table `mata_pelajaran`
--

CREATE TABLE IF NOT EXISTS `mata_pelajaran` (
  `ID_MAPEL` int(11) NOT NULL AUTO_INCREMENT,
  `ID_JURUSAN` smallint(6) DEFAULT NULL,
  `KODE_MAPEL` varchar(10) DEFAULT NULL,
  `NAMA_MAPEL` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`ID_MAPEL`),
  KEY `FK_JURUSAN_MAPEL` (`ID_JURUSAN`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `mata_pelajaran`
--

INSERT INTO `mata_pelajaran` (`ID_MAPEL`, `ID_JURUSAN`, `KODE_MAPEL`, `NAMA_MAPEL`) VALUES
(1, 1, 'MAT', 'Matematika'),
(2, 1, 'FIS', 'Fisika'),
(3, 1, 'KIM', 'Kimia'),
(4, 1, 'BIO', 'Biologi'),
(5, 2, 'GEO', 'Geografi'),
(6, 2, 'EKO', 'Ekonomi'),
(7, 2, 'SOS', 'Sosiologi'),
(8, 2, 'SEJ', 'Sejarah');

-- --------------------------------------------------------

--
-- Table structure for table `nilai`
--

CREATE TABLE IF NOT EXISTS `nilai` (
  `ID_NILAI` bigint(20) NOT NULL AUTO_INCREMENT,
  `ID_MAPEL` int(11) DEFAULT NULL,
  `ID_SISWA` int(11) DEFAULT NULL,
  `JUMLAH_NILAI` float DEFAULT NULL,
  PRIMARY KEY (`ID_NILAI`),
  KEY `FK_NILAI_MAPEL` (`ID_MAPEL`),
  KEY `FK_NILAI_SISWA` (`ID_SISWA`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `rerata_nilai`
--

CREATE TABLE IF NOT EXISTS `rerata_nilai` (
  `ID_RERATA_NILAI` bigint(20) NOT NULL AUTO_INCREMENT,
  `ID_JURUSAN` smallint(6) DEFAULT NULL,
  `ID_SISWA` int(11) DEFAULT NULL,
  `DATA_RERATA_NILAI` float DEFAULT NULL,
  PRIMARY KEY (`ID_RERATA_NILAI`),
  KEY `FK_PROSES_NILAI_SISWA` (`ID_SISWA`),
  KEY `FK_RERATA_NILAI_JURUSAN` (`ID_JURUSAN`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE IF NOT EXISTS `siswa` (
  `ID_SISWA` int(11) NOT NULL AUTO_INCREMENT,
  `ID_ANGKATAN` smallint(6) DEFAULT NULL,
  `NIS_SISWA` varchar(20) DEFAULT NULL,
  `KELAS_SISWA` varchar(5) NOT NULL,
  `NAMA_SISWA` varchar(40) DEFAULT NULL,
  `TGL_LHR_SISWA` date DEFAULT NULL,
  `TMP_LHR_SISWA` varchar(30) DEFAULT NULL,
  `JK_SISWA` char(1) DEFAULT NULL,
  `ALAMAT_SISWA` varchar(120) DEFAULT NULL,
  `ORTU_SISWA` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`ID_SISWA`),
  KEY `FK_ANGKATAN_SISWA` (`ID_ANGKATAN`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `mata_pelajaran`
--
ALTER TABLE `mata_pelajaran`
  ADD CONSTRAINT `FK_JURUSAN_MAPEL` FOREIGN KEY (`ID_JURUSAN`) REFERENCES `jurusan` (`ID_JURUSAN`);

--
-- Constraints for table `nilai`
--
ALTER TABLE `nilai`
  ADD CONSTRAINT `FK_NILAI_MAPEL` FOREIGN KEY (`ID_MAPEL`) REFERENCES `mata_pelajaran` (`ID_MAPEL`),
  ADD CONSTRAINT `FK_NILAI_SISWA` FOREIGN KEY (`ID_SISWA`) REFERENCES `siswa` (`ID_SISWA`);

--
-- Constraints for table `rerata_nilai`
--
ALTER TABLE `rerata_nilai`
  ADD CONSTRAINT `FK_PROSES_NILAI_SISWA` FOREIGN KEY (`ID_SISWA`) REFERENCES `siswa` (`ID_SISWA`),
  ADD CONSTRAINT `FK_RERATA_NILAI_JURUSAN` FOREIGN KEY (`ID_JURUSAN`) REFERENCES `jurusan` (`ID_JURUSAN`);

--
-- Constraints for table `siswa`
--
ALTER TABLE `siswa`
  ADD CONSTRAINT `FK_ANGKATAN_SISWA` FOREIGN KEY (`ID_ANGKATAN`) REFERENCES `angkatan` (`ID_ANGKATAN`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
