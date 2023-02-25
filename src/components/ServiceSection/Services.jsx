import { Container, Row } from "react-bootstrap";
import SectionHeader from "../SectionHeader/SectionHeader";
import ServiceItem from "./ServiceItems";
import {
  UilLaptop,
  UilPen,
  UilSearchAlt,
  UilChartLine,
} from "@iconscout/react-unicons";
import Lottie from "react-lottie";
import ServiceAnimation from "./lottie/services.json";
import "./Services.css";
const defaultOptions = {
  loop: true,
  autoplay: true,
  animationData: ServiceAnimation,
  rendererSettings: {
    preserveAspectRatio: "xMidYMid meet",
  },
};

function Services() {
  return (
    <section className="service" id="services">
      <Container>
        <SectionHeader
          Align="text-center"
          SectionSubTitle="Votre succès en ligne commence ici"
          SectionTitle="découvrez nos services"
        />
        <Row>
          <ServiceItem
            ServiceIcon={<UilLaptop />}
            ServiceTitle="Conception de sites web"
            ServiceDescription="Nous offrons une conception de sites Web professionnelle pour vous aider à construire une présence en ligne forte. Nous travaillons avec vous pour comprendre vos besoins et créer un site Web qui représente votre entreprise de manière cohérente et attrayante."
          />
          <ServiceItem
            ServiceIcon={<UilPen />}
            ServiceTitle="Conception graphique"
            ServiceDescription="Nous créons des logos professionnels pour les entreprises, ainsi que des designs pour les réseaux sociaux et les campagnes publicitaires en ligne. Nous travaillons avec vous pour développer un look cohérent pour votre marque en ligne."
          />
          <ServiceItem
            ServiceIcon={<UilSearchAlt />}
            ServiceTitle="Référencement naturel"
            ServiceDescription="Nous aidons les entreprises à améliorer leur visibilité en ligne grâce à nos stratégies d'optimisation SEO efficaces. Nous croyons que le référencement naturel est la clé d'un site Web réussi et nous sommes déterminés à vous aider à atteindre vos objectifs en ligne."
          />
          <ServiceItem
            ServiceIcon={<UilChartLine />}
            ServiceTitle="Stratégie de contenu"
            ServiceDescription="Nous aidons les entreprises à développer une stratégie de contenu efficace pour améliorer leur présence en ligne. Nous travaillons avec vous pour comprendre vos objectifs et créer du contenu qui les soutient, y compris des articles de blog et des publications sur les réseaux sociaux."
          />
        </Row>
        <Lottie
          options={defaultOptions}
          height={350}
          style={{ margin: " -7% 0" }}
        />
      </Container>
    </section>
  );
}

export default Services;
