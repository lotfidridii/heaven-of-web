import { Col } from "react-bootstrap";
import EngagementImg1 from "./img/Engagement-1.webp";
import EngagementImg2 from "./img/Engagement-2.webp";
import EngagementImg3 from "./img/Engagement-3.webp";
import EngagementImg4 from "./img/Engagement-4.webp";

const Engagements = [
  {
    src: EngagementImg1,
    alt: "Qualité du service    ",
    Title: "Qualité du service",
    Description:
      "Nous nous engageons à fournir un service de qualité supérieure à tous nos clients, peu importe leur taille ou leur budget. Nous travaillons en étroite collaboration avec nos clients pour comprendre leurs besoins et leur offrir des solutions personnalisées.",
  },
  {
    src: EngagementImg2,
    alt: "Collaboration efficace",
    Title: "Collaboration efficace",
    Description:
      "Nous nous engageons à travailler en étroite collaboration avec nos clients pour comprendre leurs objectifs et leur offrir des solutions qui les aideront à atteindre leurs objectifs en ligne.",
  },
  {
    src: EngagementImg3,
    alt: "Rapidité et fiabilité",
    Title: "Rapidité et fiabilité",
    Description:
      "Nous nous engageons à offrir un service rapide et fiable. Nous savons que les projets en ligne peuvent être critiques pour le succès de nos clients et nous nous efforçons de les livrer rapidement et de manière fiable.",
  },
  {
    src: EngagementImg4,
    alt: "Satisfaction garantie",
    Title: "Satisfaction garantie",
    Description:
      "Nous nous engageons à offrir une satisfaction garantie à tous nos clients. Nous sommes fiers de nos réalisations passées et nous nous engageons à continuer de fournir des services de qualité à l'avenir. Si nos clients ne sont pas satisfaits, nous travaillerons avec eux jusqu'à ce qu'ils le soient.",
  },
];
const EngagementItem = () => {
  return (
    <>
      {Engagements.map((Engagement, index) => (
        <Col lg="6">
          <div className="engagement-item">
            <img src={Engagement.src} alt={Engagement.alt} />
            <div className="engagement-text">
              <h3>{Engagement.Title}</h3>
              <p>{Engagement.Description}</p>
            </div>
          </div>
        </Col>
      ))}
    </>
  );
};

export default EngagementItem;
