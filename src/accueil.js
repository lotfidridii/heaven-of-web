import Header from './components/Header/Header';
import Hero from './components/HeroSection/Hero';
import About from './components/AboutSection/About';
import Services from './components/ServiceSection/Services';
import Engagement from './components/EngagementsSection/Engagement';
import Portfolio from './components/PortfolioSection/Portfolio';
import Contact from './components/ContactSection/Contact';
import Footer from './components/Footer/Footer';
import ScrollUp from './components/BackToTop/BackToTop';
import Preloader from './components/Preloader/Preloader';

function Accueil() {
    return (
        <>
            <Preloader />
            <Header />
            <Hero />
            <About />
            <Services />
            <Engagement />
            <Portfolio />
            <Contact />
            <Footer />
            <ScrollUp />
        </>
    )
}

export default Accueil;