import React, { useState, useEffect, useRef } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  SafeAreaView,
  TouchableOpacity,
  ActivityIndicator,
  RefreshControl,
} from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
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

const SupportMessagesScreen = () => {
  const router = useRouter();
  const { socket, isConnected } = useSocket();
  const [supportMessages, setSupportMessages] = useState<SupportMessage[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const scrollViewRef = useRef<ScrollView>(null);

  useEffect(() => {
    fetchSupportMessages();
  }, []);

  // Set up Socket.io listeners for real-time updates
  useEffect(() => {
    if (!socket) return;

    // Listen for new messages
    socket.on('support_message_updated', (data: SupportMessage) => {
      setSupportMessages((prev) => {
        const existing = prev.find((msg) => msg.id === data.id);
        if (existing) {
          return prev.map((msg) => (msg.id === data.id ? { ...msg, ...data } : msg));
        }
        return [data, ...prev];
      });
    });

    // Listen for admin responses
    socket.on('support_message_response', (data: { id: string; adminResponse?: string; status: string; respondedBy?: { id: string; username: string }; respondedAt?: string }) => {
      setSupportMessages((prev) =>
        prev.map((msg) =>
          msg.id === data.id
            ? {
                ...msg,
                adminResponse: data.adminResponse,
                status: data.status,
                respondedBy: data.respondedBy,
                respondedAt: data.respondedAt,
              }
            : msg
        )
      );
    });

    // Listen for confirmation when message is sent
    socket.on('support_message_sent', (data: SupportMessage) => {
      setSupportMessages((prev) => {
        const existing = prev.find((msg) => msg.id === data.id);
        if (!existing) {
          return [data, ...prev];
        }
        return prev;
      });
    });

    return () => {
      socket.off('support_message_updated');
      socket.off('support_message_response');
      socket.off('support_message_sent');
    };
  }, [socket]);

  const fetchSupportMessages = async () => {
    try {
      const res = await api.get('/support');
      if (res.data?.success) {
        setSupportMessages(res.data.messages || res.data.data || []);
      }
    } catch (error) {
      console.error('Error fetching support messages:', error);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const onRefresh = () => {
    setRefreshing(true);
    fetchSupportMessages();
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

  if (loading) {
    return (
      <SafeAreaView style={styles.container}>
        <StatusBar style="light" />
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color="#ED80E9" />
          <Text style={styles.loadingText}>Loading messages...</Text>
        </View>
      </SafeAreaView>
    );
  }

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
        <View style={styles.headerTitleContainer}>
          <Text style={styles.headerTitle}>Support Messages</Text>
          {isConnected && (
            <View style={styles.connectionIndicator}>
              <View style={[styles.connectionDot, { backgroundColor: '#10B981' }]} />
              <Text style={styles.connectionText}>Live</Text>
            </View>
          )}
        </View>
        <View style={{ width: 40 }} />
      </LinearGradient>

      {/* Messages List */}
      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.scrollContent}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor="#ED80E9" />}
      >
        {supportMessages.length > 0 ? (
          supportMessages.map((msg) => (
            <View key={msg.id} style={styles.conversationContainer}>
              {/* Conversation Header */}
              <View style={styles.conversationHeader}>
                <View style={styles.conversationHeaderLeft}>
                  <Ionicons name="chatbubbles" size={20} color="#ED80E9" />
                  <Text style={styles.conversationSubject}>{msg.subject}</Text>
                </View>
                <View style={[styles.statusBadge, { backgroundColor: getStatusColor(msg.status) + '20' }]}>
                  <Text style={[styles.statusText, { color: getStatusColor(msg.status) }]}>
                    {msg.status.charAt(0).toUpperCase() + msg.status.slice(1).replace('_', ' ')}
                  </Text>
                </View>
              </View>

              {/* Chat Messages */}
              <View style={styles.chatMessages}>
                {/* User Message (Right Side) */}
                <View style={styles.messageBubbleContainer}>
                  <View style={styles.userMessageBubble}>
                    <Text style={styles.userMessageText}>{msg.message}</Text>
                    <Text style={styles.messageTime}>{formatDate(msg.createdAt)}</Text>
                  </View>
                  <View style={styles.messageAvatar}>
                    <Ionicons name="person" size={16} color="#ED80E9" />
                  </View>
                </View>

                {/* Admin Response (Left Side) */}
                {msg.adminResponse ? (
                  <View style={styles.messageBubbleContainer}>
                    <View style={styles.adminMessageAvatar}>
                      <Ionicons name="shield-checkmark" size={16} color="#10B981" />
                    </View>
                    <View style={styles.adminMessageBubble}>
                      <View style={styles.adminMessageHeader}>
                        <Text style={styles.adminName}>
                          {msg.respondedBy?.username || 'Admin'}
                        </Text>
                        <Ionicons name="checkmark-circle" size={14} color="#10B981" />
                      </View>
                      <Text style={styles.adminMessageText}>{msg.adminResponse}</Text>
                      <Text style={styles.messageTime}>
                        {formatDate(msg.respondedAt || msg.createdAt)}
                      </Text>
                    </View>
                  </View>
                ) : (
                  <View style={styles.pendingResponseContainer}>
                    <Ionicons name="time-outline" size={16} color="#9CA3AF" />
                    <Text style={styles.pendingResponseText}>Waiting for admin response...</Text>
                  </View>
                )}
              </View>
            </View>
          ))
        ) : (
          <View style={styles.emptyContainer}>
            <Ionicons name="chatbubbles-outline" size={64} color="#6B7280" />
            <Text style={styles.emptyText}>No support messages yet</Text>
            <Text style={styles.emptySubtext}>
              Send a message from the Help & Support page to start a conversation
            </Text>
            <TouchableOpacity
              style={styles.newMessageButton}
              onPress={() => router.push('/(tabs)/contact')}
            >
              <Ionicons name="add-circle-outline" size={20} color="#ED80E9" />
              <Text style={styles.newMessageButtonText}>Send New Message</Text>
            </TouchableOpacity>
          </View>
        )}
      </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#000000',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingText: {
    color: '#fff',
    marginTop: 16,
    fontSize: 16,
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
  headerTitleContainer: {
    flex: 1,
    alignItems: 'center',
    gap: 4,
  },
  headerTitle: {
    fontSize: 24,
    fontWeight: '700',
    color: '#fff',
  },
  connectionIndicator: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    marginTop: 2,
  },
  connectionDot: {
    width: 6,
    height: 6,
    borderRadius: 3,
  },
  connectionText: {
    fontSize: 10,
    color: '#10B981',
    fontWeight: '600',
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    padding: 20,
    paddingBottom: 40,
  },
  conversationContainer: {
    backgroundColor: '#1C2233',
    borderRadius: 16,
    padding: 16,
    marginBottom: 16,
    borderWidth: 1,
    borderColor: '#374151',
  },
  conversationHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 16,
    paddingBottom: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#374151',
  },
  conversationHeaderLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    flex: 1,
  },
  conversationSubject: {
    fontSize: 16,
    fontWeight: '700',
    color: '#fff',
    flex: 1,
  },
  statusBadge: {
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 12,
  },
  statusText: {
    fontSize: 11,
    fontWeight: '600',
  },
  chatMessages: {
    gap: 12,
  },
  messageBubbleContainer: {
    flexDirection: 'row',
    alignItems: 'flex-end',
    gap: 8,
  },
  userMessageBubble: {
    backgroundColor: '#ED80E9',
    borderRadius: 18,
    borderTopRightRadius: 4,
    paddingHorizontal: 16,
    paddingVertical: 12,
    maxWidth: '75%',
    marginLeft: 'auto',
  },
  userMessageText: {
    fontSize: 15,
    color: '#fff',
    lineHeight: 20,
    marginBottom: 4,
  },
  adminMessageBubble: {
    backgroundColor: '#1F2937',
    borderRadius: 18,
    borderTopLeftRadius: 4,
    paddingHorizontal: 16,
    paddingVertical: 12,
    maxWidth: '75%',
    borderWidth: 1,
    borderColor: '#374151',
  },
  adminMessageHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    marginBottom: 6,
  },
  adminName: {
    fontSize: 12,
    fontWeight: '600',
    color: '#10B981',
  },
  adminMessageText: {
    fontSize: 15,
    color: '#D1D5DB',
    lineHeight: 20,
    marginBottom: 4,
  },
  messageTime: {
    fontSize: 11,
    color: 'rgba(255, 255, 255, 0.7)',
    marginTop: 4,
  },
  messageAvatar: {
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: '#ED80E920',
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 2,
    borderColor: '#ED80E9',
  },
  adminMessageAvatar: {
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: '#10B98120',
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 2,
    borderColor: '#10B981',
  },
  pendingResponseContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    padding: 12,
    backgroundColor: '#1F2937',
    borderRadius: 12,
    borderWidth: 1,
    borderColor: '#374151',
    borderStyle: 'dashed',
    marginLeft: 40,
  },
  pendingResponseText: {
    fontSize: 13,
    color: '#9CA3AF',
    fontStyle: 'italic',
  },
  emptyContainer: {
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 60,
  },
  emptyText: {
    fontSize: 18,
    fontWeight: '600',
    color: '#fff',
    marginTop: 16,
  },
  emptySubtext: {
    fontSize: 14,
    color: '#9CA3AF',
    marginTop: 8,
    textAlign: 'center',
    paddingHorizontal: 40,
  },
  newMessageButton: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginTop: 24,
    paddingHorizontal: 20,
    paddingVertical: 12,
    backgroundColor: '#ED80E920',
    borderRadius: 12,
    borderWidth: 1,
    borderColor: '#ED80E9',
  },
  newMessageButtonText: {
    color: '#ED80E9',
    fontSize: 16,
    fontWeight: '600',
  },
});

export default SupportMessagesScreen;

