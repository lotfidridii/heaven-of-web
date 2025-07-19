import React, { memo } from 'react';
import { Col } from "react-bootstrap";
import EngagementImg1 from "./img/Engagement-1.webp";
import EngagementImg2 from "./img/Engagement-2.webp";
import EngagementImg3 from "./img/Engagement-3.webp";
import EngagementImg4 from "./img/Engagement-4.webp";

const ENGAGEMENTS = [
  {
    id: 'quality-service',
    src: EngagementImg1,
    alt: "Qualité du service",
    title: "Qualité du service",
    description:
      "Nous nous engageons à fournir un service de qualité supérieure à tous nos clients, peu importe leur taille ou leur budget. Nous travaillons en étroite collaboration avec nos clients pour comprendre leurs besoins et leur offrir des solutions personnalisées.",
  },
  {
    id: 'effective-collaboration',
    src: EngagementImg2,
    alt: "Collaboration efficace",
    title: "Collaboration efficace",
    description:
      "Nous nous engageons à travailler en étroite collaboration avec nos clients pour comprendre leurs objectifs et leur offrir des solutions qui les aideront à atteindre leurs objectifs en ligne.",
  },
  {
    id: 'speed-reliability',
    src: EngagementImg3,
    alt: "Rapidité et fiabilité",
    title: "Rapidité et fiabilité",
    description:
      "Nous nous engageons à offrir un service rapide et fiable. Nous savons que les projets en ligne peuvent être critiques pour le succès de nos clients et nous nous efforçons de les livrer rapidement et de manière fiable.",
  },
  {
    id: 'guaranteed-satisfaction',
    src: EngagementImg4,
    alt: "Satisfaction garantie",
    title: "Satisfaction garantie",
    description:
      "Nous nous engageons à offrir une satisfaction garantie à tous nos clients. Nous sommes fiers de nos réalisations passées et nous nous engageons à continuer de fournir des services de qualité à l'avenir. Si nos clients ne sont pas satisfaits, nous travaillerons avec eux jusqu'à ce qu'ils le soient.",
  },
];
const EngagementItem = memo(() => {
  return (
    <>
      {ENGAGEMENTS.map((engagement) => (
        <Col key={engagement.id} lg="6">
          <article className="engagement-item">
            <img 
              src={engagement.src} 
              alt={engagement.alt}
              loading="lazy"
              width="75"
              height="75"
            />
            <div className="engagement-text">
              <h3>{engagement.title}</h3>
              <p>{engagement.description}</p>
            </div>
          </article>
        </Col>
      ))}
    </>
  );
});

EngagementItem.displayName = 'EngagementItem';

export default EngagementItem;
