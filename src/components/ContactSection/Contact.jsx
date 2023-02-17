import { Container, Row, Col } from "react-bootstrap";
import Lottie from "react-lottie";
import {
  phoneNumber,
  phoneNumberLink,
  email,
  emailLink,
  facebookLink,
  twitterLink,
  instagramLink,
  linkedinLink,
  githubLink,
} from "./ContactInfo";
import {
  UilTwitterAlt,
  UilFacebookF,
  UilInstagram,
  UilLinkedinAlt,
  UilGithub,
  UilEnvelopes,
  UilPhone,
} from "@iconscout/react-unicons";
import ContactForm from "./ContactForm";
import SectionHeader from "../SectionHeader/SectionHeader";
import ContactAnimation from "./lottie/contact-animation.json";
import "./Contact.css";
const defaultOptions = {
  loop: true,
  autoplay: true,
  animationData: ContactAnimation,
  rendererSettings: {
    preserveAspectRatio: "xMidYMid meet",
  },
};

function Contact() {
  return (
    <section className="contact" id="contact">
      <Container>
        <SectionHeader
          Align="text-center"
          SectionSubTitle="Si vous avez des questions ou si vous souhaitez discuter de votre projet. Nous sommes là pour vous aider."
          SectionTitle="n'hésitez pas à nous contacter"
        />
        <Row>
          <Col md="6" className="contact-info">
            <Lottie options={defaultOptions} height={160} />
            <div className="contact-menu">
              <a href={emailLink}>
                <UilEnvelopes /> {email}
              </a>
              <a href={phoneNumberLink}>
                <UilPhone /> {phoneNumber}
              </a>
            </div>
            <div className="contact-social">
              <a href={twitterLink}>
                <UilTwitterAlt />
              </a>
              <a href={facebookLink}>
                <UilFacebookF />
              </a>
              <a href={instagramLink}>
                <UilInstagram />
              </a>
              <a href={linkedinLink}>
                <UilLinkedinAlt />
              </a>
              <a href={githubLink}>
                <UilGithub />
              </a>
            </div>
          </Col>
          <Col md="6">
            <ContactForm />
          </Col>
        </Row>
      </Container>
    </section>
  );
}

export default Contact;
