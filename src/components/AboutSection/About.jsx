import { Row, Col, Container } from "react-bootstrap";
import SectionHeader from "../SectionHeader/SectionHeader";
import AboutImg from "./About-img.webp";
import "./About.css";
function About() {
  return (
    <section className="about" id="àpropos">
      <Container>
        <Row>
          <Col lg="6" md={{ span: 12, order: "second" }}>
            <div className="about-content">
              <SectionHeader
                SectionTitle="Qui sommes-nous?"
                SectionSubTitle="Un partenaire fiable pour votre présence en ligne"
              />
              <div className="about-text">
                <p>
                  Heaven of Web est une entreprise spécialisée dans la
                  conception de sites Web professionnels, la conception
                  graphique pour les logos et le contenu de médias sociaux,
                  ainsi que l'optimisation SEO. Nous croyons que votre présence
                  en ligne est la clé de votre réussite commerciale, et nous
                  sommes déterminés à vous aider à atteindre vos objectifs en
                  ligne. Avec notre équipe expérimentée et talentueuse, nous
                  vous offrons des services de qualité supérieure pour vous
                  aider à créer une présence en ligne forte et à vous démarquer
                  de la concurrence.
                  <br />
                  Contactez-nous aujourd'hui pour en savoir plus sur comment
                  nous pouvons vous aider.
                  <br />
                  <strong>
                    Nous construisons votre présence en ligne, pas seulement un
                    site Web.
                  </strong>
                </p>
              </div>
            </div>
          </Col>
          <Col lg="6" md={{ span: 12, order: "first" }}>
            <div className="about-img">
              <img src={AboutImg} alt="Heaven of Web" />
            </div>
          </Col>
        </Row>
      </Container>
    </section>
  );
}

export default About;
