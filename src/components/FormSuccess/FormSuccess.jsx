import React from "react";
import Logo from "../Header/logo.webp";
import { Link } from "react-router-dom";
import { UilArrowLeft } from '@iconscout/react-unicons'
import './FormSuccess.css'
function FormSuccess() {
  return (
      <section className="success">
        <div className="success-content">
          <Link to="/">
          <img src={Logo} alt="Logo" />
          </Link>
          <div className="success-box">
            <h1>Merci !</h1>
            <p>Votre formulaire a été envoyé avec succès. Nous vous contacterons bientôt.</p>
            <Link to="/"><UilArrowLeft /> Cliquez ici pour revenir au site web</Link>
          </div>
        </div>
      </section>
  );
}

export default FormSuccess;
