USE expense_management;
UPDATE transactions SET attachment_path = 'tx_687e0914bf0369.84473881_38.png' WHERE id = 6;
UPDATE transactions SET attachment_path = 'tx_687e092c3630d2.54617301_photo_2024-11-19_03-40-41.jpg' WHERE id = 9;
UPDATE transactions SET attachment_path = 'tx_687e0914bf0369.84473881_38.png' WHERE id = 10;
UPDATE transactions SET attachment_path = 'tx_687e0914bf0369.84473881_38.png' WHERE id = 215;
UPDATE transactions SET attachment_path = 'tx_687e0914bf0369.84473881_38.png' WHERE id = 221;
UPDATE transactions SET attachment_path = 'tx_687e0914bf0369.84473881_38.png' WHERE id = 215;
SELECT id, attachment_path FROM transactions WHERE id IN (6, 9, 10, 215, 221);