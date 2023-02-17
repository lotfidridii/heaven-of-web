import React from 'react'
import 'bootstrap/dist/css/bootstrap.min.css';
import './App.css'
import Accueil from './accueil';
import Success from './success';
import { BrowserRouter as Router, Routes, Route }
    from 'react-router-dom';

function App() {
    return (
        <Router>
            <Routes>
                <Route path='/' exact element={<Accueil />} />
                <Route path='/Soumissiondeformulaire' exact element={<Success />} />
            </Routes>
        </Router>
    )
}

export default App