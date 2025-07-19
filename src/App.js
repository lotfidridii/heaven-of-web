import React from 'react';
import 'bootstrap/dist/css/bootstrap.min.css';
import './App.css';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import { SiteDataProvider } from './contexts/SiteDataContext';
import ErrorBoundary from './components/ErrorBoundary/ErrorBoundary';
import Accueil from './accueil';
import Success from './success';
import SimpleAdmin from './components/SimpleAdmin/SimpleAdmin';

function App() {
    return (
        <ErrorBoundary>
            <SiteDataProvider>
                <Router future={{ v7_startTransition: true, v7_relativeSplatPath: true }}>
                    <Routes>
                        <Route path='/' element={<Accueil />} />
                        <Route path='/Soumissiondeformulaire' element={<Success />} />
                        <Route path='/admin' element={<SimpleAdmin />} />
                    </Routes>
                </Router>
            </SiteDataProvider>
        </ErrorBoundary>
    );
}

export default App;