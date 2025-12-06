import React from 'react';
import { View, Text, StyleSheet, ScrollView, SafeAreaView } from 'react-native';
import { StatusBar } from 'expo-status-bar';
import { LinearGradient } from 'expo-linear-gradient';

const AgreementsPage = () => {
  return (
    <SafeAreaView style={styles.container}>
      <StatusBar style="light" />

      {/* Gradient Header */}
      <LinearGradient
       colors={["#03042c", "#302b63", "#24243e"]}
        start={{ x: 0, y: 0 }}
        end={{ x: 1, y: 0 }}
        style={styles.headerContainer}
      >
        <Text style={styles.header}>Terms & Conditions / Privacy Policy</Text>
      </LinearGradient>

      <ScrollView contentContainerStyle={styles.scrollContainer}>
        <Text style={styles.text}>
          Welcome to Master Fund. Please read these Terms & Conditions and Privacy Policy carefully before using our services.{"\n\n"}
          1. **Use of Service:** Users must follow all applicable laws and regulations. {"\n\n"}
          2. **Privacy:** We respect your privacy and handle your data securely. {"\n\n"}
          3. **Liability:** Master Fund is not responsible for any financial loss incurred due to improper usage. {"\n\n"}
          4. **Termination:** We may suspend or terminate accounts that violate these terms. {"\n\n"}
          5. **Amendments:** Terms may be updated periodically. Users are advised to review regularly.{"\n\n"}
          {/* Add full text here */}
        </Text>
      </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: { 
    flex: 1, 
    backgroundColor: 'black', 
    padding: 20,
  },
  headerContainer: {
    borderRadius: 16,
    paddingVertical: 14,
    paddingHorizontal: 10,
    marginTop: 80,
    marginBottom: 20,
  },
  header: { 
    fontSize: 22, 
    fontWeight: '700', 
    color: 'white',
    textAlign: 'center',
  },
  scrollContainer: { 
    paddingHorizontal: 10, 
    alignItems: 'center',
  },
  text: { 
    fontSize: 14, 
    color: '#ffffff', 
    lineHeight: 22, 
    textAlign: 'center',
  },
});

export default AgreementsPage;
