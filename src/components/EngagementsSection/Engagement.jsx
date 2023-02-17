import { Row, Container } from "react-bootstrap";
import SectionHeader from "../SectionHeader/SectionHeader";
import EngagementItem from "./EngagementItem";
import "./Engagement.css";
function Engagement() {
  return (
    <section className="engagement">
      <Container>
        <SectionHeader
          Align="text-center"
          SectionSubTitle="Garantissant la qualité, la collaboration, la rapidité et la satisfaction"
          SectionTitle="Nos engagements envers nos clients"
        />
        <div className="engagement-items">
          <Row>
            <EngagementItem />
          </Row>
        </div>
      </Container>
    </section>
  );
}

export default Engagement;
