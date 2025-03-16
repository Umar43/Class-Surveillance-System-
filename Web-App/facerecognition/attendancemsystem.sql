-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 23, 2025 at 06:34 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `attendancemsystem`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbladmin`
--

CREATE TABLE `tbladmin` (
  `Id` int(10) NOT NULL,
  `firstName` varchar(50) NOT NULL,
  `lastName` varchar(50) NOT NULL,
  `emailAddress` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tbladmin`
--

INSERT INTO `tbladmin` (`Id`, `firstName`, `lastName`, `emailAddress`, `password`) VALUES
(1, 'Admin', '', 'admin@gmail.com', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `tblattendance`
--

CREATE TABLE `tblattendance` (
  `attendanceID` int(11) NOT NULL,
  `studentRegistrationNumber` varchar(50) NOT NULL,
  `course` varchar(50) NOT NULL,
  `venue` varchar(20) NOT NULL,
  `attendanceStatus` varchar(50) NOT NULL,
  `dateMarked` date NOT NULL,
  `availabilityTime` float DEFAULT 0,
  `mobileUsage` float DEFAULT 0,
  `sleepingTime` float DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblattendance`
--

INSERT INTO `tblattendance` (`attendanceID`, `studentRegistrationNumber`, `course`, `venue`, `attendanceStatus`, `dateMarked`, `availabilityTime`, `mobileUsage`, `sleepingTime`) VALUES
(135, '201980043', 'AI', 'F1', 'Absent', '2025-02-23', 0, 0, 0),
(136, '201980023', 'AI', 'F1', 'Absent', '2025-02-23', 0, 0, 0),
(137, '201980009', 'AI', 'F1', 'Absent', '2025-02-23', 0, 0, 0),
(138, '201980032', 'AI', 'F1', 'Absent', '2025-02-23', 2, 10, 5);

-- --------------------------------------------------------

--
-- Table structure for table `tblattendancee`
--

CREATE TABLE `tblattendancee` (
  `attendanceID` int(50) NOT NULL,
  `studentRegistrationNumber` varchar(100) NOT NULL,
  `course` varchar(100) NOT NULL,
  `attendanceStatus` varchar(100) NOT NULL,
  `dateMarked` date NOT NULL,
  `availabilityTime` float NOT NULL,
  `mobileUsage` float NOT NULL,
  `sleepingTime` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblattendancee`
--

INSERT INTO `tblattendancee` (`attendanceID`, `studentRegistrationNumber`, `course`, `attendanceStatus`, `dateMarked`, `availabilityTime`, `mobileUsage`, `sleepingTime`) VALUES
(478, '201980032', 'AI', 'Present', '2024-05-02', 0, 0, 0),
(479, '201980023', 'AI', 'Present', '2024-05-02', 0, 0, 0),
(480, '201980043', 'AI', 'Present', '2024-05-02', 0, 0, 0),
(481, '201980041', 'AI', 'Present', '2024-05-02', 0, 0, 0),
(483, '201980041', 'AI', 'Absent', '2024-05-03', 0, 0, 0),
(484, '201980043', 'AI', 'Absent', '2024-05-03', 0, 0, 0),
(485, '201980023', 'AI', 'Absent', '2024-05-03', 0, 0, 0),
(486, '201980032', 'AI', 'Absent', '2024-05-03', 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `tblcourse`
--

CREATE TABLE `tblcourse` (
  `ID` int(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `facultyID` int(50) NOT NULL,
  `dateCreated` date NOT NULL,
  `courseCode` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblcourse`
--

INSERT INTO `tblcourse` (`ID`, `name`, `facultyID`, `dateCreated`, `courseCode`) VALUES
(10, 'Artificial Intelligence', 8, '2024-04-07', 'AI'),
(11, 'Machine Learning', 8, '2024-04-07', 'ML'),
(12, 'Intro to Data Science', 8, '2024-04-07', 'DS'),
(13, 'English', 10, '2024-04-07', 'ENG1'),
(14, 'Mindset', 10, '2024-04-07', 'MS1'),
(22, 'hi', 8, '2025-02-23', '123');

-- --------------------------------------------------------

--
-- Table structure for table `tblfaculty`
--

CREATE TABLE `tblfaculty` (
  `Id` int(10) NOT NULL,
  `facultyName` varchar(255) NOT NULL,
  `facultyCode` varchar(50) NOT NULL,
  `dateRegistered` date NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tblfaculty`
--

INSERT INTO `tblfaculty` (`Id`, `facultyName`, `facultyCode`, `dateRegistered`) VALUES
(8, 'Computer Science', 'CS', '2024-04-07'),
(9, 'Safada', 'SF', '2024-04-07'),
(10, 'English', 'ENG', '2024-04-07'),
(11, 'Islamiyat', 'ISL', '2024-04-07'),
(12, 'Mass Com', 'MC', '2024-04-07'),
(16, 'dsfdsf', '23234', '2025-02-23');

-- --------------------------------------------------------

--
-- Table structure for table `tbllecture`
--

CREATE TABLE `tbllecture` (
  `Id` int(10) NOT NULL,
  `firstName` varchar(255) NOT NULL,
  `lastName` varchar(255) NOT NULL,
  `emailAddress` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phoneNo` varchar(50) NOT NULL,
  `facultyCode` varchar(50) NOT NULL,
  `dateCreated` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tbllecture`
--

INSERT INTO `tbllecture` (`Id`, `firstName`, `lastName`, `emailAddress`, `password`, `phoneNo`, `facultyCode`, `dateCreated`) VALUES
(22, 'Zuhaib', 'Hussain', 'zuhaib@gmail.com', 'zuhaib', '07123456789', 'CS', '2024-05-02'),
(16, 'Umar', 'Ramzan', 'umar@gmail.com', 'umar', '07123456789', 'CS', '2024-04-07'),
(15, 'Usman', 'Ali', 'usman@gmail.com', 'usman', '07123456789', 'CS', '2024-04-07'),
(14, 'Fizza', 'Ali', 'fizza@gmail.com', 'fizza', '07123456789', 'CS', '2024-04-07'),
(23, 'Faizan', 'Ahmad', 'faizan@gmail.com', 'faizan', '12345', 'CS', '2024-12-20');

-- --------------------------------------------------------

--
-- Table structure for table `tblstudents`
--

CREATE TABLE `tblstudents` (
  `Id` int(10) NOT NULL,
  `firstName` varchar(255) NOT NULL,
  `lastName` varchar(255) NOT NULL,
  `registrationNumber` varchar(255) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` char(30) NOT NULL,
  `faculty` varchar(10) NOT NULL,
  `courseCode` varchar(20) NOT NULL,
  `studentImage1` varchar(300) NOT NULL,
  `studentImage2` varchar(300) NOT NULL,
  `dateRegistered` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tblstudents`
--

INSERT INTO `tblstudents` (`Id`, `firstName`, `lastName`, `registrationNumber`, `email`, `password`, `faculty`, `courseCode`, `studentImage1`, `studentImage2`, `dateRegistered`) VALUES
(3, 'Ameer', 'Hamza', '201980023', 'ameer@gmail.com', '1234', 'CS', 'AI', 'CIT-222-003-2020_image1.png', 'CIT-222-003-2020_image2.png', '2024-04-09'),
(2, 'Umar', 'Rajpoot', '201980043', 'umar@gmail.com', '1234', 'CS', 'AI', 'CIT-222-002-2020_image1.png', 'CIT-222-002-2020_image2.png', '2024-04-09'),
(1, 'Abdullah', 'Asad', '201980032', 'abdullah@gmail.com', '1234', 'CS', 'AI', 'CIT-222-001-2020_image1.png', 'CIT-222-001-2020_image2.png', '2024-04-09'),
(4, 'Naeem', 'Ali', '201980009', 'naeem@gmail.com', '1234', 'CS', 'AI', 'CIT-222-004-2020_image1.png', 'CIT-222-004-2020_image2.png', '2024-04-26'),
(125, 'pp', 'pp', '201980008', 'pp@gmail.com', '', 'CS', 'ML', '201980008_image1.png', '201980008_image2.png', '2025-02-23');

-- --------------------------------------------------------

--
-- Table structure for table `tblvenue`
--

CREATE TABLE `tblvenue` (
  `ID` int(10) NOT NULL,
  `className` varchar(50) NOT NULL,
  `facultyCode` varchar(50) NOT NULL,
  `currentStatus` varchar(50) NOT NULL,
  `capacity` int(10) NOT NULL,
  `classification` varchar(50) NOT NULL,
  `dateCreated` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblvenue`
--

INSERT INTO `tblvenue` (`ID`, `className`, `facultyCode`, `currentStatus`, `capacity`, `classification`, `dateCreated`) VALUES
(1, 'F1', 'CS', 'available', 100, 'class', '2024-04-07'),
(2, 'G1', 'CS', 'available', 80, 'class', '2024-04-07'),
(3, 'S1', 'CS', 'available', 80, 'laboratory', '2024-04-07'),
(4, 'T1', 'MC', 'available', 200, 'lectureHall', '2024-04-07'),
(5, 'S9', 'ENG', 'available', 100, 'computerLab', '2024-04-09');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbladmin`
--
ALTER TABLE `tbladmin`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `tblattendance`
--
ALTER TABLE `tblattendance`
  ADD PRIMARY KEY (`attendanceID`);

--
-- Indexes for table `tblattendancee`
--
ALTER TABLE `tblattendancee`
  ADD PRIMARY KEY (`attendanceID`);

--
-- Indexes for table `tblcourse`
--
ALTER TABLE `tblcourse`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tblfaculty`
--
ALTER TABLE `tblfaculty`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `tbllecture`
--
ALTER TABLE `tbllecture`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `tblstudents`
--
ALTER TABLE `tblstudents`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `tblvenue`
--
ALTER TABLE `tblvenue`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbladmin`
--
ALTER TABLE `tbladmin`
  MODIFY `Id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tblattendance`
--
ALTER TABLE `tblattendance`
  MODIFY `attendanceID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=139;

--
-- AUTO_INCREMENT for table `tblattendancee`
--
ALTER TABLE `tblattendancee`
  MODIFY `attendanceID` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=487;

--
-- AUTO_INCREMENT for table `tblcourse`
--
ALTER TABLE `tblcourse`
  MODIFY `ID` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `tblfaculty`
--
ALTER TABLE `tblfaculty`
  MODIFY `Id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `tbllecture`
--
ALTER TABLE `tbllecture`
  MODIFY `Id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `tblstudents`
--
ALTER TABLE `tblstudents`
  MODIFY `Id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

--
-- AUTO_INCREMENT for table `tblvenue`
--
ALTER TABLE `tblvenue`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
