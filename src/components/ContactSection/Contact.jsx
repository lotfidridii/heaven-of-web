import { Container, Row, Col } from "react-bootstrap";
import { Player } from "@lottiefiles/react-lottie-player";
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
import { useSiteData } from "../../contexts/SiteDataContext";
import "./Contact.css";

function Contact() {
  const { data } = useSiteData();
  const contactData = data.contact;

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
            <Player
              autoplay
              loop
              src={ContactAnimation}
              style={{ height: '160px', width: '160px' }}
            />
            <div className="contact-menu">
              <a href={contactData.emailLink}>
                <UilEnvelopes /> {contactData.email}
              </a>
              <a href={contactData.phoneLink}>
                <UilPhone /> {contactData.phone}
              </a>
            </div>
            <div className="contact-social">
              {contactData.socialMedia.twitter && (
                <a href={contactData.socialMedia.twitter} target="_blank" rel="noreferrer">
                  <UilTwitterAlt />
                </a>
              )}
              {contactData.socialMedia.facebook && (
                <a href={contactData.socialMedia.facebook} target="_blank" rel="noreferrer">
                  <UilFacebookF />
                </a>
              )}
              {contactData.socialMedia.instagram && (
                <a href={contactData.socialMedia.instagram} target="_blank" rel="noreferrer">
                  <UilInstagram />
                </a>
              )}
              {contactData.socialMedia.linkedin && (
                <a href={contactData.socialMedia.linkedin} target="_blank" rel="noreferrer">
                  <UilLinkedinAlt />
                </a>
              )}
              {contactData.socialMedia.github && (
                <a href={contactData.socialMedia.github} target="_blank" rel="noreferrer">
                  <UilGithub />
                </a>
              )}
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
