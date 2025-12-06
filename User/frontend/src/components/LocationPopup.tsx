import React, { useState, useEffect } from 'react';
import {
  Modal,
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  ActivityIndicator,
  Alert,
  Platform,
} from 'react-native';
import * as Location from 'expo-location';
import { Ionicons } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';

interface LocationPopupProps {
  visible: boolean;
  onClose: () => void;
  onLocationObtained: (location: {
    latitude: number;
    longitude: number;
    address?: string;
  }) => void;
  title?: string;
  message?: string;
}

export default function LocationPopup({
  visible,
  onClose,
  onLocationObtained,
  title = 'Location Access',
  message = 'We need your location to provide better service. Please allow location access.',
}: LocationPopupProps) {
  const [loading, setLoading] = useState(false);
  const [location, setLocation] = useState<Location.LocationObject | null>(null);
  const [address, setAddress] = useState<string>('');
  const [error, setError] = useState<string>('');

  useEffect(() => {
    if (visible) {
      requestLocation();
    } else {
      // Reset state when modal closes
      setLocation(null);
      setAddress('');
      setError('');
    }
  }, [visible]);

  const requestLocation = async () => {
    try {
      setLoading(true);
      setError('');

      // Check if location services are enabled
      const servicesEnabled = await Location.hasServicesEnabledAsync();
      if (!servicesEnabled) {
        setError('Location services are disabled. Please enable them in your device settings.');
        setLoading(false);
        return;
      }

      // Request permissions
      const { status } = await Location.requestForegroundPermissionsAsync();
      if (status !== 'granted') {
        setError('Location permission was denied. Please enable it in your device settings.');
        setLoading(false);
        return;
      }

      // Get current location
      const currentLocation = await Location.getCurrentPositionAsync({
        accuracy: Location.Accuracy.Balanced,
      });

      setLocation(currentLocation);

      // Reverse geocode to get address
      try {
        const reverseGeocode = await Location.reverseGeocodeAsync({
          latitude: currentLocation.coords.latitude,
          longitude: currentLocation.coords.longitude,
        });

        if (reverseGeocode && reverseGeocode.length > 0) {
          const addr = reverseGeocode[0];
          const addressString = [
            addr.street,
            addr.city,
            addr.region,
            addr.country,
          ]
            .filter(Boolean)
            .join(', ');
          setAddress(addressString);
        }
      } catch (geocodeError) {
        console.warn('Reverse geocoding failed:', geocodeError);
        // Continue without address
      }

      setLoading(false);
    } catch (err: any) {
      console.error('Location error:', err);
      setError(err.message || 'Failed to get location. Please try again.');
      setLoading(false);
    }
  };

  const handleConfirm = () => {
    if (location) {
      onLocationObtained({
        latitude: location.coords.latitude,
        longitude: location.coords.longitude,
        address: address || undefined,
      });
      onClose();
    }
  };

  const handleRetry = () => {
    requestLocation();
  };

  const handleOpenSettings = () => {
    if (Platform.OS === 'ios') {
      Location.enableNetworkProviderAsync();
    }
    Alert.alert(
      'Enable Location',
      'Please enable location services in your device settings.',
      [
        { text: 'Cancel', style: 'cancel' },
        { text: 'Open Settings', onPress: () => Location.requestForegroundPermissionsAsync() },
      ]
    );
  };

  return (
    <Modal
      visible={visible}
      transparent
      animationType="fade"
      onRequestClose={onClose}
    >
      <View style={styles.overlay}>
        <View style={styles.container}>
          <View style={styles.header}>
            <Ionicons name="location" size={32} color="#f97316" />
            <Text style={styles.title}>{title}</Text>
            <Text style={styles.message}>{message}</Text>
          </View>

          {loading && (
            <View style={styles.loadingContainer}>
              <ActivityIndicator size="large" color="#f97316" />
              <Text style={styles.loadingText}>Getting your location...</Text>
            </View>
          )}

          {error && (
            <View style={styles.errorContainer}>
              <Ionicons name="alert-circle" size={24} color="#ef4444" />
              <Text style={styles.errorText}>{error}</Text>
              <TouchableOpacity
                style={styles.retryButton}
                onPress={handleRetry}
              >
                <Text style={styles.retryButtonText}>Retry</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={styles.settingsButton}
                onPress={handleOpenSettings}
              >
                <Text style={styles.settingsButtonText}>Open Settings</Text>
              </TouchableOpacity>
            </View>
          )}

          {location && !loading && !error && (
            <View style={styles.locationContainer}>
              <View style={styles.locationInfo}>
                <View style={styles.coordinateRow}>
                  <Ionicons name="navigate" size={20} color="#4EFA8A" />
                  <Text style={styles.coordinateText}>
                    {location.coords.latitude.toFixed(6)}, {location.coords.longitude.toFixed(6)}
                  </Text>
                </View>
                {address && (
                  <View style={styles.addressRow}>
                    <Ionicons name="map" size={20} color="#4EFA8A" />
                    <Text style={styles.addressText}>{address}</Text>
                  </View>
                )}
                {location.coords.accuracy && (
                  <Text style={styles.accuracyText}>
                    Accuracy: ±{Math.round(location.coords.accuracy)}m
                  </Text>
                )}
              </View>
            </View>
          )}

          <View style={styles.buttonContainer}>
            <TouchableOpacity
              style={styles.cancelButton}
              onPress={onClose}
            >
              <Text style={styles.cancelButtonText}>Cancel</Text>
            </TouchableOpacity>

            {location && !loading && !error && (
              <TouchableOpacity
                style={styles.confirmButtonWrapper}
                onPress={handleConfirm}
              >
                <LinearGradient
                  colors={['#03042c', '#302b63', '#24243e']}
                  start={{ x: 0, y: 0 }}
                  end={{ x: 1, y: 1 }}
                  style={styles.confirmButton}
                >
                  <Text style={styles.confirmButtonText}>Confirm Location</Text>
                </LinearGradient>
              </TouchableOpacity>
            )}
          </View>
        </View>
      </View>
    </Modal>
  );
}

const styles = StyleSheet.create({
  overlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.7)',
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  container: {
    backgroundColor: '#1e1e2f',
    borderRadius: 20,
    padding: 24,
    width: '100%',
    maxWidth: 400,
    elevation: 10,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 8,
  },
  header: {
    alignItems: 'center',
    marginBottom: 24,
  },
  title: {
    fontSize: 22,
    fontWeight: '700',
    color: '#fff',
    marginTop: 12,
    marginBottom: 8,
  },
  message: {
    fontSize: 14,
    color: '#aaa',
    textAlign: 'center',
    lineHeight: 20,
  },
  loadingContainer: {
    alignItems: 'center',
    paddingVertical: 32,
  },
  loadingText: {
    marginTop: 16,
    color: '#aaa',
    fontSize: 14,
  },
  errorContainer: {
    alignItems: 'center',
    paddingVertical: 16,
  },
  errorText: {
    color: '#ef4444',
    fontSize: 14,
    textAlign: 'center',
    marginTop: 12,
    marginBottom: 16,
    lineHeight: 20,
  },
  retryButton: {
    backgroundColor: '#f97316',
    paddingHorizontal: 24,
    paddingVertical: 10,
    borderRadius: 8,
    marginBottom: 8,
  },
  retryButtonText: {
    color: '#fff',
    fontWeight: '600',
    fontSize: 14,
  },
  settingsButton: {
    paddingHorizontal: 24,
    paddingVertical: 10,
  },
  settingsButtonText: {
    color: '#4EFA8A',
    fontWeight: '600',
    fontSize: 14,
  },
  locationContainer: {
    backgroundColor: '#2a2a3e',
    borderRadius: 12,
    padding: 16,
    marginBottom: 16,
  },
  locationInfo: {
    gap: 12,
  },
  coordinateRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  coordinateText: {
    color: '#fff',
    fontSize: 14,
    fontFamily: Platform.OS === 'ios' ? 'Courier' : 'monospace',
  },
  addressRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: 8,
    marginTop: 4,
  },
  addressText: {
    color: '#ccc',
    fontSize: 13,
    flex: 1,
    lineHeight: 18,
  },
  accuracyText: {
    color: '#888',
    fontSize: 12,
    marginTop: 4,
  },
  buttonContainer: {
    flexDirection: 'row',
    gap: 12,
    marginTop: 8,
  },
  cancelButton: {
    flex: 1,
    paddingVertical: 14,
    borderRadius: 8,
    backgroundColor: '#444',
    alignItems: 'center',
  },
  cancelButtonText: {
    color: '#fff',
    fontWeight: '600',
    fontSize: 16,
  },
  confirmButtonWrapper: {
    flex: 1,
    borderRadius: 8,
    overflow: 'hidden',
  },
  confirmButton: {
    paddingVertical: 14,
    alignItems: 'center',
  },
  confirmButtonText: {
    color: '#fff',
    fontWeight: '700',
    fontSize: 16,
  },
});

