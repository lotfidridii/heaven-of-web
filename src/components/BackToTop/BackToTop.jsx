import { useState, useEffect } from "react";
import { UilAngleUp } from "@iconscout/react-unicons";
import "./BackToTop.css";
const ScrollUp = () => {
  const [scroll, setScroll] = useState(0);

  useEffect(() => {
    const handleScroll = () => {
      setScroll(window.scrollY);
    };
    window.addEventListener("scroll", handleScroll);

    return () => {
      window.removeEventListener("scroll", handleScroll);
    };
  }, []);

  const showScroll = scroll >= 560;
  const scrollUpClass = showScroll ? "show-scroll" : "";

  return (
    <a href="#accueil" id="scroll-up" className={`scrollup btn ${scrollUpClass}`}>
      <UilAngleUp className="scrollup__icon" />
    </a>
  );
};

export default ScrollUp;
