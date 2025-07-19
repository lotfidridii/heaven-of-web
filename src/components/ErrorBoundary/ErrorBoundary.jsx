import React from 'react';
import { Container, Alert, Button } from 'react-bootstrap';
import './ErrorBoundary.css';

class ErrorBoundary extends React.Component {
  constructor(props) {
    super(props);
    this.state = { hasError: false, error: null, errorInfo: null };
  }

  static getDerivedStateFromError(error) {
    // Update state so the next render will show the fallback UI
    return { hasError: true };
  }

  componentDidCatch(error, errorInfo) {
    // Log the error to an error reporting service
    this.setState({
      error: error,
      errorInfo: errorInfo
    });
    
    // In production, you would send this to an error monitoring service
    if (process.env.NODE_ENV === 'development') {
      console.error('Error caught by boundary:', error, errorInfo);
    }
  }

  handleReload = () => {
    window.location.reload();
  };

  handleReset = () => {
    this.setState({ hasError: false, error: null, errorInfo: null });
  };

  render() {
    if (this.state.hasError) {
      return (
        <Container className="error-boundary-container">
          <div className="error-boundary-content">
            <Alert variant="danger" className="error-boundary-alert">
              <Alert.Heading>
                <i className="fas fa-exclamation-triangle me-2"></i>
                Oops! Something went wrong
              </Alert.Heading>
              <p>
                We're sorry, but something unexpected happened. This error has been logged 
                and we'll work to fix it as soon as possible.
              </p>
              
              {process.env.NODE_ENV === 'development' && this.state.error && (
                <details className="error-details">
                  <summary>Error Details (Development Only)</summary>
                  <pre className="error-stack">
                    {this.state.error && this.state.error.toString()}
                    <br />
                    {this.state.errorInfo.componentStack}
                  </pre>
                </details>
              )}
              
              <hr />
              <div className="error-actions">
                <Button 
                  variant="outline-danger" 
                  onClick={this.handleReset}
                  className="me-2"
                >
                  Try Again
                </Button>
                <Button 
                  variant="danger" 
                  onClick={this.handleReload}
                >
                  Reload Page
                </Button>
              </div>
            </Alert>
          </div>
        </Container>
      );
    }

    return this.props.children;
  }
}

export default ErrorBoundary;
