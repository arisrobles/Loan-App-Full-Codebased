import React, { useState, useEffect, useCallback } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TextInput,
  TouchableOpacity,
  ScrollView,
  SafeAreaView,
  Alert,
  ActivityIndicator,
} from 'react-native';
import { StatusBar } from 'expo-status-bar';
import { LinearGradient } from 'expo-linear-gradient';
import { useRouter } from 'expo-router';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { api } from '../../src/config/api';

const AccountSettings = () => {
  const router = useRouter();
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [phone, setPhone] = useState('');
  const [address, setAddress] = useState('');
  const [sex, setSex] = useState('');
  const [birthday, setBirthday] = useState('');
  const [occupation, setOccupation] = useState('');
  const [income, setIncome] = useState('');
  const [civilStatus, setCivilStatus] = useState('');
  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');

  const fetchProfile = useCallback(async () => {
    try {
      const res = await api.get('/users/profile');
      if (res.data) {
        setName(res.data.fullName || '');
        setEmail(res.data.email || '');
        setPhone(res.data.phone || '');
        setAddress(res.data.address || '');
        setSex(res.data.sex || '');
        setBirthday(res.data.birthday ? new Date(res.data.birthday).toISOString().split('T')[0] : '');
        setOccupation(res.data.occupation || '');
        setIncome(res.data.monthlyIncome || '');
        setCivilStatus(res.data.civilStatus || '');
      }
    } catch (error: any) {
      console.error('Error fetching profile:', error);
      if (error.response?.status === 401) {
        Alert.alert(
          'Session Expired',
          'Please login again to continue.',
          [
            {
              text: 'OK',
              onPress: async () => {
                await AsyncStorage.removeItem('authToken');
                router.replace('./login');
              },
            },
          ]
        );
      } else {
        Alert.alert('Error', 'Failed to load profile data');
      }
    } finally {
      setLoading(false);
    }
  }, [router]);

  useEffect(() => {
    fetchProfile();
  }, [fetchProfile]);

  const handleSave = async () => {
    if (password && password !== confirmPassword) {
      Alert.alert('Error', 'Passwords do not match');
      return;
    }

    try {
      setSaving(true);
      
      // Validate required fields
      if (!name || name.trim() === '') {
        Alert.alert('Error', 'Full name is required');
        setSaving(false);
        return;
      }

      if (!email || email.trim() === '') {
        Alert.alert('Error', 'Email is required');
        setSaving(false);
        return;
      }

      // Build update payload with all fields
      // Send all fields that have values to ensure they're saved
      const updateData: any = {
        fullName: name.trim(),
        email: email.trim(),
      };

      // Add optional fields only if they have values
      if (phone && phone.trim()) updateData.phone = phone.trim();
      if (address && address.trim()) updateData.address = address.trim();
      if (sex && sex.trim()) updateData.sex = sex.trim();
      if (birthday && birthday.trim()) updateData.birthday = birthday.trim();
      if (occupation && occupation.trim()) updateData.occupation = occupation.trim();
      if (income && income.trim()) {
        const incomeNum = parseFloat(income.trim());
        if (!isNaN(incomeNum)) {
          updateData.monthlyIncome = incomeNum;
        }
      }
      if (civilStatus && civilStatus.trim()) updateData.civilStatus = civilStatus.trim();

      const res = await api.put('/users/profile', updateData);
      
      if (res.data?.success) {
        Alert.alert('✅ Success', 'Profile updated successfully!', [
          {
            text: 'OK',
            onPress: () => router.back(),
          },
        ]);
      } else {
        Alert.alert('Error', 'Failed to update profile');
      }
    } catch (error: any) {
      console.error('Error updating profile:', error);
      const message = error.response?.data?.message || 'Failed to update profile';
      Alert.alert('Error', message);
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return (
      <SafeAreaView style={styles.container}>
        <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center' }}>
          <ActivityIndicator size="large" color="#ED80E9" />
          <Text style={{ color: '#fff', marginTop: 16 }}>Loading profile...</Text>
        </View>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar style="light" />
      <ScrollView contentContainerStyle={{ paddingBottom: 40 }}>
        <View style={styles.header}>
          <Text style={styles.headerTitle}>Account Settings</Text>
        </View>

        {/* Profile Section */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Profile Information</Text>
          <TextInput
            style={styles.input}
            placeholder="Full Name"
            placeholderTextColor="#9CA3AF"
            value={name}
            onChangeText={setName}
          />
          <TextInput
            style={styles.input}
            placeholder="Email Address"
            placeholderTextColor="#9CA3AF"
            keyboardType="email-address"
            value={email}
            onChangeText={setEmail}
            autoCapitalize="none"
          />
          <TextInput
            style={styles.input}
            placeholder="Phone Number"
            placeholderTextColor="#9CA3AF"
            keyboardType="phone-pad"
            value={phone}
            onChangeText={setPhone}
          />
          <TextInput
            style={styles.input}
            placeholder="Address"
            placeholderTextColor="#9CA3AF"
            value={address}
            onChangeText={setAddress}
          />
          <TextInput
            style={styles.input}
            placeholder="Sex (Male / Female / Other)"
            placeholderTextColor="#9CA3AF"
            value={sex}
            onChangeText={setSex}
          />
          <TextInput
            style={styles.input}
            placeholder="Birthday (YYYY-MM-DD)"
            placeholderTextColor="#9CA3AF"
            value={birthday}
            onChangeText={setBirthday}
          />
          <TextInput
            style={styles.input}
            placeholder="Occupation"
            placeholderTextColor="#9CA3AF"
            value={occupation}
            onChangeText={setOccupation}
          />
          <TextInput
            style={styles.input}
            placeholder="Monthly Income"
            placeholderTextColor="#9CA3AF"
            keyboardType="numeric"
            value={income}
            onChangeText={setIncome}
          />
          <TextInput
            style={styles.input}
            placeholder="Civil Status (Single / Married / etc.)"
            placeholderTextColor="#9CA3AF"
            value={civilStatus}
            onChangeText={setCivilStatus}
          />
        </View>

        {/* Change Password */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Change Password</Text>
          <TextInput
            style={styles.input}
            placeholder="New Password"
            placeholderTextColor="#9CA3AF"
            secureTextEntry
            value={password}
            onChangeText={setPassword}
          />
          <TextInput
            style={styles.input}
            placeholder="Confirm Password"
            placeholderTextColor="#9CA3AF"
            secureTextEntry
            value={confirmPassword}
            onChangeText={setConfirmPassword}
          />
        </View>

        {/* Preferences */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Preferences</Text>
          <Text style={styles.preferenceText}>Notifications, Language, etc.</Text>
        </View>

        {/* Save Button with Gradient */}
        <TouchableOpacity onPress={handleSave} disabled={saving}>
          <LinearGradient
            colors={["#03042c", "#302b63", "#24243e"]}
            start={{ x: 0, y: 0 }}
            end={{ x: 1, y: 1 }}
            style={[styles.saveButton, saving && styles.saveButtonDisabled]}
          >
            {saving ? (
              <ActivityIndicator color="#fff" />
            ) : (
              <Text style={styles.saveButtonText}>Save Changes</Text>
            )}
          </LinearGradient>
        </TouchableOpacity>
      </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: 'black',
    paddingHorizontal: 20,
  },
  header: {
    marginTop: 40,
    marginBottom: 20,
  },
  headerTitle: {
    fontSize: 24,
    fontWeight: '700',
    color: '#ffffff',
  },
  section: {
    marginBottom: 30,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: '#ffffff',
    marginBottom: 12,
  },
  input: {
    backgroundColor: '#1C2233',
    color: '#ffffff',
    padding: 14,
    borderRadius: 12,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: '#374151',
  },
  preferenceText: {
    color: '#9CA3AF',
    fontSize: 14,
  },
  saveButton: {
    paddingVertical: 16,
    borderRadius: 16,
    alignItems: 'center',
    marginTop: 10,
  },
  saveButtonText: {
    color: '#ffffff',
    fontWeight: '700',
    fontSize: 16,
  },
  saveButtonDisabled: {
    opacity: 0.6,
  },
});

export default AccountSettings;
