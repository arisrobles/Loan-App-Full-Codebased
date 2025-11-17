import React, { useState } from 'react';
import { View, Text, TextInput, TouchableOpacity, StyleSheet, SafeAreaView, ScrollView } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';

const SupportScreen = () => {
  const [message, setMessage] = useState('');

  const handleSubmit = () => {
    console.log('Message sent:', message);
    setMessage('');
  };

  return (
    <SafeAreaView style={styles.safe}>
      <ScrollView contentContainerStyle={styles.container}>
        <Ionicons name="chatbubbles-outline" size={48} color="#302b63" />
        <Text style={styles.title}>Contact Support</Text>
        <Text style={styles.sub}>Let us know how we can help you.</Text>

        <Text style={styles.label}>Your Message</Text>
        <TextInput
          style={styles.input}
          multiline
          numberOfLines={6}
          placeholder="Type your message here..."
          placeholderTextColor="#9ca3af"
          value={message}
          onChangeText={setMessage}
        />

        <TouchableOpacity onPress={handleSubmit} activeOpacity={0.85}>
          <LinearGradient
       colors={["#03042c", "#302b63", "#24243e"]}
            start={{ x: 0, y: 0 }}
            end={{ x: 1, y: 0 }}
            style={styles.button}
          >
            <Ionicons name="send" size={20} color="#fff" style={{ marginRight: 6 }} />
            <Text style={styles.buttonText}>Send Message</Text>
          </LinearGradient>
        </TouchableOpacity>
      </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  safe: {
    flex: 1,
    backgroundColor: 'black',
  },
  container: {
    padding: 24,
    justifyContent: 'center',
    flex: 1,
    alignItems: 'center',
  },
  title: {
    fontSize: 24,
    fontWeight: '700',
    color: 'white',
    marginTop: 12,
  },
  sub: {
    fontSize: 14,
    color: '#6b7280',
    marginBottom: 24,
  },
  label: {
    alignSelf: 'flex-start',
    color: '#374151',
    fontWeight: '600',
    marginBottom: 8,
  },
  input: {
    width: '100%',
    borderWidth: 1,
    borderColor: '#d1d5db',
    borderRadius: 12,
    padding: 16,
    fontSize: 16,
    backgroundColor: '#f9fafb',
    color: '#111827',
    marginBottom: 24,
    textAlignVertical: 'top',
  },
  button: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 14,
    paddingHorizontal: 24,
    borderRadius: 12,
  },
  buttonText: {
    color: '#fff',
    fontWeight: '600',
    fontSize: 16,
  },
});

export default SupportScreen;
