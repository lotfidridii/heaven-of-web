import React, { createContext, useContext, useState, useEffect, useCallback } from 'react';
import initialData from '../data/siteData.json';

const SiteDataContext = createContext();

// Generate unique IDs
const generateId = () => {
  return `${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
};

export const useSiteData = () => {
  const context = useContext(SiteDataContext);
  if (!context) {
    throw new Error('useSiteData must be used within a SiteDataProvider');
  }
  return context;
};

export const SiteDataProvider = ({ children }) => {
  const [data, setData] = useState(initialData);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState(null);

  // Load data from localStorage if available
  useEffect(() => {
    const loadData = async () => {
      try {
        setIsLoading(true);
        setError(null);
        
        const savedData = localStorage.getItem('heavenofweb-site-data');
        if (savedData) {
          const parsedData = JSON.parse(savedData);
          setData(parsedData);
        }
      } catch (err) {
        setError(new Error('Failed to load site data'));
        // Keep initial data as fallback
        setData(initialData);
      } finally {
        setIsLoading(false);
      }
    };

    loadData();
  }, []);

  // Save data to localStorage with better error handling
  const saveData = useCallback((newData) => {
    try {
      setData(newData);
      localStorage.setItem('heavenofweb-site-data', JSON.stringify(newData));
      setError(null); // Clear any previous errors
      return true;
    } catch (err) {
      const errorMessage = 'Failed to save data. Please try again.';
      setError(new Error(errorMessage));
      return false;
    }
  }, []);

  // Update contact information
  const updateContact = useCallback((contactData) => {
    const updatedData = {
      ...data,
      contact: contactData
    };
    return saveData(updatedData);
  }, [data, saveData]);

  // Update portfolio
  const updatePortfolio = useCallback((portfolioData) => {
    const updatedData = {
      ...data,
      portfolio: portfolioData
    };
    return saveData(updatedData);
  }, [data, saveData]);

  // Add portfolio item with better ID generation
  const addPortfolioItem = useCallback((item) => {
    const newItem = {
      ...item,
      id: generateId(),
      createdAt: new Date().toISOString()
    };
    const updatedPortfolio = [...data.portfolio, newItem];
    return updatePortfolio(updatedPortfolio);
  }, [data.portfolio, updatePortfolio]);

  // Remove portfolio item
  const removePortfolioItem = useCallback((id) => {
    const updatedPortfolio = data.portfolio.filter(item => item.id !== id);
    return updatePortfolio(updatedPortfolio);
  }, [data.portfolio, updatePortfolio]);

  // Reset to defaults
  const resetData = useCallback(() => {
    try {
      localStorage.removeItem('heavenofweb-site-data');
      setData(initialData);
      setError(null);
    } catch (err) {
      setError(new Error('Failed to reset data'));
    }
  }, []);

  const value = {
    data,
    isLoading,
    error,
    updateContact,
    updatePortfolio,
    addPortfolioItem,
    removePortfolioItem,
    resetData,
    siteData: data // Alias for backward compatibility
  };

  return (
    <SiteDataContext.Provider value={value}>
      {children}
    </SiteDataContext.Provider>
  );
};

export default SiteDataContext;
