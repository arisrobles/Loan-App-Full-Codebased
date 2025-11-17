import axios from 'axios';
import { Platform } from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';

// API Configuration for development
// For physical devices, use your computer's local IP (192.168.8.105)
// For emulators, use the default values
const API_BASE_URL = __DEV__
  ? Platform.OS === 'android'
    ? 'http://192.168.8.105:8080' // Physical device - use your computer's IP
    // ? 'http://10.0.2.2:8080' // Android Emulator (uncomment if using emulator)
    : 'http://192.168.8.105:8080' // Physical device - use your computer's IP
    // : 'http://localhost:8080' // iOS Simulator (uncomment if using simulator)
  : 'http://your-production-server.com:8080'; // Production

// Create axios instance
export const api = axios.create({
  baseURL: `${API_BASE_URL}/api/v1`,
  timeout: 10000,
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

