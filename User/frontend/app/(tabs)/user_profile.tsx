import AsyncStorage from '@react-native-async-storage/async-storage';
import { LinearGradient } from 'expo-linear-gradient';
import { useRouter } from 'expo-router';
import React, { useCallback, useEffect, useState } from 'react';
import {
    ActivityIndicator,
    Alert,
    Image,
    SafeAreaView,
    ScrollView,
    StyleSheet,
    Text,
    TouchableOpacity,
    View,
    Animated,
    Dimensions,
    Modal,
} from 'react-native';
import { StatusBar } from 'expo-status-bar';
import { Ionicons, MaterialIcons } from '@expo/vector-icons';
import { api } from '../../src/config/api';

const { width: SCREEN_WIDTH } = Dimensions.get('window');
const DRAWER_WIDTH = SCREEN_WIDTH * 0.8;

interface UserProfile {
  fullName: string;
  email: string;
  phone?: string;
  address?: string;
  referenceNo?: string;
  sex?: string;
  occupation?: string;
  birthday?: string;
  monthlyIncome?: string;
  civilStatus?: string;
}

export default function UserProfileScreen() {
  const router = useRouter();
  const [user, setUser] = useState<UserProfile | null>(null);
  const [loading, setLoading] = useState(true);
  const [drawerVisible, setDrawerVisible] = useState(false);
  const drawerAnimation = useState(new Animated.Value(-DRAWER_WIDTH))[0];
  const overlayOpacity = useState(new Animated.Value(0))[0];

  const fetchProfile = useCallback(async () => {
    try {
      const res = await api.get('/users/profile');
      if (res.data) {
        setUser(res.data);
      }
    } catch (error: any) {
      console.error('Error fetching profile:', error);
      // If unauthorized, redirect to login
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
      } else if (error.code === 'ECONNREFUSED' || error.message === 'Network Error') {
        // Backend server is not running
        Alert.alert(
          'Backend Not Available',
          'Cannot connect to the server. Please make sure the backend is running on port 8080.',
          [{ text: 'OK' }]
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

  const handleEdit = () => {
    router.push('./setting'); // Navigate to edit profile
  };

  const formatDate = (dateString?: string) => {
    if (!dateString) return 'Not set';
    try {
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
      });
    } catch {
      return 'Not set';
    }
  };

  const formatCurrency = (amount?: string) => {
    if (!amount) return 'Not set';
    const num = parseFloat(amount);
    if (isNaN(num)) return 'Not set';
    return `₱${num.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;
  };

  const openDrawer = () => {
    setDrawerVisible(true);
    Animated.parallel([
      Animated.timing(drawerAnimation, {
        toValue: 0,
        duration: 300,
        useNativeDriver: true,
      }),
      Animated.timing(overlayOpacity, {
        toValue: 1,
        duration: 300,
        useNativeDriver: true,
      }),
    ]).start();
  };

  const closeDrawer = () => {
    Animated.parallel([
      Animated.timing(drawerAnimation, {
        toValue: -DRAWER_WIDTH,
        duration: 300,
        useNativeDriver: true,
      }),
      Animated.timing(overlayOpacity, {
        toValue: 0,
        duration: 300,
        useNativeDriver: true,
      }),
    ]).start(() => {
      setDrawerVisible(false);
    });
  };

  const handleDrawerItemPress = (route: string) => {
    closeDrawer();
    setTimeout(() => {
      router.push(route as any);
    }, 300);
  };

  const handleLogout = async () => {
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
            try {
              await api.post('/auth/logout');
            } catch (error) {
              console.error('Logout error:', error);
              // Continue with logout even if API call fails
            } finally {
              // Clear all stored data
              await AsyncStorage.removeItem('authToken');
              // Clear any other cached data if needed
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
      <SafeAreaView style={styles.safeContainer}>
        <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center' }}>
          <ActivityIndicator size="large" color="#ED80E9" />
        </View>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.safeContainer}>
      <StatusBar style="light" />
      <ScrollView 
        contentContainerStyle={styles.container}
        showsVerticalScrollIndicator={false}
      >
        {/* Header Section */}
        <View style={styles.header}>
          <View style={styles.headerTop}>
            <TouchableOpacity 
              style={styles.menuButton}
              onPress={openDrawer}
            >
              <Ionicons name="menu" size={26} color="#ED80E9" />
            </TouchableOpacity>
            <Text style={styles.headerTitle}>Profile</Text>
            <TouchableOpacity 
              style={styles.editButton}
              onPress={handleEdit}
            >
              <Ionicons name="create-outline" size={22} color="#ED80E9" />
            </TouchableOpacity>
          </View>
        </View>

        {/* Profile Header Card */}
        <LinearGradient
          colors={["#03042c", "#302b63", "#24243e"]}
          start={{ x: 0, y: 0 }}
          end={{ x: 1, y: 1 }}
          style={styles.profileHeader}
        >
          <View style={styles.avatarContainer}>
            <Image source={{ uri: avatarUrl }} style={styles.avatar} />
            <View style={styles.avatarBadge}>
              <Ionicons name="checkmark" size={16} color="#fff" />
            </View>
          </View>
          <Text style={styles.name}>{user?.fullName || 'User'}</Text>
          <Text style={styles.email}>{user?.email || ''}</Text>
          {user?.referenceNo && (
            <View style={styles.referenceBadge}>
              <Text style={styles.referenceText}>ID: {user.referenceNo}</Text>
            </View>
          )}
        </LinearGradient>

        {/* Personal Information Section */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Personal Information</Text>
          <LinearGradient
            colors={["#03042c", "#302b63", "#24243e"]}
            start={{ x: 0, y: 0 }}
            end={{ x: 1, y: 1 }}
            style={styles.infoCard}
          >
            <InfoRow 
              icon="call-outline" 
              label="Phone" 
              value={user?.phone || 'Not provided'} 
            />
            <View style={styles.separator} />
            <InfoRow 
              icon="location-outline" 
              label="Address" 
              value={user?.address || 'Not provided'} 
            />
            <View style={styles.separator} />
            <InfoRow 
              icon="calendar-outline" 
              label="Birthday" 
              value={formatDate(user?.birthday)} 
            />
            <View style={styles.separator} />
            <InfoRow 
              icon="person-outline" 
              label="Sex" 
              value={user?.sex || 'Not provided'} 
            />
            <View style={styles.separator} />
            <InfoRow 
              icon="briefcase-outline" 
              label="Occupation" 
              value={user?.occupation || 'Not provided'} 
            />
            <View style={styles.separator} />
            <InfoRow 
              icon="heart-outline" 
              label="Civil Status" 
              value={user?.civilStatus || 'Not provided'} 
            />
            <View style={styles.separator} />
            <InfoRow 
              icon="cash-outline" 
              label="Monthly Income" 
              value={formatCurrency(user?.monthlyIncome)} 
            />
          </LinearGradient>
        </View>
      </ScrollView>

      {/* Drawer Menu */}
      <Modal
        visible={drawerVisible}
        transparent={true}
        animationType="none"
        onRequestClose={closeDrawer}
      >
        <View style={styles.drawerContainer}>
          {/* Overlay */}
          <Animated.View
            style={[
              styles.drawerOverlay,
              {
                opacity: overlayOpacity,
              },
            ]}
          >
            <TouchableOpacity
              style={styles.overlayTouchable}
              activeOpacity={1}
              onPress={closeDrawer}
            />
          </Animated.View>

          {/* Drawer */}
          <Animated.View
            style={[
              styles.drawer,
              {
                transform: [{ translateX: drawerAnimation }],
              },
            ]}
          >
            <ScrollView style={styles.drawerContent} showsVerticalScrollIndicator={false}>
              {/* Drawer Header */}
              <View style={styles.drawerHeader}>
                <Image source={{ uri: avatarUrl }} style={styles.drawerAvatar} />
                <Text style={styles.drawerName}>{user?.fullName || 'User'}</Text>
                <Text style={styles.drawerEmail}>{user?.email || ''}</Text>
              </View>

              {/* Drawer Menu Items */}
              <View style={styles.drawerMenu}>
                <DrawerMenuItem
                  icon="person-outline"
                  label="Edit Profile"
                  onPress={() => handleDrawerItemPress('./setting')}
                />
                <DrawerMenuItem
                  icon="settings-outline"
                  label="Settings"
                  onPress={() => handleDrawerItemPress('./user_settings')}
                />
                <DrawerMenuItem
                  icon="document-text-outline"
                  label="Loan History"
                  onPress={() => handleDrawerItemPress('./loan_history')}
                />
                <DrawerMenuItem
                  icon="receipt-outline"
                  label="Payment History"
                  onPress={() => handleDrawerItemPress('./payment_history')}
                />
                <DrawerMenuItem
                  icon="card-outline"
                  label="Credit Score"
                  onPress={() => handleDrawerItemPress('./credit')}
                />
                <DrawerMenuItem
                  icon="notifications-outline"
                  label="Notifications"
                  onPress={() => handleDrawerItemPress('./notif')}
                />
                <DrawerMenuItem
                  icon="help-circle-outline"
                  label="Help & Support"
                  onPress={() => handleDrawerItemPress('./contact')}
                />
                <DrawerMenuItem
                  icon="document-outline"
                  label="Terms & Conditions"
                  onPress={() => handleDrawerItemPress('./legal')}
                />
                <DrawerMenuItem
                  icon="information-circle-outline"
                  label="About"
                  onPress={() => handleDrawerItemPress('./about')}
                />
              </View>

              {/* App Version */}
              <View style={styles.drawerFooter}>
                <Text style={styles.drawerVersion}>App Version v1.0.0</Text>
              </View>
            </ScrollView>

            {/* Logout Button in Drawer */}
            <TouchableOpacity
              style={styles.drawerLogout}
              onPress={() => {
                closeDrawer();
                setTimeout(() => {
                  handleLogout();
                }, 300);
              }}
            >
              <MaterialIcons name="logout" size={20} color="#FF6B6B" />
              <Text style={styles.drawerLogoutText}>Logout</Text>
            </TouchableOpacity>
          </Animated.View>
        </View>
      </Modal>
    </SafeAreaView>
  );
}

interface InfoRowProps {
  icon: string;
  label: string;
  value: string;
}

const InfoRow = ({ icon, label, value }: InfoRowProps) => (
  <View style={styles.infoRow}>
    <Ionicons name={icon as any} size={20} color="#ED80E9" />
    <View style={styles.infoContent}>
      <Text style={styles.infoLabel}>{label}</Text>
      <Text style={styles.infoValue}>{value}</Text>
    </View>
  </View>
);

interface DrawerMenuItemProps {
  icon: string;
  label: string;
  onPress: () => void;
}

const DrawerMenuItem = ({ icon, label, onPress }: DrawerMenuItemProps) => (
  <TouchableOpacity style={styles.drawerMenuItem} onPress={onPress}>
    <Ionicons name={icon as any} size={22} color="#ED80E9" />
    <Text style={styles.drawerMenuItemLabel}>{label}</Text>
    <Ionicons name="chevron-forward" size={20} color="rgba(255, 255, 255, 0.4)" style={{ marginLeft: 'auto' }} />
  </TouchableOpacity>
);

const styles = StyleSheet.create({
  safeContainer: {
    flex: 1,
    backgroundColor: '#000',
  },
  container: {
    paddingBottom: 30,
  },
  header: {
    paddingHorizontal: 20,
    paddingTop: 20,
    paddingBottom: 16,
  },
  headerTop: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  menuButton: {
    width: 44,
    height: 44,
    borderRadius: 22,
    backgroundColor: 'rgba(237, 128, 233, 0.1)',
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: 'rgba(237, 128, 233, 0.3)',
  },
  headerTitle: {
    fontSize: 32,
    fontWeight: '700',
    color: '#fff',
    flex: 1,
    textAlign: 'center',
  },
  editButton: {
    width: 44,
    height: 44,
    borderRadius: 22,
    backgroundColor: 'rgba(237, 128, 233, 0.1)',
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: 'rgba(237, 128, 233, 0.3)',
  },
  profileHeader: {
    marginHorizontal: 20,
    marginTop: 20,
    borderRadius: 20,
    padding: 24,
    alignItems: 'center',
    shadowColor: '#ED80E9',
    shadowOpacity: 0.3,
    shadowRadius: 12,
    shadowOffset: { width: 0, height: 4 },
    elevation: 8,
  },
  avatarContainer: {
    position: 'relative',
    marginBottom: 16,
  },
  avatar: {
    width: 120,
    height: 120,
    borderRadius: 60,
    borderWidth: 4,
    borderColor: '#ED80E9',
  },
  avatarBadge: {
    position: 'absolute',
    bottom: 0,
    right: 0,
    width: 36,
    height: 36,
    borderRadius: 18,
    backgroundColor: '#ED80E9',
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 3,
    borderColor: '#000',
  },
  name: {
    fontSize: 26,
    fontWeight: '700',
    color: '#fff',
    marginBottom: 6,
    textAlign: 'center',
  },
  email: {
    fontSize: 16,
    color: 'rgba(255, 255, 255, 0.7)',
    marginBottom: 12,
  },
  referenceBadge: {
    backgroundColor: 'rgba(237, 128, 233, 0.2)',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: 'rgba(237, 128, 233, 0.4)',
  },
  referenceText: {
    fontSize: 12,
    color: '#ED80E9',
    fontWeight: '600',
  },
  section: {
    marginTop: 24,
    paddingHorizontal: 20,
  },
  sectionTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: '#fff',
    marginBottom: 12,
  },
  infoCard: {
    borderRadius: 16,
    padding: 20,
    shadowColor: '#000',
    shadowOpacity: 0.3,
    shadowRadius: 8,
    shadowOffset: { width: 0, height: 4 },
    elevation: 6,
  },
  infoRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    paddingVertical: 12,
  },
  infoContent: {
    flex: 1,
    marginLeft: 16,
  },
  infoLabel: {
    fontSize: 12,
    color: 'rgba(255, 255, 255, 0.6)',
    marginBottom: 4,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  infoValue: {
    fontSize: 16,
    color: '#fff',
    fontWeight: '500',
  },
  separator: {
    height: 1,
    backgroundColor: 'rgba(255, 255, 255, 0.1)',
    marginVertical: 4,
  },
  // Drawer Styles
  drawerContainer: {
    flex: 1,
    flexDirection: 'row',
  },
  drawerOverlay: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
  },
  overlayTouchable: {
    flex: 1,
  },
  drawer: {
    width: DRAWER_WIDTH,
    height: '100%',
    backgroundColor: '#000',
    borderRightWidth: 1,
    borderRightColor: 'rgba(237, 128, 233, 0.2)',
  },
  drawerContent: {
    flex: 1,
  },
  drawerHeader: {
    paddingTop: 60,
    paddingHorizontal: 20,
    paddingBottom: 30,
    alignItems: 'center',
    borderBottomWidth: 1,
    borderBottomColor: 'rgba(255, 255, 255, 0.1)',
  },
  drawerAvatar: {
    width: 80,
    height: 80,
    borderRadius: 40,
    borderWidth: 3,
    borderColor: '#ED80E9',
    marginBottom: 16,
  },
  drawerName: {
    fontSize: 20,
    fontWeight: '700',
    color: '#fff',
    marginBottom: 4,
  },
  drawerEmail: {
    fontSize: 14,
    color: 'rgba(255, 255, 255, 0.6)',
  },
  drawerMenu: {
    paddingVertical: 20,
  },
  drawerMenuItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingVertical: 16,
    borderBottomWidth: 1,
    borderBottomColor: 'rgba(255, 255, 255, 0.05)',
  },
  drawerMenuItemLabel: {
    marginLeft: 16,
    fontSize: 16,
    color: '#fff',
    fontWeight: '500',
    flex: 1,
  },
  drawerFooter: {
    paddingHorizontal: 20,
    paddingVertical: 20,
    borderTopWidth: 1,
    borderTopColor: 'rgba(255, 255, 255, 0.1)',
  },
  drawerVersion: {
    fontSize: 12,
    color: 'rgba(255, 255, 255, 0.4)',
    textAlign: 'center',
  },
  drawerLogout: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 16,
    marginHorizontal: 20,
    marginBottom: 20,
    borderRadius: 12,
    backgroundColor: 'rgba(255, 107, 107, 0.1)',
    borderWidth: 1,
    borderColor: 'rgba(255, 107, 107, 0.3)',
  },
  drawerLogoutText: {
    marginLeft: 8,
    fontSize: 16,
    fontWeight: '600',
    color: '#FF6B6B',
  },
});
