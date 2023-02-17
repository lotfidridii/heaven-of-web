import { useState, useEffect } from "react";
import Container from "react-bootstrap/Container";
import Bounce from "react-reveal/Bounce";
import {
  UilEstate,
  UilUser,
  UilMultiply,
  UilApps,
  UilSuitcaseAlt,
  UilImage,
  UilMessage,
} from "@iconscout/react-unicons";
import logo from "./logo.webp";
import "./Header.css";

const sections = [
  { id: "accueil", icon: UilEstate, text: "Accueil" },
  { id: "àpropos", icon: UilUser, text: "À propos" },
  { id: "services", icon: UilSuitcaseAlt, text: "Services" },
  { id: "portfolio", icon: UilImage, text: "Réalisations" },
  { id: "contact", icon: UilMessage, text: "Contact" },
];
function Header() {
  const [menuClass, setMenuClass] = useState("");
  const [logoClass, setLogoClass] = useState("");
  const [scrollY, setScrollY] = useState(0);

  const [activeSection, setActiveSection] = useState(null);

  useEffect(() => {
    const sections = document.querySelectorAll("[id]");

    const handleScroll = () => {
      const scrollY = window.pageYOffset;

      sections.forEach((current) => {
        const sectionHeight = current.offsetHeight;
        const sectionTop = current.offsetTop - 250;
        const sectionId = current.getAttribute("id");

        if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
          setActiveSection(sectionId);
        }
      });
    };

    window.addEventListener("scroll", handleScroll);
    return () => {
      window.removeEventListener("scroll", handleScroll);
    };
  }, []);

  useEffect(() => {
    function handleScroll() {
      setScrollY(window.pageYOffset);
    }

    window.addEventListener("scroll", handleScroll);
    return () => {
      window.removeEventListener("scroll", handleScroll);
    };
  }, []);

  const handleToggle = () => {
    setMenuClass("show-menu");
    setLogoClass("hide");
  };

  const handleClose = () => {
    setMenuClass("");
  };

  const handleLinkClick = () => {
    setMenuClass("");
  };

  const headerClass = scrollY >= 80 ? "scroll-header" : "";

  return (
    <div className={`header ${headerClass}`} id="header">
      <Container>
        <nav className="nav">
          <Bounce top delay={500}>
            <a
              href="#accueil"
              className={`nav_logo ${logoClass}`}
              id="nav-logo"
            >
              <img src={logo} alt="Logo" />
            </a>
          </Bounce>

          <div className={`nav_menu ${menuClass}`} id="nav-menu">
            <ul className="nav_list grid">
              {sections.map((section) => (
                <li className="nav_item" key={section.id}>
                  <a
                    href={`#${section.id}`}
                    className={`nav_link ${
                      activeSection === section.id ? "active-link" : ""
                    }`}
                    onClick={handleLinkClick}
                  >
                    <section.icon className="nav_icons" />
                    {section.text}
                  </a>
                </li>
              ))}
            </ul>
            <UilMultiply
              className="nav_close"
              id="nav-close"
              onClick={handleClose}
            />
          </div>
          <div className="nav_btns">
            <div className="nav_toggle" id="nav-toggle" onClick={handleToggle}>
              <UilApps />
            </div>
          </div>
        </nav>
      </Container>
    </div>
  );
}

export default Header;
