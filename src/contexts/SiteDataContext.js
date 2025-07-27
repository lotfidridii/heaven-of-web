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
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState(null);

  // Load data from server on mount
  useEffect(() => {
    loadDataFromServer();
  }, []);

  const loadDataFromServer = async () => {
    setIsLoading(true);
    setError(null);
    try {
      // In development, use localStorage since PHP server is not available
      if (process.env.NODE_ENV !== 'production') {
        const savedData = localStorage.getItem('heavenofweb-site-data');
        if (savedData) {
          const parsedData = JSON.parse(savedData);
          setData(parsedData);
        }
        return;
      }

      // Production: use PHP API
      const response = await fetch('/api/load-data.php');
      const result = await response.json();
      
      if (result.success) {
        setData(result.data);
      } else {
        throw new Error(result.message || 'Failed to load data');
      }
    } catch (error) {
      console.error('Error loading data from server:', error);
      setError('Failed to load data from server');
      // Fallback to localStorage if server fails
      try {
        const savedData = localStorage.getItem('heavenofweb-site-data');
        if (savedData) {
          const parsedData = JSON.parse(savedData);
          setData(parsedData);
        }
      } catch (localError) {
        console.error('Error loading from localStorage:', localError);
      }
    } finally {
      setIsLoading(false);
    }
  };

  // Save data to server with better error handling
  const saveDataToServer = useCallback(async (newData) => {
    try {
      setData(newData);
      
      // In development, only save to localStorage since PHP server is not available
      if (process.env.NODE_ENV !== 'production') {
        localStorage.setItem('heavenofweb-site-data', JSON.stringify(newData));
        console.log('Development mode: Data saved to localStorage');
        return;
      }

      // Production: save to server
      const dataToSave = {
        ...newData,
        adminPassword: 'heaven2025' // Add admin password for server validation
      };
      
      const response = await fetch('/api/save-data.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(dataToSave)
      });
      
      const result = await response.json();
      
      if (!result.success) {
        throw new Error(result.message || 'Failed to save data to server');
      }
      
      // Also save to localStorage as backup
      localStorage.setItem('heavenofweb-site-data', JSON.stringify(newData));
      
    } catch (err) {
      console.error('Error saving data:', err);
      setError(new Error('Failed to save site data: ' + err.message));
      throw err;
    }
  }, []);

  // Update contact information
  const updateContact = useCallback(async (contactData) => {
    const updatedData = {
      ...data,
      contact: contactData
    };
    return await saveDataToServer(updatedData);
  }, [data, saveDataToServer]);

  // Update portfolio
  const updatePortfolio = useCallback(async (portfolioData) => {
    const updatedData = {
      ...data,
      portfolio: portfolioData
    };
    return await saveDataToServer(updatedData);
  }, [data, saveDataToServer]);

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
  const resetData = useCallback(async () => {
    try {
      // Reset server data
      await saveDataToServer(initialData);
      // Also clear localStorage
      localStorage.removeItem('heavenofweb-site-data');
      setData(initialData);
      setError(null);
    } catch (err) {
      setError(new Error('Failed to reset data'));
    }
  }, [saveDataToServer]);

  // Refresh data from server
  const refreshData = useCallback(() => {
    return loadDataFromServer();
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
    refreshData,
    siteData: data // Alias for backward compatibility
  };

  return (
    <SiteDataContext.Provider value={value}>
      {children}
    </SiteDataContext.Provider>
  );
};

export default SiteDataContext;
