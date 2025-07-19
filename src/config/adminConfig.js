// Admin Configuration
// Change the password below to secure your admin panel

export const ADMIN_CONFIG = {
  // Default password - CHANGE THIS for security
  password: process.env.REACT_APP_ADMIN_PASSWORD || 'heaven2025',
  
  // Session timeout in hours (24 hours default)
  sessionTimeout: 24,
  
  // Enable/disable auto-logout
  autoLogout: true,
  
  // Admin panel title
  title: 'Heaven of Web - Admin Panel'
};

// Security recommendations:
// 1. Change the default password above
// 2. Use a strong password with letters, numbers, and symbols
// 3. Consider implementing environment variables for production
// 4. Enable HTTPS in production
// 5. Consider implementing rate limiting for login attempts

export default ADMIN_CONFIG;
