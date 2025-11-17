import { LinearGradient } from 'expo-linear-gradient';
import { useRouter } from 'expo-router';
import { StatusBar } from 'expo-status-bar';
import React from 'react';
import { SafeAreaView, StyleSheet, Text, TouchableOpacity, View } from 'react-native';

const LandingPage = () => {
  const router = useRouter();

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar style="light" />
      <View style={styles.overlay}>
        <Text style={styles.title}>Welcome to Master Fund</Text>
        <Text style={styles.subtitle}>
          Your trusted platform for easy, secure, and fast loans.
        </Text>

        <TouchableOpacity onPress={() => router.push('./login')} activeOpacity={0.85}>
          <LinearGradient
             colors={["#03042c", "#302b63", "#24243e"]}
            start={{ x: 0, y: 0 }}
            end={{ x: 1, y: 0 }}
            style={styles.getStartedButton}
          >
            <Text style={styles.getStartedText}>Get Started</Text>
          </LinearGradient>
        </TouchableOpacity>
      </View>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: 'black',
    justifyContent: 'center',
    alignItems: 'center',
  },
  overlay: {
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 20,
  },
  title: {
    color: 'white',
    fontSize: 36,
    fontWeight: '700',
    textAlign: 'center',
    marginBottom: 16,
  },
  subtitle: {
    color: '#ffffff',
    fontSize: 16,
    textAlign: 'center',
    marginBottom: 40,
    lineHeight: 22,
  },
  getStartedButton: {
    paddingVertical: 16,
    paddingHorizontal: 60,
    borderRadius: 16,
    justifyContent: 'center',
    alignItems: 'center',
  },
  getStartedText: {
    color: '#ffffff',
    fontSize: 16,
    fontWeight: '700',
  },
});

export default LandingPage;
