import React, { useEffect, useState, useCallback } from 'react';
import {
  View,
  Text,
  StyleSheet,
  SafeAreaView,
  ScrollView,
  ActivityIndicator,
  TouchableOpacity,
  RefreshControl,
  Modal,
  Image,
  Alert,
} from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
import { useRouter, useLocalSearchParams } from 'expo-router';
import { api } from '../../src/config/api';
import { WebView } from 'react-native-webview';

interface Loan {
  id: string;
  reference: string;
  principalAmount: string;
  interestRate: string;
  status: string;
  applicationDate?: string;
  maturityDate?: string;
  releaseDate?: string;
  totalDisbursed: string;
  totalPaid: string;
  totalPenalties: string;
  repayments?: {
    id: string;
    dueDate: string;
    amountDue: string;
    amountPaid: string;
    paidAt?: string;
    penaltyApplied: string;
    note?: string;
  }[];
}

interface Payment {
  id: string;
  amount: string;
  penaltyAmount: string;
  status: string;
  paidAt?: string;
  approvedAt?: string;
  rejectionReason?: string;
  receiptUrl?: string;
  receiptFileName?: string;
}

const LoanDetailsScreen = () => {
  const router = useRouter();
  const params = useLocalSearchParams();
  const loanId = params.loanId as string;

  const [loan, setLoan] = useState<Loan | null>(null);
  const [payments, setPayments] = useState<Payment[]>([]);
  const [expandedRepaymentId, setExpandedRepaymentId] = useState<string | null>(null);
  const [selectedReceipt, setSelectedReceipt] = useState<string | null>(null);
  const [receiptModalVisible, setReceiptModalVisible] = useState(false);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [demandLetter, setDemandLetter] = useState<string | null>(null);
  const [demandLetterModalVisible, setDemandLetterModalVisible] = useState(false);
  const [generatingDemandLetter, setGeneratingDemandLetter] = useState(false);

  const fetchLoanDetails = useCallback(async () => {
    try {
      const [loanRes, paymentsRes] = await Promise.all([
        api.get(`/loans/${loanId}`),
        api.get(`/payments?loanId=${loanId}`).catch(() => ({ data: { payments: [] } }))
      ]);
      
      if (loanRes.data?.data) {
        setLoan(loanRes.data.data);
      }
      
      if (paymentsRes.data?.payments) {
        setPayments(paymentsRes.data.payments);
      }
    } catch (error) {
      console.error('Error fetching loan details:', error);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, [loanId]);

  useEffect(() => {
    if (loanId) {
      fetchLoanDetails();
    }
  }, [loanId, fetchLoanDetails]);

  const onRefresh = () => {
    setRefreshing(true);
    fetchLoanDetails();
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
      month: 'long',
      day: 'numeric',
      year: 'numeric',
    });
  };

  const getStatusInfo = (status: string) => {
    const statusMap: { [key: string]: { label: string; color: string } } = {
      'new_application': { label: 'New Application', color: '#FFD700' },
      'under_review': { label: 'Under Review', color: '#FFA500' },
      'approved': { label: 'Approved', color: '#4EFA8A' },
      'for_release': { label: 'For Release', color: '#87CEEB' },
      'disbursed': { label: 'Disbursed', color: '#4169E1' },
      'closed': { label: 'Closed', color: '#32CD32' },
      'rejected': { label: 'Rejected', color: '#FF6B6B' },
      'cancelled': { label: 'Cancelled', color: '#9CA3AF' },
      'restructured': { label: 'Restructured', color: '#9370DB' },
    };
    return statusMap[status] || { label: status.replace('_', ' '), color: '#9CA3AF' };
  };

  const calculateOutstanding = () => {
    if (!loan?.repayments) return 0;
    return loan.repayments.reduce((total, rep) => {
      const due = parseFloat(rep.amountDue);
      const paid = parseFloat(rep.amountPaid);
      const penalty = parseFloat(rep.penaltyApplied);
      return total + Math.max(0, due + penalty - paid);
    }, 0);
  };

  const getReceiptImageUrl = (fileUrl: string) => {
    // Construct full URL - fileUrl is like "/uploads/filename.jpg"
    const baseURL = api.defaults.baseURL?.replace('/api/v1', '') || '';
    return `${baseURL}${fileUrl}`;
  };

  const openReceipt = (receiptUrl: string) => {
    setSelectedReceipt(receiptUrl);
    setReceiptModalVisible(true);
  };

  const handleGenerateDemandLetter = async () => {
    if (!loanId) return;

    try {
      setGeneratingDemandLetter(true);
      const res = await api.post('/legal/demand-letter', {
        loanId,
        daysToComply: 5,
      });

      if (res.data?.data?.letter) {
        setDemandLetter(res.data.data.letter);
        setDemandLetterModalVisible(true);
      }
    } catch (error: any) {
      console.error('Error generating demand letter:', error);
      const errorMessage = error.response?.data?.message || 'Failed to generate demand letter';
      Alert.alert('Error', errorMessage);
    } finally {
      setGeneratingDemandLetter(false);
    }
  };

  const generateDocumentHTML = (content: string, title: string): string => {
    const htmlContent = content
      .split('\n')
      .map((line) => {
        if (line.trim() === '') return '<br/>';
        if (line === line.toUpperCase() && line.length > 5 && !line.includes('(') && !line.includes('Php')) {
          return `<h2 style="font-size: 16px; font-weight: bold; margin-top: 20px; margin-bottom: 10px; text-align: center;">${line}</h2>`;
        }
        if (line.includes('________________') || line.includes('___')) {
          return `<p style="margin: 10px 0; text-align: center; font-size: 12px;">${line}</p>`;
        }
        return `<p style="margin: 8px 0; line-height: 1.6; font-size: 12px; text-align: justify;">${line}</p>`;
      })
      .join('');

    return `
      <!DOCTYPE html>
      <html>
        <head>
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <style>
            body {
              font-family: 'Times New Roman', serif;
              padding: 20px;
              background-color: #1a1a2e;
              color: #ffffff;
              line-height: 1.8;
              max-width: 800px;
              margin: 0 auto;
            }
            h2 {
              color: #ffffff;
              border-bottom: 2px solid #4EFA8A;
              padding-bottom: 10px;
            }
            p {
              color: #e0e0e0;
            }
            @media print {
              body {
                background-color: white;
                color: black;
              }
              h2 {
                color: black;
                border-bottom: 2px solid black;
              }
              p {
                color: black;
              }
            }
          </style>
        </head>
        <body>
          <h1 style="text-align: center; font-size: 18px; margin-bottom: 30px; color: #4EFA8A;">${title}</h1>
          ${htmlContent}
        </body>
      </html>
    `;
  };

  const getRepaymentStatus = (repayment: NonNullable<Loan['repayments']>[0]) => {
    const due = parseFloat(repayment.amountDue);
    const paid = parseFloat(repayment.amountPaid);
    const penalty = parseFloat(repayment.penaltyApplied);
    const total = due + penalty;
    // const outstanding = total - paid; // Not used in this function
    const dueDate = new Date(repayment.dueDate);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    dueDate.setHours(0, 0, 0, 0);

    if (paid >= total) {
      return { status: 'paid', color: '#4EFA8A', label: 'Paid' };
    } else if (dueDate < today) {
      return { status: 'overdue', color: '#FF6B6B', label: 'Overdue' };
    } else {
      return { status: 'pending', color: '#FFD700', label: 'Pending' };
    }
  };

  if (loading) {
    return (
      <SafeAreaView style={styles.container}>
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color="#4EFA8A" />
          <Text style={styles.loadingText}>Loading loan details...</Text>
        </View>
      </SafeAreaView>
    );
  }

  if (!loan) {
    return (
      <SafeAreaView style={styles.container}>
        <View style={styles.errorContainer}>
          <Ionicons name="alert-circle-outline" size={64} color="#FF6B6B" />
          <Text style={styles.errorText}>Loan not found</Text>
          <TouchableOpacity
            style={styles.backButton}
            onPress={() => router.back()}
          >
            <Text style={styles.backButtonText}>Go Back</Text>
          </TouchableOpacity>
        </View>
      </SafeAreaView>
    );
  }

  const statusInfo = getStatusInfo(loan.status);
  const outstanding = calculateOutstanding();
  // const totalAmount = parseFloat(loan.principalAmount); // Not used
  // const totalPaid = parseFloat(loan.totalPaid); // Not used (loan.totalPaid is used directly)
  const totalPenalties = parseFloat(loan.totalPenalties);
  const interestRate = parseFloat(loan.interestRate) * 100; // Convert to percentage

  return (
    <SafeAreaView style={styles.container}>
      <ScrollView
        contentContainerStyle={styles.scrollView}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor="#4EFA8A" />
        }
      >
        {/* Header */}
        <View style={styles.header}>
          <TouchableOpacity onPress={() => router.back()} style={styles.backButton}>
            <Ionicons name="arrow-back" size={24} color="#ffffff" />
          </TouchableOpacity>
          <Text style={styles.headerTitle}>Loan Details</Text>
          <View style={{ width: 24 }} />
        </View>

        {/* Loan Overview Card */}
        <LinearGradient
          colors={["#03042c", "#302b63", "#24243e"]}
          start={{ x: 0, y: 0 }}
          end={{ x: 1, y: 1 }}
          style={styles.overviewCard}
        >
          <View style={styles.overviewHeader}>
            <Text style={styles.loanReference}>{loan.reference}</Text>
            <View style={[styles.statusBadge, { backgroundColor: `${statusInfo.color}20` }]}>
              <Text style={[styles.statusText, { color: statusInfo.color }]}>
                {statusInfo.label}
              </Text>
            </View>
          </View>

          <Text style={styles.principalAmount}>
            {formatCurrency(loan.principalAmount)}
          </Text>

          <View style={styles.overviewDetails}>
            <View style={styles.detailRow}>
              <Text style={styles.detailLabel}>Interest Rate</Text>
              <Text style={styles.detailValue}>{interestRate.toFixed(2)}%</Text>
            </View>
            <View style={styles.detailRow}>
              <Text style={styles.detailLabel}>Application Date</Text>
              <Text style={styles.detailValue}>{formatDate(loan.applicationDate)}</Text>
            </View>
            {loan.releaseDate && (
              <View style={styles.detailRow}>
                <Text style={styles.detailLabel}>Release Date</Text>
                <Text style={styles.detailValue}>{formatDate(loan.releaseDate)}</Text>
              </View>
            )}
            {loan.maturityDate && (
              <View style={styles.detailRow}>
                <Text style={styles.detailLabel}>Maturity Date</Text>
                <Text style={styles.detailValue}>{formatDate(loan.maturityDate)}</Text>
              </View>
            )}
          </View>
        </LinearGradient>

        {/* Financial Summary */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Financial Summary</Text>
          <View style={styles.summaryCard}>
            <View style={styles.summaryRow}>
              <Text style={styles.summaryLabel}>Total Disbursed</Text>
              <Text style={styles.summaryValue}>
                {formatCurrency(loan.totalDisbursed)}
              </Text>
            </View>
            <View style={styles.summaryRow}>
              <Text style={styles.summaryLabel}>Total Paid</Text>
              <Text style={[styles.summaryValue, { color: '#4EFA8A' }]}>
                {formatCurrency(loan.totalPaid)}
              </Text>
            </View>
            {totalPenalties > 0 && (
              <View style={styles.summaryRow}>
                <Text style={styles.summaryLabel}>Total Penalties</Text>
                <Text style={[styles.summaryValue, { color: '#FF6B6B' }]}>
                  {formatCurrency(loan.totalPenalties)}
                </Text>
              </View>
            )}
            {outstanding > 0 && (
              <View style={[styles.summaryRow, styles.outstandingRow]}>
                <Text style={styles.outstandingLabel}>Outstanding Balance</Text>
                <Text style={styles.outstandingValue}>
                  {formatCurrency(outstanding)}
                </Text>
              </View>
            )}
          </View>
        </View>

        {/* Repayment Schedule */}
        {loan.repayments && loan.repayments.length > 0 && (
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>Repayment Schedule</Text>
            <Text style={styles.sectionSubtitle}>
              {loan.repayments.length} payment{loan.repayments.length !== 1 ? 's' : ''} scheduled
            </Text>
            {loan.repayments.map((repayment, index) => {
              const repaymentStatus = getRepaymentStatus(repayment);
              const due = parseFloat(repayment.amountDue);
              const paid = parseFloat(repayment.amountPaid);
              const penalty = parseFloat(repayment.penaltyApplied);
              const total = due + penalty;
              const remaining = Math.max(0, total - paid);
              
              // Get payments for this specific repayment
              const repPayments = payments.filter((p: any) => {
                return p.repaymentId === repayment.id;
              });
              
              const approvedPayments = repPayments.filter((p: Payment) => p.status === 'approved');
              const pendingPayments = repPayments.filter((p: Payment) => p.status === 'pending');
              const rejectedPayments = repPayments.filter((p: Payment) => p.status === 'rejected');
              
              const totalApprovedAmount = approvedPayments.reduce((sum, p) => {
                return sum + parseFloat(p.amount || "0");
              }, 0);
              
              const isExpanded = expandedRepaymentId === repayment.id;
              const hasPayments = repPayments.length > 0;

              return (
                <View key={repayment.id} style={styles.repaymentCard}>
                  <TouchableOpacity
                    onPress={() => setExpandedRepaymentId(isExpanded ? null : repayment.id)}
                    activeOpacity={0.7}
                    disabled={!hasPayments}
                  >
                    <View style={styles.repaymentHeader}>
                      <View style={styles.repaymentHeaderLeft}>
                        <View style={styles.repaymentNumber}>
                          <Text style={styles.repaymentNumberText}>#{index + 1}</Text>
                        </View>
                        <View style={[styles.repaymentStatusBadge, { backgroundColor: `${repaymentStatus.color}20` }]}>
                          <Text style={[styles.repaymentStatusText, { color: repaymentStatus.color }]}>
                            {repaymentStatus.label}
                          </Text>
                        </View>
                        {hasPayments && (
                          <View style={styles.paymentCountBadge}>
                            <Text style={styles.paymentCountText}>
                              {repPayments.length} payment{repPayments.length !== 1 ? 's' : ''}
                            </Text>
                          </View>
                        )}
                      </View>
                      {hasPayments && (
                        <Ionicons 
                          name={isExpanded ? "chevron-up" : "chevron-down"} 
                          size={20} 
                          color="#9CA3AF" 
                        />
                      )}
                    </View>

                    <View style={styles.repaymentDetails}>
                      <View style={styles.repaymentDetailRow}>
                        <Ionicons name="calendar-outline" size={16} color="#9CA3AF" />
                        <Text style={styles.repaymentDetailText}>
                          Due: {formatDate(repayment.dueDate)}
                        </Text>
                      </View>
                      {repayment.paidAt && (
                        <View style={styles.repaymentDetailRow}>
                          <Ionicons name="checkmark-circle-outline" size={16} color="#4EFA8A" />
                          <Text style={styles.repaymentDetailText}>
                            Paid: {formatDate(repayment.paidAt)}
                          </Text>
                        </View>
                      )}
                      {totalApprovedAmount > 0 && (
                        <View style={styles.repaymentDetailRow}>
                          <Ionicons name="checkmark-circle" size={16} color="#22C55E" />
                          <Text style={[styles.repaymentDetailText, { color: '#22C55E' }]}>
                            Confirmed: {formatCurrency(totalApprovedAmount)}
                          </Text>
                        </View>
                      )}
                      {pendingPayments.length > 0 && (
                        <View style={styles.repaymentDetailRow}>
                          <Ionicons name="hourglass" size={16} color="#3B82F6" />
                          <Text style={[styles.repaymentDetailText, { color: '#3B82F6' }]}>
                            {pendingPayments.length} pending approval
                          </Text>
                        </View>
                      )}
                    </View>

                    <View style={styles.repaymentAmounts}>
                      <View style={styles.repaymentAmountRow}>
                        <Text style={styles.repaymentAmountLabel}>Amount Due</Text>
                        <Text style={styles.repaymentAmountValue}>
                          {formatCurrency(due)}
                        </Text>
                      </View>
                      {penalty > 0 && (
                        <View style={styles.repaymentAmountRow}>
                          <Text style={styles.repaymentAmountLabel}>Penalty</Text>
                          <Text style={[styles.repaymentAmountValue, { color: '#FF6B6B' }]}>
                            {formatCurrency(penalty)}
                          </Text>
                        </View>
                      )}
                      <View style={styles.repaymentAmountRow}>
                        <Text style={styles.repaymentAmountLabel}>Amount Paid</Text>
                        <Text style={[styles.repaymentAmountValue, { color: '#4EFA8A' }]}>
                          {formatCurrency(paid)}
                        </Text>
                      </View>
                      {remaining > 0 && (
                        <View style={[styles.repaymentAmountRow, styles.remainingRow]}>
                          <Text style={styles.remainingLabel}>Remaining</Text>
                          <Text style={styles.remainingValue}>
                            {formatCurrency(remaining)}
                          </Text>
                        </View>
                      )}
                    </View>

                    {repayment.note && (
                      <Text style={styles.repaymentNote}>{repayment.note}</Text>
                    )}
                  </TouchableOpacity>

                  {/* Payment History - Expandable */}
                  {isExpanded && hasPayments && (
                    <View style={styles.paymentHistoryContainer}>
                      <Text style={styles.paymentHistoryTitle}>Payment History</Text>
                      {approvedPayments.length > 0 && (
                        <View style={styles.paymentHistorySection}>
                          <Text style={styles.paymentHistorySectionTitle}>
                            ✓ Approved Payments ({approvedPayments.length})
                          </Text>
                          {approvedPayments.map((payment) => (
                            <View key={payment.id} style={styles.paymentHistoryItem}>
                              <View style={styles.paymentHistoryItemLeft}>
                                <Ionicons name="checkmark-circle" size={20} color="#22C55E" />
                                <View style={styles.paymentHistoryItemDetails}>
                                  <Text style={styles.paymentHistoryItemAmount}>
                                    {formatCurrency(parseFloat(payment.amount))}
                                  </Text>
                                  <Text style={styles.paymentHistoryItemDate}>
                                    Approved: {payment.approvedAt ? formatDate(payment.approvedAt) : 'N/A'}
                                  </Text>
                                  {payment.paidAt && (
                                    <Text style={styles.paymentHistoryItemSubtext}>
                                      Paid: {formatDate(payment.paidAt)}
                                    </Text>
                                  )}
                                  {parseFloat(payment.penaltyAmount || "0") > 0 && (
                                    <Text style={styles.paymentHistoryItemPenalty}>
                                      Penalty: {formatCurrency(parseFloat(payment.penaltyAmount || "0"))}
                                    </Text>
                                  )}
                                </View>
                              </View>
                              {payment.receiptUrl && (
                                <TouchableOpacity
                                  onPress={() => openReceipt(payment.receiptUrl!)}
                                >
                                  <Ionicons name="receipt-outline" size={20} color="#3B82F6" />
                                </TouchableOpacity>
                              )}
                            </View>
                          ))}
                        </View>
                      )}
                      
                      {pendingPayments.length > 0 && (
                        <View style={styles.paymentHistorySection}>
                          <Text style={styles.paymentHistorySectionTitle}>
                            ⏳ Pending Approval ({pendingPayments.length})
                          </Text>
                          {pendingPayments.map((payment) => (
                            <View key={payment.id} style={styles.paymentHistoryItem}>
                              <View style={styles.paymentHistoryItemLeft}>
                                <Ionicons name="hourglass" size={20} color="#3B82F6" />
                                <View style={styles.paymentHistoryItemDetails}>
                                  <Text style={styles.paymentHistoryItemAmount}>
                                    {formatCurrency(parseFloat(payment.amount))}
                                  </Text>
                                  <Text style={styles.paymentHistoryItemDate}>
                                    Submitted: {payment.paidAt ? formatDate(payment.paidAt) : 'N/A'}
                                  </Text>
                                  <Text style={styles.paymentHistoryItemSubtext}>
                                    Awaiting admin approval
                                  </Text>
                                </View>
                              </View>
                            </View>
                          ))}
                        </View>
                      )}
                      
                      {rejectedPayments.length > 0 && (
                        <View style={styles.paymentHistorySection}>
                          <Text style={styles.paymentHistorySectionTitle}>
                            ✗ Rejected Payments ({rejectedPayments.length})
                          </Text>
                          {rejectedPayments.map((payment) => (
                            <View key={payment.id} style={styles.paymentHistoryItem}>
                              <View style={styles.paymentHistoryItemLeft}>
                                <Ionicons name="close-circle" size={20} color="#FF6B6B" />
                                <View style={styles.paymentHistoryItemDetails}>
                                  <Text style={styles.paymentHistoryItemAmount}>
                                    {formatCurrency(parseFloat(payment.amount))}
                                  </Text>
                                  <Text style={styles.paymentHistoryItemDate}>
                                    Rejected: {payment.approvedAt ? formatDate(payment.approvedAt) : 'N/A'}
                                  </Text>
                                  {payment.rejectionReason && (
                                    <Text style={styles.paymentHistoryItemRejection}>
                                      Reason: {payment.rejectionReason}
                                    </Text>
                                  )}
                                </View>
                              </View>
                            </View>
                          ))}
                        </View>
                      )}
                    </View>
                  )}
                </View>
              );
            })}
          </View>
        )}

        {/* Action Buttons */}
        {loan.status === 'disbursed' && outstanding > 0 && (
          <View style={styles.actionsSection}>
            <TouchableOpacity
              style={styles.actionButton}
              onPress={() => router.push('/(tabs)/attatch_receipt')}
            >
              <Ionicons name="receipt-outline" size={20} color="#ffffff" />
              <Text style={styles.actionButtonText}>Make Payment</Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={[styles.actionButton, styles.demandLetterButton]}
              onPress={handleGenerateDemandLetter}
              disabled={generatingDemandLetter}
            >
              {generatingDemandLetter ? (
                <ActivityIndicator color="#ffffff" />
              ) : (
                <>
                  <Ionicons name="document-text-outline" size={20} color="#ffffff" />
                  <Text style={styles.actionButtonText}>View Demand Letter</Text>
                </>
              )}
            </TouchableOpacity>
          </View>
        )}
      </ScrollView>

      {/* Receipt Viewer Modal */}
      <Modal
        visible={receiptModalVisible}
        transparent={true}
        animationType="fade"
        onRequestClose={() => setReceiptModalVisible(false)}
      >
        <View style={styles.receiptModalContainer}>
          <View style={styles.receiptModalContent}>
            <View style={styles.receiptModalHeader}>
              <Text style={styles.receiptModalTitle}>Receipt</Text>
              <TouchableOpacity
                onPress={() => setReceiptModalVisible(false)}
                style={styles.receiptModalCloseButton}
              >
                <Ionicons name="close" size={28} color="#fff" />
              </TouchableOpacity>
            </View>
            {selectedReceipt && (
              <Image
                source={{ uri: getReceiptImageUrl(selectedReceipt) }}
                style={styles.receiptModalImage}
                resizeMode="contain"
              />
            )}
          </View>
        </View>
      </Modal>

      {/* Demand Letter Modal */}
      <Modal
        visible={demandLetterModalVisible}
        transparent={true}
        animationType="slide"
        onRequestClose={() => setDemandLetterModalVisible(false)}
      >
        <View style={styles.receiptModalContainer}>
          <View style={styles.receiptModalContent}>
            <View style={styles.receiptModalHeader}>
              <Text style={styles.receiptModalTitle}>Final Demand Letter</Text>
              <TouchableOpacity
                onPress={() => setDemandLetterModalVisible(false)}
                style={styles.receiptModalCloseButton}
              >
                <Ionicons name="close" size={28} color="#fff" />
              </TouchableOpacity>
            </View>
            {demandLetter && (
              <WebView
                source={{
                  html: generateDocumentHTML(demandLetter, 'Final Demand Letter'),
                }}
                style={styles.demandLetterWebView}
                showsVerticalScrollIndicator={true}
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
  errorContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  errorText: {
    fontSize: 20,
    fontWeight: '600',
    color: '#ffffff',
    marginTop: 16,
    marginBottom: 24,
  },
  scrollView: {
    paddingBottom: 40,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    padding: 20,
    paddingTop: 60,
  },
  backButton: {
    padding: 8,
  },
  headerTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: '#ffffff',
  },
  backButtonText: {
    color: '#4EFA8A',
    fontSize: 16,
    fontWeight: '600',
  },
  overviewCard: {
    borderRadius: 16,
    padding: 20,
    margin: 20,
    marginBottom: 0,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.3,
    shadowRadius: 6,
    elevation: 4,
  },
  overviewHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 16,
  },
  loanReference: {
    fontSize: 18,
    fontWeight: '700',
    color: '#ffffff',
  },
  statusBadge: {
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 12,
  },
  statusText: {
    fontSize: 12,
    fontWeight: '600',
    textTransform: 'capitalize',
  },
  principalAmount: {
    fontSize: 32,
    fontWeight: '700',
    color: '#ffffff',
    marginBottom: 20,
  },
  overviewDetails: {
    gap: 12,
  },
  detailRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  detailLabel: {
    fontSize: 14,
    color: '#9CA3AF',
  },
  detailValue: {
    fontSize: 14,
    fontWeight: '600',
    color: '#ffffff',
  },
  section: {
    padding: 20,
    paddingTop: 24,
  },
  sectionTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: '#ffffff',
    marginBottom: 8,
  },
  sectionSubtitle: {
    fontSize: 14,
    color: '#9CA3AF',
    marginBottom: 16,
  },
  summaryCard: {
    backgroundColor: 'rgba(255, 255, 255, 0.05)',
    borderRadius: 12,
    padding: 16,
    gap: 12,
  },
  summaryRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  summaryLabel: {
    fontSize: 14,
    color: '#9CA3AF',
  },
  summaryValue: {
    fontSize: 16,
    fontWeight: '600',
    color: '#ffffff',
  },
  outstandingRow: {
    marginTop: 8,
    paddingTop: 12,
    borderTopWidth: 1,
    borderTopColor: 'rgba(255, 255, 255, 0.1)',
  },
  outstandingLabel: {
    fontSize: 16,
    fontWeight: '600',
    color: '#ffffff',
  },
  outstandingValue: {
    fontSize: 20,
    fontWeight: '700',
    color: '#FF6B6B',
  },
  repaymentCard: {
    backgroundColor: 'rgba(255, 255, 255, 0.05)',
    borderRadius: 12,
    padding: 16,
    marginBottom: 12,
  },
  repaymentHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
  },
  repaymentNumber: {
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: 'rgba(78, 250, 138, 0.2)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  repaymentNumberText: {
    fontSize: 14,
    fontWeight: '700',
    color: '#4EFA8A',
  },
  repaymentStatusBadge: {
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 8,
  },
  repaymentStatusText: {
    fontSize: 11,
    fontWeight: '600',
  },
  repaymentDetails: {
    gap: 8,
    marginBottom: 12,
  },
  repaymentDetailRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  repaymentDetailText: {
    fontSize: 13,
    color: '#9CA3AF',
  },
  repaymentAmounts: {
    gap: 8,
    marginTop: 8,
    paddingTop: 12,
    borderTopWidth: 1,
    borderTopColor: 'rgba(255, 255, 255, 0.1)',
  },
  repaymentAmountRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  repaymentAmountLabel: {
    fontSize: 13,
    color: '#9CA3AF',
  },
  repaymentAmountValue: {
    fontSize: 14,
    fontWeight: '600',
    color: '#ffffff',
  },
  remainingRow: {
    marginTop: 4,
    paddingTop: 8,
    borderTopWidth: 1,
    borderTopColor: 'rgba(255, 255, 255, 0.1)',
  },
  remainingLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: '#ffffff',
  },
  remainingValue: {
    fontSize: 16,
    fontWeight: '700',
    color: '#FF6B6B',
  },
  repaymentNote: {
    fontSize: 12,
    color: '#9CA3AF',
    fontStyle: 'italic',
    marginTop: 8,
  },
  repaymentHeaderLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    flex: 1,
  },
  paymentCountBadge: {
    backgroundColor: 'rgba(59, 130, 246, 0.2)',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 8,
  },
  paymentCountText: {
    color: '#3B82F6',
    fontSize: 10,
    fontWeight: '600',
  },
  paymentHistoryContainer: {
    marginTop: 16,
    paddingTop: 16,
    borderTopWidth: 1,
    borderTopColor: 'rgba(255, 255, 255, 0.1)',
  },
  paymentHistoryTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: '#ffffff',
    marginBottom: 12,
  },
  paymentHistorySection: {
    marginBottom: 16,
  },
  paymentHistorySectionTitle: {
    fontSize: 13,
    fontWeight: '600',
    color: '#9CA3AF',
    marginBottom: 8,
  },
  paymentHistoryItem: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    backgroundColor: 'rgba(255, 255, 255, 0.03)',
    padding: 12,
    borderRadius: 8,
    marginBottom: 8,
  },
  paymentHistoryItemLeft: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: 12,
    flex: 1,
  },
  paymentHistoryItemDetails: {
    flex: 1,
    gap: 4,
  },
  paymentHistoryItemAmount: {
    fontSize: 15,
    fontWeight: '700',
    color: '#ffffff',
  },
  paymentHistoryItemDate: {
    fontSize: 12,
    color: '#9CA3AF',
  },
  paymentHistoryItemSubtext: {
    fontSize: 11,
    color: '#6B7280',
  },
  paymentHistoryItemPenalty: {
    fontSize: 11,
    color: '#FF6B6B',
    fontWeight: '500',
  },
  paymentHistoryItemRejection: {
    fontSize: 11,
    color: '#FF6B6B',
    fontStyle: 'italic',
    marginTop: 4,
  },
  actionsSection: {
    padding: 20,
    paddingTop: 0,
  },
  actionButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    backgroundColor: '#4EFA8A',
    paddingVertical: 16,
    borderRadius: 12,
  },
  actionButtonText: {
    color: '#000',
    fontSize: 16,
    fontWeight: '700',
  },
  demandLetterButton: {
    marginTop: 12,
    backgroundColor: '#f97316',
  },
  demandLetterWebView: {
    flex: 1,
    backgroundColor: '#1a1a2e',
  },
  receiptModalContainer: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.8)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  receiptModalContent: {
    width: '90%',
    height: '80%',
    backgroundColor: '#1a1a2e',
    borderRadius: 12,
    overflow: 'hidden',
  },
  receiptModalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 16,
    borderBottomWidth: 1,
    borderBottomColor: 'rgba(255, 255, 255, 0.1)',
  },
  receiptModalTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: '#ffffff',
  },
  receiptModalCloseButton: {
    padding: 4,
  },
  receiptModalImage: {
    width: '100%',
    height: '100%',
    resizeMode: 'contain',
  },
});

export default LoanDetailsScreen;

