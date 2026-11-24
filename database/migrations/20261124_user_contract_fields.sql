ALTER TABLE `User`
  ADD COLUMN idCardNumber VARCHAR(13) DEFAULT NULL AFTER phone,
  ADD COLUMN birthDate DATE DEFAULT NULL AFTER idCardNumber,
  ADD COLUMN address VARCHAR(255) DEFAULT NULL AFTER birthDate,
  ADD COLUMN occupation VARCHAR(100) DEFAULT NULL AFTER address,
  ADD COLUMN workplace VARCHAR(150) DEFAULT NULL AFTER occupation,
  ADD COLUMN emergencyContactName VARCHAR(100) DEFAULT NULL AFTER workplace,
  ADD COLUMN emergencyContactRelation VARCHAR(50) DEFAULT NULL AFTER emergencyContactName,
  ADD COLUMN emergencyContactPhone VARCHAR(10) DEFAULT NULL AFTER emergencyContactRelation;

ALTER TABLE Contract
  ADD COLUMN coTenantUserId VARCHAR(8) DEFAULT NULL AFTER userId,
  ADD COLUMN idCardNumber VARCHAR(13) DEFAULT NULL AFTER coTenantUserId,
  ADD COLUMN coIdCardNumber VARCHAR(13) DEFAULT NULL AFTER idCardNumber,
  ADD COLUMN idCardDoc VARCHAR(255) DEFAULT NULL AFTER coIdCardNumber,
  ADD COLUMN coIdCardDoc VARCHAR(255) DEFAULT NULL AFTER idCardDoc,
  ADD COLUMN idCardImage VARCHAR(255) DEFAULT NULL AFTER coIdCardDoc,
  ADD COLUMN tenantSignature LONGTEXT DEFAULT NULL AFTER status,
  ADD COLUMN coTenantSignature LONGTEXT DEFAULT NULL AFTER tenantSignature,
  ADD COLUMN landlordSignature LONGTEXT DEFAULT NULL AFTER coTenantSignature,
  ADD KEY idx_contract_cotenant (coTenantUserId),
  ADD CONSTRAINT fk_contract_cotenant FOREIGN KEY (coTenantUserId) REFERENCES `User` (userId);
