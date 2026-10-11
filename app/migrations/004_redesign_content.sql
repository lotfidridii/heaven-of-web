-- Content for the redesigned pages: hero eyebrow, highlight strip, stats, global process, hero photo.

INSERT INTO settings (skey, lang, value) VALUES
('img_hero', '', 'uploads/seed/hero.webp'),

('hero_eyebrow', 'fr', 'Votre partenaire digital'),
('hero_eyebrow', 'en', 'Your digital partner'),
('hero_eyebrow', 'ar', 'شريكك الرقمي'),

('highlights', 'fr', 'rocket|Sites modernes et performants\nshield|Design sur mesure et responsive\nchart|Optimisation SEO pour plus de visibilité\nheadset|Accompagnement de A à Z'),
('highlights', 'en', 'rocket|Modern, fast websites\nshield|Custom, responsive design\nchart|SEO for more visibility\nheadset|Support from start to finish'),
('highlights', 'ar', 'rocket|مواقع حديثة وسريعة\nshield|تصميم خاص ومتجاوب\nchart|تحسين محركات البحث لظهور أكبر\nheadset|مرافقة من البداية إلى النهاية'),

('stats', 'fr', '{projects}|Projets réalisés\n4|Domaines d’expertise\n3|Langues : français, anglais, arabe'),
('stats', 'en', '{projects}|Projects delivered\n4|Areas of expertise\n3|Languages: French, English, Arabic'),
('stats', 'ar', '{projects}|مشروعًا منجزًا\n4|مجالات خبرة\n3|لغات: الفرنسية والإنجليزية والعربية'),

('process_title', 'fr', 'De la conception à la mise en ligne'),
('process_title', 'en', 'From design to launch'),
('process_title', 'ar', 'من التصميم إلى الإطلاق'),
('process_intro', 'fr', 'Une collaboration simple et efficace, pour un résultat à la hauteur de vos attentes.'),
('process_intro', 'en', 'A simple, effective collaboration for a result that lives up to your expectations.'),
('process_intro', 'ar', 'تعاون بسيط وفعّال، لنتيجة ترقى إلى توقعاتك.'),
('process', 'fr', 'Écoute & analyse : Nous échangeons avec vous pour comprendre vos besoins et vos objectifs.\nConception : Nous créons des maquettes modernes et intuitives pour votre projet.\nDéveloppement : Nous développons votre site avec des technologies fiables et performantes.\nTests & optimisation : Nous vérifions chaque détail pour un site rapide, sécurisé et responsive.\nMise en ligne : Votre site est en ligne ! Nous restons à vos côtés pour la suite.'),
('process', 'en', 'Listening & analysis : We talk with you to understand your needs and goals.\nDesign : We create modern, intuitive mock-ups for your project.\nDevelopment : We build your website with reliable, fast technologies.\nTesting & optimisation : We check every detail for a fast, secure, responsive website.\nLaunch : Your website is live! We stay by your side afterwards.'),
('process', 'ar', 'الاستماع والتحليل : نتحاور معك لفهم احتياجاتك وأهدافك.\nالتصميم : نصمم نماذج حديثة وسهلة الاستخدام لمشروعك.\nالتطوير : نطوّر موقعك بتقنيات موثوقة وعالية الأداء.\nالاختبار والتحسين : نتحقق من كل تفصيل لموقع سريع وآمن ومتجاوب.\nالإطلاق : موقعك على الإنترنت! ونبقى إلى جانبك بعد ذلك.')
ON DUPLICATE KEY UPDATE value = VALUES(value);

INSERT INTO migrations (name) VALUES ('004_redesign_content');
