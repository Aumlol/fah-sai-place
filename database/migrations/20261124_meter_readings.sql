ALTER TABLE MeterRecord
  ADD COLUMN waterReading DECIMAL(10,2) DEFAULT NULL AFTER electricUnit,
  ADD COLUMN electricReading DECIMAL(10,2) DEFAULT NULL AFTER waterReading,
  ADD CONSTRAINT chk_meter_water_reading CHECK (waterReading IS NULL OR waterReading >= 0),
  ADD CONSTRAINT chk_meter_electric_reading CHECK (electricReading IS NULL OR electricReading >= 0);

ALTER TABLE MeterRecord
  ADD COLUMN usageCalculated TINYINT(1) NOT NULL DEFAULT 0 AFTER electricReading,
  ADD COLUMN waterReset TINYINT(1) NOT NULL DEFAULT 0 AFTER usageCalculated,
  ADD COLUMN electricReset TINYINT(1) NOT NULL DEFAULT 0 AFTER waterReset,
  ADD CONSTRAINT chk_meter_water_reset CHECK (waterReset IN (0, 1)),
  ADD CONSTRAINT chk_meter_electric_reset CHECK (electricReset IN (0, 1));

UPDATE MeterRecord
SET
  waterReading = COALESCE(waterReading, waterUnit),
  electricReading = COALESCE(electricReading, electricUnit),
  usageCalculated = 0,
  waterReset = 0,
  electricReset = 0
WHERE waterReading IS NULL OR electricReading IS NULL;
