import AsyncStorage from '@react-native-async-storage/async-storage';
import { api } from '../config/api';

/**
 * Utility function to handle user logout
 * Clears authentication token and optionally calls logout API endpoint
 */
export const logoutUser = async (callApi: boolean = true): Promise<void> => {
  try {
    if (callApi) {
      await api.post('/auth/logout');
    }
  } catch (error) {
    console.error('Logout API error:', error);
    // Continue with logout even if API call fails
  } finally {
    // Always clear the token regardless of API call success
    await AsyncStorage.removeItem('authToken');
  }
};

