import { Container } from "react-bootstrap";
import TunisiaFlag from "./tunisia.webp";
import "./Footer.css"
const currentYear = new Date().getFullYear();
function Footer() {
  return (
    <footer className="footer">
      <Container fluid>
        <Container className="copyright">
          <p>
            &copy; {currentYear} <a href="#accueil">Heaven of web</a>, Tous droits réservés | Made in Tunisia <img src={TunisiaFlag} alt="" />
          </p>
        </Container>
      </Container>
    </footer>
  );
}

export default Footer;
