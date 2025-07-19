/**
 * Utility functions for managing uploaded images
 */

/**
 * Get list of uploaded images from public/img directory
 * This would typically be called from the backend
 */
export const getUploadedImages = async () => {
  try {
    // This would be implemented on the backend to scan the public/img directory
    // For now, we'll return an empty array as this is a frontend utility
    return [];
  } catch (error) {
    console.error('Error getting uploaded images:', error);
    return [];
  }
};

/**
 * Get list of images currently used in portfolio data
 */
export const getUsedImages = (portfolioData) => {
  if (!portfolioData || !Array.isArray(portfolioData)) {
    return [];
  }
  
  return portfolioData
    .map(item => item.src)
    .filter(src => src && src.includes('Portfolio-'))
    .map(src => src.replace('./img/', '').replace('/img/', ''));
};

/**
 * Find unused uploaded images
 */
export const findUnusedImages = (uploadedImages, usedImages) => {
  return uploadedImages.filter(image => !usedImages.includes(image));
};

/**
 * Generate a clean filename for uploaded images
 */
export const generateCleanFilename = (originalName) => {
  const timestamp = Date.now();
  const cleanName = originalName
    .toLowerCase()
    .replace(/[^a-z0-9.]/g, '-')
    .replace(/-+/g, '-')
    .replace(/^-|-$/g, '');
  
  const extension = cleanName.split('.').pop();
  const nameWithoutExt = cleanName.replace(`.${extension}`, '');
  
  return `portfolio-${nameWithoutExt}-${timestamp}.${extension}`;
};

/**
 * Validate image file type and size
 */
export const validateImageFile = (file, maxSizeMB = 5) => {
  const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
  const maxSizeBytes = maxSizeMB * 1024 * 1024;
  
  const errors = [];
  
  if (!validTypes.includes(file.type)) {
    errors.push('Invalid file type. Please use JPEG, PNG, or WebP format.');
  }
  
  if (file.size > maxSizeBytes) {
    errors.push(`File size too large. Maximum size is ${maxSizeMB}MB.`);
  }
  
  return {
    isValid: errors.length === 0,
    errors
  };
};

/**
 * Create optimized image preview URL
 */
export const createImagePreview = (file) => {
  return new Promise((resolve, reject) => {
    if (!file) {
      reject(new Error('No file provided'));
      return;
    }
    
    const reader = new FileReader();
    reader.onload = (e) => resolve(e.target.result);
    reader.onerror = () => reject(new Error('Failed to read file'));
    reader.readAsDataURL(file);
  });
};

export default {
  getUploadedImages,
  getUsedImages,
  findUnusedImages,
  generateCleanFilename,
  validateImageFile,
  createImagePreview
};
