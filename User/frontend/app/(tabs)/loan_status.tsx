import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, SafeAreaView, ScrollView, ActivityIndicator, TouchableOpacity, RefreshControl } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import { useRouter } from 'expo-router';
import { api } from '../../src/config/api';

interface Loan {
  id: string;
  reference: string;
  principalAmount: string;
  status: string;
  applicationDate?: string;
  maturityDate?: string;
  releaseDate?: string;
  totalPaid: string;
  totalDisbursed: string;
  repayments?: {
    id: string;
    dueDate: string;
    amountDue: string;
    amountPaid: string;
    paidAt?: string;
    penaltyApplied: string;
  }[];
}

const getStatusInfo = (status: string) => {
  const statusMap: { [key: string]: { label: string; icon: string; color: string } } = {
    'new_application': { label: 'New Application', icon: 'document-text-outline', color: '#FFD700' },
    'under_review': { label: 'Under Review', icon: 'time-outline', color: '#FFA500' },
    'approved': { label: 'Approved', icon: 'checkmark-circle-outline', color: '#4EFA8A' },
    'for_release': { label: 'For Release', icon: 'hourglass-outline', color: '#87CEEB' },
    'disbursed': { label: 'Disbursed', icon: 'wallet-outline', color: '#4169E1' },
    'closed': { label: 'Closed', icon: 'checkmark-done-circle-outline', color: '#32CD32' },
    'rejected': { label: 'Rejected', icon: 'close-circle-outline', color: '#FF6B6B' },
    'cancelled': { label: 'Cancelled', icon: 'ban-outline', color: '#9CA3AF' },
    'restructured': { label: 'Restructured', icon: 'refresh-outline', color: '#9370DB' },
  };
  
  return statusMap[status] || { label: status.replace('_', ' '), icon: 'help-circle-outline', color: '#9CA3AF' };
};

const LoanStatusScreen = () => {
  const router = useRouter();
  const [loans, setLoans] = useState<Loan[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  useEffect(() => {
    fetchLoans();
  }, []);

  const fetchLoans = async () => {
    try {
      const response = await api.get('/loans');
      if (response.data?.loans) {
        setLoans(response.data.loans);
      }
    } catch (error) {
      console.error('Error fetching loans:', error);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const onRefresh = () => {
    setRefreshing(true);
    fetchLoans();
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
    });
  };

  const getOutstandingAmount = (loan: Loan) => {
    if (!loan.repayments) return 0;
    return loan.repayments.reduce((total, rep) => {
      const due = parseFloat(rep.amountDue);
      const paid = parseFloat(rep.amountPaid);
      const penalty = parseFloat(rep.penaltyApplied);
      return total + Math.max(0, due + penalty - paid);
    }, 0);
  };

  if (loading) {
    return (
      <SafeAreaView style={styles.container}>
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color="#4EFA8A" />
          <Text style={styles.loadingText}>Loading loans...</Text>
        </View>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.container}>
      <Text style={styles.title}>Loan Status Tracking</Text>
      <Text style={styles.subtitle}>Track all your loan applications and their current status</Text>

      <ScrollView 
        contentContainerStyle={styles.scrollView}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor="#4EFA8A" />
        }
      >
        {loans.length === 0 ? (
          <View style={styles.emptyContainer}>
            <Ionicons name="document-outline" size={64} color="#9CA3AF" />
            <Text style={styles.emptyText}>No loans found</Text>
            <Text style={styles.emptySubtext}>Apply for a loan to get started</Text>
            <TouchableOpacity
              style={styles.applyButton}
              onPress={() => router.push('/(tabs)/loan_request')}
            >
              <Text style={styles.applyButtonText}>Apply for Loan</Text>
            </TouchableOpacity>
          </View>
        ) : (
          loans.map((loan) => {
            const statusInfo = getStatusInfo(loan.status);
            const outstanding = getOutstandingAmount(loan);
            const isActive = loan.status === 'disbursed' || loan.status === 'approved' || loan.status === 'for_release';

            return (
              <TouchableOpacity
                key={loan.id}
                onPress={() => router.push({
                  pathname: '/(tabs)/loan_details' as any,
                  params: { loanId: loan.id }
                })}
                activeOpacity={0.8}
              >
          <LinearGradient
            colors={["#03042c", "#302b63", "#24243e"]}
            start={{ x: 0, y: 0 }}
            end={{ x: 1, y: 1 }}
            style={styles.statusCard}
          >
                  <View style={styles.cardHeader}>
                    <View style={styles.cardHeaderLeft}>
                      <Ionicons name={statusInfo.icon as any} size={32} color={statusInfo.color} style={styles.icon} />
                      <View style={styles.cardTitleContainer}>
                        <Text style={styles.cardReference}>{loan.reference}</Text>
                        <Text style={[styles.statusText, { color: statusInfo.color }]}>
                          {statusInfo.label}
                        </Text>
                      </View>
                    </View>
                    <Ionicons name="chevron-forward" size={20} color="#9CA3AF" />
                  </View>

                  <View style={styles.cardBody}>
                    <View style={styles.amountRow}>
            <View>
                        <Text style={styles.amountLabel}>Loan Amount</Text>
                        <Text style={styles.amountValue}>
                          {formatCurrency(loan.principalAmount)}
                        </Text>
                      </View>
                      {isActive && outstanding > 0 && (
                        <View style={styles.outstandingContainer}>
                          <Text style={styles.outstandingLabel}>Outstanding</Text>
                          <Text style={styles.outstandingValue}>
                            {formatCurrency(outstanding)}
                          </Text>
                        </View>
                      )}
                    </View>

                    <View style={styles.detailsRow}>
                      <View style={styles.detailItem}>
                        <Ionicons name="calendar-outline" size={16} color="#9CA3AF" />
                        <Text style={styles.detailText}>
                          Applied: {formatDate(loan.applicationDate)}
                        </Text>
                      </View>
                      {loan.releaseDate && (
                        <View style={styles.detailItem}>
                          <Ionicons name="checkmark-circle-outline" size={16} color="#4EFA8A" />
                          <Text style={styles.detailText}>
                            Released: {formatDate(loan.releaseDate)}
                          </Text>
                        </View>
                      )}
                    </View>

                    {loan.status === 'disbursed' && loan.repayments && (
                      <View style={styles.repaymentSummary}>
                        <Text style={styles.repaymentSummaryText}>
                          {loan.repayments.filter(r => parseFloat(r.amountPaid) >= parseFloat(r.amountDue)).length} / {loan.repayments.length} payments completed
                        </Text>
                      </View>
                    )}
            </View>
          </LinearGradient>
              </TouchableOpacity>
            );
          })
        )}
      </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: 'black',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingText: {
    color: '#9CA3AF',
    marginTop: 12,
    fontSize: 16,
  },
  title: {
    fontSize: 28,
    fontWeight: '700',
    color: '#ffffff',
    marginTop: 60,
    marginBottom: 8,
    paddingHorizontal: 20,
  },
  subtitle: {
    fontSize: 14,
    color: '#9CA3AF',
    marginBottom: 20,
    paddingHorizontal: 20,
  },
  scrollView: {
    padding: 20,
    paddingBottom: 40,
  },
  emptyContainer: {
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 60,
  },
  emptyText: {
    fontSize: 20,
    fontWeight: '600',
    color: '#ffffff',
    marginTop: 16,
    marginBottom: 8,
  },
  emptySubtext: {
    fontSize: 14,
    color: '#9CA3AF',
    marginBottom: 24,
  },
  applyButton: {
    backgroundColor: '#4EFA8A',
    paddingHorizontal: 24,
    paddingVertical: 12,
    borderRadius: 8,
  },
  applyButtonText: {
    color: '#000',
    fontSize: 16,
    fontWeight: '600',
  },
  statusCard: {
    borderRadius: 16,
    padding: 20,
    marginBottom: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.3,
    shadowRadius: 6,
    elevation: 4,
  },
  cardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 16,
  },
  cardHeaderLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
  },
  icon: {
    marginRight: 12,
  },
  cardTitleContainer: {
    flex: 1,
  },
  cardReference: {
    fontSize: 18,
    fontWeight: '700',
    color: '#ffffff',
    marginBottom: 4,
  },
  statusText: {
    fontSize: 14,
    fontWeight: '600',
    textTransform: 'capitalize',
  },
  cardBody: {
    gap: 12,
  },
  amountRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
  },
  amountLabel: {
    fontSize: 12,
    color: '#9CA3AF',
    marginBottom: 4,
  },
  amountValue: {
    fontSize: 24,
    fontWeight: '700',
    color: '#ffffff',
  },
  outstandingContainer: {
    alignItems: 'flex-end',
  },
  outstandingLabel: {
    fontSize: 12,
    color: '#9CA3AF',
    marginBottom: 4,
  },
  outstandingValue: {
    fontSize: 18,
    fontWeight: '700',
    color: '#FF6B6B',
  },
  detailsRow: {
    gap: 8,
  },
  detailItem: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  detailText: {
    fontSize: 13,
    color: '#9CA3AF',
  },
  repaymentSummary: {
    marginTop: 8,
    paddingTop: 12,
    borderTopWidth: 1,
    borderTopColor: 'rgba(255, 255, 255, 0.1)',
  },
  repaymentSummaryText: {
    fontSize: 13,
    color: '#4EFA8A',
    fontWeight: '500',
  },
});

export default LoanStatusScreen;
