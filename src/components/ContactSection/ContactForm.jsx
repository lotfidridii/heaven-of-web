import { useState } from "react";
import { Form, Button, Alert } from "react-bootstrap";
import { useNavigate } from "react-router-dom";

const ContactForm = () => {
  const [validated, setValidated] = useState(false);
  const [formValues, setFormValues] = useState({
    name: "",
    email: "",
    subject: "",
    message: "",
  });
  const [showSuccess, setShowSuccess] = useState(false);
  const navigate = useNavigate();

  const handleSubmit = (event) => {
    event.preventDefault();
    const form = event.currentTarget;

    if (form.checkValidity() === false) {
      event.stopPropagation();
      setValidated(true);
    } else {
      fetch("/", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams(new FormData(form)).toString(),
      })
        .then(() => {
          navigate("/Soumissiondeformulaire");
          setShowSuccess(true);
          setTimeout(() => {
            setShowSuccess(false);
          }, 3000);
        })
        .catch((error) => alert(error));
    }
  };

  const handleInputChange = (event) => {
    setFormValues({
      ...formValues,
      [event.target.name]: event.target.value,
    });
  };

  return (
    <Form
      method="POST"
      action="#"
      noValidate
      validated={validated}
      onSubmit={handleSubmit}
      className="contact-form"
      name="contact"
      data-netlify="true"
      data-netlify-honeypot="bot-field"
    >
      <input type="hidden" name="form-name" value="contact" />
      <p hidden>
        <label>
          Don’t fill this out:{" "}
          <input name="bot-field" onChange={handleInputChange} />
        </label>
      </p>
      <Form.Group controlId="formName">
        <Form.Control
          required
          type="text"
          placeholder={"Entrez votre nom"}
          name="name"
          value={formValues.name}
          onChange={handleInputChange}
        />
        <Form.Control.Feedback type="invalid">
          Veuillez saisir votre nom.
        </Form.Control.Feedback>
      </Form.Group>
      <Form.Group controlId="formEmail">
        <Form.Control
          required
          type="email"
          placeholder="Entrez votre email"
          name="email"
          value={formValues.email}
          onChange={handleInputChange}
        />
        <Form.Control.Feedback type="invalid">
          Veuillez inscrire une adresse email valide.
        </Form.Control.Feedback>
      </Form.Group>
      <Form.Group controlId="formName">
        <Form.Control
          required
          type="text"
          placeholder="Entrez le sujet"
          name="subject"
          value={formValues.subject}
          onChange={handleInputChange}
        />
        <Form.Control.Feedback type="invalid">
          Veuillez saisir un sujet.
        </Form.Control.Feedback>
      </Form.Group>
      <Form.Group controlId="formMessage">
        <Form.Control
          required
          as="textarea"
          rows="3"
          placeholder="Enterz le message"
          name="message"
          value={formValues.message}
          onChange={handleInputChange}
        />
        <Form.Control.Feedback type="invalid">
          Veuillez saisir un message.
        </Form.Control.Feedback>
      </Form.Group>
      <Button type="submit">Envoyer</Button>
      {showSuccess && (
        <Alert variant="success" className="mt-3">
          votre message a été envoyer avec succès.
        </Alert>
      )}
    </Form>
  );
};

export default ContactForm;
