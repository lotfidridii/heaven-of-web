import React, { useState } from 'react';
import { Container, Row, Col, Card, Tab, Nav, Alert, Button } from 'react-bootstrap';
import { motion } from 'framer-motion';
import ContactEditor from './ContactEditor';
import PortfolioEditor from './PortfolioEditor';
import AdminAuth, { logoutAdmin, isAdminAuthenticated } from './AdminAuth';
import './SimpleAdmin.css';

const SimpleAdmin = () => {
  const [activeTab, setActiveTab] = useState('contact');
  const [showAlert, setShowAlert] = useState(false);
  const [alertMessage, setAlertMessage] = useState('');
  const [alertType, setAlertType] = useState('success');
  const [isAuthenticated, setIsAuthenticated] = useState(isAdminAuthenticated());

  const showNotification = (message, type = 'success') => {
    setAlertMessage(message);
    setAlertType(type);
    setShowAlert(true);
    setTimeout(() => setShowAlert(false), 3000);
  };

  const handleAuthenticated = () => {
    setIsAuthenticated(true);
  };

  const handleLogout = () => {
    logoutAdmin();
    setIsAuthenticated(false);
  };

  // Show login screen if not authenticated
  if (!isAuthenticated) {
    return <AdminAuth onAuthenticated={handleAuthenticated} />;
  }

  return (
    <div className="simple-admin">
      <Container>
        <motion.div
          initial={{ opacity: 0, y: -20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.5 }}
        >
          <Card className="admin-header-card">
            <Card.Body>
              <div className="d-flex justify-content-between align-items-center mb-3">
                <div className="text-center flex-grow-1">
                  <h1>🌐 Heaven of Web - Admin Panel</h1>
                  <p className="mb-0">Easily manage your contact information and portfolio items</p>
                </div>
                <div className="d-flex gap-2">
                  <Button 
                    variant="outline-success" 
                    size="sm"
                    onClick={() => window.location.href = '/'}
                    title="Go to Home Page"
                  >
                    🏠 Home
                  </Button>
                  <Button 
                    variant="outline-danger" 
                    size="sm"
                    onClick={handleLogout}
                    title="Logout from Admin Panel"
                  >
                    🚪 Logout
                  </Button>
                </div>
              </div>
              
              <div className="admin-instructions text-center">
                <small>
                  <strong>Instructions:</strong> Use the tabs below to edit your contact details or manage your portfolio. 
                  Changes are saved automatically and will appear on your website immediately.
                </small>
              </div>
              
              {showAlert && (
                <Alert variant={alertType} dismissible onClose={() => setShowAlert(false)} className="mt-3">
                  {alertMessage}
                </Alert>
              )}
            </Card.Body>
          </Card>
        </motion.div>

        <Row className="mt-4">
          <Col>
            <Tab.Container activeKey={activeTab} onSelect={setActiveTab}>
              <Card>
                <Card.Header>
                  <Nav variant="tabs">
                    <Nav.Item>
                      <Nav.Link eventKey="contact">
                        📞 Contact Information
                        <br />
                        <small>Edit email, phone & social links</small>
                      </Nav.Link>
                    </Nav.Item>
                    <Nav.Item>
                      <Nav.Link eventKey="portfolio">
                        🎨 Portfolio Management
                        <br />
                        <small>Add, edit & remove portfolio items</small>
                      </Nav.Link>
                    </Nav.Item>
                  </Nav>
                </Card.Header>
                
                <Card.Body>
                  <Tab.Content>
                    <Tab.Pane eventKey="contact">
                      <ContactEditor onNotification={showNotification} />
                    </Tab.Pane>
                    
                    <Tab.Pane eventKey="portfolio">
                      <PortfolioEditor onNotification={showNotification} />
                    </Tab.Pane>
                  </Tab.Content>
                </Card.Body>
              </Card>
            </Tab.Container>
          </Col>
        </Row>
      </Container>
    </div>
  );
};

export default SimpleAdmin;
