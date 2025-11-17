import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity, SafeAreaView, Alert } from 'react-native';
import { StatusBar } from 'expo-status-bar';
import { Ionicons } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';

const LegalInfoPage = () => {
  const handleView = (type) => {
    Alert.alert(type, `Opening ${type}...`);
    // Replace Alert with navigation to the respective page
  };

  const handleDownload = (file) => {
    Alert.alert('Download', `Downloading ${file}...`);
    // Replace Alert with actual download logic
  };

  const GradientButton = ({ text, onPress, iconName }) => (
    <TouchableOpacity onPress={onPress} activeOpacity={0.85}>
      <LinearGradient
           colors={["#03042c", "#302b63", "#24243e"]}
        start={{ x: 0, y: 0 }}
        end={{ x: 1, y: 0 }}
        style={styles.item}
      >
        <Text style={styles.itemText}>{text}</Text>
        <Ionicons name={iconName} size={24} color="#fff" />
      </LinearGradient>
    </TouchableOpacity>
  );

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar style="light" />
      <Text style={styles.header}>Legal / Info</Text>

      <GradientButton 
        text="Terms & Conditions" 
        onPress={() => handleView('Terms & Conditions')} 
        iconName="chevron-forward" 
      />

      <GradientButton 
        text="Privacy Policy" 
        onPress={() => handleView('Privacy Policy')} 
        iconName="chevron-forward" 
      />

      <GradientButton 
        text="Agreement / Consent" 
        onPress={() => handleDownload('Agreement / Consent')} 
        iconName="download" 
      />
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: 'black',
    padding: 20,
  },
  header: {
    fontSize: 24,
    fontWeight: '700',
    color: '#10B981',
    marginBottom: 24,
    textAlign: 'center',
  },
  item: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 18,
    borderRadius: 16,
    marginBottom: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.06,
    shadowRadius: 6,
    elevation: 3,
  },
  itemText: {
    color: '#ffffff',
    fontSize: 16,
    fontWeight: '600',
  },
});

export default LegalInfoPage;
