import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  SafeAreaView,
  TouchableOpacity,
  Linking,
} from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
import { useRouter } from 'expo-router';
import { StatusBar } from 'expo-status-bar';

const AboutScreen = () => {
  const router = useRouter();

  const openLink = (url: string) => {
    Linking.openURL(url).catch((err) => console.error('Failed to open URL:', err));
  };

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar style="light" />
      
      {/* Header */}
      <LinearGradient
        colors={['#03042c', '#302b63', '#24243e']}
        start={{ x: 0, y: 0 }}
        end={{ x: 1, y: 1 }}
        style={styles.header}
      >
        <TouchableOpacity onPress={() => router.back()} style={styles.backButton}>
          <Ionicons name="arrow-back" size={24} color="#fff" />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>About</Text>
        <View style={{ width: 40 }} />
      </LinearGradient>

      <ScrollView style={styles.scrollView} contentContainerStyle={styles.scrollContent}>
        {/* App Logo/Icon Section */}
        <View style={styles.logoSection}>
          <LinearGradient
            colors={['#ED80E9', '#7B2CBF', '#2E026D']}
            start={{ x: 0, y: 0 }}
            end={{ x: 1, y: 1 }}
            style={styles.logoContainer}
          >
            <Text style={styles.logoText}>MF</Text>
          </LinearGradient>
          <Text style={styles.appName}>MasterFunds</Text>
          <Text style={styles.appTagline}>Your Trusted Financial Partner</Text>
        </View>

        {/* App Version */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>App Information</Text>
          <View style={styles.infoCard}>
            <View style={styles.infoRow}>
              <Text style={styles.infoLabel}>Version</Text>
              <Text style={styles.infoValue}>1.0.0</Text>
            </View>
            <View style={styles.separator} />
            <View style={styles.infoRow}>
              <Text style={styles.infoLabel}>Platform</Text>
              <Text style={styles.infoValue}>React Native / Expo</Text>
            </View>
            <View style={styles.separator} />
            <View style={styles.infoRow}>
              <Text style={styles.infoLabel}>Build Date</Text>
              <Text style={styles.infoValue}>2024</Text>
            </View>
          </View>
        </View>

        {/* Description */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>About MasterFunds</Text>
          <View style={styles.descriptionCard}>
            <Text style={styles.descriptionText}>
              MasterFunds is a comprehensive loan management application designed to help you manage
              your loans efficiently. With MasterFunds, you can:
            </Text>
            <View style={styles.featureList}>
              <View style={styles.featureItem}>
                <Ionicons name="checkmark-circle" size={20} color="#10B981" />
                <Text style={styles.featureText}>Apply for loans easily</Text>
              </View>
              <View style={styles.featureItem}>
                <Ionicons name="checkmark-circle" size={20} color="#10B981" />
                <Text style={styles.featureText}>Track your loan status in real-time</Text>
              </View>
              <View style={styles.featureItem}>
                <Ionicons name="checkmark-circle" size={20} color="#10B981" />
                <Text style={styles.featureText}>Submit payments with receipt upload</Text>
              </View>
              <View style={styles.featureItem}>
                <Ionicons name="checkmark-circle" size={20} color="#10B981" />
                <Text style={styles.featureText}>View detailed payment history</Text>
              </View>
              <View style={styles.featureItem}>
                <Ionicons name="checkmark-circle" size={20} color="#10B981" />
                <Text style={styles.featureText}>Monitor your credit score</Text>
              </View>
              <View style={styles.featureItem}>
                <Ionicons name="checkmark-circle" size={20} color="#10B981" />
                <Text style={styles.featureText}>Receive important notifications</Text>
              </View>
            </View>
          </View>
        </View>

        {/* Company Information */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Company</Text>
          <View style={styles.infoCard}>
            <View style={styles.infoRow}>
              <Ionicons name="business-outline" size={20} color="#ED80E9" />
              <View style={styles.infoContent}>
                <Text style={styles.infoLabel}>MasterFunds Inc.</Text>
                <Text style={styles.infoSubtext}>Financial Services Provider</Text>
              </View>
            </View>
          </View>
        </View>

        {/* Contact Information */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Contact Us</Text>
          <View style={styles.infoCard}>
            <TouchableOpacity
              style={styles.contactRow}
              onPress={() => openLink('mailto:support@masterfunds.com')}
            >
              <Ionicons name="mail-outline" size={20} color="#ED80E9" />
              <Text style={styles.contactText}>support@masterfunds.com</Text>
              <Ionicons name="chevron-forward" size={20} color="#6B7280" style={{ marginLeft: 'auto' }} />
            </TouchableOpacity>
            <View style={styles.separator} />
            <TouchableOpacity
              style={styles.contactRow}
              onPress={() => openLink('tel:+1234567890')}
            >
              <Ionicons name="call-outline" size={20} color="#ED80E9" />
              <Text style={styles.contactText}>+1 (234) 567-8900</Text>
              <Ionicons name="chevron-forward" size={20} color="#6B7280" style={{ marginLeft: 'auto' }} />
            </TouchableOpacity>
            <View style={styles.separator} />
            <TouchableOpacity
              style={styles.contactRow}
              onPress={() => router.push('/(tabs)/contact')}
            >
              <Ionicons name="help-circle-outline" size={20} color="#ED80E9" />
              <Text style={styles.contactText}>Help & Support</Text>
              <Ionicons name="chevron-forward" size={20} color="#6B7280" style={{ marginLeft: 'auto' }} />
            </TouchableOpacity>
          </View>
        </View>

        {/* Legal Links */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Legal</Text>
          <View style={styles.infoCard}>
            <TouchableOpacity
              style={styles.legalRow}
              onPress={() => router.push('/(tabs)/legal')}
            >
              <Ionicons name="document-text-outline" size={20} color="#ED80E9" />
              <Text style={styles.legalText}>Terms & Conditions</Text>
              <Ionicons name="chevron-forward" size={20} color="#6B7280" style={{ marginLeft: 'auto' }} />
            </TouchableOpacity>
            <View style={styles.separator} />
            <TouchableOpacity
              style={styles.legalRow}
              onPress={() => router.push('/(tabs)/legal')}
            >
              <Ionicons name="shield-checkmark-outline" size={20} color="#ED80E9" />
              <Text style={styles.legalText}>Privacy Policy</Text>
              <Ionicons name="chevron-forward" size={20} color="#6B7280" style={{ marginLeft: 'auto' }} />
            </TouchableOpacity>
          </View>
        </View>

        {/* Developer Credits */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Credits</Text>
          <View style={styles.creditsCard}>
            <Text style={styles.creditsText}>
              Developed with ❤️ for better financial management
            </Text>
            <Text style={styles.creditsSubtext}>
              © 2024 MasterFunds. All rights reserved.
            </Text>
          </View>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#000000',
  },
  header: {
    paddingTop: 50,
    paddingBottom: 20,
    paddingHorizontal: 20,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  backButton: {
    padding: 8,
  },
  headerTitle: {
    fontSize: 24,
    fontWeight: '700',
    color: '#fff',
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    padding: 20,
    paddingBottom: 40,
  },
  logoSection: {
    alignItems: 'center',
    marginBottom: 32,
    marginTop: 20,
  },
  logoContainer: {
    width: 100,
    height: 100,
    borderRadius: 50,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 16,
  },
  logoText: {
    fontSize: 36,
    fontWeight: '700',
    color: '#fff',
  },
  appName: {
    fontSize: 28,
    fontWeight: '700',
    color: '#fff',
    marginBottom: 8,
  },
  appTagline: {
    fontSize: 16,
    color: '#9CA3AF',
  },
  section: {
    marginBottom: 24,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: '#fff',
    marginBottom: 12,
  },
  infoCard: {
    backgroundColor: '#1C2233',
    borderRadius: 12,
    padding: 16,
    borderWidth: 1,
    borderColor: '#374151',
  },
  infoRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  infoContent: {
    flex: 1,
  },
  infoLabel: {
    fontSize: 16,
    fontWeight: '600',
    color: '#fff',
  },
  infoSubtext: {
    fontSize: 14,
    color: '#9CA3AF',
    marginTop: 4,
  },
  infoValue: {
    fontSize: 16,
    color: '#D1D5DB',
    marginLeft: 'auto',
  },
  separator: {
    height: 1,
    backgroundColor: '#374151',
    marginVertical: 12,
  },
  descriptionCard: {
    backgroundColor: '#1C2233',
    borderRadius: 12,
    padding: 16,
    borderWidth: 1,
    borderColor: '#374151',
  },
  descriptionText: {
    fontSize: 16,
    color: '#D1D5DB',
    lineHeight: 24,
    marginBottom: 16,
  },
  featureList: {
    gap: 12,
  },
  featureItem: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  featureText: {
    fontSize: 15,
    color: '#D1D5DB',
  },
  contactRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    paddingVertical: 4,
  },
  contactText: {
    fontSize: 16,
    color: '#D1D5DB',
    flex: 1,
  },
  legalRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    paddingVertical: 4,
  },
  legalText: {
    fontSize: 16,
    color: '#D1D5DB',
    flex: 1,
  },
  creditsCard: {
    backgroundColor: '#1C2233',
    borderRadius: 12,
    padding: 20,
    borderWidth: 1,
    borderColor: '#374151',
    alignItems: 'center',
  },
  creditsText: {
    fontSize: 16,
    color: '#D1D5DB',
    textAlign: 'center',
    marginBottom: 8,
  },
  creditsSubtext: {
    fontSize: 14,
    color: '#9CA3AF',
    textAlign: 'center',
  },
});

export default AboutScreen;

