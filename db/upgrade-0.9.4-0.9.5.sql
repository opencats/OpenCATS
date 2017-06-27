/* Upgrade fila for DB schema update from version 0.9.4 to 0.9.5 */

/* new column in joborder table for import */
ALTER TABLE `joborder`
ADD COLUMN `import_id` int(11) NOT NULL DEFAULT '0' AFTER `questionnaire_id`;
