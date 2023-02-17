import { Container, Row, Col } from "react-bootstrap";
import { Fade, Zoom } from "react-reveal";
import Buttons from "../Buttons/Buttons";
import TypedText from "./TypedText";
import Lottie from "react-lottie";
import ScrollAnimation from "./Scroll.json";
import "./Hero.css";
const defaultOptions = {
  loop: true,
  autoplay: true,
  animationData: ScrollAnimation,
  rendererSettings: {
    preserveAspectRatio: "xMidYMid meet",
  },
};
function Hero() {
  return (
    <section className="hero" id="accueil">
      <Container>
        <Row>
          <Col sm="12" md="12">
            <div className="hero-content">
              <div className="hero-text">
                <Zoom right cascade count={2} timeout={5000} mirror>
                  <h1>HEAVEN OF WEB</h1>
                </Zoom>
                <Fade left>
                  <h2 className="typed-text">
                    <TypedText />
                  </h2>
                </Fade>
              </div>
              <div className="hero-btn">
                <Buttons href="#contact" btnName="Contactez-nous" />
              </div>
              <div className="hero-scroll">
                <a href="#àpropos">
                  <Lottie options={defaultOptions} height={75} />
                </a>
              </div>
            </div>
          </Col>
        </Row>
      </Container>
    </section>
  );
}

export default Hero;
