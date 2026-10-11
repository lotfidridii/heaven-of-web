-- Heaven of Web is based in Sousse, Tunisia (not Angers). Client projects keep their own locations.

-- City / country / region become translatable (one value per language).
DELETE FROM settings WHERE skey IN ('company_city', 'service_region');
INSERT INTO settings (skey, lang, value) VALUES
('company_city', '', 'Sousse'), ('company_city', 'fr', 'Sousse'), ('company_city', 'en', 'Sousse'), ('company_city', 'ar', 'سوسة'),
('company_country', '', 'Tunisie'), ('company_country', 'fr', 'Tunisie'), ('company_country', 'en', 'Tunisia'), ('company_country', 'ar', 'تونس'),
('service_region', 'fr', 'Sahel tunisien'), ('service_region', 'en', 'the Tunisian Sahel'), ('service_region', 'ar', 'الساحل التونسي'),
('service_areas', '', 'Sousse, Hammam Sousse, Monastir, Mahdia, Msaken, Kalâa Kebira, Enfidha, Kairouan, Sfax, Tunis')
ON DUPLICATE KEY UPDATE value = VALUES(value);

-- Home page SEO
UPDATE settings SET value = 'Heaven of Web — Création de sites web, logos et référencement à Sousse, Tunisie' WHERE skey = 'meta_title' AND lang = 'fr';
UPDATE settings SET value = 'Heaven of Web — Websites, logos and SEO in Sousse, Tunisia' WHERE skey = 'meta_title' AND lang = 'en';
UPDATE settings SET value = 'Heaven of Web — تصميم المواقع والشعارات وتحسين محركات البحث في سوسة، تونس' WHERE skey = 'meta_title' AND lang = 'ar';
UPDATE settings SET value = 'Agence web à Sousse (Tunisie) : création de sites internet, identité visuelle, référencement naturel (SEO) et contenus pour les réseaux sociaux, pour les entreprises en Tunisie et en France. Devis gratuit.' WHERE skey = 'meta_description' AND lang = 'fr';
UPDATE settings SET value = 'Web agency in Sousse, Tunisia: website design, visual identity, search engine optimisation (SEO) and social media content for businesses in Tunisia and France. Free quote.' WHERE skey = 'meta_description' AND lang = 'en';
UPDATE settings SET value = 'وكالة ويب في سوسة، تونس: تصميم مواقع الإنترنت، الهوية البصرية، تحسين الظهور في محركات البحث ومحتوى الشبكات الاجتماعية للشركات في تونس وفرنسا. عرض سعر مجاني.' WHERE skey = 'meta_description' AND lang = 'ar';
UPDATE settings SET value = 'Des sites, des logos et des contenus livrés pour des entreprises en France, depuis Sousse.' WHERE skey = 'projects_intro' AND lang = 'fr';
UPDATE settings SET value = 'Websites, logos and content delivered for businesses in France, from Sousse.' WHERE skey = 'projects_intro' AND lang = 'en';
UPDATE settings SET value = 'مواقع وشعارات ومحتوى أنجزناها لشركات في فرنسا، انطلاقًا من سوسة.' WHERE skey = 'projects_intro' AND lang = 'ar';

-- Legal pages: registered office, governing law, data protection authority
UPDATE settings SET value = REPLACE(value, 'Angers, France', 'Sousse, Tunisie') WHERE skey IN ('legal_notice', 'privacy') AND lang = 'fr';
UPDATE settings SET value = REPLACE(value, 'Angers, France', 'Sousse, Tunisia') WHERE skey IN ('legal_notice', 'privacy') AND lang = 'en';
UPDATE settings SET value = REPLACE(value, 'أنجيه، فرنسا', 'سوسة، تونس') WHERE skey IN ('legal_notice', 'privacy') AND lang = 'ar';
UPDATE settings SET value = REPLACE(value, 'Conformément aux articles 6-III et 19 de la loi n° 2004-575 du 21 juin 2004 pour la confiance dans l’économie numérique (LCEN), vous trouverez', 'Conformément à la réglementation applicable en Tunisie et, pour nos visiteurs européens, à la loi française n° 2004-575 du 21 juin 2004 (LCEN), vous trouverez') WHERE skey = 'legal_notice' AND lang = 'fr';
UPDATE settings SET value = REPLACE(value, 'Les présentes mentions légales sont régies par le droit français. En cas de litige, et à défaut de résolution amiable, les tribunaux français seront seuls compétents.', 'Les présentes mentions légales sont régies par le droit tunisien. En cas de litige, et à défaut de résolution amiable, les tribunaux de Sousse seront compétents.') WHERE skey = 'legal_notice' AND lang = 'fr';
UPDATE settings SET value = REPLACE(value, 'In accordance with articles 6-III and 19 of French law no. 2004-575 of 21 June 2004 on confidence in the digital economy (LCEN), you will find', 'In accordance with the regulations applicable in Tunisia and, for our European visitors, French law no. 2004-575 of 21 June 2004 (LCEN), you will find') WHERE skey = 'legal_notice' AND lang = 'en';
UPDATE settings SET value = REPLACE(value, 'This legal notice is governed by French law. In the event of a dispute that cannot be settled amicably, the French courts have sole jurisdiction.', 'This legal notice is governed by Tunisian law. In the event of a dispute that cannot be settled amicably, the courts of Sousse have jurisdiction.') WHERE skey = 'legal_notice' AND lang = 'en';
UPDATE settings SET value = REPLACE(value, 'وفقًا للمادتين 6-III و19 من القانون الفرنسي رقم 2004-575 الصادر في 21 يونيو 2004 بشأن الثقة في الاقتصاد الرقمي، تجد', 'وفقًا للتشريعات المعمول بها في تونس، وبالنسبة لزوارنا الأوروبيين للقانون الفرنسي رقم 2004-575 الصادر في 21 يونيو 2004، تجد') WHERE skey = 'legal_notice' AND lang = 'ar';
UPDATE settings SET value = REPLACE(value, 'يخضع هذا الإشعار القانوني للقانون الفرنسي. وفي حال النزاع وتعذّر الحل الودي، تختص المحاكم الفرنسية وحدها.', 'يخضع هذا الإشعار القانوني للقانون التونسي. وفي حال النزاع وتعذّر الحل الودي، تختص محاكم سوسة.') WHERE skey = 'legal_notice' AND lang = 'ar';
UPDATE settings SET value = REPLACE(value, 'conformément au Règlement général sur la protection des données (RGPD) et à la loi Informatique et Libertés.', 'conformément à la loi organique tunisienne n° 2004-63 du 27 juillet 2004 sur la protection des données personnelles et, pour les visiteurs de l’Union européenne, au Règlement général sur la protection des données (RGPD).') WHERE skey = 'privacy' AND lang = 'fr';
UPDATE settings SET value = REPLACE(value, 'vous pouvez adresser une réclamation à la CNIL : [www.cnil.fr](https://www.cnil.fr).', 'vous pouvez saisir l’Instance nationale de protection des données personnelles (INPDP) : [www.inpdp.tn](https://www.inpdp.tn). Les visiteurs de l’Union européenne peuvent aussi s’adresser à l’autorité de leur pays (en France, la CNIL : [www.cnil.fr](https://www.cnil.fr)).') WHERE skey = 'privacy' AND lang = 'fr';
UPDATE settings SET value = REPLACE(value, 'in accordance with the General Data Protection Regulation (GDPR).', 'in accordance with Tunisian Organic Law no. 2004-63 of 27 July 2004 on personal data protection and, for visitors from the European Union, the General Data Protection Regulation (GDPR).') WHERE skey = 'privacy' AND lang = 'en';
UPDATE settings SET value = REPLACE(value, 'you can lodge a complaint with the French data protection authority (CNIL): [www.cnil.fr](https://www.cnil.fr).', 'you can contact the Tunisian National Authority for Personal Data Protection (INPDP): [www.inpdp.tn](https://www.inpdp.tn). Visitors from the European Union can also contact their own national authority (in France, the CNIL: [www.cnil.fr](https://www.cnil.fr)).') WHERE skey = 'privacy' AND lang = 'en';
UPDATE settings SET value = REPLACE(value, 'وفقًا للائحة العامة لحماية البيانات (RGPD).', 'وفقًا للقانون الأساسي التونسي عدد 63 لسنة 2004 المتعلق بحماية المعطيات الشخصية، وبالنسبة لزوار الاتحاد الأوروبي للائحة العامة لحماية البيانات (RGPD).') WHERE skey = 'privacy' AND lang = 'ar';
UPDATE settings SET value = REPLACE(value, 'يمكنك تقديم شكوى إلى الهيئة الفرنسية لحماية البيانات (CNIL): [www.cnil.fr](https://www.cnil.fr).', 'يمكنك تقديم شكوى إلى الهيئة الوطنية لحماية المعطيات الشخصية (INPDP): [www.inpdp.tn](https://www.inpdp.tn).') WHERE skey = 'privacy' AND lang = 'ar';
UPDATE settings SET value = CURDATE() WHERE skey = 'legal_updated';

-- Service pages: local SEO now targets Sousse and Tunisia
UPDATE services SET slug = REPLACE(slug, '-angers', '-sousse') WHERE slug LIKE '%-angers';

UPDATE translations SET value = REPLACE(value, '« électricien Angers » ou « rénovation fenêtres Trélazé »', '« électricien Sousse » ou « climatisation Monastir »') WHERE entity = 'service' AND lang = 'fr';
UPDATE translations SET value = REPLACE(value, 'Travaillez-vous uniquement à Angers ?\nNous sommes basés à Angers et intervenons dans tout le Maine-et-Loire. Nous accompagnons aussi des entreprises partout en France, à distance.', 'Travaillez-vous uniquement à Sousse ?\nNous sommes basés à Sousse et travaillons avec des entreprises dans toute la Tunisie, ainsi qu’en France, à distance.') WHERE entity = 'service' AND lang = 'fr';
UPDATE translations SET value = REPLACE(value, 'à Angers et dans le Maine-et-Loire', 'à Sousse et partout en Tunisie') WHERE entity = 'service' AND lang = 'fr';
UPDATE translations SET value = REPLACE(value, 'vos clients d’Angers et du Maine-et-Loire', 'vos clients, à Sousse, en Tunisie ou en France') WHERE entity = 'service' AND lang = 'fr';
UPDATE translations SET value = REPLACE(value, 'votre métier à Angers et dans les communes autour', 'votre métier à Sousse, à Monastir et partout en Tunisie') WHERE entity = 'service' AND lang = 'fr';
UPDATE translations SET value = REPLACE(value, 'Angers', 'Sousse') WHERE entity = 'service' AND lang = 'fr';

UPDATE translations SET value = REPLACE(value, 'in Angers, France', 'in Sousse, Tunisia') WHERE entity = 'service' AND lang = 'en';
UPDATE translations SET value = REPLACE(value, 'in Angers and Maine-et-Loire', 'in Sousse and across Tunisia') WHERE entity = 'service' AND lang = 'en';
UPDATE translations SET value = REPLACE(value, 'your customers around Angers', 'your customers in Sousse, across Tunisia or in France') WHERE entity = 'service' AND lang = 'en';
UPDATE translations SET value = REPLACE(value, 'your trade around Angers', 'your trade in Sousse and across Tunisia') WHERE entity = 'service' AND lang = 'en';
UPDATE translations SET value = REPLACE(value, 'Angers', 'Sousse') WHERE entity = 'service' AND lang = 'en';

UPDATE translations SET value = REPLACE(value, 'في أنجيه ومنطقة مين إي لوار', 'في سوسة وكامل تونس') WHERE entity = 'service' AND lang = 'ar';
UPDATE translations SET value = REPLACE(value, 'في أنجيه وما حولها', 'في سوسة وكامل تونس') WHERE entity = 'service' AND lang = 'ar';
UPDATE translations SET value = REPLACE(value, 'أنجيه', 'سوسة') WHERE entity = 'service' AND lang = 'ar';

INSERT INTO migrations (name) VALUES ('009_location_sousse');
