import { Ionicons } from '@expo/vector-icons';
import * as ImagePicker from 'expo-image-picker';
import { LinearGradient } from 'expo-linear-gradient';
import { useRouter } from 'expo-router';
import React, { useEffect, useRef, useState } from 'react';
import {
    ActivityIndicator,
    Alert,
    Animated,
    SafeAreaView,
    StyleSheet,
    Text,
    TouchableOpacity,
    View
} from 'react-native';
import { api } from '../../src/config/api';

export default function AttachReceiptScreen() {
  const [image, setImage] = useState<string | null>(null);
  const [hasGalleryPermission, setHasGalleryPermission] = useState<boolean | null>(null);
  const [hasCameraPermission, setHasCameraPermission] = useState<boolean | null>(null);
  const [loading, setLoading] = useState(false);
  const fadeAnim = useRef(new Animated.Value(0)).current;
  const router = useRouter();

  useEffect(() => {
    (async () => {
      const galleryStatus = await ImagePicker.requestMediaLibraryPermissionsAsync();
      setHasGalleryPermission(galleryStatus.status === 'granted');

      const cameraStatus = await ImagePicker.requestCameraPermissionsAsync();
      setHasCameraPermission(cameraStatus.status === 'granted');
    })();
  }, []);

  const handleImageSet = (uri: string) => {
    setImage(uri);
    fadeAnim.setValue(0);
    Animated.timing(fadeAnim, {
      toValue: 1,
      duration: 300,
      useNativeDriver: true,
    }).start();
  };

  const pickImage = async () => {
    if (!hasGalleryPermission) {
      return Alert.alert('Permission denied', 'Gallery access is required.');
    }

    const result = await ImagePicker.launchImageLibraryAsync({
      mediaTypes: ImagePicker.MediaTypeOptions.Images,
      quality: 0.5, // compressed
    });

    if (!result.canceled) {
      handleImageSet(result.assets[0].uri);
    }
  };

  const takePhoto = async () => {
    if (!hasCameraPermission) {
      return Alert.alert('Permission denied', 'Camera access is required.');
    }

    const result = await ImagePicker.launchCameraAsync({
      quality: 0.5,
    });

    if (!result.canceled) {
      handleImageSet(result.assets[0].uri);
    }
  };

  const handleSubmit = async () => {
    if (!image) {
      return Alert.alert('No receipt', 'Please upload or take a photo of the receipt.');
    }

    try {
      setLoading(true);

      // Convert image URI to FormData
      const filename = image.split('/').pop() || 'receipt.jpg';
      const fileType = filename.split('.').pop() || 'jpg';
      const formData = new FormData();
      
      formData.append('file', {
        uri: image,
        name: filename,
        type: `image/${fileType}`,
      } as any);

      formData.append('documentType', 'RECEIPT');

      const res = await api.post('/documents/upload', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });

      Alert.alert('✅ Success', 'Receipt uploaded successfully!', [
        {
          text: 'OK',
          onPress: () => router.replace('/(tabs)'),
        },
      ]);
    } catch (err: any) {
      console.error('Upload error:', err);
      const message = err.response?.data?.message || 'Failed to upload receipt';
      Alert.alert('Upload Failed', message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <SafeAreaView style={styles.safeContainer}>
      <View style={styles.container}>
        <Text style={styles.title}>Attach Receipt</Text>
        <Text style={styles.subtitle}>Upload or capture a photo of your payment receipt</Text>

        <View style={styles.card}>
          {image ? (
            <Animated.Image source={{ uri: image }} style={[styles.preview, { opacity: fadeAnim }]} />
          ) : (
            <View style={styles.placeholder}>
              <Ionicons name="camera" size={50} color="#9CA3AF" />
              <Text style={styles.placeholderText}>No image selected</Text>
            </View>
          )}
        </View>

        <TouchableOpacity
          onPress={pickImage}
          activeOpacity={0.85}
          disabled={!hasGalleryPermission}
          style={{ width: '100%', marginBottom: 12 }}
        >
          <LinearGradient
            colors={["#03042c", "#302b63", "#24243e"]}
            start={{ x: 0, y: 0 }}
            end={{ x: 1, y: 0 }}
            style={styles.gradientButton}
          >
            <Text style={styles.gradientButtonText}>Choose from Gallery</Text>
          </LinearGradient>
        </TouchableOpacity>

        <TouchableOpacity
          onPress={takePhoto}
          activeOpacity={0.85}
          disabled={!hasCameraPermission}
          style={{ width: '100%', marginBottom: 12 }}
        >
          <LinearGradient
         colors={["#03042c", "#302b63", "#24243e"]}
            start={{ x: 0, y: 0 }}
            end={{ x: 1, y: 0 }}
            style={styles.gradientButton}
          >
            <Text style={styles.gradientButtonText}>Take Photo</Text>
          </LinearGradient>
        </TouchableOpacity>

        <TouchableOpacity
          onPress={handleSubmit}
          activeOpacity={0.85}
          style={{ width: '100%' }}
          disabled={loading}
        >
          <LinearGradient
            colors={["#03042c", "#302b63", "#24243e"]}
            start={{ x: 0, y: 0 }}
            end={{ x: 1, y: 0 }}
            style={styles.gradientButton}
          >
            {loading ? (
              <ActivityIndicator color="#fff" />
            ) : (
              <Text style={styles.gradientButtonText}>Submit</Text>
            )}
          </LinearGradient>
        </TouchableOpacity>
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeContainer: { flex: 1, backgroundColor: 'black' },
  container: { flex: 1, padding: 24, justifyContent: 'center', alignItems: 'center' },
  title: { fontSize: 24, fontWeight: 'bold', color: '#fff', marginBottom: 8 },
  subtitle: { fontSize: 16, color: '#9CA3AF', textAlign: 'center', marginBottom: 24 },
  card: {
    width: '100%',
    height: 200,
    borderWidth: 1,
    borderColor: '#374151',
    borderRadius: 12,
    marginBottom: 20,
    justifyContent: 'center',
    alignItems: 'center',
    overflow: 'hidden',
    backgroundColor: '#1C2233',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.25,
    shadowRadius: 3.84,
    elevation: 5,
  },
  preview: { width: '100%', height: '100%', resizeMode: 'cover' },
  placeholder: { justifyContent: 'center', alignItems: 'center', flex: 1 },
  placeholderText: { color: '#9CA3AF', fontSize: 16, marginTop: 8 },
  gradientButton: {
    paddingVertical: 14,
    borderRadius: 12,
    alignItems: 'center',
    justifyContent: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.3,
    shadowRadius: 3,
    elevation: 3,
  },
  gradientButtonText: { color: '#fff', fontWeight: '600', fontSize: 16 },
});
