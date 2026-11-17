CREATE TABLE Invoice (
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

CREATE TABLE Fine (
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

ALTER TABLE Payment
  ADD COLUMN invoiceId VARCHAR(16) DEFAULT NULL AFTER contractId,
  ADD COLUMN fineId VARCHAR(16) DEFAULT NULL AFTER invoiceId,
  ADD KEY idx_payment_invoice (invoiceId),
  ADD KEY idx_payment_fine (fineId),
  ADD CONSTRAINT fk_payment_invoice FOREIGN KEY (invoiceId) REFERENCES Invoice (invoiceId),
  ADD CONSTRAINT fk_payment_fine FOREIGN KEY (fineId) REFERENCES Fine (fineId),
  ADD CONSTRAINT chk_payment_one_billing_item CHECK (invoiceId IS NULL OR fineId IS NULL);
