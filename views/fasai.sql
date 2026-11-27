SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- ล้างตารางเดิมที่ค้างอยู่ก่อนสร้างใหม่
DROP TABLE IF EXISTS `repairdetail`, `repair`, `payment`, `meterrecord`, `invoice`, `fine`, `contract`, `news`, `room`, `costtype`, `user`;

-- --------------------------------------------------------
-- Table structure for table `contract`
-- --------------------------------------------------------

CREATE TABLE `contract` (
  `contractId` varchar(12) NOT NULL,
  `userId` varchar(8) NOT NULL,
  `coTenantUserId` varchar(8) DEFAULT NULL,
  `idCardNumber` varchar(13) DEFAULT NULL,
  `coIdCardNumber` varchar(13) DEFAULT NULL,
  `idCardDoc` varchar(255) DEFAULT NULL,
  `coIdCardDoc` varchar(255) DEFAULT NULL,
  `idCardImage` varchar(255) DEFAULT NULL,
  `roomId` varchar(9) NOT NULL,
  `startDate` date NOT NULL,
  `endDate` date DEFAULT NULL,
  `deposit` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(50) NOT NULL DEFAULT 'ใช้งานอยู่',
  `lateFeeClauseAccepted` tinyint(1) NOT NULL DEFAULT 0,
  `tenantSignature` longtext DEFAULT NULL,
  `coTenantSignature` longtext DEFAULT NULL,
  `landlordSignature` longtext DEFAULT NULL
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `contract` (`contractId`, `userId`, `coTenantUserId`, `idCardNumber`, `coIdCardNumber`, `idCardDoc`, `coIdCardDoc`, `idCardImage`, `roomId`, `startDate`, `endDate`, `deposit`, `status`, `lateFeeClauseAccepted`, `tenantSignature`, `coTenantSignature`, `landlordSignature`) VALUES
('CON-2026-001', 'USR-0002', NULL, NULL, NULL, NULL, NULL, NULL, 'ROOM-0001', '2026-11-20', NULL, 5000.00, 'ใช้งานอยู่', 0, NULL, NULL, NULL),
('CON-2027', 'USR-0005', NULL, '1580700068884', NULL, 'bdbe2848da0aeee39d98b098a789c823.jpg', NULL, NULL, 'ROOM-0002', '2026-11-22', '2027-11-22', 3000.00, 'ใช้งานอยู่', 0, 'eef0fbba9fce3e4ca46deac915e009c6.jpg', NULL, 'data:image/png;base64,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'),
('CON-2028', 'USR-0007', NULL, NULL, NULL, NULL, NULL, NULL, 'ROOM-0003', '2026-11-26', '2027-11-26', 3000.00, 'ใช้งานอยู่', 1, NULL, NULL, NULL),
('CON-2029', 'USR-0006', NULL, NULL, NULL, NULL, NULL, NULL, 'ROOM-0004', '2026-11-26', '2027-11-26', 3000.00, 'ใช้งานอยู่', 1, NULL, NULL, NULL),
('CON-2030', 'USR-0008', NULL, NULL, NULL, NULL, NULL, NULL, 'ROOM-0005', '2026-11-26', '2027-11-26', 3000.00, 'ใช้งานอยู่', 1, NULL, NULL, NULL);

-- --------------------------------------------------------
-- Table structure for table `costtype`
-- --------------------------------------------------------

CREATE TABLE `costtype` (
  `costTypeId` varchar(5) NOT NULL,
  `costTypeName` varchar(100) NOT NULL
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `costtype` (`costTypeId`, `costTypeName`) VALUES
('CT004', 'ค่าขนส่งอุปกรณ์'),
('CT005', 'ค่าตรวจสอบและประเมินงานซ่อม'),
('CT002', 'ค่าอุปกรณ์'),
('CT001', 'ค่าแรง'),
('CT003', 'ค่าใช้จ่ายอื่น ๆ');

-- --------------------------------------------------------
-- Table structure for table `fine`
-- --------------------------------------------------------

CREATE TABLE `fine` (
  `fineId` varchar(16) NOT NULL,
  `contractId` varchar(12) NOT NULL,
  `invoiceId` varchar(16) DEFAULT NULL,
  `fineDate` date NOT NULL,
  `description` varchar(500) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'รอชำระ'
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `fine` (`fineId`, `contractId`, `invoiceId`, `fineDate`, `description`, `amount`, `status`) VALUES
('FINE-0001', 'CON-2027', NULL, '2026-11-26', 'ข้อ 9. ประกอบอาหารที่มีควันและเปลวไฟในห้องพัก', 1000.00, 'ชำระแล้ว');

-- --------------------------------------------------------
-- Table structure for table `invoice`
-- --------------------------------------------------------

CREATE TABLE `invoice` (
  `invoiceId` varchar(16) NOT NULL,
  `contractId` varchar(12) NOT NULL,
  `billMonth` varchar(20) NOT NULL,
  `dueDate` date NOT NULL,
  `rentAmount` decimal(10,2) NOT NULL,
  `waterUnit` decimal(10,2) NOT NULL DEFAULT 0.00,
  `electricUnit` decimal(10,2) NOT NULL DEFAULT 0.00,
  `waterRate` decimal(10,2) NOT NULL,
  `electricRate` decimal(10,2) NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'รอชำระ'
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `invoice` (`invoiceId`, `contractId`, `billMonth`, `dueDate`, `rentAmount`, `waterUnit`, `electricUnit`, `waterRate`, `electricRate`, `status`) VALUES
('INV-0001', 'CON-2026-001', '11/2569', '2026-12-01', 3500.00, 100.00, 120.00, 18.00, 8.00, 'รอชำระ'),
('INV-0002', 'CON-2027', '11/2569', '2026-12-01', 3000.00, 100.00, 100.00, 18.00, 8.00, 'ชำระแล้ว'),
('INV-0003', 'CON-2028', '11/2569', '2026-12-01', 3500.00, 100.00, 20.00, 18.00, 8.00, 'รอชำระ'),
('INV-0004', 'CON-2029', '11/2569', '2026-12-01', 3500.00, 100.00, 20.00, 18.00, 8.00, 'รอชำระ'),
('INV-0005', 'CON-2030', '11/2569', '2026-12-01', 3500.00, 100.00, 20.00, 18.00, 8.00, 'รอชำระ');

-- --------------------------------------------------------
-- Table structure for table `meterrecord`
-- --------------------------------------------------------

CREATE TABLE `meterrecord` (
  `meterId` varchar(12) NOT NULL,
  `roomId` varchar(9) NOT NULL,
  `waterUnit` decimal(10,2) NOT NULL DEFAULT 0.00,
  `electricUnit` decimal(10,2) NOT NULL DEFAULT 0.00,
  `waterReading` decimal(10,2) DEFAULT NULL,
  `electricReading` decimal(10,2) DEFAULT NULL,
  `usageCalculated` tinyint(1) NOT NULL DEFAULT 0,
  `waterReset` tinyint(1) NOT NULL DEFAULT 0,
  `electricReset` tinyint(1) NOT NULL DEFAULT 0,
  `recordDate` date NOT NULL,
  `month` varchar(20) NOT NULL
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `meterrecord` (`meterId`, `roomId`, `waterUnit`, `electricUnit`, `waterReading`, `electricReading`, `usageCalculated`, `waterReset`, `electricReset`, `recordDate`, `month`) VALUES
('MTR-0001', 'ROOM-0001', 100.00, 120.00, 100.00, 120.00, 1, 0, 0, '2026-11-26', '11/2569'),
('MTR-0002', 'ROOM-0002', 100.00, 100.00, 100.00, 100.00, 1, 0, 0, '2026-11-26', '11/2569'),
('MTR-0003', 'ROOM-0003', 100.00, 20.00, 100.00, 20.00, 1, 0, 0, '2026-11-26', '11/2569'),
('MTR-0004', 'ROOM-0004', 100.00, 20.00, 100.00, 20.00, 1, 0, 0, '2026-11-26', '11/2569'),
('MTR-0005', 'ROOM-0005', 100.00, 20.00, 100.00, 20.00, 1, 0, 0, '2026-11-26', '11/2569');

-- --------------------------------------------------------
-- Table structure for table `news`
-- --------------------------------------------------------

CREATE TABLE `news` (
  `newsId` varchar(13) NOT NULL,
  `title` varchar(200) NOT NULL,
  `detail` varchar(1000) NOT NULL,
  `publishDate` date NOT NULL,
  `image` varchar(255) DEFAULT NULL
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `payment`
-- --------------------------------------------------------

CREATE TABLE `payment` (
  `paymentId` varchar(12) NOT NULL,
  `contractId` varchar(12) NOT NULL,
  `invoiceId` varchar(16) DEFAULT NULL,
  `fineId` varchar(16) DEFAULT NULL,
  `paymentDate` date NOT NULL,
  `submittedAt` datetime NOT NULL DEFAULT current_timestamp(),
  `amount` decimal(10,2) NOT NULL,
  `evidence` varchar(255) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'รอตรวจสอบ'
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `payment` (`paymentId`, `contractId`, `invoiceId`, `fineId`, `paymentDate`, `submittedAt`, `amount`, `evidence`, `status`) VALUES
('PAY-0001', 'CON-2027', NULL, 'FINE-0001', '2026-11-26', '2026-11-26 20:44:28', 1000.00, '3ed3736cc89aefb3b6febb3e857e8d40.jpg', 'อนุมัติ'),
('PAY-0002', 'CON-2027', 'INV-0002', NULL, '2026-11-26', '2026-11-26 20:44:34', 5600.00, 'd51bcd2541b7a3b1003057621fce5c48.jpg', 'อนุมัติ');

-- --------------------------------------------------------
-- Table structure for table `repair`
-- --------------------------------------------------------

CREATE TABLE `repair` (
  `repairId` varchar(12) NOT NULL,
  `userId` varchar(8) NOT NULL,
  `roomId` varchar(9) NOT NULL,
  `maintenanceUserId` varchar(8) DEFAULT NULL,
  `detail` varchar(500) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `repairResult` text DEFAULT NULL,
  `completionImage` varchar(255) DEFAULT NULL,
  `quoteAmount` decimal(10,2) DEFAULT NULL,
  `quoteNote` varchar(500) DEFAULT NULL,
  `workImage` varchar(255) DEFAULT NULL,
  `repairStatus` varchar(30) NOT NULL DEFAULT 'รอดำเนินการ',
  `repairDate` date NOT NULL
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `repair` (`repairId`, `userId`, `roomId`, `maintenanceUserId`, `detail`, `image`, `repairResult`, `completionImage`, `quoteAmount`, `quoteNote`, `workImage`, `repairStatus`, `repairDate`) VALUES
('REP-2026-001', 'USR-0002', 'ROOM-0001', 'USR-0004', 'ท่อเเตก', '2d454bf4a9012ab4266fbdb36fc9a757.png', 'ซ่อมท่อ', '9268834ab95d280f1022769090a4e3e0.jpg', 500.00, 'ค่าเเรกค่าท่อ', 'bb6db96ee59cc4889e82d8704c39474a.jpg', 'ซ่อมเสร็จแล้ว', '2026-11-20'),
('REP-2027', 'USR-0005', 'ROOM-0002', 'USR-0004', 'ท่อเเตก', '28004f3b87777ebcd34705efe5e845bc.jpg', 'ปกติเเล้ว', NULL, 300.00, NULL, NULL, 'ซ่อมเสร็จแล้ว', '2026-11-27');

-- --------------------------------------------------------
-- Table structure for table `repairdetail`
-- --------------------------------------------------------

CREATE TABLE `repairdetail` (
  `repairId` varchar(12) NOT NULL,
  `costTypeId` varchar(5) NOT NULL,
  `equipment` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `repairdetail` (`repairId`, `costTypeId`, `equipment`, `amount`) VALUES
('REP-2027', 'CT001', 'ค่าแรง', 300.00),
('REP-2027', 'CT002', 'ค่าอุปกรณ์', 0.00),
('REP-2027', 'CT003', 'ค่าใช้จ่ายอื่น ๆ', 0.00),
('REP-2027', 'CT004', 'ค่าขนส่งอุปกรณ์', 0.00),
('REP-2027', 'CT005', 'ค่าตรวจสอบและประเมินงานซ่อม', 0.00);

-- --------------------------------------------------------
-- Table structure for table `room`
-- --------------------------------------------------------

CREATE TABLE `room` (
  `roomId` varchar(9) NOT NULL,
  `roomNumber` varchar(20) NOT NULL,
  `roomStatus` varchar(30) NOT NULL DEFAULT 'ว่าง',
  `rentalPrice` decimal(10,2) NOT NULL DEFAULT 0.00
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `room` (`roomId`, `roomNumber`, `roomStatus`, `rentalPrice`) VALUES
('ROOM-0001', '1', 'มีผู้เช่า', 3500.00),
('ROOM-0002', '2', 'มีผู้เช่า', 3000.00),
('ROOM-0003', '3', 'มีผู้เช่า', 3500.00),
('ROOM-0004', '4', 'มีผู้เช่า', 3500.00),
('ROOM-0005', '5', 'มีผู้เช่า', 3500.00);

-- --------------------------------------------------------
-- Table structure for table `user`
-- --------------------------------------------------------

CREATE TABLE `user` (
  `userId` varchar(8) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(10) NOT NULL,
  `idCardNumber` varchar(13) DEFAULT NULL,
  `birthDate` date DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `workplace` varchar(150) DEFAULT NULL,
  `emergencyContactName` varchar(100) DEFAULT NULL,
  `emergencyContactRelation` varchar(50) DEFAULT NULL,
  `emergencyContactPhone` varchar(10) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('Tenant','Staff','Admin','Technician') NOT NULL,
  `profileImage` varchar(255) DEFAULT NULL
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `user` (`userId`, `username`, `password`, `name`, `phone`, `idCardNumber`, `birthDate`, `address`, `occupation`, `workplace`, `emergencyContactName`, `emergencyContactRelation`, `emergencyContactPhone`, `email`, `role`, `profileImage`) VALUES
('USR-0001', 'ณฐนนท์', '$2y$12$V8j..tD63Wklv3XzfIEP8uicRK7wnzldqOsYRwDseg.nBemARM/UC', 'ณฐนนท์ ปิยชาติโชติกุล', '0807484169', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'vendettath22@gmail.com', 'Admin', NULL),
('USR-0002', 'demotenant', '$2y$12$N0mAIwBCGfFts30vUrL7OeEvyl2z1URVyLqBwTmZ8aJIWpP1kOOMy', 'นายสาว พัชรา ก่าคำ', '0888888889', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '123456@gmail.com', 'Tenant', NULL),
('USR-0003', 'demostaff', '$2y$12$gFt27Hoh37T3gutLbw9CWOzJg6lptH74hD6SFwitIXYrDnuYQJhrK', 'Demo staff', '0888888810', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1234536@gmail.com', 'Staff', NULL),
('USR-0004', 'demotechnician', '$2y$12$o2dZN1D52eGu.dAi8QFB4epBy8Ztp.pLMIqmxryIDlelmlB4.VOym', 'Demo Technician', '0807484161', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1234156@gmail.com', 'Technician', NULL),
('USR-0005', 'karina', '$2y$12$4OaxUD/80s7VEPSSHyNB0.DmWRNAMI6ZA1zton5msKO0tFw7xsRRC', 'นางสาว ยูจีมิน คารินะ', '0111111111', '1580700068884', '2004-11-01', '259/79', 'นักศึกษา', 'มหาวิทยาลัยราชฏัภเชียงใหม่', 'นางสาว พัชรา ก่าคำ', 'เเฟน', '0631071959', '1234511@gmail.com', 'Tenant', NULL),
('USR-0006', 'leon1', '$2y$12$cwwNRqMRAw.vVJT1DoqXu..LHfsO0eK8KcY1DiHdlhfTL6BbifAGm', 'นาย ลีออนเอส  เคเนดี้', '0807484161', NULL, '2026-11-26', '1233', 'นักศึกษา', '2323232', '2332', '3', '2322323232', 'vendet1tath22@gmail.com', 'Tenant', NULL),
('USR-0007', 'simon', '$2y$12$E855ANWD1HMEhMWEiy0hdO1jGQ0TUCIbCujU6QiMC0bD37d05cPWq', 'นาย ซิมอน เลลี่', '0882888889', NULL, '2026-11-26', '1', 'นักศึกษา', 'มหาวิทยาลัยราชฏัภเชียงใหม่', 'นางสาว พัชรา ก่าค', 'เเฟน', '0348383493', '12345///@gmail.com', 'Tenant', NULL),
('USR-0008', 'shank', '$2y$12$pgvFUlnQIl2u7HtOorfTf.CVZq7CtG4BZriYGj1776hqcYsY.Sofa', 'นาย เเชงคูด', '0807484469', NULL, '2020-11-26', 'sdfsd', 'ข้าราชการ / พนักงานรัฐวิสาหกิจ', 'มหาวิทยาลัยราชฏัภเชียงใหม่', 'sfsdfsdf', 'sdfsdfsd', '2313123132', '12345dad6@gmail.com', 'Tenant', NULL);

-- --------------------------------------------------------
-- Indexes for dumped tables
-- --------------------------------------------------------

ALTER TABLE `contract`
  ADD PRIMARY KEY (`contractId`),
  ADD KEY `idx_contract_user` (`userId`),
  ADD KEY `idx_contract_room` (`roomId`),
  ADD KEY `idx_contract_status` (`status`),
  ADD KEY `idx_contract_cotenant` (`coTenantUserId`);

ALTER TABLE `costtype`
  ADD PRIMARY KEY (`costTypeId`),
  ADD UNIQUE KEY `uq_cost_type_name` (`costTypeName`);

ALTER TABLE `fine`
  ADD PRIMARY KEY (`fineId`),
  ADD UNIQUE KEY `uq_fine_invoice_day` (`invoiceId`,`fineDate`),
  ADD KEY `idx_fine_contract_status` (`contractId`,`status`);

ALTER TABLE `invoice`
  ADD PRIMARY KEY (`invoiceId`),
  ADD UNIQUE KEY `uq_invoice_contract_month` (`contractId`,`billMonth`),
  ADD KEY `idx_invoice_due_status` (`dueDate`,`status`);

ALTER TABLE `meterrecord`
  ADD PRIMARY KEY (`meterId`),
  ADD UNIQUE KEY `uq_meter_room_month` (`roomId`,`month`),
  ADD KEY `idx_meter_month` (`month`);

ALTER TABLE `news`
  ADD PRIMARY KEY (`newsId`),
  ADD KEY `idx_news_publish_date` (`publishDate`);

ALTER TABLE `payment`
  ADD PRIMARY KEY (`paymentId`),
  ADD KEY `idx_payment_contract` (`contractId`),
  ADD KEY `idx_payment_invoice` (`invoiceId`),
  ADD KEY `idx_payment_fine` (`fineId`),
  ADD KEY `idx_payment_status` (`status`),
  ADD KEY `idx_payment_date` (`paymentDate`),
  ADD KEY `idx_payment_invoice_status_submitted` (`invoiceId`,`status`,`submittedAt`);

ALTER TABLE `repair`
  ADD PRIMARY KEY (`repairId`),
  ADD KEY `idx_repair_user` (`userId`),
  ADD KEY `idx_repair_room` (`roomId`),
  ADD KEY `idx_repair_maintenance` (`maintenanceUserId`),
  ADD KEY `idx_repair_status` (`repairStatus`);

ALTER TABLE `repairdetail`
  ADD PRIMARY KEY (`repairId`,`costTypeId`),
  ADD KEY `idx_repair_detail_cost_type` (`costTypeId`);

ALTER TABLE `room`
  ADD PRIMARY KEY (`roomId`),
  ADD UNIQUE KEY `uq_room_number` (`roomNumber`);

ALTER TABLE `user`
  ADD PRIMARY KEY (`userId`),
  ADD UNIQUE KEY `uq_user_username` (`username`),
  ADD UNIQUE KEY `uq_user_email` (`email`);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;