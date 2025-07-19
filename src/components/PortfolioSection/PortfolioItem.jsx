import { useState, useEffect } from "react";
import { motion, AnimatePresence } from "framer-motion";
import {
  UilTimes,
  UilAngleLeft,
  UilAngleRight,
  UilArrowRight,
} from "@iconscout/react-unicons";
import { Col } from "react-bootstrap";
import { useSiteData } from "../../contexts/SiteDataContext";

// Import all portfolio images statically
import Portfolio1 from "./img/Portfolio-1.webp";
import Portfolio2 from "./img/Portfolio-2.webp";
import Portfolio3 from "./img/Portfolio-3.webp";
import Portfolio4 from "./img/Portfolio-4.webp";
import Portfolio5 from "./img/Portfolio-5.webp";
import Portfolio6 from "./img/Portfolio-6.webp";
import Portfolio7 from "./img/Portfolio-7.webp";
import Portfolio8 from "./img/Portfolio-8.webp";

// Create a mapping for static images
const staticImages = {
  "./img/Portfolio-1.webp": Portfolio1,
  "./img/Portfolio-2.webp": Portfolio2,
  "./img/Portfolio-3.webp": Portfolio3,
  "./img/Portfolio-4.webp": Portfolio4,
  "./img/Portfolio-5.webp": Portfolio5,
  "./img/Portfolio-6.webp": Portfolio6,
  "./img/Portfolio-7.webp": Portfolio7,
  "./img/Portfolio-8.webp": Portfolio8,
};

function PortfolioItem() {
  const { siteData, isLoading, error } = useSiteData();
  const [lightboxImage, setLightboxImage] = useState(null);
  const [currentImageIndex, setCurrentImageIndex] = useState(0);

  // Handle loading and error states
  if (isLoading) {
    return (
      <Col className="text-center py-5">
        <div className="spinner-border text-primary" role="status">
          <span className="visually-hidden">Loading...</span>
        </div>
        <p className="mt-2">Loading portfolio items...</p>
      </Col>
    );
  }

  if (error) {
    return (
      <Col className="text-center py-5">
        <div className="alert alert-danger">
          Error loading portfolio: {error.message}
        </div>
      </Col>
    );
  }

  // Check if siteData or portfolio is not available yet
  if (!siteData || !siteData.portfolio) {
    return (
      <Col className="text-center py-5">
        <p>No portfolio items available at the moment.</p>
      </Col>
    );
  }

  // Process images to handle both static and uploaded images
  const isProduction = process.env.NODE_ENV === 'production';
const uploadServerUrl = isProduction 
  ? 'https://heavenofweb.fr:3001' 
  : 'http://localhost:3001';
  const images = siteData.portfolio.map((item, index) => {
    let imageSrc = '';
    
    if (item.src) {
      // Check if it's a static image first (predefined images)
      if (staticImages[item.src]) {
        imageSrc = staticImages[item.src];
      }
      // For uploaded images, serve them from the upload server
      else if (item.src.includes('Portfolio-') && (item.src.startsWith('./img/') || item.src.startsWith('/img/'))) {
        // Extract filename and serve from upload server
        const filename = item.src.replace('./img/', '').replace('/img/', '');
        // Serve uploaded images from the upload server on port 3001
        imageSrc = `${uploadServerUrl}/uploads/${filename}`;
      }
      // Fallback to original path
      else {
        imageSrc = item.src;
      }
    }
    
    return {
      ...item,
      id: item.id || `item-${index}`, // Ensure each item has a unique id
      src: imageSrc
    };
  }).filter(item => item.src); // Filter out any items without a valid src

  const openLightbox = (index) => {
    setCurrentImageIndex(index);
    setLightboxImage(images[index]);
  };

  const closeLightbox = () => {
    setLightboxImage(null);
  };

  const goToNextImage = () => {
    setCurrentImageIndex((currentImageIndex + 1) % images.length);
    setLightboxImage(images[(currentImageIndex + 1) % images.length]);
  };

  const goToPrevImage = () => {
    setCurrentImageIndex((currentImageIndex - 1 + images.length) % images.length);
    setLightboxImage(images[(currentImageIndex - 1 + images.length) % images.length]);
  };

  return (
    <>
      {images.length === 0 ? (
        <Col className="text-center py-5">
          <p>No portfolio items to display.</p>
        </Col>
      ) : (
        images.map((image, index) => (
          <Col key={image.id || `portfolio-${index}`} lg="3" md="6" className="portfolio-item">
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
        ))
      )}
      <AnimatePresence>
        {lightboxImage && (
          <Lightbox
            image={lightboxImage}
            closeLightbox={closeLightbox}
            goToNextImage={goToNextImage}
            goToPrevImage={goToPrevImage}
          />
        )}
      </AnimatePresence>
    </>
  );
};

const Lightbox = ({ image, closeLightbox, goToNextImage, goToPrevImage }) => {
  useEffect(() => {
    const handleKeyDown = (event) => {
      switch (event.key) {
        case 'Escape':
          closeLightbox();
          break;
        case 'ArrowLeft':
          goToPrevImage();
          break;
        case 'ArrowRight':
          goToNextImage();
          break;
        default:
          break;
      }
    };

    // Add event listener
    document.addEventListener('keydown', handleKeyDown);

    // Cleanup event listener on component unmount
    return () => {
      document.removeEventListener('keydown', handleKeyDown);
    };
  }, [closeLightbox, goToNextImage, goToPrevImage]);

  return (
    <motion.div
      className="lightbox-overlay"
      initial={{ opacity: 0 }}
      animate={{ opacity: 1 }}
      exit={{ opacity: 0 }}
      transition={{ duration: 0.3 }}
      onClick={closeLightbox}
    >
      {/* Navigation Buttons Outside */}
      <motion.button
        className="nav-button prev-button"
        onClick={(e) => {
          e.stopPropagation();
          goToPrevImage();
        }}
        initial={{ opacity: 0, x: -30 }}
        animate={{ opacity: 1, x: 0 }}
        transition={{ duration: 0.4, delay: 0.3 }}
        whileHover={{ scale: 1.1, x: -5 }}
        whileTap={{ scale: 0.9 }}
      >
        <UilAngleLeft />
      </motion.button>
      
      <motion.button
        className="nav-button next-button"
        onClick={(e) => {
          e.stopPropagation();
          goToNextImage();
        }}
        initial={{ opacity: 0, x: 30 }}
        animate={{ opacity: 1, x: 0 }}
        transition={{ duration: 0.4, delay: 0.3 }}
        whileHover={{ scale: 1.1, x: 5 }}
        whileTap={{ scale: 0.9 }}
      >
        <UilAngleRight />
      </motion.button>

      <motion.div
        className="lightbox"
        initial={{ scale: 0.8, opacity: 0, y: 50 }}
        animate={{ scale: 1, opacity: 1, y: 0 }}
        exit={{ scale: 0.8, opacity: 0, y: 50 }}
        transition={{ duration: 0.4, ease: "easeOut" }}
        onClick={(e) => e.stopPropagation()}
      >
        <motion.img
          src={image.src}
          alt={image.alt}
          initial={{ scale: 0.9 }}
          animate={{ scale: 1 }}
          transition={{ duration: 0.3, delay: 0.1 }}
        />
        <motion.button
          className="close"
          onClick={closeLightbox}
          initial={{ opacity: 0, rotate: -90 }}
          animate={{ opacity: 1, rotate: 0 }}
          transition={{ duration: 0.3, delay: 0.2 }}
          whileHover={{ scale: 1.1, rotate: 90 }}
          whileTap={{ scale: 0.9 }}
        >
          <UilTimes />
        </motion.button>

        <motion.div
          className="lightbox-info"
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.3, delay: 0.3 }}
        >
          <p>{image.title}</p>
          {image.link && (
            <a href={image.link} target="_blank" rel="noreferrer">
              {image.linkText}
            </a>
          )}
        </motion.div>
      </motion.div>
    </motion.div>
  );
};
export default PortfolioItem;
