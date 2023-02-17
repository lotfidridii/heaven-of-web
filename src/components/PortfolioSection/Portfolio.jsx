import { Row, Container } from "react-bootstrap";
import SectionHeader from "../SectionHeader/SectionHeader";
import PortfolioItem from "./PortfolioItem";
import "./Portfolio.css";
function Portfolio() {
  return (
    <section className="portfolio" id="portfolio">
      <Container>
        <SectionHeader
          Align="text-center"
          SectionSubTitle="Découvrez les projets que nous avons menés à bien pour nos clients"
          SectionTitle="Nos réalisations"
        />
          <Row className="portfolio-container">
            <PortfolioItem />
          </Row>
      </Container>
    </section>
  );
}

export default Portfolio;
