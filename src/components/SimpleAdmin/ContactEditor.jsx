import React, { useState, useEffect } from 'react';
import { Form, Button, Row, Col, Card } from 'react-bootstrap';
import { motion } from 'framer-motion';
import { useSiteData } from '../../contexts/SiteDataContext';

const ContactEditor = ({ onNotification }) => {
  const { data, updateContact } = useSiteData();
  const [contactData, setContactData] = useState(data.contact);

  useEffect(() => {
    setContactData(data.contact);
  }, [data.contact]);

  const handleInputChange = (field, value) => {
    if (field.includes('.')) {
      const [parent, child] = field.split('.');
      setContactData(prev => ({
        ...prev,
        [parent]: {
          ...prev[parent],
          [child]: value
        }
      }));
    } else {
      setContactData(prev => ({
        ...prev,
        [field]: value
      }));
    }
  };

  const handleSave = async () => {
    try {
      await updateContact(contactData);
      onNotification('Contact information updated successfully!', 'success');
    } catch (error) {
      console.error('Error updating contact information:', error);
      onNotification('Error updating contact information: ' + error.message, 'danger');
    }
  };

  const handleReset = () => {
    setContactData(data.contact);
    onNotification('Changes reset', 'info');
  };

  return (
    <motion.div
      initial={{ opacity: 0 }}
      animate={{ opacity: 1 }}
      transition={{ duration: 0.3 }}
    >
      <Row>
        <Col md={8}>
          <Card>
            <Card.Header>
              <h5>📞 Edit Contact Information</h5>
              <small>Update your contact details that appear on the website</small>
            </Card.Header>
            <Card.Body>
              <Form>
                <div className="form-section-header">
                  <h6>📧 Primary Contact</h6>
                </div>
                <Row>
                  <Col md={6}>
                    <Form.Group className="mb-3">
                      <Form.Label>Email Address</Form.Label>
                      <Form.Control
                        type="email"
                        value={contactData.email}
                        onChange={(e) => handleInputChange('email', e.target.value)}
                        placeholder="contact@example.com"
                      />
                    </Form.Group>
                  </Col>
                  <Col md={6}>
                    <Form.Group className="mb-3">
                      <Form.Label>Email Link</Form.Label>
                      <Form.Control
                        type="text"
                        value={contactData.emailLink}
                        onChange={(e) => handleInputChange('emailLink', e.target.value)}
                        placeholder="mailto:contact@example.com"
                      />
                    </Form.Group>
                  </Col>
                </Row>

                <Row>
                  <Col md={6}>
                    <Form.Group className="mb-3">
                      <Form.Label>Phone Number</Form.Label>
                      <Form.Control
                        type="text"
                        value={contactData.phone}
                        onChange={(e) => handleInputChange('phone', e.target.value)}
                        placeholder="+33 1 23 45 67 89"
                      />
                    </Form.Group>
                  </Col>
                  <Col md={6}>
                    <Form.Group className="mb-3">
                      <Form.Label>Phone Link</Form.Label>
                      <Form.Control
                        type="text"
                        value={contactData.phoneLink}
                        onChange={(e) => handleInputChange('phoneLink', e.target.value)}
                        placeholder="tel:+33123456789"
                      />
                    </Form.Group>
                  </Col>
                </Row>

                <div className="form-section-header">
                  <h6>🌍 Social Media Links</h6>
                </div>
                <Row>
                  <Col md={6}>
                    <Form.Group className="mb-3">
                      <Form.Label>Twitter</Form.Label>
                      <Form.Control
                        type="url"
                        value={contactData.socialMedia.twitter}
                        onChange={(e) => handleInputChange('socialMedia.twitter', e.target.value)}
                        placeholder="https://twitter.com/username"
                      />
                    </Form.Group>
                  </Col>
                  <Col md={6}>
                    <Form.Group className="mb-3">
                      <Form.Label>Facebook</Form.Label>
                      <Form.Control
                        type="url"
                        value={contactData.socialMedia.facebook}
                        onChange={(e) => handleInputChange('socialMedia.facebook', e.target.value)}
                        placeholder="https://facebook.com/page"
                      />
                    </Form.Group>
                  </Col>
                </Row>

                <Row>
                  <Col md={6}>
                    <Form.Group className="mb-3">
                      <Form.Label>Instagram</Form.Label>
                      <Form.Control
                        type="url"
                        value={contactData.socialMedia.instagram}
                        onChange={(e) => handleInputChange('socialMedia.instagram', e.target.value)}
                        placeholder="https://instagram.com/username"
                      />
                    </Form.Group>
                  </Col>
                  <Col md={6}>
                    <Form.Group className="mb-3">
                      <Form.Label>LinkedIn</Form.Label>
                      <Form.Control
                        type="url"
                        value={contactData.socialMedia.linkedin}
                        onChange={(e) => handleInputChange('socialMedia.linkedin', e.target.value)}
                        placeholder="https://linkedin.com/company/name"
                      />
                    </Form.Group>
                  </Col>
                </Row>

                <Row>
                  <Col md={6}>
                    <Form.Group className="mb-3">
                      <Form.Label>GitHub</Form.Label>
                      <Form.Control
                        type="url"
                        value={contactData.socialMedia.github}
                        onChange={(e) => handleInputChange('socialMedia.github', e.target.value)}
                        placeholder="https://github.com/username"
                      />
                    </Form.Group>
                  </Col>
                </Row>

                <div className="d-flex gap-2 mt-4">
                  <Button variant="primary" onClick={handleSave}>
                    Save Changes
                  </Button>
                  <Button variant="secondary" onClick={handleReset}>
                    Reset
                  </Button>
                </div>
              </Form>
            </Card.Body>
          </Card>
        </Col>

        <Col md={4}>
          <Card>
            <Card.Header>
              <h6>Preview</h6>
            </Card.Header>
            <Card.Body>
              <div className="contact-preview">
                <p><strong>Email:</strong> {contactData.email}</p>
                <p><strong>Phone:</strong> {contactData.phone}</p>
                <div className="social-links mt-3">
                  <h6>Social Media:</h6>
                  {Object.entries(contactData.socialMedia).map(([platform, url]) => (
                    url && (
                      <div key={platform}>
                        <strong>{platform.charAt(0).toUpperCase() + platform.slice(1)}:</strong>
                        <br />
                        <small className="text-muted">{url}</small>
                      </div>
                    )
                  ))}
                </div>
              </div>
            </Card.Body>
          </Card>
        </Col>
      </Row>
    </motion.div>
  );
};

export default ContactEditor;
