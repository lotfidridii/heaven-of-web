import { Container } from "react-bootstrap";
import "./Footer.css"
const currentYear = new Date().getFullYear();
function Footer() {
  return (
    <footer className="footer">
      <Container fluid>
        <Container className="copyright">
          <p>
            &copy; {currentYear} <a href="#accueil">Heaven of web</a>, Tous droits réservés
          </p>
        </Container>
      </Container>
    </footer>
  );
}

export default Footer;
