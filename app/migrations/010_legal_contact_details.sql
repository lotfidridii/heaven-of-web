-- Legal notice: drop company registration rows, add phone and publication director.
-- Privacy policy: drop the address row. (Lines are "Label : value" table rows.)

UPDATE settings SET value = REGEXP_REPLACE(value, '\nForme juridique : [^\n]*', '') WHERE skey = 'legal_notice' AND lang = 'fr';
UPDATE settings SET value = REGEXP_REPLACE(value, '\nSiège social : [^\n]*', '') WHERE skey = 'legal_notice' AND lang = 'fr';
UPDATE settings SET value = REGEXP_REPLACE(value, '\nSIRET : [^\n]*', '') WHERE skey = 'legal_notice' AND lang = 'fr';
UPDATE settings SET value = REGEXP_REPLACE(value, '\nN° TVA intracommunautaire : [^\n]*', '') WHERE skey = 'legal_notice' AND lang = 'fr';
UPDATE settings SET value = REGEXP_REPLACE(value, 'Téléphone : [^\n]*', 'Téléphone : [+216 28 74 40 87](tel:+21628744087)') WHERE skey = 'legal_notice' AND lang = 'fr';
UPDATE settings SET value = REGEXP_REPLACE(value, 'Directeur de la publication : [^\n]*', 'Directeur de la publication : Dridi Lotfi') WHERE skey = 'legal_notice' AND lang = 'fr';

UPDATE settings SET value = REGEXP_REPLACE(value, '\nLegal form : [^\n]*', '') WHERE skey = 'legal_notice' AND lang = 'en';
UPDATE settings SET value = REGEXP_REPLACE(value, '\nRegistered office : [^\n]*', '') WHERE skey = 'legal_notice' AND lang = 'en';
UPDATE settings SET value = REGEXP_REPLACE(value, '\nCompany number[^\n]*', '') WHERE skey = 'legal_notice' AND lang = 'en';
UPDATE settings SET value = REGEXP_REPLACE(value, '\nEU VAT number : [^\n]*', '') WHERE skey = 'legal_notice' AND lang = 'en';
UPDATE settings SET value = REGEXP_REPLACE(value, 'Phone : [^\n]*', 'Phone : [+216 28 74 40 87](tel:+21628744087)') WHERE skey = 'legal_notice' AND lang = 'en';
UPDATE settings SET value = REGEXP_REPLACE(value, 'Publication director : [^\n]*', 'Publication director : Dridi Lotfi') WHERE skey = 'legal_notice' AND lang = 'en';

UPDATE settings SET value = REGEXP_REPLACE(value, '\nالشكل القانوني : [^\n]*', '') WHERE skey = 'legal_notice' AND lang = 'ar';
UPDATE settings SET value = REGEXP_REPLACE(value, '\nالمقر الرئيسي : [^\n]*', '') WHERE skey = 'legal_notice' AND lang = 'ar';
UPDATE settings SET value = REGEXP_REPLACE(value, '\nرقم التسجيل[^\n]*', '') WHERE skey = 'legal_notice' AND lang = 'ar';
UPDATE settings SET value = REGEXP_REPLACE(value, '\nرقم ضريبة القيمة المضافة : [^\n]*', '') WHERE skey = 'legal_notice' AND lang = 'ar';
UPDATE settings SET value = REGEXP_REPLACE(value, 'الهاتف : [^\n]*', 'الهاتف : [+216 28 74 40 87](tel:+21628744087)') WHERE skey = 'legal_notice' AND lang = 'ar';
UPDATE settings SET value = REGEXP_REPLACE(value, 'مدير النشر : [^\n]*', 'مدير النشر : Dridi Lotfi') WHERE skey = 'legal_notice' AND lang = 'ar';

UPDATE settings SET value = REGEXP_REPLACE(value, '\nAdresse : [^\n]*', '') WHERE skey = 'privacy' AND lang = 'fr';
UPDATE settings SET value = REGEXP_REPLACE(value, '\nAddress : [^\n]*', '') WHERE skey = 'privacy' AND lang = 'en';
UPDATE settings SET value = REGEXP_REPLACE(value, '\nالعنوان : [^\n]*', '') WHERE skey = 'privacy' AND lang = 'ar';

UPDATE settings SET value = CURDATE() WHERE skey = 'legal_updated';

INSERT INTO migrations (name) VALUES ('010_legal_contact_details');
