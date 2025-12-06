import React, { useEffect, useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  ActivityIndicator,
  SafeAreaView,
  TouchableOpacity,
  Modal,
  Image,
  RefreshControl,
} from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
import { useRouter } from 'expo-router';
import { StatusBar } from 'expo-status-bar';
import { api } from '../../src/config/api';

interface Payment {
  id: string;
  amount: string;
  penaltyAmount?: string;
  status: string;
  paidAt?: string;
  approvedAt?: string;
  rejectionReason?: string;
  loanId: string;
  loanReference?: string;
  receiptUrl?: string;
  receiptFileName?: string;
  repaymentId?: string;
}

const PaymentHistoryScreen = () => {
  const router = useRouter();
  const [payments, setPayments] = useState<Payment[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [selectedReceipt, setSelectedReceipt] = useState<string | null>(null);
  const [receiptModalVisible, setReceiptModalVisible] = useState(false);
  const [filter, setFilter] = useState<'all' | 'approved' | 'pending' | 'rejected'>('all');

  useEffect(() => {
    fetchPayments();
  }, []);

  const fetchPayments = async () => {
    try {
      const res = await api.get('/payments');
      if (res.data?.payments) {
        setPayments(res.data.payments);
      }
    } catch (error) {
      console.error('Error fetching payments:', error);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const onRefresh = () => {
    setRefreshing(true);
    fetchPayments();
  };

  const formatCurrency = (value: string | number) => {
    const num = typeof value === 'string' ? parseFloat(value) : value;
    return new Intl.NumberFormat('en-PH', {
      style: 'currency',
      currency: 'PHP',
      minimumFractionDigits: 2,
    }).format(num);
  };

  const formatDate = (dateString?: string) => {
    if (!dateString) return 'N/A';
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
      case 'approved':
        return '#10B981';
      case 'pending':
        return '#F59E0B';
      case 'rejected':
        return '#EF4444';
      default:
        return '#6B7280';
    }
  };

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'approved':
        return 'checkmark-circle';
      case 'pending':
        return 'time';
      case 'rejected':
        return 'close-circle';
      default:
        return 'help-circle';
    }
  };

  const getReceiptImageUrl = (fileUrl: string) => {
    const baseURL = api.defaults.baseURL?.replace('/api/v1', '') || 'http://192.168.8.107:8080';
    return `${baseURL}${fileUrl}`;
  };

  const openReceiptModal = (receiptUrl: string) => {
    setSelectedReceipt(receiptUrl);
    setReceiptModalVisible(true);
  };

  const filteredPayments = payments.filter(payment => {
    if (filter === 'all') return true;
    return payment.status === filter;
  });

  const statistics = {
    total: payments.length,
    approved: payments.filter(p => p.status === 'approved').length,
    pending: payments.filter(p => p.status === 'pending').length,
    rejected: payments.filter(p => p.status === 'rejected').length,
    totalAmount: payments
      .filter(p => p.status === 'approved')
      .reduce((sum, p) => sum + parseFloat(p.amount), 0),
    pendingAmount: payments
      .filter(p => p.status === 'pending')
      .reduce((sum, p) => sum + parseFloat(p.amount), 0),
  };

  if (loading) {
    return (
      <SafeAreaView style={styles.container}>
        <StatusBar style="light" />
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color="#ED80E9" />
          <Text style={styles.loadingText}>Loading payment history...</Text>
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
        <Text style={styles.headerTitle}>Payment History</Text>
        <View style={{ width: 40 }} />
      </LinearGradient>

      {/* Statistics Cards */}
      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.scrollContent}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor="#ED80E9" />}
      >
        <View style={styles.statsContainer}>
          <View style={styles.statCard}>
            <Text style={styles.statLabel}>Total Payments</Text>
            <Text style={styles.statValue}>{statistics.total}</Text>
          </View>
          <View style={[styles.statCard, { backgroundColor: '#10B98120' }]}>
            <Text style={styles.statLabel}>Approved</Text>
            <Text style={[styles.statValue, { color: '#10B981' }]}>{statistics.approved}</Text>
          </View>
          <View style={[styles.statCard, { backgroundColor: '#F59E0B20' }]}>
            <Text style={styles.statLabel}>Pending</Text>
            <Text style={[styles.statValue, { color: '#F59E0B' }]}>{statistics.pending}</Text>
          </View>
          <View style={[styles.statCard, { backgroundColor: '#EF444420' }]}>
            <Text style={styles.statLabel}>Rejected</Text>
            <Text style={[styles.statValue, { color: '#EF4444' }]}>{statistics.rejected}</Text>
          </View>
        </View>

        <View style={styles.amountStats}>
          <View style={styles.amountCard}>
            <Text style={styles.amountLabel}>Total Paid</Text>
            <Text style={styles.amountValue}>{formatCurrency(statistics.totalAmount)}</Text>
          </View>
          <View style={styles.amountCard}>
            <Text style={styles.amountLabel}>Pending Amount</Text>
            <Text style={[styles.amountValue, { color: '#F59E0B' }]}>
              {formatCurrency(statistics.pendingAmount)}
            </Text>
          </View>
        </View>

        {/* Filter Buttons */}
        <View style={styles.filterContainer}>
          <TouchableOpacity
            style={[styles.filterButton, filter === 'all' && styles.filterButtonActive]}
            onPress={() => setFilter('all')}
          >
            <Text style={[styles.filterText, filter === 'all' && styles.filterTextActive]}>All</Text>
          </TouchableOpacity>
          <TouchableOpacity
            style={[styles.filterButton, filter === 'approved' && styles.filterButtonActive]}
            onPress={() => setFilter('approved')}
          >
            <Text style={[styles.filterText, filter === 'approved' && styles.filterTextActive]}>Approved</Text>
          </TouchableOpacity>
          <TouchableOpacity
            style={[styles.filterButton, filter === 'pending' && styles.filterButtonActive]}
            onPress={() => setFilter('pending')}
          >
            <Text style={[styles.filterText, filter === 'pending' && styles.filterTextActive]}>Pending</Text>
          </TouchableOpacity>
          <TouchableOpacity
            style={[styles.filterButton, filter === 'rejected' && styles.filterButtonActive]}
            onPress={() => setFilter('rejected')}
          >
            <Text style={[styles.filterText, filter === 'rejected' && styles.filterTextActive]}>Rejected</Text>
          </TouchableOpacity>
        </View>

        {/* Payments List */}
        {filteredPayments.length > 0 ? (
          <View style={styles.paymentsList}>
            {filteredPayments.map((payment) => (
              <LinearGradient
                key={payment.id}
                colors={['#1C2233', '#2A2F42']}
                start={{ x: 0, y: 0 }}
                end={{ x: 1, y: 1 }}
                style={styles.paymentCard}
              >
                <View style={styles.paymentHeader}>
                  <View style={styles.paymentInfo}>
                    <Text style={styles.paymentAmount}>{formatCurrency(payment.amount)}</Text>
                    {payment.loanReference && (
                      <Text style={styles.loanReference}>Loan: {payment.loanReference}</Text>
                    )}
                  </View>
                  <View
                    style={[
                      styles.statusBadge,
                      { backgroundColor: getStatusColor(payment.status) + '20' },
                    ]}
                  >
                    <Ionicons
                      name={getStatusIcon(payment.status) as any}
                      size={16}
                      color={getStatusColor(payment.status)}
                    />
                    <Text
                      style={[styles.statusText, { color: getStatusColor(payment.status) }]}
                    >
                      {payment.status.charAt(0).toUpperCase() + payment.status.slice(1)}
                    </Text>
                  </View>
                </View>

                <View style={styles.paymentDetails}>
                  <View style={styles.detailRow}>
                    <Ionicons name="calendar-outline" size={16} color="#9CA3AF" />
                    <Text style={styles.detailText}>
                      Paid: {formatDate(payment.paidAt)}
                    </Text>
                  </View>
                  {payment.approvedAt && (
                    <View style={styles.detailRow}>
                      <Ionicons name="checkmark-circle-outline" size={16} color="#10B981" />
                      <Text style={styles.detailText}>
                        Approved: {formatDate(payment.approvedAt)}
                      </Text>
                    </View>
                  )}
                  {payment.penaltyAmount && parseFloat(payment.penaltyAmount) > 0 && (
                    <View style={styles.detailRow}>
                      <Ionicons name="alert-circle-outline" size={16} color="#F59E0B" />
                      <Text style={styles.detailText}>
                        Penalty: {formatCurrency(payment.penaltyAmount)}
                      </Text>
                    </View>
                  )}
                  {payment.rejectionReason && (
                    <View style={styles.detailRow}>
                      <Ionicons name="close-circle-outline" size={16} color="#EF4444" />
                      <Text style={[styles.detailText, { color: '#EF4444' }]}>
                        Reason: {payment.rejectionReason}
                      </Text>
                    </View>
                  )}
                </View>

                {payment.receiptUrl && (
                  <TouchableOpacity
                    style={styles.receiptButton}
                    onPress={() => openReceiptModal(payment.receiptUrl!)}
                  >
                    <Ionicons name="receipt-outline" size={18} color="#ED80E9" />
                    <Text style={styles.receiptButtonText}>View Receipt</Text>
                  </TouchableOpacity>
                )}

                {payment.loanId && (
                  <TouchableOpacity
                    style={styles.viewLoanButton}
                    onPress={() => router.push(`/(tabs)/loan_details?loanId=${payment.loanId}`)}
                  >
                    <Text style={styles.viewLoanText}>View Loan Details</Text>
                    <Ionicons name="chevron-forward" size={18} color="#ED80E9" />
                  </TouchableOpacity>
                )}
              </LinearGradient>
            ))}
          </View>
        ) : (
          <View style={styles.emptyContainer}>
            <Ionicons name="receipt-outline" size={64} color="#6B7280" />
            <Text style={styles.emptyText}>No payments found</Text>
            <Text style={styles.emptySubtext}>
              {filter !== 'all' ? `No ${filter} payments` : 'You haven\'t made any payments yet'}
            </Text>
          </View>
        )}
      </ScrollView>

      {/* Receipt Modal */}
      <Modal
        visible={receiptModalVisible}
        transparent={true}
        animationType="fade"
        onRequestClose={() => setReceiptModalVisible(false)}
      >
        <View style={styles.modalContainer}>
          <View style={styles.modalContent}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Receipt</Text>
              <TouchableOpacity
                onPress={() => setReceiptModalVisible(false)}
                style={styles.closeButton}
              >
                <Ionicons name="close" size={24} color="#fff" />
              </TouchableOpacity>
            </View>
            {selectedReceipt && (
              <Image
                source={{ uri: getReceiptImageUrl(selectedReceipt) }}
                style={styles.receiptImage}
                resizeMode="contain"
              />
            )}
          </View>
        </View>
      </Modal>
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
  statsContainer: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
    marginBottom: 20,
  },
  statCard: {
    width: '48%',
    backgroundColor: '#1C2233',
    padding: 16,
    borderRadius: 12,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: '#374151',
  },
  statLabel: {
    fontSize: 12,
    color: '#9CA3AF',
    marginBottom: 8,
  },
  statValue: {
    fontSize: 24,
    fontWeight: '700',
    color: '#fff',
  },
  amountStats: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 20,
  },
  amountCard: {
    flex: 1,
    backgroundColor: '#1C2233',
    padding: 16,
    borderRadius: 12,
    marginHorizontal: 6,
    borderWidth: 1,
    borderColor: '#374151',
  },
  amountLabel: {
    fontSize: 12,
    color: '#9CA3AF',
    marginBottom: 8,
  },
  amountValue: {
    fontSize: 20,
    fontWeight: '700',
    color: '#10B981',
  },
  filterContainer: {
    flexDirection: 'row',
    marginBottom: 20,
    gap: 8,
  },
  filterButton: {
    flex: 1,
    paddingVertical: 10,
    paddingHorizontal: 16,
    borderRadius: 8,
    backgroundColor: '#1C2233',
    borderWidth: 1,
    borderColor: '#374151',
    alignItems: 'center',
  },
  filterButtonActive: {
    backgroundColor: '#ED80E9',
    borderColor: '#ED80E9',
  },
  filterText: {
    color: '#9CA3AF',
    fontSize: 14,
    fontWeight: '500',
  },
  filterTextActive: {
    color: '#fff',
    fontWeight: '700',
  },
  paymentsList: {
    gap: 12,
  },
  paymentCard: {
    padding: 16,
    borderRadius: 12,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: '#374151',
  },
  paymentHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: 12,
  },
  paymentInfo: {
    flex: 1,
  },
  paymentAmount: {
    fontSize: 24,
    fontWeight: '700',
    color: '#fff',
    marginBottom: 4,
  },
  loanReference: {
    fontSize: 14,
    color: '#9CA3AF',
  },
  statusBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 20,
    gap: 6,
  },
  statusText: {
    fontSize: 12,
    fontWeight: '600',
  },
  paymentDetails: {
    gap: 8,
    marginBottom: 12,
  },
  detailRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  detailText: {
    fontSize: 14,
    color: '#D1D5DB',
  },
  receiptButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 10,
    marginTop: 8,
    borderRadius: 8,
    backgroundColor: '#ED80E920',
    borderWidth: 1,
    borderColor: '#ED80E9',
    gap: 8,
  },
  receiptButtonText: {
    color: '#ED80E9',
    fontSize: 14,
    fontWeight: '600',
  },
  viewLoanButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 10,
    marginTop: 8,
    borderRadius: 8,
    gap: 8,
  },
  viewLoanText: {
    color: '#ED80E9',
    fontSize: 14,
    fontWeight: '600',
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
  },
  modalContainer: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.9)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  modalContent: {
    width: '90%',
    height: '80%',
    backgroundColor: '#1C2233',
    borderRadius: 12,
    overflow: 'hidden',
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 16,
    borderBottomWidth: 1,
    borderBottomColor: '#374151',
  },
  modalTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: '#fff',
  },
  closeButton: {
    padding: 4,
  },
  receiptImage: {
    width: '100%',
    height: '100%',
    flex: 1,
  },
});

export default PaymentHistoryScreen;

