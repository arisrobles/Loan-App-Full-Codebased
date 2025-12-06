import React, { useEffect, useState, useCallback } from 'react';
import { View, Text, StyleSheet, ScrollView, SafeAreaView, TouchableOpacity, ActivityIndicator, RefreshControl } from 'react-native';
import { StatusBar } from 'expo-status-bar';
import { Ionicons } from '@expo/vector-icons';
import { useRouter } from 'expo-router';
import { api } from '../../src/config/api';

interface Notification {
  id: string;
  type: string;
  title: string;
  message: string;
  isRead: boolean;
  readAt?: string;
  createdAt: string;
  loanId?: string;
  loanReference?: string;
}

const NotificationsPage = () => {
  const router = useRouter();
  const [notifications, setNotifications] = useState<Notification[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [unreadCount, setUnreadCount] = useState(0);

  const fetchNotifications = useCallback(async () => {
    try {
      const res = await api.get('/notifications');
      if (res.data?.success) {
        setNotifications(res.data.data || []);
        setUnreadCount(res.data.unreadCount || 0);
      }
    } catch (error: any) {
      console.error('Error fetching notifications:', error);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useEffect(() => {
    fetchNotifications();
  }, [fetchNotifications]);

  const handleMarkAsRead = async (id: string) => {
    try {
      await api.patch(`/notifications/${id}/read`);
      // Update local state
      setNotifications(prev =>
        prev.map(notif =>
          notif.id === id ? { ...notif, isRead: true, readAt: new Date().toISOString() } : notif
        )
      );
      setUnreadCount(prev => Math.max(0, prev - 1));
    } catch (error) {
      console.error('Error marking notification as read:', error);
    }
  };

  const handleMarkAllAsRead = async () => {
    try {
      await api.patch('/notifications/read-all');
      // Update local state
      setNotifications(prev =>
        prev.map(notif => ({ ...notif, isRead: true, readAt: new Date().toISOString() }))
      );
      setUnreadCount(0);
    } catch (error) {
      console.error('Error marking all as read:', error);
    }
  };

  const onRefresh = () => {
    setRefreshing(true);
    fetchNotifications();
  };

  if (loading) {
    return (
      <SafeAreaView style={styles.container}>
        <StatusBar style="light" />
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color="#10B981" />
          <Text style={styles.loadingText}>Loading notifications...</Text>
        </View>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar style="light" />
      
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={() => router.back()} style={styles.backButton}>
          <Ionicons name="arrow-back" size={24} color="#fff" />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Notifications</Text>
        {unreadCount > 0 && (
          <TouchableOpacity onPress={handleMarkAllAsRead} style={styles.markAllButton}>
            <Text style={styles.markAllText}>Mark all read</Text>
          </TouchableOpacity>
        )}
      </View>

      {/* Notifications List */}
      <ScrollView
        contentContainerStyle={styles.scrollContainer}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor="#10B981" />}
      >
        {notifications.length > 0 ? (
          notifications.map((notif) => {
            // Determine navigation based on notification type and loanId
            const handleNotificationPress = () => {
              // Mark as read if unread
              if (!notif.isRead) {
                handleMarkAsRead(notif.id);
              }
              
              // Check if it's a support response notification
              if (notif.title === 'Support Response' || notif.message?.includes('support message')) {
                // Navigate to support messages screen
                router.push(`/(tabs)/support_messages`);
                return;
              }
              
              // Navigate based on notification type and loanId
              if (notif.loanId) {
                if (notif.type === 'payment_received' || notif.type === 'payment_due') {
                  // Navigate to payment history or loan details
                  router.push(`/(tabs)/payment_history`);
                } else if (notif.type === 'loan_status_change') {
                  // Navigate to loan details
                  router.push(`/(tabs)/loan_details?loanId=${notif.loanId}`);
                } else {
                  // Default: navigate to loan details
                  router.push(`/(tabs)/loan_details?loanId=${notif.loanId}`);
                }
              } else if (notif.type === 'payment_received' || notif.type === 'payment_due') {
                // Navigate to payment history if no specific loan
                router.push(`/(tabs)/payment_history`);
              }
              // If no loanId and not payment-related, just mark as read (no navigation)
            };

            return (
              <TouchableOpacity
                key={notif.id}
                style={[
                  styles.notificationItem,
                  getTypeStyle(notif.type),
                  !notif.isRead && styles.unreadNotification,
                ]}
                onPress={handleNotificationPress}
              >
              <View style={styles.notificationContent}>
                <View style={styles.notificationHeader}>
                  <Text style={styles.title}>{notif.title}</Text>
                  <View style={styles.headerRight}>
                    {!notif.isRead && <View style={styles.unreadDot} />}
                    {(notif.loanId || notif.type === 'payment_received' || notif.type === 'payment_due') && (
                      <Ionicons name="chevron-forward" size={16} color="#9CA3AF" style={{ marginLeft: 8 }} />
                    )}
                  </View>
                </View>
                <Text style={styles.message}>{notif.message}</Text>
                {notif.loanReference && (
                  <View style={styles.loanRefContainer}>
                    <Ionicons name="document-text-outline" size={14} color="#10B981" />
                    <Text style={styles.loanRef}>Loan: {notif.loanReference}</Text>
                  </View>
                )}
                <Text style={styles.date}>
                  {new Date(notif.createdAt).toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                  })}
                </Text>
              </View>
              </TouchableOpacity>
            );
          })
        ) : (
          <View style={styles.emptyContainer}>
            <Ionicons name="notifications-off-outline" size={64} color="#6B7280" />
            <Text style={styles.emptyText}>No notifications yet</Text>
          </View>
        )}
      </ScrollView>
    </SafeAreaView>
  );
};

// Helper function to set accent color based on notification type
const getTypeStyle = (type: string) => {
  switch (type) {
    case 'reminder':
      return { borderLeftColor: '#06B6D4' }; // cyan
    case 'approval':
      return { borderLeftColor: '#10B981' }; // green
    case 'info':
      return { borderLeftColor: '#3B82F6' }; // blue
    default:
      return { borderLeftColor: '#ffffff' };
  }
};

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#0B0F1A' },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingText: {
    color: '#10B981',
    marginTop: 16,
    fontSize: 16,
  },
  header: { 
    flexDirection: 'row', 
    justifyContent: 'space-between', 
    alignItems: 'center', 
    paddingVertical: 16, 
    paddingHorizontal: 20, 
    borderBottomWidth: 1, 
    borderBottomColor: '#1C2233',
  },
  backButton: {
    padding: 8,
  },
  headerTitle: {  
    fontSize: 22, 
    fontWeight: '700', 
    color: '#fff',
    flex: 1,
    marginLeft: 8,
  },
  markAllButton: {
    padding: 8,
  },
  markAllText: {
    color: '#10B981',
    fontSize: 14,
    fontWeight: '600',
  },
  scrollContainer: { padding: 20, paddingBottom: 40 },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingTop: 100,
  },
  emptyText: {
    color: '#6B7280',
    fontSize: 16,
    marginTop: 16,
  },
  notificationItem: {
    backgroundColor: '#1C2233',
    padding: 16,
    borderRadius: 16,
    marginBottom: 14,
    borderLeftWidth: 5,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.2,
    shadowRadius: 4,
    elevation: 3,
    // Make it clear it's clickable
    opacity: 1,
  },
  unreadNotification: {
    backgroundColor: '#1F2937',
    borderLeftWidth: 6,
  },
  notificationContent: {
    flex: 1,
  },
  notificationHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 8,
  },
  headerRight: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  unreadDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: '#10B981',
  },
  title: { fontSize: 16, fontWeight: '700', color: '#ffffff', flex: 1 },
  message: { fontSize: 14, color: '#D1D5DB', lineHeight: 20, marginBottom: 8 },
  loanRefContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    marginBottom: 4,
  },
  loanRef: {
    fontSize: 12,
    color: '#10B981',
    fontWeight: '600',
  },
  date: {
    fontSize: 12,
    color: '#6B7280',
    marginTop: 4,
  },
});

export default NotificationsPage;
