import React, { useState, useEffect } from 'react';
import { Form, Button, Card, Alert, Container, Row, Col } from 'react-bootstrap';
import { motion } from 'framer-motion';
import { ADMIN_CONFIG } from '../../config/adminConfig';

const AdminAuth = ({ onAuthenticated }) => {
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [isLoading, setIsLoading] = useState(false);

  // Get password from configuration
  const ADMIN_PASSWORD = ADMIN_CONFIG.password;

  useEffect(() => {
    // Check if already authenticated (session storage)
    const isAuthenticated = sessionStorage.getItem('adminAuthenticated');
    if (isAuthenticated === 'true') {
      onAuthenticated();
    }
  }, [onAuthenticated]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setIsLoading(true);
    setError('');

    // Simulate a small delay for better UX
    await new Promise(resolve => setTimeout(resolve, 500));

    if (password === ADMIN_PASSWORD) {
      // Store authentication in session storage
      sessionStorage.setItem('adminAuthenticated', 'true');
      sessionStorage.setItem('adminLoginTime', new Date().toISOString());
      onAuthenticated();
    } else {
      setError('Invalid password. Please try again.');
      setPassword('');
    }
    
    setIsLoading(false);
  };

  return (
    <Container fluid className="min-vh-100 d-flex align-items-center justify-content-center" 
               style={{ backgroundColor: '#041c32' }}>
      <Row className="w-100 justify-content-center">
        <Col xs={12} sm={8} md={6} lg={4}>
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5 }}
          >
            <Card className="shadow-lg border-0" style={{ backgroundColor: '#1a1a2e' }}>
              <Card.Body className="p-4">
                <div className="text-center mb-4">
                  <motion.h2 
                    className="text-white mb-2"
                    initial={{ scale: 0.9 }}
                    animate={{ scale: 1 }}
                    transition={{ delay: 0.2 }}
                  >
                    🔐 Admin Access
                  </motion.h2>
                  <p className="text-muted">Heaven of Web Dashboard</p>
                </div>

                {error && (
                  <motion.div
                    initial={{ opacity: 0, x: -20 }}
                    animate={{ opacity: 1, x: 0 }}
                  >
                    <Alert variant="danger" className="mb-3">
                      {error}
                    </Alert>
                  </motion.div>
                )}

                <Form onSubmit={handleSubmit}>
                  <Form.Group className="mb-3">
                    <Form.Label className="text-white">Password</Form.Label>
                    <Form.Control
                      type="password"
                      value={password}
                      onChange={(e) => setPassword(e.target.value)}
                      placeholder="Enter admin password"
                      required
                      disabled={isLoading}
                      style={{
                        backgroundColor: '#041c32',
                        borderColor: '#ff4848',
                        color: 'white'
                      }}
                      onFocus={(e) => e.target.style.borderColor = '#ff4848'}
                    />
                  </Form.Group>

                  <motion.div
                    whileHover={{ scale: 1.02 }}
                    whileTap={{ scale: 0.98 }}
                  >
                    <Button
                      type="submit"
                      className="w-100"
                      disabled={isLoading || !password.trim()}
                      style={{
                        backgroundColor: '#ff4848',
                        borderColor: '#ff4848',
                        fontWeight: 'bold'
                      }}
                    >
                      {isLoading ? (
                        <>
                          <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                          Authenticating...
                        </>
                      ) : (
                        'Access Dashboard'
                      )}
                    </Button>
                  </motion.div>
                </Form>

                <div className="text-center mt-4">
                  <small className="text-muted">
                    Secure access to content management
                  </small>
                </div>
              </Card.Body>
            </Card>
          </motion.div>
        </Col>
      </Row>
    </Container>
  );
};

// Export logout function for use in other components
export const logoutAdmin = () => {
  sessionStorage.removeItem('adminAuthenticated');
  sessionStorage.removeItem('adminLoginTime');
  window.location.reload();
};

// Check if admin is authenticated
export const isAdminAuthenticated = () => {
  const isAuth = sessionStorage.getItem('adminAuthenticated') === 'true';
  const loginTime = sessionStorage.getItem('adminLoginTime');
  
  // Auto-logout based on configuration
  if (isAuth && loginTime && ADMIN_CONFIG.autoLogout) {
    const loginDate = new Date(loginTime);
    const now = new Date();
    const hoursDiff = (now - loginDate) / (1000 * 60 * 60);
    
    if (hoursDiff > ADMIN_CONFIG.sessionTimeout) {
      logoutAdmin();
      return false;
    }
  }
  
  return isAuth;
};

export default AdminAuth;
