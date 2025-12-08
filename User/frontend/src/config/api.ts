import axios from 'axios';
import { Platform } from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import Constants from 'expo-constants';

const API_PORT = 8080;
const STORAGE_KEY = 'api_base_url';

/**
 * Extracts IP address from Expo debugger URL
 * Format: exp://192.168.8.110:8081 or 192.168.8.110:8081
 */
const extractIPFromExpoUrl = (): string | null => {
  try {
    // Try multiple sources for the debugger host
    const sources = [
      Constants.expoConfig?.hostUri,
      Constants.expoConfig?.extra?.debuggerHost,
      Constants.manifest?.debuggerHost,
      Constants.manifest2?.extra?.expoGo?.debuggerHost,
    ];

    for (const source of sources) {
      if (!source) continue;
      
      // Extract IP from formats like "192.168.8.110:8081", "exp://192.168.8.110:8081", or "localhost:8081"
      const match = source.match(/(\d+\.\d+\.\d+\.\d+)/);
      if (match && match[1]) {
        return match[1];
      }
    }

    // Also try to extract from the connection string directly
    const connectionString = Constants.manifest?.debuggerHost || Constants.manifest2?.extra?.expoGo?.debuggerHost;
    if (connectionString) {
      const ipMatch = connectionString.match(/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/);
      if (ipMatch && ipMatch[1]) {
        return ipMatch[1];
      }
    }
  } catch (error) {
    console.warn('Could not extract IP from Expo URL:', error);
  }
  return null;
};

/**
 * Test if an API URL is reachable
 */
const testApiConnection = async (url: string): Promise<boolean> => {
  try {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 3000);
    
    const response = await fetch(`${url}/health`, {
      method: 'GET',
      signal: controller.signal,
    });
    
    clearTimeout(timeoutId);
    return response.ok;
  } catch {
    return false;
  }
};

/**
 * Gets the API base URL with automatic IP detection
 * Fast initialization - doesn't block on connection tests
 */
const getApiBaseUrl = async (forceRefresh: boolean = false, testConnection: boolean = false): Promise<string> => {
  // 1. Check environment variable first (highest priority)
  if (process.env.EXPO_PUBLIC_API_URL) {
    return process.env.EXPO_PUBLIC_API_URL;
  }

  // 2. Production URL
  if (!__DEV__) {
    return process.env.EXPO_PUBLIC_PROD_API_URL || 'http://your-production-server.com:8080';
  }

  // 3. Check manually configured URL (user set via settings)
  if (!forceRefresh) {
    try {
      const manualUrl = await AsyncStorage.getItem(STORAGE_KEY);
      if (manualUrl && manualUrl.startsWith('http')) {
        // Only test connection if explicitly requested (not during init)
        if (testConnection) {
          const isReachable = await testApiConnection(manualUrl);
          if (isReachable) {
            return manualUrl;
          }
          // If not reachable, clear it and continue with auto-detection
          await AsyncStorage.removeItem(STORAGE_KEY);
        } else {
          // During init, just return cached URL without testing
          return manualUrl;
        }
      }
    } catch (error) {
      console.warn('Could not read cached API URL:', error);
    }
  }

  // 4. Try to extract IP from Expo debugger URL (always fresh)
  const detectedIP = extractIPFromExpoUrl();
  if (detectedIP && detectedIP !== '127.0.0.1') {
    const url = `http://${detectedIP}:${API_PORT}`;
    // Only test connection if explicitly requested
    if (testConnection) {
      const isReachable = await testApiConnection(url);
      if (isReachable) {
        return url;
      }
      console.warn(`Detected IP ${detectedIP} is not reachable, trying fallbacks...`);
    } else {
      // During init, just return detected IP without testing
      return url;
    }
  }

  // 5. Fallback based on platform (no connection test during init)
  if (Platform.OS === 'android') {
    return `http://10.0.2.2:${API_PORT}`; // Android Emulator default
  } else if (Platform.OS === 'ios') {
    return `http://localhost:${API_PORT}`; // iOS Simulator default
  }

  // 6. Last resort fallback
  return `http://localhost:${API_PORT}`;
};

// Initialize API base URL (will be set asynchronously)
let API_BASE_URL = __DEV__ 
  ? Platform.OS === 'android' 
    ? 'http://10.0.2.2:8080' 
    : 'http://localhost:8080'
  : 'http://your-production-server.com:8080';

// Initialize API URL with automatic detection (fast, no connection tests)
const initializeApiUrl = async () => {
  try {
    // Fast initialization - don't test connections, just use detected/cached URL
    const url = await getApiBaseUrl(false, false);
    API_BASE_URL = url;
    // Update axios instance baseURL
    api.defaults.baseURL = `${url}/api/v1`;
    console.log(`🌐 API Base URL: ${API_BASE_URL}`);
  } catch (error) {
    console.error('Error initializing API URL:', error);
  }
};

// Initialize on app start (non-blocking)
initializeApiUrl();

// Re-initialize on network changes (if available)
if (typeof window !== 'undefined' && 'addEventListener' in window) {
  // Listen for online/offline events to refresh API URL
  window.addEventListener('online', () => {
    console.log('Network online, refreshing API URL...');
    initializeApiUrl();
  });
}

/**
 * Manually set the API base URL (useful for configuration screens)
 */
export const setApiBaseUrl = async (url: string, skipTest: boolean = false): Promise<void> => {
  try {
    // Validate URL format
    if (!url.startsWith('http://') && !url.startsWith('https://')) {
      throw new Error('URL must start with http:// or https://');
    }
    
    // Test connection before saving (unless skipped)
    if (!skipTest) {
      const isReachable = await testApiConnection(url);
      if (!isReachable) {
        throw new Error('Cannot reach server at this URL. Please check if the backend is running.');
      }
    }
    
    await AsyncStorage.setItem(STORAGE_KEY, url);
    API_BASE_URL = url;
    // Update axios instance baseURL
    api.defaults.baseURL = `${url}/api/v1`;
    console.log(`✅ API Base URL updated to: ${url}`);
  } catch (error: any) {
    console.error('Error setting API URL:', error);
    throw error;
  }
};

/**
 * Refresh API URL (re-detect from Expo with connection testing)
 */
export const refreshApiUrl = async (): Promise<string> => {
  const url = await getApiBaseUrl(true, true); // Force refresh + test connections
  API_BASE_URL = url;
  api.defaults.baseURL = `${url}/api/v1`;
  console.log(`🔄 API Base URL refreshed: ${API_BASE_URL}`);
  return url;
};

/**
 * Get the current API base URL
 */
export const getCurrentApiBaseUrl = (): string => {
  return API_BASE_URL;
};

// Create axios instance
export const api = axios.create({
  baseURL: `${API_BASE_URL}/api/v1`,
  timeout: 30000, // 30 second timeout (increased for file uploads)
  headers: {
    'Content-Type': 'application/json',
  },
});

// Request interceptor to add auth token
api.interceptors.request.use(
  async (config) => {
    try {
      const token = await AsyncStorage.getItem('authToken');
      if (token) {
        config.headers.Authorization = `Bearer ${token}`;
      }
      
      // If FormData, handle Content-Type properly
      if (config.data instanceof FormData) {
        // If Content-Type is explicitly set to 'multipart/form-data', keep it
        // Otherwise, let axios set it automatically with boundary
        if (config.headers['Content-Type'] !== 'multipart/form-data') {
          delete config.headers['Content-Type'];
        }
        // Increase timeout for file uploads
        if (!config.timeout || config.timeout < 60000) {
          config.timeout = 60000; // 60 seconds for file uploads
        }
        console.log('📤 FormData upload detected, timeout set to 60s');
      } else {
        // Log JSON requests for demand letter endpoint to debug missing fields
        if (config.url?.includes('demand-letter/preview') && config.data) {
          console.log('🔍 Axios Request Interceptor - Demand Letter Preview:');
          console.log('URL:', config.url);
          console.log('Request Data:', JSON.stringify(config.data, null, 2));
          if (config.data.borrower) {
            console.log('Borrower object keys:', Object.keys(config.data.borrower));
            console.log('Borrower object:', JSON.stringify(config.data.borrower, null, 2));
            console.log('Has barangay:', 'barangay' in config.data.borrower);
            console.log('Has city:', 'city' in config.data.borrower);
            console.log('Barangay value:', config.data.borrower.barangay);
            console.log('City value:', config.data.borrower.city);
          }
        }
      }
    } catch (error) {
      console.error('Error getting auth token:', error);
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor for error handling
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    // Handle network errors - try to refresh API URL (but don't block)
    if (error.code === 'ECONNREFUSED' || error.message === 'Network Error' || !error.response) {
      console.warn('Network error detected, attempting to refresh API URL...');
      // Refresh in background, don't wait for it
      refreshApiUrl().catch((refreshError) => {
        console.error('Failed to refresh API URL:', refreshError);
      });
      // Don't retry automatically - let the error propagate
    }
    
    if (error.response?.status === 401) {
      // Token expired or invalid - clear storage and redirect to login
      await AsyncStorage.removeItem('authToken');
      // You might want to use navigation here if needed
    }
    return Promise.reject(error);
  }
);

// Use named export to avoid ESLint warnings
// export default api; // Removed - use named import instead

