/**
 * Performance monitoring utilities for React app
 */

/**
 * Report Web Vitals to analytics service
 */
export const reportWebVitals = (onPerfEntry) => {
  if (onPerfEntry && onPerfEntry instanceof Function) {
    import('web-vitals').then(({ getCLS, getFID, getFCP, getLCP, getTTFB }) => {
      getCLS(onPerfEntry);
      getFID(onPerfEntry);
      getFCP(onPerfEntry);
      getLCP(onPerfEntry);
      getTTFB(onPerfEntry);
    });
  }
};

/**
 * Measure component render performance
 */
export const measureRenderTime = (componentName, renderFunction) => {
  const startTime = performance.now();
  const result = renderFunction();
  const endTime = performance.now();
  
  if (process.env.NODE_ENV === 'development') {
    console.log(`${componentName} render time: ${endTime - startTime}ms`);
  }
  
  return result;
};

/**
 * Debounce function for performance optimization
 */
export const debounce = (func, wait, immediate = false) => {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      timeout = null;
      if (!immediate) func(...args);
    };
    const callNow = immediate && !timeout;
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
    if (callNow) func(...args);
  };
};

/**
 * Throttle function for performance optimization
 */
export const throttle = (func, limit) => {
  let inThrottle;
  return function(...args) {
    if (!inThrottle) {
      func.apply(this, args);
      inThrottle = true;
      setTimeout(() => inThrottle = false, limit);
    }
  };
};

/**
 * Lazy load images with intersection observer
 */
export const createLazyImageObserver = (callback) => {
  if (!('IntersectionObserver' in window)) {
    // Fallback for older browsers
    return null;
  }
  
  return new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        callback(entry.target);
      }
    });
  }, {
    rootMargin: '50px 0px',
    threshold: 0.01
  });
};

/**
 * Preload critical resources
 */
export const preloadResource = (href, as = 'image', type = null) => {
  const link = document.createElement('link');
  link.rel = 'preload';
  link.href = href;
  link.as = as;
  if (type) link.type = type;
  
  document.head.appendChild(link);
  
  return new Promise((resolve, reject) => {
    link.onload = resolve;
    link.onerror = reject;
  });
};

/**
 * Memory usage monitoring (development only)
 */
export const monitorMemoryUsage = () => {
  if (process.env.NODE_ENV === 'development' && 'memory' in performance) {
    const memInfo = performance.memory;
    console.log('Memory Usage:', {
      used: `${Math.round(memInfo.usedJSHeapSize / 1048576)} MB`,
      total: `${Math.round(memInfo.totalJSHeapSize / 1048576)} MB`,
      limit: `${Math.round(memInfo.jsHeapSizeLimit / 1048576)} MB`
    });
  }
};

export default {
  reportWebVitals,
  measureRenderTime,
  debounce,
  throttle,
  createLazyImageObserver,
  preloadResource,
  monitorMemoryUsage
};
