-- Detailed project pages: completion date, technologies, and translated detail fields
-- (tagline, highlights, features, image stats, results) stored in `translations`.

ALTER TABLE projects ADD COLUMN completed_on DATE NULL AFTER year;
ALTER TABLE projects ADD COLUMN technologies TEXT NULL AFTER completed_on;

-- Taglines (short subtitle under the project name)
INSERT INTO translations (entity, entity_id, lang, field, value) VALUES
('project', 1, 'fr', 'tagline', 'Un site vitrine qui transforme les visites en demandes de devis'),
('project', 2, 'fr', 'tagline', 'Le site d’un installateur de bornes de recharge à Angers'),
('project', 3, 'fr', 'tagline', 'Un site clair pour un spécialiste de la rénovation de l’habitat'),
('project', 4, 'fr', 'tagline', 'Un site direct pour les travaux et dépannages électriques'),
('project', 5, 'fr', 'tagline', 'Une mascotte et un logo faciles à reconnaître'),
('project', 6, 'fr', 'tagline', 'Un logo qui dit le métier au premier coup d’œil'),
('project', 7, 'fr', 'tagline', 'Des publications régulières aux couleurs de la marque'),
('project', 8, 'fr', 'tagline', 'Des visuels Instagram qui mettent en avant les chantiers'),
('project', 1, 'en', 'tagline', 'A showcase website that turns visits into quote requests'),
('project', 2, 'en', 'tagline', 'The website of an EV charging station installer in Angers'),
('project', 3, 'en', 'tagline', 'A clear website for a home renovation specialist'),
('project', 4, 'en', 'tagline', 'A straightforward website for electrical work and repairs'),
('project', 5, 'en', 'tagline', 'A mascot and logo that are easy to recognise'),
('project', 6, 'en', 'tagline', 'A logo that tells you the trade at a glance'),
('project', 7, 'en', 'tagline', 'Regular, on-brand social media posts'),
('project', 8, 'en', 'tagline', 'Instagram visuals that showcase completed work'),
('project', 1, 'ar', 'tagline', 'موقع تعريفي يحوّل الزيارات إلى طلبات عروض أسعار'),
('project', 2, 'ar', 'tagline', 'موقع شركة لتركيب محطات شحن السيارات في أنجيه'),
('project', 3, 'ar', 'tagline', 'موقع واضح لمتخصص في تجديد المساكن'),
('project', 4, 'ar', 'tagline', 'موقع مباشر لأشغال الكهرباء والإصلاح'),
('project', 5, 'ar', 'tagline', 'شخصية رمزية وشعار يسهل التعرف عليهما'),
('project', 6, 'ar', 'tagline', 'شعار يعبّر عن المهنة من النظرة الأولى'),
('project', 7, 'ar', 'tagline', 'منشورات منتظمة بألوان العلامة'),
('project', 8, 'ar', 'tagline', 'تصاميم Instagram تُبرز الأعمال المنجزة');

-- Key features of the four websites, as visible on the delivered sites
INSERT INTO translations (entity, entity_id, lang, field, value) VALUES
('project', 1, 'fr', 'features', 'Une page par équipement : pompe à chaleur, ballon thermodynamique, VMC\nOffres et promotions mises en avant dès l’accueil\nFormulaire de contact présent sur chaque page\nCoordonnées et réseaux sociaux toujours visibles\nNavigation adaptée au mobile'),
('project', 2, 'fr', 'features', 'Présentation des bornes proposées (Wallbox, EVBox, Hager)\nExplication des aides : TVA réduite et crédit d’impôt\nBouton « Demande de devis » dans l’en-tête\nPage contact avec téléphone et email directs\nDesign adapté au mobile et à la tablette'),
('project', 3, 'fr', 'features', 'Pages détaillées par service : menuiserie, isolation, toiture\nGalerie de réalisations avec localisation\nAvis clients mis en avant\nCarte et horaires d’ouverture\nAdresse, email et téléphone en haut de chaque page'),
('project', 4, 'fr', 'features', 'Boutons « Demande d’intervention » et « Appeler » toujours visibles\nPrestations présentées avec des pictogrammes\nZone d’intervention expliquée\nFormulaire de demande de devis\nMarques partenaires affichées'),
('project', 1, 'en', 'features', 'One page per product: heat pump, thermodynamic water heater, ventilation\nOffers and promotions highlighted on the home page\nContact form on every page\nContact details and social links always visible\nMobile-friendly navigation'),
('project', 2, 'en', 'features', 'Presentation of the charging stations on offer\nFinancial incentives explained\n“Request a quote” button in the header\nContact page with direct phone and email\nMobile and tablet friendly design'),
('project', 3, 'en', 'features', 'Detailed service pages: joinery, insulation, roofing\nGallery of completed work with locations\nCustomer reviews highlighted\nMap and opening hours\nAddress, email and phone at the top of every page'),
('project', 4, 'en', 'features', '“Request a call-out” and “Call” buttons always visible\nServices presented with icons\nService area explained\nQuote request form\nPartner brands displayed'),
('project', 1, 'ar', 'features', 'صفحة لكل جهاز: مضخة حرارية، سخان ديناميكي حراري، تهوية\nعروض بارزة في الصفحة الرئيسية\nنموذج اتصال في كل صفحة\nمعلومات الاتصال ظاهرة دائمًا\nتصفح متوافق مع الجوال'),
('project', 2, 'ar', 'features', 'عرض محطات الشحن المقترحة\nشرح المساعدات المالية المتاحة\nزر «طلب عرض سعر» في أعلى الصفحة\nصفحة اتصال بالهاتف والبريد مباشرة\nتصميم متوافق مع الجوال والجهاز اللوحي'),
('project', 3, 'ar', 'features', 'صفحات مفصلة لكل خدمة: نوافذ، عزل، أسقف\nمعرض للأعمال المنجزة\nإبراز آراء العملاء\nخريطة ومواعيد العمل\nالعنوان والبريد والهاتف أعلى كل صفحة'),
('project', 4, 'ar', 'features', 'زرّا «طلب تدخل» و«اتصال» ظاهران دائمًا\nعرض الخدمات بأيقونات\nشرح منطقة التدخل\nنموذج طلب عرض سعر\nعرض العلامات الشريكة');

-- Short highlights (icon|text) shown under the project overview
INSERT INTO translations (entity, entity_id, lang, field, value) VALUES
('project', 1, 'fr', 'highlights', 'browser|Design moderne et responsive\nmail|Demande de devis sur chaque page\nsearch|Pages optimisées pour Google'),
('project', 2, 'fr', 'highlights', 'browser|Design moderne et responsive\nbolt|Offre de bornes claire\nmail|Parcours de devis simple'),
('project', 3, 'fr', 'highlights', 'browser|Design moderne et responsive\nimage|Galerie de réalisations\nstar|Avis clients en avant'),
('project', 4, 'fr', 'highlights', 'browser|Design moderne et responsive\nphone|Appel en un clic\nmail|Demande d’intervention rapide'),
('project', 1, 'en', 'highlights', 'browser|Modern, responsive design\nmail|Quote request on every page\nsearch|Pages optimised for Google'),
('project', 2, 'en', 'highlights', 'browser|Modern, responsive design\nbolt|Clear charging station offer\nmail|Simple quote journey'),
('project', 3, 'en', 'highlights', 'browser|Modern, responsive design\nimage|Gallery of completed work\nstar|Customer reviews highlighted'),
('project', 4, 'en', 'highlights', 'browser|Modern, responsive design\nphone|One-tap calling\nmail|Fast call-out requests'),
('project', 1, 'ar', 'highlights', 'browser|تصميم حديث ومتجاوب\nmail|طلب عرض سعر في كل صفحة\nsearch|صفحات محسّنة لـ Google'),
('project', 2, 'ar', 'highlights', 'browser|تصميم حديث ومتجاوب\nbolt|عرض واضح لمحطات الشحن\nmail|مسار بسيط لطلب السعر'),
('project', 3, 'ar', 'highlights', 'browser|تصميم حديث ومتجاوب\nimage|معرض للأعمال المنجزة\nstar|إبراز آراء العملاء'),
('project', 4, 'ar', 'highlights', 'browser|تصميم حديث ومتجاوب\nphone|اتصال بنقرة واحدة\nmail|طلب تدخل سريع');

INSERT INTO migrations (name) VALUES ('005_project_details');
