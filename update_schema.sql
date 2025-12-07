-- Run this script in your database (e.g., via phpMyAdmin) to update the ID columns to support custom formats like D-0025 and P-034.

-- 1. Modify departments table to support string IDs
ALTER TABLE `departments` CHANGE `id` `id` VARCHAR(20) NOT NULL;

-- 2. Modify programs table to support string IDs
ALTER TABLE `programs` CHANGE `programId` `programId` VARCHAR(20) NOT NULL;

-- 3. (Optional) If 'department' table is also used, update it too
-- ALTER TABLE `department` CHANGE `ID` `ID` VARCHAR(20) NOT NULL;
