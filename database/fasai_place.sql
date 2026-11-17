CREATE DATABASE IF NOT EXISTS fasai_place
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE fasai_place;

CREATE TABLE IF NOT EXISTS `User` (
  userId VARCHAR(8) NOT NULL,
  username VARCHAR(50) NOT NULL,
  password VARCHAR(255) NOT NULL,
  name VARCHAR(100) NOT NULL,
  phone VARCHAR(10) NOT NULL,
  email VARCHAR(100) NOT NULL,
  role ENUM('Tenant', 'Staff', 'Admin', 'Technician') NOT NULL,
  profileImage VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (userId),
  UNIQUE KEY uq_user_username (username),
  UNIQUE KEY uq_user_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS Room (
  roomId VARCHAR(9) NOT NULL,
  roomNumber VARCHAR(20) NOT NULL,
  roomStatus VARCHAR(30) NOT NULL DEFAULT 'ว่าง',
  rentalPrice DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (roomId),
  UNIQUE KEY uq_room_number (roomNumber),
  CONSTRAINT chk_room_rental_price CHECK (rentalPrice >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS Contract (
  contractId VARCHAR(12) NOT NULL,
  userId VARCHAR(8) NOT NULL,
  roomId VARCHAR(9) NOT NULL,
  startDate DATE NOT NULL,
  endDate DATE DEFAULT NULL,
  deposit DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  status VARCHAR(50) NOT NULL DEFAULT 'ใช้งานอยู่',
  PRIMARY KEY (contractId),
  KEY idx_contract_user (userId),
  KEY idx_contract_room (roomId),
  KEY idx_contract_status (status),
  CONSTRAINT fk_contract_user FOREIGN KEY (userId) REFERENCES `User` (userId),
  CONSTRAINT fk_contract_room FOREIGN KEY (roomId) REFERENCES Room (roomId),
  CONSTRAINT chk_contract_deposit CHECK (deposit >= 0),
  CONSTRAINT chk_contract_dates CHECK (endDate IS NULL OR endDate >= startDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS Invoice (
  invoiceId VARCHAR(16) NOT NULL,
  contractId VARCHAR(12) NOT NULL,
  billMonth VARCHAR(20) NOT NULL,
  dueDate DATE NOT NULL,
  rentAmount DECIMAL(10,2) NOT NULL,
  waterUnit DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  electricUnit DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  waterRate DECIMAL(10,2) NOT NULL,
  electricRate DECIMAL(10,2) NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'รอชำระ',
  PRIMARY KEY (invoiceId),
  UNIQUE KEY uq_invoice_contract_month (contractId, billMonth),
  KEY idx_invoice_due_status (dueDate, status),
  CONSTRAINT fk_invoice_contract FOREIGN KEY (contractId) REFERENCES Contract (contractId),
  CONSTRAINT chk_invoice_rent CHECK (rentAmount >= 0),
  CONSTRAINT chk_invoice_water CHECK (waterUnit >= 0),
  CONSTRAINT chk_invoice_electric CHECK (electricUnit >= 0),
  CONSTRAINT chk_invoice_water_rate CHECK (waterRate >= 0),
  CONSTRAINT chk_invoice_electric_rate CHECK (electricRate >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS Fine (
  fineId VARCHAR(16) NOT NULL,
  contractId VARCHAR(12) NOT NULL,
  fineDate DATE NOT NULL,
  description VARCHAR(500) NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'รอชำระ',
  PRIMARY KEY (fineId),
  KEY idx_fine_contract_status (contractId, status),
  CONSTRAINT fk_fine_contract FOREIGN KEY (contractId) REFERENCES Contract (contractId),
  CONSTRAINT chk_fine_amount CHECK (amount > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS Payment (
  paymentId VARCHAR(12) NOT NULL,
  contractId VARCHAR(12) NOT NULL,
  invoiceId VARCHAR(16) DEFAULT NULL,
  fineId VARCHAR(16) DEFAULT NULL,
  paymentDate DATE NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  evidence VARCHAR(255) DEFAULT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'รอตรวจสอบ',
  PRIMARY KEY (paymentId),
  KEY idx_payment_contract (contractId),
  KEY idx_payment_invoice (invoiceId),
  KEY idx_payment_fine (fineId),
  KEY idx_payment_status (status),
  KEY idx_payment_date (paymentDate),
  CONSTRAINT fk_payment_contract FOREIGN KEY (contractId) REFERENCES Contract (contractId),
  CONSTRAINT fk_payment_invoice FOREIGN KEY (invoiceId) REFERENCES Invoice (invoiceId),
  CONSTRAINT fk_payment_fine FOREIGN KEY (fineId) REFERENCES Fine (fineId),
  CONSTRAINT chk_payment_one_billing_item CHECK (invoiceId IS NULL OR fineId IS NULL),
  CONSTRAINT chk_payment_amount CHECK (amount >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS MeterRecord (
  meterId VARCHAR(12) NOT NULL,
  roomId VARCHAR(9) NOT NULL,
  waterUnit DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  electricUnit DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  recordDate DATE NOT NULL,
  month VARCHAR(20) NOT NULL,
  PRIMARY KEY (meterId),
  UNIQUE KEY uq_meter_room_month (roomId, month),
  KEY idx_meter_month (month),
  CONSTRAINT fk_meter_room FOREIGN KEY (roomId) REFERENCES Room (roomId),
  CONSTRAINT chk_meter_water CHECK (waterUnit >= 0),
  CONSTRAINT chk_meter_electric CHECK (electricUnit >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS Repair (
  repairId VARCHAR(12) NOT NULL,
  userId VARCHAR(8) NOT NULL,
  roomId VARCHAR(9) NOT NULL,
  maintenanceUserId VARCHAR(8) DEFAULT NULL,
  detail VARCHAR(500) NOT NULL,
  image VARCHAR(255) DEFAULT NULL,
  repairResult TEXT DEFAULT NULL,
  completionImage VARCHAR(255) DEFAULT NULL,
  repairStatus VARCHAR(30) NOT NULL DEFAULT 'รอดำเนินการ',
  repairDate DATE NOT NULL,
  PRIMARY KEY (repairId),
  KEY idx_repair_user (userId),
  KEY idx_repair_room (roomId),
  KEY idx_repair_maintenance (maintenanceUserId),
  KEY idx_repair_status (repairStatus),
  CONSTRAINT fk_repair_user FOREIGN KEY (userId) REFERENCES `User` (userId),
  CONSTRAINT fk_repair_room FOREIGN KEY (roomId) REFERENCES Room (roomId),
  CONSTRAINT fk_repair_maintenance FOREIGN KEY (maintenanceUserId) REFERENCES `User` (userId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS CostType (
  costTypeId VARCHAR(5) NOT NULL,
  costTypeName VARCHAR(100) NOT NULL,
  PRIMARY KEY (costTypeId),
  UNIQUE KEY uq_cost_type_name (costTypeName)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS RepairDetail (
  repairId VARCHAR(12) NOT NULL,
  costTypeId VARCHAR(5) NOT NULL,
  equipment VARCHAR(100) NOT NULL,
  amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (repairId, costTypeId),
  KEY idx_repair_detail_cost_type (costTypeId),
  CONSTRAINT fk_repair_detail_repair FOREIGN KEY (repairId) REFERENCES Repair (repairId),
  CONSTRAINT fk_repair_detail_cost_type FOREIGN KEY (costTypeId) REFERENCES CostType (costTypeId),
  CONSTRAINT chk_repair_detail_amount CHECK (amount >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS News (
  newsId VARCHAR(13) NOT NULL,
  title VARCHAR(200) NOT NULL,
  detail VARCHAR(1000) NOT NULL,
  publishDate DATE NOT NULL,
  image VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (newsId),
  KEY idx_news_publish_date (publishDate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO CostType (costTypeId, costTypeName) VALUES
  ('CT001', 'ค่าแรง'),
  ('CT002', 'ค่าอุปกรณ์'),
  ('CT003', 'ค่าใช้จ่ายอื่น ๆ'),
  ('CT004', 'ค่าขนส่งอุปกรณ์'),
  ('CT005', 'ค่าตรวจสอบและประเมินงานซ่อม')
ON DUPLICATE KEY UPDATE costTypeName = VALUES(costTypeName);
