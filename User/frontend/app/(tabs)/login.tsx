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
  View
} from 'react-native';
import { api } from '../../src/config/api';

export default function LoginScreen() {
  const router = useRouter();
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);

  const handleLogin = async () => {
    if (!username || !password) {
      Alert.alert('Missing fields', 'Please enter your username and password');
      return;
    }

    try {
      setLoading(true);

      const res = await api.post('/auth/login', {
        username,
        password,
      });

      if (res.data?.accessToken) {
        await AsyncStorage.setItem('authToken', res.data.accessToken);
        Alert.alert('✅ Success', 'Login successful!', [
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
      console.error('Login error:', err);

      // 🔍 Distinguish between error types
      if (err.code === 'ECONNABORTED') {
        Alert.alert('Timeout', 'The request took too long. Please try again.');
      } else if (err.response) {
        // Server responded with error status (e.g., 400, 401, 500)
        const status = err.response.status;
        const message = err.response.data?.message || 'Login failed';
        switch (status) {
          case 400:
            Alert.alert('Bad Request', message);
            break;
          case 401:
            Alert.alert('Unauthorized', 'Incorrect username or password.');
            break;
          case 404:
            Alert.alert('Not Found', 'Login endpoint not found on server.');
            break;
          case 500:
            Alert.alert('Server Error', 'Something went wrong on the server.');
            break;
          default:
            Alert.alert('Error', message);
        }
      } else if (err.request) {
        // No response received from server
        Alert.alert(
          'Network Error',
          'Unable to reach the server. Check your internet or IP address.'
        );
      } else {
        // Something else went wrong (JS/Parsing)
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
            <Text style={styles.title}>MasterFunds</Text>
            <Text style={styles.subtitle}>
              Sign in to continue managing your loans
            </Text>
          </View>

          <View style={styles.form}>
            <TextInput
              style={styles.input}
              placeholder="Username"
              placeholderTextColor="#9CA3AF"
              onChangeText={setUsername}
              value={username}
              autoCapitalize="none"
            />
            <TextInput
              style={styles.input}
              placeholder="Password"
              placeholderTextColor="#9CA3AF"
              secureTextEntry
              onChangeText={setPassword}
              value={password}
            />

            <TouchableOpacity
              style={{ alignSelf: 'flex-end', marginBottom: 24 }}
              onPress={() => router.push('./forgot_password')}
            >
              <Text style={{ color: '#9CA3AF' }}>Forgot Password?</Text>
            </TouchableOpacity>

            <TouchableOpacity
              activeOpacity={0.85}
              onPress={handleLogin}
              disabled={loading}
            >
              <LinearGradient
                colors={["#03042c", "#302b63", "#24243e"]}
                start={{ x: 0, y: 0 }}
                end={{ x: 1, y: 1 }}
                style={styles.button}
              >
                {loading ? (
                  <ActivityIndicator color="#fff" />
                ) : (
                  <Text style={styles.buttonText}>Login</Text>
                )}
              </LinearGradient>
            </TouchableOpacity>



            <TouchableOpacity
              style={styles.registerContainer}
              onPress={() => router.push('./register')}
            >
              <Text style={styles.link}>
                Don’t have an account?{' '}
                <Text style={styles.linkHighlight}>Register</Text>
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
    color: 'white',
    marginBottom: 8,
    textAlign: 'center',
  },
  subtitle: {
    fontSize: 16,
    color: 'white',
    textAlign: 'center',
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
    shadowColor: '#ED80E9',
    shadowOpacity: 0.2,
    shadowRadius: 6,
    shadowOffset: { width: 0, height: 4 },
    elevation: 3,
  },
  buttonText: {
    color: '#ffffff',
    fontSize: 18,
    fontWeight: '600',
  },
  registerContainer: {
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
