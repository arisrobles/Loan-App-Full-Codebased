import AsyncStorage from '@react-native-async-storage/async-storage';
import { LinearGradient } from 'expo-linear-gradient';
import { useRouter } from 'expo-router';
import React, { useState } from 'react';
import {
    ActivityIndicator,
    Alert,
    Keyboard,
    KeyboardAvoidingView,
    Platform,
    SafeAreaView,
    StyleSheet,
    Text,
    TextInput,
    TouchableOpacity,
    TouchableWithoutFeedback,
    View,
} from 'react-native';
import { api } from '../../src/config/api';

export default function RegisterScreen() {
  const router = useRouter();
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [reference, setReference] = useState('');
  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [loading, setLoading] = useState(false);

  const handleRegister = async () => {
    if (!name || !email || !password || !confirmPassword) {
      Alert.alert('Missing fields', 'Please fill in all required fields');
      return;
    }

    if (password !== confirmPassword) {
      Alert.alert('Password mismatch', 'Passwords do not match');
      return;
    }

    if (password.length < 6) {
      Alert.alert('Weak password', 'Password must be at least 6 characters');
      return;
    }

    try {
      setLoading(true);
      const res = await api.post('/auth/register', {
        fullName: name,
        email,
        referenceNo: reference || undefined,
        password,
      });

      if (res.data?.accessToken) {
        await AsyncStorage.setItem('authToken', res.data.accessToken);
        Alert.alert('✅ Success', 'Registration successful!', [
          {
            text: 'OK',
            onPress: () => {
              router.replace('/(tabs)');
            },
          },
        ]);
      } else {
        Alert.alert('Error', 'Unexpected response from server.');
      }
    } catch (err: any) {
      console.error('Register error:', err);
      if (err.response) {
        // Server responded with error
        const status = err.response.status;
        const message = err.response.data?.message || 'Registration failed';
        if (status === 400 && message.includes('already exists')) {
          Alert.alert('Email Exists', 'This email or reference number is already registered. Please login instead.');
        } else {
          Alert.alert('Registration Failed', message);
        }
      } else if (err.code === 'ECONNREFUSED' || err.message === 'Network Error' || err.request) {
        // Backend server is not running
        Alert.alert(
          'Backend Not Available',
          'Cannot connect to the server.\n\nPlease start the backend server:\n\n1. Open terminal\n2. cd backend\n3. npm run dev\n\nMake sure the server is running on port 8080.',
          [{ text: 'OK' }]
        );
      } else {
        Alert.alert('Error', 'An unexpected error occurred.');
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <SafeAreaView style={styles.safeContainer}>
      <TouchableWithoutFeedback onPress={Keyboard.dismiss}>
        <KeyboardAvoidingView
          behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
          style={styles.container}
        >
          <View style={styles.headerContainer}>
            <Text style={styles.title}>Create Account</Text>
            <Text style={styles.subtitle}>Get started with your loan management</Text>
          </View>

          <View style={styles.form}>
            <TextInput
              style={styles.input}
              placeholder="Full Name"
              placeholderTextColor="#9CA3AF"
              value={name}
              onChangeText={setName}
            />
            <TextInput
              style={styles.input}
              placeholder="Email address"
              placeholderTextColor="#9CA3AF"
              value={email}
              onChangeText={setEmail}
              autoCapitalize="none"
              keyboardType="email-address"
            />
            <TextInput
              style={styles.input}
              placeholder="Reference no."
              placeholderTextColor="#9CA3AF"
              value={reference}
              onChangeText={setReference}
            />
            <TextInput
              style={styles.input}
              placeholder="Password"
              placeholderTextColor="#9CA3AF"
              value={password}
              onChangeText={setPassword}
              secureTextEntry
            />
            <TextInput
              style={styles.input}
              placeholder="Confirm Password"
              placeholderTextColor="#9CA3AF"
              value={confirmPassword}
              onChangeText={setConfirmPassword}
              secureTextEntry
            />

            {/* Gradient Register Button */}
            <TouchableOpacity onPress={handleRegister} activeOpacity={0.8} disabled={loading}>
              <LinearGradient
               colors={["#03042c", "#302b63", "#24243e"]}
                start={{ x: 0, y: 0 }}
                end={{ x: 1, y: 1 }}
                style={styles.button}
              >
                {loading ? (
                  <ActivityIndicator color="#fff" />
                ) : (
                  <Text style={styles.buttonText}>Register</Text>
                )}
              </LinearGradient>
            </TouchableOpacity>

            <TouchableOpacity
              style={styles.loginLinkContainer}
              onPress={() => router.push('./login')}
            >
              <Text style={styles.link}>
                Already have an account?{' '}
                <Text style={styles.linkHighlight}>Login</Text>
              </Text>
            </TouchableOpacity>
          </View>
        </KeyboardAvoidingView>
      </TouchableWithoutFeedback>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeContainer: {
    flex: 1,
    backgroundColor: 'black',
  },
  container: {
    flex: 1,
    paddingHorizontal: 24,
    justifyContent: 'center',
  },
  headerContainer: {
    marginBottom: 48,
  },
  title: {
    fontSize: 32,
    fontWeight: '800',
    color: '#ffffff',
    marginBottom: 8,
  },
  subtitle: {
    fontSize: 16,
    color: '#9CA3AF',
  },
  form: {
    width: '100%',
  },
  input: {
    backgroundColor: '#1C2233',
    padding: 16,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: '#374151',
    marginBottom: 16,
    fontSize: 16,
    color: '#ffffff',
  },
  button: {
    paddingVertical: 16,
    borderRadius: 12,
    alignItems: 'center',
    marginTop: 10,
  },
  buttonText: {
    color: '#ffffff',
    fontSize: 18,
    fontWeight: '600',
  },
  loginLinkContainer: {
    marginTop: 24,
    alignItems: 'center',
  },
  link: {
    fontSize: 14,
    color: '#9CA3AF',
  },
  linkHighlight: {
    color: 'white',
    fontWeight: '600',
  },
});
