import React, { useState, useEffect } from 'react';
import { View, Text, TextInput, TouchableOpacity, StyleSheet, SafeAreaView, ScrollView, Alert, Linking } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import { useRouter } from 'expo-router';
import { StatusBar } from 'expo-status-bar';
import { api } from '../../src/config/api';
import { useSocket } from '../../src/contexts/SocketContext';

interface SupportMessage {
  id: string;
  subject: string;
  message: string;
  status: string;
  adminResponse?: string;
  respondedAt?: string;
  respondedBy?: {
    id: string;
    username: string;
  };
  createdAt: string;
}

const SupportScreen = () => {
  const router = useRouter();
  const { socket, isConnected } = useSocket();
  const [message, setMessage] = useState('');
  const [subject, setSubject] = useState('');
  const [sending, setSending] = useState(false);
  const [supportMessages, setSupportMessages] = useState<SupportMessage[]>([]);
  const [loadingMessages, setLoadingMessages] = useState(false);

  const handleSubmit = async () => {
    if (!message.trim()) {
      Alert.alert('Error', 'Please enter your message');
      return;
    }

    if (!subject.trim()) {
      Alert.alert('Error', 'Please enter a subject');
      return;
    }

    setSending(true);
    
    try {
      // Try Socket.io first if connected, otherwise fall back to REST API
      if (socket && isConnected) {
        socket.emit('new_support_message', {
          subject: subject.trim(),
          message: message.trim(),
        });

        // Wait for confirmation
        const confirmation = await new Promise<SupportMessage>((resolve, reject) => {
          const timeout = setTimeout(() => {
            socket.off('support_message_sent');
            reject(new Error('Timeout waiting for confirmation'));
          }, 5000);

          socket.once('support_message_sent', (data: SupportMessage) => {
            clearTimeout(timeout);
            resolve(data);
          });

          socket.once('error', (error: { message: string }) => {
            clearTimeout(timeout);
            socket.off('support_message_sent');
            reject(new Error(error.message));
          });
        });

        Alert.alert(
          'Message Sent',
          'Your message has been sent. We will get back to you soon.',
          [
            {
              text: 'OK',
              onPress: () => {
                setMessage('');
                setSubject('');
                fetchSupportMessages(); // Refresh support messages list
              },
            },
          ]
        );
      } else {
        // Fall back to REST API
        const response = await api.post('/support', {
          subject: subject.trim(),
          message: message.trim(),
        });

        if (response.data?.success) {
          Alert.alert(
            'Message Sent',
            response.data.message || 'Thank you for contacting us. We will get back to you within 24-48 hours.',
            [
              {
                text: 'OK',
                onPress: () => {
                  setMessage('');
                  setSubject('');
                  fetchSupportMessages(); // Refresh support messages list
                },
              },
            ]
          );
        } else {
          throw new Error(response.data?.message || 'Failed to send message');
        }
      }
    } catch (error: any) {
      console.error('Send message error:', error);
      const errorMessage = error.response?.data?.message || error.message || 'Failed to send message. Please try again.';
      Alert.alert('Error', errorMessage);
    } finally {
      setSending(false);
    }
  };

  const openEmail = () => {
    Linking.openURL('mailto:support@masterfunds.com?subject=Support Request').catch((err) =>
      console.error('Failed to open email:', err)
    );
  };

  const openPhone = () => {
    Linking.openURL('tel:+1234567890').catch((err) => console.error('Failed to open phone:', err));
  };

  useEffect(() => {
    // Only fetch count, not full messages
    fetchSupportMessages();
  }, []);

  const fetchSupportMessages = async () => {
    try {
      const res = await api.get('/support');
      if (res.data?.success) {
        setSupportMessages(res.data.messages || res.data.data || []);
      }
    } catch (error) {
      console.error('Error fetching support messages:', error);
      setSupportMessages([]);
    } finally {
      setLoadingMessages(false);
    }
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'resolved':
        return '#10B981';
      case 'in_progress':
        return '#3B82F6';
      case 'closed':
        return '#6B7280';
      default:
        return '#F59E0B';
    }
  };

  const faqItems = [
    {
      question: 'How do I apply for a loan?',
      answer: 'Navigate to the Loan Request screen, fill in the required information (amount, tenor), and submit your application.',
    },
    {
      question: 'How long does loan approval take?',
      answer: 'Loan applications are typically reviewed within 1-3 business days. You will receive a notification once a decision is made.',
    },
    {
      question: 'How do I make a payment?',
      answer: 'Go to the Attach Receipt screen, select your loan, choose the repayment period, upload a receipt, and submit. Payments require admin approval.',
    },
    {
      question: 'What happens if I miss a payment?',
      answer: 'Late payments may incur penalties based on your loan agreement. Contact support if you need assistance with payment arrangements.',
    },
    {
      question: 'How can I check my loan status?',
      answer: 'You can view your loan status in the Loan Status screen or Loan History screen. You will also receive notifications for status updates.',
    },
  ];

  return (
    <SafeAreaView style={styles.safe}>
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
        <Text style={styles.headerTitle}>Help & Support</Text>
        <View style={{ width: 40 }} />
      </LinearGradient>

      <ScrollView style={styles.scrollView} contentContainerStyle={styles.scrollContent}>
        {/* View Support Messages Link */}
        {supportMessages.length > 0 && (
          <View style={styles.section}>
            <TouchableOpacity
              style={styles.viewMessagesCard}
              onPress={() => router.push('/(tabs)/support_messages')}
            >
              <LinearGradient
                colors={['#ED80E920', '#7B2CBF20']}
                style={styles.viewMessagesGradient}
              >
                <View style={styles.viewMessagesContent}>
                  <View style={styles.viewMessagesLeft}>
                    <Ionicons name="chatbubbles" size={24} color="#ED80E9" />
                    <View>
                      <Text style={styles.viewMessagesTitle}>My Support Messages</Text>
                      <Text style={styles.viewMessagesSubtitle}>
                        {supportMessages.length} conversation{supportMessages.length !== 1 ? 's' : ''} • {supportMessages.filter(m => m.status === 'pending' || m.status === 'in_progress').length} active
                      </Text>
                    </View>
                  </View>
                  <Ionicons name="chevron-forward" size={24} color="#ED80E9" />
                </View>
              </LinearGradient>
            </TouchableOpacity>
          </View>
        )}

        {/* Contact Information */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Contact Information</Text>
          <View style={styles.contactCard}>
            <TouchableOpacity style={styles.contactRow} onPress={openEmail}>
              <LinearGradient
                colors={['#ED80E920', '#7B2CBF20']}
                style={styles.contactIconContainer}
              >
                <Ionicons name="mail-outline" size={24} color="#ED80E9" />
              </LinearGradient>
              <View style={styles.contactInfo}>
                <Text style={styles.contactLabel}>Email</Text>
                <Text style={styles.contactValue}>support@masterfunds.com</Text>
              </View>
              <Ionicons name="chevron-forward" size={20} color="#6B7280" />
            </TouchableOpacity>

            <View style={styles.separator} />

            <TouchableOpacity style={styles.contactRow} onPress={openPhone}>
              <LinearGradient
                colors={['#ED80E920', '#7B2CBF20']}
                style={styles.contactIconContainer}
              >
                <Ionicons name="call-outline" size={24} color="#ED80E9" />
              </LinearGradient>
              <View style={styles.contactInfo}>
                <Text style={styles.contactLabel}>Phone</Text>
                <Text style={styles.contactValue}>+1 (234) 567-8900</Text>
              </View>
              <Ionicons name="chevron-forward" size={20} color="#6B7280" />
            </TouchableOpacity>

            <View style={styles.separator} />

            <View style={styles.contactRow}>
              <LinearGradient
                colors={['#ED80E920', '#7B2CBF20']}
                style={styles.contactIconContainer}
              >
                <Ionicons name="time-outline" size={24} color="#ED80E9" />
              </LinearGradient>
              <View style={styles.contactInfo}>
                <Text style={styles.contactLabel}>Business Hours</Text>
                <Text style={styles.contactValue}>Mon-Fri: 9:00 AM - 6:00 PM</Text>
                <Text style={styles.contactValue}>Sat: 9:00 AM - 1:00 PM</Text>
              </View>
            </View>
          </View>
        </View>

        {/* FAQ Section */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Frequently Asked Questions</Text>
          {faqItems.map((item, index) => (
            <View key={index} style={styles.faqCard}>
              <View style={styles.faqQuestion}>
                <Ionicons name="help-circle-outline" size={20} color="#ED80E9" />
                <Text style={styles.faqQuestionText}>{item.question}</Text>
              </View>
              <Text style={styles.faqAnswer}>{item.answer}</Text>
            </View>
          ))}
        </View>

        {/* Contact Form */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Send us a Message</Text>
          <View style={styles.formCard}>
            <Text style={styles.label}>Subject</Text>
            <TextInput
              style={styles.input}
              placeholder="Enter subject..."
              placeholderTextColor="#9ca3af"
              value={subject}
              onChangeText={setSubject}
            />

            <Text style={styles.label}>Your Message</Text>
            <TextInput
              style={[styles.input, styles.textArea]}
              multiline
              numberOfLines={6}
              placeholder="Type your message here..."
              placeholderTextColor="#9ca3af"
              value={message}
              onChangeText={setMessage}
              textAlignVertical="top"
            />

            <TouchableOpacity
              onPress={handleSubmit}
              activeOpacity={0.85}
              disabled={sending}
              style={sending && styles.buttonDisabled}
            >
              <LinearGradient
                colors={['#03042c', '#302b63', '#24243e']}
                start={{ x: 0, y: 0 }}
                end={{ x: 1, y: 0 }}
                style={styles.button}
              >
                <Ionicons name="send" size={20} color="#fff" style={{ marginRight: 6 }} />
                <Text style={styles.buttonText}>{sending ? 'Sending...' : 'Send Message'}</Text>
              </LinearGradient>
            </TouchableOpacity>
          </View>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  safe: {
    flex: 1,
    backgroundColor: 'black',
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
    fontSize: 20,
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
  section: {
    marginBottom: 24,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: '#fff',
    marginBottom: 12,
  },
  contactCard: {
    backgroundColor: '#1C2233',
    borderRadius: 12,
    padding: 16,
    borderWidth: 1,
    borderColor: '#374151',
  },
  contactRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 12,
  },
  contactIconContainer: {
    width: 48,
    height: 48,
    borderRadius: 24,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  contactInfo: {
    flex: 1,
  },
  contactLabel: {
    fontSize: 12,
    color: '#9CA3AF',
    marginBottom: 4,
  },
  contactValue: {
    fontSize: 16,
    color: '#D1D5DB',
    fontWeight: '500',
  },
  separator: {
    height: 1,
    backgroundColor: '#374151',
    marginVertical: 8,
  },
  faqCard: {
    backgroundColor: '#1C2233',
    borderRadius: 12,
    padding: 16,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: '#374151',
  },
  faqQuestion: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginBottom: 8,
  },
  faqQuestionText: {
    fontSize: 16,
    fontWeight: '600',
    color: '#ED80E9',
    flex: 1,
  },
  faqAnswer: {
    fontSize: 14,
    color: '#D1D5DB',
    lineHeight: 20,
    marginLeft: 28,
  },
  formCard: {
    backgroundColor: '#1C2233',
    borderRadius: 12,
    padding: 16,
    borderWidth: 1,
    borderColor: '#374151',
  },
  label: {
    fontSize: 14,
    color: '#D1D5DB',
    fontWeight: '600',
    marginBottom: 8,
    marginTop: 12,
  },
  input: {
    width: '100%',
    borderWidth: 1,
    borderColor: '#374151',
    borderRadius: 12,
    padding: 16,
    fontSize: 16,
    backgroundColor: '#0F172A',
    color: '#fff',
    marginBottom: 16,
  },
  textArea: {
    minHeight: 120,
    textAlignVertical: 'top',
  },
  button: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 14,
    paddingHorizontal: 24,
    borderRadius: 12,
    marginTop: 8,
  },
  buttonDisabled: {
    opacity: 0.6,
  },
  buttonText: {
    color: '#fff',
    fontWeight: '600',
    fontSize: 16,
  },
  viewMessagesCard: {
    borderRadius: 12,
    overflow: 'hidden',
    borderWidth: 1,
    borderColor: '#ED80E9',
  },
  viewMessagesGradient: {
    padding: 16,
  },
  viewMessagesContent: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  viewMessagesLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    flex: 1,
  },
  viewMessagesTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: '#fff',
    marginBottom: 4,
  },
  viewMessagesSubtitle: {
    fontSize: 12,
    color: '#9CA3AF',
  },
});

export default SupportScreen;
