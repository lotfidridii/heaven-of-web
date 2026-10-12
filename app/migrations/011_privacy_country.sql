-- Privacy policy: explain the visitor-country statistic (IP looked up on our server, only the country kept).
UPDATE settings SET value = REPLACE(value, 'Si vous refusez les statistiques, aucune visite n’est enregistrée pour vous.', 'Le pays de connexion est déduit de l’adresse IP au moment de la visite (base DB-IP consultée sur notre propre serveur) : seul le pays est conservé. Si vous refusez les statistiques, aucune visite n’est enregistrée pour vous.') WHERE skey = 'privacy' AND lang = 'fr';
UPDATE settings SET value = REPLACE(value, 'If you refuse statistics, no visit is recorded for you.', 'Your country is derived from your IP address at the time of the visit (DB-IP database, queried on our own server): only the country is kept. If you refuse statistics, no visit is recorded for you.') WHERE skey = 'privacy' AND lang = 'en';
UPDATE settings SET value = REPLACE(value, 'إذا رفضت الإحصاءات فلن تُسجَّل أي زيارة لك.', 'يُستنتج بلد الزيارة من عنوان IP لحظة الزيارة (قاعدة بيانات DB-IP على خادمنا)، ولا يُحفظ سوى البلد. إذا رفضت الإحصاءات فلن تُسجَّل أي زيارة لك.') WHERE skey = 'privacy' AND lang = 'ar';
UPDATE settings SET value = CURDATE() WHERE skey = 'legal_updated';

INSERT INTO migrations (name) VALUES ('011_privacy_country');
