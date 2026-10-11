-- Colour palettes on projects, a link from projects to services, and one page per service.

ALTER TABLE projects ADD COLUMN palette TEXT NULL AFTER cover;
ALTER TABLE projects ADD COLUMN service_id INT UNSIGNED NULL AFTER palette;
ALTER TABLE services ADD COLUMN slug VARCHAR(120) NULL AFTER id;
ALTER TABLE services ADD COLUMN image VARCHAR(255) NULL AFTER icon;
ALTER TABLE services ADD UNIQUE KEY uq_services_slug (slug);

UPDATE services SET slug = 'creation-site-web-angers' WHERE id = 1;
UPDATE services SET slug = 'identite-visuelle-logo-angers' WHERE id = 2;
UPDATE services SET slug = 'referencement-seo-angers' WHERE id = 3;
UPDATE services SET slug = 'reseaux-sociaux-contenus-angers' WHERE id = 4;

UPDATE projects SET service_id = 1 WHERE id IN (1, 2, 3, 4);
UPDATE projects SET service_id = 2 WHERE id IN (5, 6);
UPDATE projects SET service_id = 4 WHERE id IN (7, 8);

-- Approximate brand colours read from the project visuals; check them in the admin.
UPDATE projects SET palette = '[{"hex":"#0F2A4A","name":"Bleu nuit"},{"hex":"#F5B400","name":"Jaune soleil"},{"hex":"#E30613","name":"Rouge promo"},{"hex":"#FFFFFF","name":"Blanc"}]' WHERE id IN (1, 7);
UPDATE projects SET palette = '[{"hex":"#15628F","name":"Bleu borne"},{"hex":"#56B032","name":"Vert électrique"},{"hex":"#2D5B8A","name":"Bleu acier"},{"hex":"#F2F4F5","name":"Gris clair"}]' WHERE id IN (2, 6);
UPDATE projects SET palette = '[{"hex":"#13284A","name":"Bleu marine"},{"hex":"#F26A21","name":"Orange"},{"hex":"#3A4A63","name":"Ardoise"},{"hex":"#FFFFFF","name":"Blanc"}]' WHERE id IN (3, 8);
UPDATE projects SET palette = '[{"hex":"#1B2A4A","name":"Bleu électricien"},{"hex":"#F45B0A","name":"Orange IZI"},{"hex":"#C9CDD2","name":"Gris"},{"hex":"#FFFFFF","name":"Blanc"}]' WHERE id IN (4, 5);

INSERT INTO settings (skey, lang, value) VALUES
('service_areas', '', 'Angers, Avrillé, Trélazé, Les Ponts-de-Cé, Saint-Barthélemy-d’Anjou, Beaucouzé, Saint-Sylvain-d’Anjou, Murs-Érigné, Cholet, Saumur, Segré'),
('service_region', '', 'Maine-et-Loire (49)')
ON DUPLICATE KEY UPDATE value = value;

-- Service page content (FR)
INSERT INTO translations (entity, entity_id, lang, field, value) VALUES
('service', 1, 'fr', 'meta_title', 'Création de site web à Angers — sites vitrines pour artisans et PME'),
('service', 1, 'fr', 'meta_description', 'Création de sites internet à Angers et dans le Maine-et-Loire : sites vitrines rapides, adaptés au mobile et optimisés pour Google. Devis gratuit sous 24 h.'),
('service', 1, 'fr', 'intro', 'Un site vitrine pensé pour vos clients d’Angers et du Maine-et-Loire : rapide, clair sur mobile, et construit pour transformer les visites en appels et en demandes de devis.'),
('service', 1, 'fr', 'body', '## Un site qui travaille pour votre entreprise\n\nLa plupart de vos futurs clients vous découvrent sur leur téléphone, après une recherche comme « électricien Angers » ou « rénovation fenêtres Trélazé ». En quelques secondes, ils décident de vous appeler ou de passer au suivant. Nous concevons chaque page pour ce moment-là : vos services expliqués simplement, vos réalisations en photos, vos coordonnées toujours visibles.\n\n## Pensé pour le référencement local dès le départ\n\nStructure des pages, titres, textes et données structurées : le référencement naturel est intégré à la conception, pas ajouté après coup. Votre site est rapide, sécurisé (HTTPS) et prêt pour Google Business Profile.\n\n## Vous restez autonome\n\nVous modifiez vos textes, vos photos et vos réalisations depuis une interface simple. Nous restons disponibles pour les évolutions, la maintenance et les sauvegardes.'),
('service', 1, 'fr', 'included', 'Maquette sur mesure, validée avec vous avant le développement\nSite adapté au mobile, à la tablette et à l’ordinateur\nRédaction ou optimisation des textes pour Google\nFormulaire de contact et boutons d’appel visibles\nHébergement, nom de domaine et certificat HTTPS configurés\nFormation à la mise à jour du contenu'),
('service', 1, 'fr', 'process', 'Échange découverte : vos métiers, vos clients, vos objectifs\nMaquette : une proposition visuelle de la page d’accueil et des pages clés\nDéveloppement : intégration, textes, photos et réglages SEO\nMise en ligne : tests, Search Console, statistiques\nSuivi : ajustements et accompagnement après le lancement'),
('service', 1, 'fr', 'faq', 'Combien coûte la création d’un site vitrine à Angers ?\nLe prix dépend du nombre de pages et des fonctionnalités. Après un premier échange, nous vous envoyons un devis détaillé et gratuit, sans engagement.\n\nEn combien de temps mon site sera-t-il en ligne ?\nComptez en général 3 à 6 semaines entre la validation de la maquette et la mise en ligne, selon la disponibilité des textes et des photos.\n\nPourrai-je modifier mon site moi-même ?\nOui. Vous disposez d’un espace d’administration simple pour mettre à jour vos textes, photos et réalisations, et nous vous formons à son utilisation.\n\nTravaillez-vous uniquement à Angers ?\nNous sommes basés à Angers et intervenons dans tout le Maine-et-Loire. Nous accompagnons aussi des entreprises partout en France, à distance.'),

('service', 2, 'fr', 'meta_title', 'Création de logo et identité visuelle à Angers'),
('service', 2, 'fr', 'meta_description', 'Création de logo, charte graphique, cartes de visite et visuels publicitaires à Angers. Une identité visuelle cohérente pour votre entreprise, sur tous vos supports.'),
('service', 2, 'fr', 'intro', 'Un logo et une charte graphique qui vous ressemblent, faciles à reconnaître sur votre véhicule, votre devis, votre site et vos réseaux sociaux.'),
('service', 2, 'fr', 'body', '## Une image qui inspire confiance\n\nDans les métiers du bâtiment et des services, l’image compte : un logo soigné et des couleurs cohérentes rassurent avant même le premier échange. Nous créons des identités simples, lisibles en petit comme en grand, qui fonctionnent sur un camion comme sur un écran de téléphone.\n\n## De l’idée aux fichiers prêts à imprimer\n\nNous partons de votre histoire, de vos clients et de votre secteur pour proposer plusieurs pistes. Une fois la piste choisie, nous déclinons le logo et la palette de couleurs sur vos supports, et vous livrons tous les fichiers utiles à votre imprimeur ou à votre lettreur.'),
('service', 2, 'fr', 'included', 'Plusieurs pistes de logo, puis ajustements de la piste retenue\nPalette de couleurs et typographies\nVersions couleur, monochrome et icône seule\nCartes de visite, en-tête de devis, signature email\nVisuels pour réseaux sociaux et publicités\nFichiers pour l’impression et le web (PDF, PNG, SVG)'),
('service', 2, 'fr', 'process', 'Brief : votre activité, vos valeurs, vos concurrents\nPistes créatives : deux ou trois propositions de logo\nAffinage : ajustements de la piste choisie\nDéclinaisons : couleurs, supports et charte graphique\nLivraison : fichiers prêts pour l’impression et le web'),
('service', 2, 'fr', 'faq', 'Combien de propositions de logo vais-je recevoir ?\nNous présentons en général deux à trois pistes différentes, puis nous affinons celle que vous préférez jusqu’à la version finale.\n\nLe logo m’appartient-il ?\nOui. À la livraison, vous recevez tous les fichiers sources et les droits d’utilisation de votre logo.\n\nPouvez-vous moderniser un logo existant ?\nOui, nous pouvons faire évoluer votre logo actuel en gardant ce qui fait sa reconnaissance auprès de vos clients.'),

('service', 3, 'fr', 'meta_title', 'Référencement naturel (SEO) à Angers — être visible sur Google'),
('service', 3, 'fr', 'meta_description', 'Référencement naturel et SEO local à Angers : optimisation de votre site, contenus ciblés et fiche Google Business Profile pour attirer des clients près de chez vous.'),
('service', 3, 'fr', 'intro', 'Apparaître quand vos futurs clients cherchent votre métier à Angers et dans les communes autour : optimisation technique, contenus ciblés et fiche Google Business Profile.'),
('service', 3, 'fr', 'body', '## Le référencement local, priorité des entreprises de proximité\n\nQuand un particulier cherche un artisan, Google affiche d’abord une carte avec trois entreprises, puis les sites les mieux positionnés. Être présent à ces deux endroits fait souvent la différence entre un téléphone qui sonne et un site que personne ne voit.\n\n## Une méthode claire et mesurable\n\nNous commençons par un audit : vitesse du site, structure, mots-clés recherchés dans votre zone, fiche Google, concurrence. Nous corrigeons ensuite les points techniques, renforçons vos pages de services et de villes, et optimisons votre fiche Google Business Profile. Chaque mois, vous savez où vous en êtes : positions, visites et demandes reçues.'),
('service', 3, 'fr', 'included', 'Audit technique et analyse des mots-clés de votre zone\nOptimisation des titres, textes et pages de services\nCréation de pages ciblées par ville ou par prestation\nOptimisation de la fiche Google Business Profile\nStratégie d’avis clients\nSuivi mensuel des positions et des demandes'),
('service', 3, 'fr', 'process', 'Audit : état du site, concurrence et mots-clés locaux\nCorrections techniques : vitesse, structure, indexation\nContenus : pages services et villes, textes optimisés\nGoogle Business Profile : fiche complète, photos, avis\nSuivi : rapport mensuel et nouvelles actions'),
('service', 3, 'fr', 'faq', 'En combien de temps voit-on les résultats du référencement ?\nLes premiers effets apparaissent souvent après 2 à 3 mois. Le référencement est un travail régulier : les positions se consolident sur 6 à 12 mois.\n\nPouvez-vous garantir la première place sur Google ?\nNon, et personne ne le peut honnêtement. Nous nous engageons en revanche sur une méthode, des actions concrètes et un suivi transparent de vos résultats.\n\nFaut-il refaire mon site pour être bien référencé ?\nPas forcément. L’audit nous dit si votre site actuel peut être optimisé ou si une refonte sera plus efficace.'),

('service', 4, 'fr', 'meta_title', 'Gestion des réseaux sociaux et contenus à Angers'),
('service', 4, 'fr', 'meta_description', 'Création de contenus et gestion des réseaux sociaux à Angers : publications, visuels et articles qui montrent vos chantiers et votre savoir-faire.'),
('service', 4, 'fr', 'intro', 'Des publications régulières et des visuels soignés qui montrent vos chantiers, votre équipe et votre savoir-faire, sur Instagram, Facebook et LinkedIn.'),
('service', 4, 'fr', 'body', '## Montrer votre travail, régulièrement\n\nVos clients regardent vos réseaux sociaux pour se faire une idée avant de vous contacter. Un compte actif, avec des photos de chantiers et des visuels cohérents avec votre identité, rassure et donne envie de vous faire confiance.\n\n## Un calendrier simple, des contenus prêts à publier\n\nNous préparons un calendrier éditorial adapté à votre activité et à vos saisons : promotions, réalisations, conseils, recrutement. Nous créons les visuels aux couleurs de votre marque et rédigeons les textes ; vous validez, nous publions.'),
('service', 4, 'fr', 'included', 'Calendrier éditorial mensuel\nVisuels aux couleurs de votre marque\nRédaction des publications et des articles de blog\nMise en valeur de vos chantiers et réalisations\nPublication sur Instagram, Facebook et LinkedIn\nBilan mensuel des statistiques'),
('service', 4, 'fr', 'process', 'Diagnostic : vos comptes, votre audience, vos objectifs\nLigne éditoriale : thèmes, ton et fréquence\nCréation : visuels et textes préparés à l’avance\nValidation : vous relisez avant publication\nBilan : statistiques et ajustements chaque mois'),
('service', 4, 'fr', 'faq', 'Sur quels réseaux sociaux faut-il être présent ?\nPour la plupart des artisans et entreprises locales, Instagram et Facebook sont prioritaires. LinkedIn est utile si vous travaillez avec des professionnels.\n\nCombien de publications par mois ?\nNous recommandons en général 8 à 12 publications par mois, mais le rythme s’adapte à votre budget et à votre actualité.\n\nDois-je fournir les photos ?\nVos photos de chantiers sont précieuses et nous les mettons en valeur. Nous complétons avec des visuels créés à vos couleurs.');

-- English
INSERT INTO translations (entity, entity_id, lang, field, value) VALUES
('service', 1, 'en', 'meta_title', 'Website design in Angers, France — websites for tradespeople and SMEs'),
('service', 1, 'en', 'meta_description', 'Website design in Angers and Maine-et-Loire: fast, mobile-friendly showcase websites built for Google. Free quote within one business day.'),
('service', 1, 'en', 'intro', 'A showcase website built for your customers around Angers: fast, clear on mobile, and designed to turn visits into calls and quote requests.'),
('service', 1, 'en', 'body', '## A website that works for your business\n\nMost of your future customers find you on their phone, after searching for your trade and town. Within seconds they decide to call you or move on. We design every page for that moment: your services explained simply, your work in photos, your contact details always in view.\n\n## Built for local search from day one\n\nPage structure, titles, copy and structured data: SEO is part of the design, not an afterthought. You can update your text, photos and projects yourself from a simple admin.'),
('service', 1, 'en', 'included', 'Custom design approved by you before development\nMobile, tablet and desktop layouts\nCopy written or optimised for Google\nContact form and visible call buttons\nHosting, domain and HTTPS set up\nTraining to update your content'),
('service', 1, 'en', 'process', 'Discovery call: your trade, customers and goals\nDesign: home page and key pages mock-ups\nBuild: development, copy, photos and SEO settings\nLaunch: testing, Search Console and analytics\nFollow-up: adjustments and support after launch'),
('service', 1, 'en', 'faq', 'How much does a website cost?\nIt depends on the number of pages and features. After a first call we send you a detailed, free quote.\n\nHow long does it take?\nUsually 3 to 6 weeks between design approval and launch, depending on when text and photos are ready.\n\nCan I edit the website myself?\nYes, you get a simple admin area and we show you how to use it.'),
('service', 2, 'en', 'meta_title', 'Logo design and visual identity in Angers, France'),
('service', 2, 'en', 'meta_description', 'Logo design, brand guidelines, business cards and ad visuals in Angers. A consistent visual identity across all your materials.'),
('service', 2, 'en', 'intro', 'A logo and brand guidelines that feel like you, easy to recognise on your van, your quotes, your website and your social media.'),
('service', 2, 'en', 'included', 'Several logo concepts, then refinements\nColour palette and typefaces\nColour, monochrome and icon-only versions\nBusiness cards, quote header, email signature\nSocial media and advertising visuals\nPrint and web files (PDF, PNG, SVG)'),
('service', 2, 'en', 'process', 'Brief: your business, values and competitors\nConcepts: two or three logo proposals\nRefinement: adjustments to the chosen concept\nRoll-out: colours, materials and guidelines\nDelivery: print-ready and web files'),
('service', 2, 'en', 'faq', 'How many logo concepts will I get?\nUsually two or three different directions, then we refine the one you prefer.\n\nDo I own the logo?\nYes. You receive all source files and the rights to use your logo.'),
('service', 3, 'en', 'meta_title', 'SEO in Angers, France — get found on Google'),
('service', 3, 'en', 'meta_description', 'Local SEO in Angers: website optimisation, targeted content and Google Business Profile to attract customers near you.'),
('service', 3, 'en', 'intro', 'Show up when future customers search for your trade around Angers: technical optimisation, targeted content and your Google Business Profile.'),
('service', 3, 'en', 'included', 'Technical audit and local keyword research\nOptimised titles, copy and service pages\nPages targeting towns and services\nGoogle Business Profile optimisation\nCustomer review strategy\nMonthly ranking and enquiry reports'),
('service', 3, 'en', 'process', 'Audit: website, competitors and local keywords\nTechnical fixes: speed, structure, indexing\nContent: service and town pages\nGoogle Business Profile: complete listing, photos, reviews\nFollow-up: monthly report and next actions'),
('service', 3, 'en', 'faq', 'How long before SEO shows results?\nFirst effects often appear after 2 to 3 months; rankings consolidate over 6 to 12 months.\n\nCan you guarantee the first position on Google?\nNo, and nobody honestly can. We commit to a clear method and transparent reporting.'),
('service', 4, 'en', 'meta_title', 'Social media management and content in Angers, France'),
('service', 4, 'en', 'meta_description', 'Content creation and social media management in Angers: posts, visuals and articles that show your work and expertise.'),
('service', 4, 'en', 'intro', 'Regular posts and polished visuals that show your work, your team and your expertise on Instagram, Facebook and LinkedIn.'),
('service', 4, 'en', 'included', 'Monthly content calendar\nOn-brand visuals\nPost and blog article writing\nShowcasing your completed work\nPublishing on Instagram, Facebook and LinkedIn\nMonthly statistics report'),
('service', 4, 'en', 'process', 'Review: your accounts, audience and goals\nEditorial line: topics, tone and frequency\nCreation: visuals and copy prepared ahead\nApproval: you review before publishing\nReport: statistics and adjustments every month'),
('service', 4, 'en', 'faq', 'Which social networks should I be on?\nFor most local businesses, Instagram and Facebook come first. LinkedIn helps if you work with professionals.\n\nHow many posts per month?\nUsually 8 to 12, adapted to your budget and news.');

-- Arabic
INSERT INTO translations (entity, entity_id, lang, field, value) VALUES
('service', 1, 'ar', 'meta_title', 'تصميم مواقع الويب في أنجيه — مواقع للحرفيين والشركات الصغيرة'),
('service', 1, 'ar', 'meta_description', 'تصميم مواقع إنترنت في أنجيه ومنطقة مين إي لوار: مواقع تعريفية سريعة ومتوافقة مع الجوال ومحسّنة لـ Google. عرض سعر مجاني.'),
('service', 1, 'ar', 'intro', 'موقع تعريفي مصمم لعملائك في أنجيه وما حولها: سريع وواضح على الجوال، ومبني لتحويل الزيارات إلى اتصالات وطلبات عروض أسعار.'),
('service', 1, 'ar', 'included', 'تصميم خاص توافق عليه قبل التطوير\nتوافق مع الجوال والجهاز اللوحي والحاسوب\nكتابة أو تحسين النصوص لمحركات البحث\nنموذج اتصال وأزرار اتصال ظاهرة\nإعداد الاستضافة واسم النطاق وشهادة HTTPS\nتدريب على تحديث المحتوى'),
('service', 1, 'ar', 'process', 'لقاء تعارف: مهنتك وعملاؤك وأهدافك\nالتصميم: نموذج للصفحة الرئيسية والصفحات الأساسية\nالتطوير: البرمجة والنصوص والصور وإعدادات SEO\nالإطلاق: الاختبارات وSearch Console والإحصاءات\nالمتابعة: تعديلات ودعم بعد الإطلاق'),
('service', 1, 'ar', 'faq', 'كم تكلفة تصميم موقع تعريفي؟\nيعتمد السعر على عدد الصفحات والوظائف. بعد أول لقاء نرسل لك عرض سعر مفصلًا ومجانيًا.\n\nكم يستغرق إنجاز الموقع؟\nعادةً من 3 إلى 6 أسابيع بين الموافقة على التصميم والإطلاق.'),
('service', 2, 'ar', 'meta_title', 'تصميم الشعارات والهوية البصرية في أنجيه'),
('service', 2, 'ar', 'meta_description', 'تصميم شعارات وأدلة هوية وبطاقات أعمال وتصاميم إعلانية في أنجيه، لهوية متناسقة على كل وسائلك.'),
('service', 2, 'ar', 'intro', 'شعار وهوية بصرية تشبهك، يسهل التعرف عليهما على مركبتك وعروض أسعارك وموقعك وشبكاتك الاجتماعية.'),
('service', 2, 'ar', 'included', 'عدة مقترحات للشعار ثم تعديلات\nلوحة ألوان وخطوط\nنسخ ملونة وأحادية اللون وأيقونة\nبطاقات أعمال وترويسة وتوقيع بريد\nتصاميم للشبكات الاجتماعية والإعلانات\nملفات للطباعة والويب'),
('service', 3, 'ar', 'meta_title', 'تحسين محركات البحث (SEO) في أنجيه'),
('service', 3, 'ar', 'meta_description', 'تحسين محلي لمحركات البحث في أنجيه: تحسين موقعك ومحتوى موجه وملف Google Business Profile لجذب عملاء قريبين منك.'),
('service', 3, 'ar', 'intro', 'اظهر عندما يبحث عملاؤك المستقبليون عن مهنتك في أنجيه وما حولها: تحسين تقني ومحتوى موجه وملف Google Business Profile.'),
('service', 3, 'ar', 'included', 'تدقيق تقني وتحليل الكلمات المفتاحية المحلية\nتحسين العناوين والنصوص وصفحات الخدمات\nصفحات موجهة حسب المدينة أو الخدمة\nتحسين ملف Google Business Profile\nاستراتيجية آراء العملاء\nتقرير شهري للنتائج'),
('service', 4, 'ar', 'meta_title', 'إدارة الشبكات الاجتماعية وصناعة المحتوى في أنجيه'),
('service', 4, 'ar', 'meta_description', 'صناعة المحتوى وإدارة الشبكات الاجتماعية في أنجيه: منشورات وتصاميم ومقالات تُبرز أعمالك وخبرتك.'),
('service', 4, 'ar', 'intro', 'منشورات منتظمة وتصاميم أنيقة تُبرز أعمالك وفريقك وخبرتك على Instagram وFacebook وLinkedIn.'),
('service', 4, 'ar', 'included', 'جدول نشر شهري\nتصاميم بألوان علامتك\nكتابة المنشورات والمقالات\nإبراز أعمالك المنجزة\nالنشر على Instagram وFacebook وLinkedIn\nتقرير إحصائي شهري');

INSERT INTO migrations (name) VALUES ('003_palette_service_pages');
