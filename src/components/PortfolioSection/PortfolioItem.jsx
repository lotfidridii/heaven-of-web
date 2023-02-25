import { useState } from "react";
import Slide from "react-reveal/Slide";
import {
  UilTimes,
  UilAngleLeft,
  UilAngleRight,
  UilArrowRight,
} from "@iconscout/react-unicons";
import { Col } from "react-bootstrap";
import Portfolioimg1 from "./img/Portfolio-1.webp";
import Portfolioimg2 from "./img/Portfolio-2.webp";
import Portfolioimg3 from "./img/Portfolio-3.webp";
import Portfolioimg4 from "./img/Portfolio-4.webp";
import Portfolioimg5 from "./img/Portfolio-5.webp";
import Portfolioimg6 from "./img/Portfolio-6.webp";
import Portfolioimg7 from "./img/Portfolio-7.webp";
import Portfolioimg8 from "./img/Portfolio-8.webp";
const images = [
  {
    src: Portfolioimg1,
    alt: "France Énergie Solution",
    title: "Conception de site web",
    link: "https://www.france-es.fr",
    linkText: "voir le site web",
  },
  {
    src: Portfolioimg2,
    alt: "Borne Home",
    title: "Conception de site web",
    link: "https://www.bornehome.fr",
    linkText: "voir le site web",
  },
  {
    src: Portfolioimg3,
    alt: "Fenêtre & confort",
    title: "Conception de site web",
    link: "https://www.fenetreetconfort.fr",
    linkText: "voir le site web",
  },
  {
    src: Portfolioimg4,
    alt: "IZI Elec",
    title: "Conception de site web",
    link: "https://www.izielec.fr",
    linkText: "voir le site web",
  },
  {
    src: Portfolioimg5,
    alt: "IZI Elec",
    title: "Conception d'un logo",
    link: Portfolioimg5,
  },
  {
    src: Portfolioimg6,
    alt: "Borne Home",
    title: "Conception d'un logo",
    link: Portfolioimg6,
  },
  {
    src: Portfolioimg7,
    alt: "Borne Home",
    title: "Stratégie de contenu",
    link: "https://www.instagram.com/france_energie_solution/",
  },
  {
    src: Portfolioimg8,
    alt: "Borne Home",
    title: "Stratégie de contenu",
    link: "https://www.instagram.com/fenetreetconfort/",
  },
];
const PortfolioItem = () => {
  const [lightboxIsOpen, setLightboxIsOpen] = useState(false);
  const [currentImage, setCurrentImage] = useState(0);

  const openLightbox = (index) => {
    setCurrentImage(index);
    setLightboxIsOpen(true);
    document.body.style.overflow = "hidden";
  };

  const closeLightbox = () => {
    setLightboxIsOpen(false);
    document.body.style.overflow = "auto";
  };

  const goToNextImage = () => {
    setCurrentImage((currentImage + 1) % images.length);
  };

  const goToPrevImage = () => {
    setCurrentImage((currentImage + images.length - 1) % images.length);
  };

  return (
    <>
      {images.map((image, index) => (
        <Col lg="3" md="6" className="portfolio-item">
          <div className="portfolio-wrap">
            <div className="portfolio-img">
              <img
                key={image.alt}
                src={image.src}
                alt={image.alt}
                className="portfolio-img"
                onClick={() => openLightbox(index)}
              />
              <div className="portfolio-text">
                <p>
                  {image.title}
                  <br />
                  <a href={image.link} target="_blank" rel="noreferrer">
                    {image.linkText}
                    {image.linkText ? <UilArrowRight /> : null}
                  </a>
                </p>
              </div>
            </div>
          </div>
        </Col>
      ))}
      {lightboxIsOpen && (
        <Lightbox
          image={images[currentImage]}
          closeLightbox={closeLightbox}
          goToNextImage={goToNextImage}
          goToPrevImage={goToPrevImage}
        />
      )}
    </>
  );
};

const Lightbox = ({ image, closeLightbox, goToNextImage, goToPrevImage }) => (
  <Slide bottom>
    <div className="lightbox">
      <img src={image.src} alt={image.alt} />
      <button className="close" onClick={closeLightbox}>
        <UilTimes />
      </button>
      <button className="prev" onClick={goToPrevImage}>
        <UilAngleLeft />
      </button>
      <button className="next" onClick={goToNextImage}>
        <UilAngleRight />
      </button>
    </div>
  </Slide>
);
export default PortfolioItem;
