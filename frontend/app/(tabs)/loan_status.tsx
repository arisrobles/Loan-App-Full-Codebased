import React from 'react';
import { View, Text, StyleSheet, SafeAreaView, ScrollView } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';

const statuses = [
  { label: 'Pending', icon: 'time-outline' },
  { label: 'Approved', icon: 'checkmark-circle-outline' },
  { label: 'Rejected', icon: 'close-circle-outline' },
  { label: 'Released', icon: 'wallet-outline' },
];

const LoanStatusScreen = () => {
  return (
    <SafeAreaView style={styles.container}>
      <Text style={styles.title}>Loan Status Tracking</Text>

      <ScrollView contentContainerStyle={styles.scrollView}>
        {statuses.map((status, index) => (
          <LinearGradient
            key={index}
            colors={["#03042c", "#302b63", "#24243e"]}
            start={{ x: 0, y: 0 }}
            end={{ x: 1, y: 1 }}
            style={styles.statusCard}
          >
            <Ionicons name={status.icon} size={32} color="#ffffff" style={styles.icon} />
            <View>
              <Text style={styles.statusText}>{status.label}</Text>
              <Text style={styles.subText}>Notification will be sent upon update</Text>
            </View>
          </LinearGradient>
        ))}
      </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    padding: 10,
    backgroundColor: 'black',
  },
  title: {
    fontSize: 24,
    fontWeight: '700',
    textAlign: 'center',
    marginTop: 60,
    marginBottom: 20,
    color: '#ffffff',
  },
  scrollView: {
    paddingBottom: 20,
  },
  statusCard: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 16,
    borderRadius: 12,
    marginBottom: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.3,
    shadowRadius: 6,
    elevation: 3,
  },
  icon: {
    marginRight: 16,
  },
  statusText: {
    fontSize: 18,
    fontWeight: '600',
    color: '#ffffff',
  },
  subText: {
    fontSize: 14,
    color: '#d1d5db',
  },
});

export default LoanStatusScreen;
