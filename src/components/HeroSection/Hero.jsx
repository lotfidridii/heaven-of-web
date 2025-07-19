import { Container, Row, Col } from "react-bootstrap";
import { motion } from "framer-motion";
import { Player } from "@lottiefiles/react-lottie-player";
import Buttons from "../Buttons/Buttons";
import TypedText from "./TypedText";
import ScrollAnimation from "./Scroll.json";
import "./Hero.css";
function Hero() {
  return (
    <section className="hero" id="accueil">
      <Container>
        <Row>
          <Col sm="12" md="12">
            <div className="hero-content">
              <div className="hero-text">
                <motion.h1
                  initial={{ x: 100, opacity: 0 }}
                  animate={{ x: 0, opacity: 1 }}
                  transition={{ duration: 1, delay: 0.5 }}
                >
                  HEAVEN OF WEB
                </motion.h1>
                <motion.h2
                  className="typed-text"
                  initial={{ x: -100, opacity: 0 }}
                  animate={{ x: 0, opacity: 1 }}
                  transition={{ duration: 1, delay: 1 }}
                >
                  <TypedText />
                </motion.h2>
              </div>
              <div className="hero-btn">
                <Buttons href="#contact" btnName="Contactez-nous" />
              </div>
              <div className="hero-scroll">
                <a href="#àpropos">
                  <Player
                    autoplay
                    loop
                    src={ScrollAnimation}
                    style={{ height: '75px', width: '75px' }}
                  />
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
