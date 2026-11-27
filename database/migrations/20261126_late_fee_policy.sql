ALTER TABLE Contract
  ADD COLUMN lateFeeClauseAccepted TINYINT(1) NOT NULL DEFAULT 0 AFTER status,
  ADD CONSTRAINT chk_contract_late_fee_clause CHECK (lateFeeClauseAccepted IN (0, 1));

ALTER TABLE Fine
  ADD COLUMN invoiceId VARCHAR(16) DEFAULT NULL AFTER contractId,
  ADD UNIQUE KEY uq_fine_invoice_day (invoiceId, fineDate),
  ADD CONSTRAINT fk_fine_invoice FOREIGN KEY (invoiceId) REFERENCES Invoice (invoiceId);

ALTER TABLE Payment
  ADD COLUMN submittedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER paymentDate,
  ADD KEY idx_payment_invoice_status_submitted (invoiceId, status, submittedAt);
