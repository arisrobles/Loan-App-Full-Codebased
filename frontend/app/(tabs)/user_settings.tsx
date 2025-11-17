import AsyncStorage from '@react-native-async-storage/async-storage';
import { useRouter } from 'expo-router';
import React, { useState, useEffect, useCallback } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  SafeAreaView,
  Alert,
  Image,
  Platform,
  ActivityIndicator,
} from 'react-native';
import { Ionicons, MaterialIcons, Feather } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import { api } from '../../src/config/api';

interface UserProfile {
  fullName: string;
  email: string;
  phone?: string;
  address?: string;
  referenceNo?: string;
}

const UserSettingsScreen = () => {
  const router = useRouter();
  const [logoutLoading, setLogoutLoading] = useState(false);
  const [user, setUser] = useState<UserProfile | null>(null);
  const [loading, setLoading] = useState(true);

  const fetchProfile = useCallback(async () => {
    try {
      const res = await api.get('/users/profile');
      if (res.data) {
        setUser(res.data);
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
      }
    } finally {
      setLoading(false);
    }
  }, [router]);

  useEffect(() => {
    fetchProfile();
  }, [fetchProfile]);

  const avatarUrl = user?.fullName
    ? `https://ui-avatars.com/api/?name=${encodeURIComponent(user.fullName)}&background=000000&color=ED80E9&rounded=true&size=128`
    : 'https://ui-avatars.com/api/?name=User&background=000000&color=ED80E9&rounded=true&size=128';

  const handleLogout = () => {
    Alert.alert(
      'Logout',
      'Are you sure you want to logout?',
      [
        {
          text: 'Cancel',
          style: 'cancel',
        },
        {
          text: 'Logout',
          style: 'destructive',
          onPress: async () => {
            setLogoutLoading(true);
            try {
              await api.post('/auth/logout');
            } catch (error) {
              console.error('Logout error:', error);
              // Continue with logout even if API call fails
            } finally {
              // Clear all stored data
              await AsyncStorage.removeItem('authToken');
              setLogoutLoading(false);
              // Navigate to login
              router.replace('./login');
            }
          },
        },
      ],
      { cancelable: true }
    );
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
      {/* Profile Header */}
      <View style={styles.profileCard}>
        <Image
          source={{ uri: avatarUrl }}
          style={styles.avatar}
        />
        <TouchableOpacity style={styles.editIcon}>
          <Feather name="edit-2" size={14} color="black" />
        </TouchableOpacity>
        <Text style={styles.name}>{user?.fullName || 'User'}</Text>
        <Text style={styles.email}>{user?.email || ''}</Text>
        {user?.phone && (
          <Text style={styles.phone}>{user.phone}</Text>
        )}
      </View>

      {/* Settings Card */}
      <LinearGradient
      colors={["#03042c", "#302b63", "#24243e"]}
        start={{ x: 0, y: 0 }}
        end={{ x: 1, y: 1 }}
        style={styles.settingsCard}
      >
        <TouchableOpacity 
          style={styles.settingRow}
          onPress={() => router.push('./setting')}
        >
          <Ionicons name="person-outline" size={20} color="white" />
          <Text style={styles.settingText}>Edit Profile</Text>
          <Ionicons name="chevron-forward" size={20} color="#9CA3AF" style={{ marginLeft: 'auto' }} />
        </TouchableOpacity>

        <View style={styles.separator} />

        <TouchableOpacity style={styles.settingRow}>
          <Ionicons name="lock-closed-outline" size={20} color="white" />
          <Text style={styles.settingText}>Change Password</Text>
        </TouchableOpacity>

        <View style={styles.separator} />

    

        <View style={styles.separator} />

        <View style={styles.settingRowBetween}>
          <Text style={styles.settingLabel}>App Version</Text>
          <Text style={styles.settingValue}>v1.0.0</Text>
        </View>
      </LinearGradient>

      {/* Logout */}
      <TouchableOpacity onPress={handleLogout} disabled={logoutLoading}>
        <LinearGradient
        colors={["#03042c", "#302b63", "#24243e"]}
          start={{ x: 0, y: 0 }}
          end={{ x: 1, y: 1 }}
          style={[styles.logoutBtn, logoutLoading && styles.logoutBtnDisabled]}
        >
          <MaterialIcons name="logout" size={20} color="white" />
          <Text style={styles.logoutText}>
            {logoutLoading ? 'Logging out...' : 'Logout'}
          </Text>
        </LinearGradient>
      </TouchableOpacity>
    </SafeAreaView>
  );
};

export default UserSettingsScreen;

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: 'black',
    justifyContent: 'center',
    paddingHorizontal: 20,
  },
  profileCard: {
    alignItems: 'center',
    marginVertical: 30,
  },
  avatar: {
    width: 110,
    height: 110,
    borderRadius: 55,
    borderWidth: 3,
    borderColor: 'white',
    margin: 10,
  },
  editIcon: {
    position: 'absolute',
    top: 105,
    right: 130,
    backgroundColor: 'white',
    padding: 6,
    borderRadius: 50,
    zIndex: 1,
  },
  name: {
    marginTop: 15,
    fontSize: 22,
    fontWeight: 'bold',
    color: 'white',
  },
  email: {
    fontSize: 14,
    color: '#aaa',
  },
  phone: {
    fontSize: 12,
    color: '#888',
    marginTop: 4,
  },
  settingsCard: {
    borderRadius: 16,
    padding: 20,
    shadowColor: '#FF6B6B',
    shadowOffset: { width: 0, height: Platform.OS === 'ios' ? 2 : 4 },
    shadowOpacity: 0.5,
    shadowRadius: 6,
    elevation: 5,
  },
  settingRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 14,
    width: '100%',
  },
  settingText: {
    marginLeft: 12,
    fontSize: 16,
    color: 'white',
    fontWeight: '500',
  },
  settingRowBetween: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 14,
  },
  settingLabel: {
    fontSize: 16,
    color: 'white',
  },
  settingValue: {
    fontSize: 16,
    fontWeight: '500',
    color: 'white',
  },
  separator: {
    height: 1,
    backgroundColor: 'rgba(255,255,255,0.2)',
    marginVertical: 6,
  },
  logoutBtn: {
    marginTop: 24,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 12,
    borderRadius: 12,
    shadowColor: '#FF6B6B',
    shadowOffset: { width: 0, height: 3 },
    shadowOpacity: 0.4,
    shadowRadius: 6,
    elevation: 4,
    alignSelf: 'center',
    width: 300,
  },
  logoutBtnDisabled: {
    opacity: 0.6,
  },
  logoutText: {
    marginLeft: 8,
    fontSize: 16,
    color: 'white',
    fontWeight: 'bold',
  },
});
