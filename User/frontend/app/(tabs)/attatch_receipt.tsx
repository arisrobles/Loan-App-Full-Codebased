import { Ionicons } from '@expo/vector-icons';
import * as ImagePicker from 'expo-image-picker';
import { LinearGradient } from 'expo-linear-gradient';
import { useRouter } from 'expo-router';
import React, { useEffect, useRef, useState } from 'react';
import {
    ActivityIndicator,
    Alert,
    Animated,
    SafeAreaView,
    StyleSheet,
    Text,
    TextInput,
    TouchableOpacity,
    View,
    ScrollView,
    Modal
} from 'react-native';
import { api } from '../../src/config/api';

interface Loan {
  id: string;
  reference: string;
  status: string;
  principalAmount: string;
  repayments?: {
    id: string;
    dueDate: string;
    amountDue: string;
    amountPaid: string;
    penaltyApplied: string;
  }[];
}

interface Repayment {
  id: string;
  dueDate: string;
  amountDue: string;
  amountPaid: string;
  penaltyApplied: string;
  paidAt?: string;
}

interface PaymentStatus {
  hasPending: boolean;
  hasApproved: boolean;
  totalApprovedAmount: number;
  isFullyPaid: boolean;
}

export default function AttachReceiptScreen() {
  const [image, setImage] = useState<string | null>(null);
  const [hasGalleryPermission, setHasGalleryPermission] = useState<boolean | null>(null);
  const [hasCameraPermission, setHasCameraPermission] = useState<boolean | null>(null);
  const [loading, setLoading] = useState(false);
  const [loans, setLoans] = useState<Loan[]>([]);
  const [selectedLoanId, setSelectedLoanId] = useState<string>('');
  const [selectedLoan, setSelectedLoan] = useState<Loan | null>(null);
  const [loadingLoans, setLoadingLoans] = useState(true);
  const [showLoanPicker, setShowLoanPicker] = useState(false);
  const [repayments, setRepayments] = useState<Repayment[]>([]);
  const [repaymentStatuses, setRepaymentStatuses] = useState<Map<string, PaymentStatus>>(new Map());
  const [selectedRepaymentId, setSelectedRepaymentId] = useState<string>('');
  const [showRepaymentPicker, setShowRepaymentPicker] = useState(false);
  const [paymentAmount, setPaymentAmount] = useState<string>('');
  const fadeAnim = useRef(new Animated.Value(0)).current;
  const router = useRouter();

  useEffect(() => {
    (async () => {
      const galleryStatus = await ImagePicker.requestMediaLibraryPermissionsAsync();
      setHasGalleryPermission(galleryStatus.status === 'granted');

      const cameraStatus = await ImagePicker.requestCameraPermissionsAsync();
      setHasCameraPermission(cameraStatus.status === 'granted');
    })();
    fetchDisbursedLoans();
  }, []);

  const fetchDisbursedLoans = async () => {
    try {
      setLoadingLoans(true);
      const res = await api.get('/loans');
      if (res.data?.loans) {
        // Filter only disbursed loans
        const disbursedLoans = res.data.loans.filter(
          (loan: Loan) => loan.status === 'disbursed'
        );
        setLoans(disbursedLoans);
        // Auto-select if only one loan
        if (disbursedLoans.length === 1) {
          setSelectedLoanId(disbursedLoans[0].id);
          setSelectedLoan(disbursedLoans[0]);
          await fetchRepayments(disbursedLoans[0].id);
        }
      }
    } catch (error) {
      console.error('Error fetching loans:', error);
      Alert.alert('Error', 'Failed to load loans. Please try again.');
    } finally {
      setLoadingLoans(false);
    }
  };

  const fetchRepayments = async (loanId: string) => {
    try {
      const [loanRes, paymentsRes] = await Promise.all([
        api.get(`/loans/${loanId}`),
        api.get(`/payments?loanId=${loanId}`)
      ]);

      if (loanRes.data?.data?.repayments) {
        const allRepayments = loanRes.data.data.repayments;
        
        // Get payment statuses for each repayment
        const statusMap = new Map<string, PaymentStatus>();
        const payments = paymentsRes.data?.payments || [];

        allRepayments.forEach((rep: Repayment) => {
          const repPayments = payments.filter((p: any) => p.repaymentId === rep.id);
          const pendingPayments = repPayments.filter((p: any) => p.status === 'pending');
          const approvedPayments = repPayments.filter((p: any) => p.status === 'approved');
          
          const totalApprovedAmount = approvedPayments.reduce((sum: number, p: any) => {
            return sum + parseFloat(p.amount);
          }, 0);

          const due = parseFloat(rep.amountDue);
          const paid = parseFloat(rep.amountPaid);
          const penalty = parseFloat(rep.penaltyApplied);
          const totalDue = due + penalty;
          const totalPaid = paid + totalApprovedAmount;
          const isFullyPaid = totalPaid >= totalDue;

          statusMap.set(rep.id, {
            hasPending: pendingPayments.length > 0,
            hasApproved: approvedPayments.length > 0,
            totalApprovedAmount,
            isFullyPaid,
          });
        });

        setRepaymentStatuses(statusMap);

        // Filter only unpaid or partially paid repayments (but show all for status visibility)
        const unpaidRepayments = allRepayments.filter((rep: Repayment) => {
          const status = statusMap.get(rep.id);
          if (!status) return true;
          
          // Show if not fully paid OR has pending payment
          const due = parseFloat(rep.amountDue);
          const paid = parseFloat(rep.amountPaid);
          const penalty = parseFloat(rep.penaltyApplied);
          const totalDue = due + penalty;
          const totalPaid = paid + status.totalApprovedAmount;
          
          return totalPaid < totalDue || status.hasPending;
        });
        
        setRepayments(unpaidRepayments);
      }
    } catch (error) {
      console.error('Error fetching repayments:', error);
    }
  };

  const handleImageSet = (uri: string) => {
    setImage(uri);
    fadeAnim.setValue(0);
    Animated.timing(fadeAnim, {
      toValue: 1,
      duration: 300,
      useNativeDriver: true,
    }).start();
  };

  const pickImage = async () => {
    if (!hasGalleryPermission) {
      return Alert.alert('Permission denied', 'Gallery access is required.');
    }

    const result = await ImagePicker.launchImageLibraryAsync({
      mediaTypes: ImagePicker.MediaTypeOptions.Images,
      quality: 0.5, // compressed
    });

    if (!result.canceled) {
      handleImageSet(result.assets[0].uri);
    }
  };

  const takePhoto = async () => {
    if (!hasCameraPermission) {
      return Alert.alert('Permission denied', 'Camera access is required.');
    }

    const result = await ImagePicker.launchCameraAsync({
      quality: 0.5,
    });

    if (!result.canceled) {
      handleImageSet(result.assets[0].uri);
    }
  };

  const handleSubmit = async () => {
    if (!image) {
      return Alert.alert('No receipt', 'Please upload or take a photo of the receipt.');
    }

    if (!selectedLoanId) {
      return Alert.alert('No loan selected', 'Please select a disbursed loan for this receipt.');
    }

    if (!selectedRepaymentId) {
      return Alert.alert('No month selected', 'Please select which repayment period you are paying for.');
    }

    if (!paymentAmount || parseFloat(paymentAmount) <= 0) {
      return Alert.alert('Invalid amount', 'Please enter a valid payment amount.');
    }

    try {
      setLoading(true);

      // Step 1: Upload receipt document
      const filename = image.split('/').pop() || 'receipt.jpg';
      const fileType = filename.split('.').pop() || 'jpg';
      const formData = new FormData();
      
      formData.append('file', {
        uri: image,
        name: filename,
        type: `image/${fileType}`,
      } as any);

      formData.append('documentType', 'RECEIPT');
      formData.append('loanId', selectedLoanId);

      const receiptRes = await api.post('/documents/upload', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });

      const receiptDocumentId = receiptRes.data?.data?.id;

      if (!receiptDocumentId) {
        throw new Error('Failed to get receipt document ID');
      }

      // Step 2: Submit payment with receipt
      await api.post('/payments', {
        loanId: selectedLoanId,
        repaymentId: selectedRepaymentId,
        amount: parseFloat(paymentAmount),
        receiptDocumentId: receiptDocumentId,
      });

      Alert.alert('✅ Success', 'Payment submitted successfully! It is pending admin approval.', [
        {
          text: 'OK',
          onPress: () => router.replace('/(tabs)'),
        },
      ]);
    } catch (err: any) {
      console.error('Submit error:', err);
      const message = err.response?.data?.message || 'Failed to submit payment';
      const errors = err.response?.data?.errors;
      
      if (errors && Array.isArray(errors)) {
        const errorMessages = errors.map((e: any) => e.message || e).join('\n');
        Alert.alert('Submission Failed', `${message}\n\n${errorMessages}`);
      } else {
        Alert.alert('Submission Failed', message);
      }
    } finally {
      setLoading(false);
    }
  };

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      month: 'long',
      year: 'numeric',
      day: 'numeric',
    });
  };

  const getSelectedRepayment = () => {
    return repayments.find(rep => rep.id === selectedRepaymentId);
  };

  const calculateOutstanding = (repayment: Repayment) => {
    const due = parseFloat(repayment.amountDue);
    const paid = parseFloat(repayment.amountPaid);
    const penalty = parseFloat(repayment.penaltyApplied);
    return Math.max(0, (due + penalty) - paid);
  };

  return (
    <SafeAreaView style={styles.safeContainer}>
      <ScrollView contentContainerStyle={styles.container}>
        <Text style={styles.title}>Attach Receipt</Text>
        <Text style={styles.subtitle}>Upload or capture a photo of your payment receipt</Text>

        {/* Loan Selection */}
        {loadingLoans ? (
          <View style={styles.loadingContainer}>
            <ActivityIndicator size="small" color="#fff" />
            <Text style={styles.loadingText}>Loading loans...</Text>
          </View>
        ) : loans.length === 0 ? (
          <View style={styles.warningCard}>
            <Text style={styles.warningText}>
              ⚠️ No disbursed loans found. Payments can only be submitted for disbursed loans.
            </Text>
          </View>
        ) : (
          <>
          <View style={styles.loanSelector}>
            <Text style={styles.label}>Select Loan <Text style={styles.required}>*</Text></Text>
            <TouchableOpacity
              style={styles.pickerContainer}
              onPress={() => setShowLoanPicker(true)}
            >
              <Text style={styles.pickerText}>
                {selectedLoan
                  ? `${selectedLoan.reference} - ₱${parseFloat(selectedLoan.principalAmount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
                  : 'Select a loan...'}
              </Text>
              <Ionicons name="chevron-down" size={20} color="#9CA3AF" />
            </TouchableOpacity>
          </View>

            {/* Repayment Period Selection */}
            {selectedLoanId && (
              <View style={styles.loanSelector}>
                <Text style={styles.label}>Select Payment Month <Text style={styles.required}>*</Text></Text>
                <TouchableOpacity
                  style={styles.pickerContainer}
                  onPress={() => setShowRepaymentPicker(true)}
                  disabled={repayments.length === 0}
                >
                  <Text style={styles.pickerText}>
                    {selectedRepaymentId && getSelectedRepayment()
                      ? `${formatDate(getSelectedRepayment()!.dueDate)} - Outstanding: ₱${calculateOutstanding(getSelectedRepayment()!).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
                      : repayments.length === 0
                      ? 'Loading repayment periods...'
                      : 'Select payment month...'}
                  </Text>
                  <Ionicons name="chevron-down" size={20} color="#9CA3AF" />
                </TouchableOpacity>
                {selectedRepaymentId && getSelectedRepayment() && (() => {
                  const status = repaymentStatuses.get(selectedRepaymentId);
                  const hasApproved = status?.hasApproved || false;
                  const totalApproved = status?.totalApprovedAmount || 0;
                  const hasPending = status?.hasPending || false;
                  
                  return (
                    <View style={styles.helperContainer}>
                      <Text style={styles.helperText}>
                        Amount Due: ₱{parseFloat(getSelectedRepayment()!.amountDue).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                        {parseFloat(getSelectedRepayment()!.penaltyApplied) > 0 && (
                          <Text style={styles.penaltyText}>
                            {' '}• Penalty: ₱{parseFloat(getSelectedRepayment()!.penaltyApplied).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                          </Text>
                        )}
                      </Text>
                      {hasApproved && totalApproved > 0 && (
                        <Text style={styles.confirmedText}>
                          ✓ Confirmed by Admin: ₱{totalApproved.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                        </Text>
                      )}
                      {hasPending && (
                        <Text style={styles.pendingText}>
                          ⏳ You have a pending payment for this month
                        </Text>
                      )}
                    </View>
                  );
                })()}
              </View>
            )}

            {/* Payment Amount Input */}
            {selectedRepaymentId && (
              <View style={styles.loanSelector}>
                <Text style={styles.label}>Payment Amount (₱) <Text style={styles.required}>*</Text></Text>
                <View style={styles.amountInputContainer}>
                  <Text style={styles.currencySymbol}>₱</Text>
                  <TextInput
                    style={styles.amountInput}
                    value={paymentAmount}
                    onChangeText={setPaymentAmount}
                    placeholder="0.00"
                    keyboardType="decimal-pad"
                    placeholderTextColor="#9CA3AF"
                  />
                </View>
                {selectedRepaymentId && getSelectedRepayment() && (
                  <Text style={styles.helperText}>
                    Outstanding: ₱{calculateOutstanding(getSelectedRepayment()!).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                  </Text>
                )}
              </View>
            )}
          </>
        )}

        {/* Loan Picker Modal */}
        <Modal
          visible={showLoanPicker}
          transparent={true}
          animationType="slide"
          onRequestClose={() => setShowLoanPicker(false)}
        >
          <View style={styles.modalOverlay}>
            <View style={styles.modalContent}>
              <View style={styles.modalHeader}>
                <Text style={styles.modalTitle}>Select Loan</Text>
                <TouchableOpacity onPress={() => setShowLoanPicker(false)}>
                  <Ionicons name="close" size={24} color="#fff" />
                </TouchableOpacity>
              </View>
              <ScrollView style={styles.modalList}>
                {loans.map((loan) => (
                  <TouchableOpacity
                    key={loan.id}
                    style={[
                      styles.loanOption,
                      selectedLoanId === loan.id && styles.loanOptionSelected
                    ]}
                    onPress={async () => {
                      setSelectedLoanId(loan.id);
                      setSelectedLoan(loan);
                      setShowLoanPicker(false);
                      setSelectedRepaymentId(''); // Reset repayment selection
                      await fetchRepayments(loan.id);
                    }}
                  >
                    <Text style={styles.loanOptionText}>
                      {loan.reference}
                    </Text>
                    <Text style={styles.loanOptionAmount}>
                      ₱{parseFloat(loan.principalAmount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                    </Text>
                    {selectedLoanId === loan.id && (
                      <Ionicons name="checkmark-circle" size={20} color="#22C55E" />
                    )}
                  </TouchableOpacity>
                ))}
              </ScrollView>
            </View>
          </View>
        </Modal>

        {/* Repayment Picker Modal */}
        <Modal
          visible={showRepaymentPicker}
          transparent={true}
          animationType="slide"
          onRequestClose={() => setShowRepaymentPicker(false)}
        >
          <View style={styles.modalOverlay}>
            <View style={styles.modalContent}>
              <View style={styles.modalHeader}>
                <Text style={styles.modalTitle}>Select Payment Month</Text>
                <TouchableOpacity onPress={() => setShowRepaymentPicker(false)}>
                  <Ionicons name="close" size={24} color="#fff" />
                </TouchableOpacity>
              </View>
              <ScrollView style={styles.modalList}>
                {repayments.length === 0 ? (
                  <View style={styles.emptyRepayments}>
                    <Text style={styles.emptyRepaymentsText}>
                      No unpaid repayment periods available
                    </Text>
                  </View>
                ) : (
                  repayments.map((repayment) => {
                    const outstanding = calculateOutstanding(repayment);
                    const isSelected = selectedRepaymentId === repayment.id;
                    const status = repaymentStatuses.get(repayment.id);
                    const hasPending = status?.hasPending || false;
                    const hasApproved = status?.hasApproved || false;
                    const isFullyPaid = status?.isFullyPaid || false;
                    const totalApproved = status?.totalApprovedAmount || 0;
                    
                    // Calculate remaining after approved payments
                    const due = parseFloat(repayment.amountDue);
                    const paid = parseFloat(repayment.amountPaid);
                    const penalty = parseFloat(repayment.penaltyApplied);
                    const totalDue = due + penalty;
                    const remainingAfterApproved = Math.max(0, totalDue - paid - totalApproved);
                    
                    return (
                      <TouchableOpacity
                        key={repayment.id}
                        style={[
                          styles.repaymentOption,
                          isSelected && styles.repaymentOptionSelected,
                          isFullyPaid && styles.repaymentOptionFullyPaid
                        ]}
                        onPress={() => {
                          if (isFullyPaid) return; // Disable if fully paid
                          setSelectedRepaymentId(repayment.id);
                          setShowRepaymentPicker(false);
                          // Auto-fill amount with remaining if not set
                          if (!paymentAmount && remainingAfterApproved > 0) {
                            setPaymentAmount(remainingAfterApproved.toFixed(2));
                          }
                        }}
                        disabled={isFullyPaid}
                      >
                        <View style={styles.repaymentOptionContent}>
                          <View style={styles.repaymentOptionHeader}>
                            <Text style={[
                              styles.repaymentOptionDate,
                              isFullyPaid && styles.repaymentOptionDateFullyPaid
                            ]}>
                              {formatDate(repayment.dueDate)}
                            </Text>
                            <View style={styles.statusBadges}>
                              {isFullyPaid && (
                                <View style={styles.statusBadgePaid}>
                                  <Ionicons name="checkmark-circle" size={14} color="#22C55E" />
                                  <Text style={styles.statusBadgeTextPaid}>Paid</Text>
                                </View>
                              )}
                              {!isFullyPaid && hasApproved && (
                                <View style={styles.statusBadgePartial}>
                                  <Ionicons name="time" size={14} color="#F59E0B" />
                                  <Text style={styles.statusBadgeTextPartial}>Partially Confirmed</Text>
                                </View>
                              )}
                              {hasPending && (
                                <View style={styles.statusBadgePending}>
                                  <Ionicons name="hourglass" size={14} color="#3B82F6" />
                                  <Text style={styles.statusBadgeTextPending}>Pending</Text>
                                </View>
                              )}
                            </View>
                          </View>
                          <View style={styles.repaymentOptionDetails}>
                            <Text style={styles.repaymentOptionAmount}>
                              Due: ₱{parseFloat(repayment.amountDue).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                            </Text>
                            {hasApproved && totalApproved > 0 && (
                              <Text style={styles.repaymentOptionApproved}>
                                ✓ Confirmed: ₱{totalApproved.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                              </Text>
                            )}
                            {!isFullyPaid && (
                              <Text style={styles.repaymentOptionOutstanding}>
                                Remaining: ₱{remainingAfterApproved.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                              </Text>
                            )}
                            {parseFloat(repayment.penaltyApplied) > 0 && (
                              <Text style={styles.repaymentOptionPenalty}>
                                Penalty: ₱{parseFloat(repayment.penaltyApplied).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                              </Text>
                            )}
                          </View>
                        </View>
                        {isSelected && !isFullyPaid && (
                          <Ionicons name="checkmark-circle" size={20} color="#22C55E" />
                        )}
                        {isFullyPaid && (
                          <Ionicons name="lock-closed" size={20} color="#9CA3AF" />
                        )}
                      </TouchableOpacity>
                    );
                  })
                )}
              </ScrollView>
            </View>
          </View>
        </Modal>

        <View style={styles.card}>
          {image ? (
            <Animated.Image source={{ uri: image }} style={[styles.preview, { opacity: fadeAnim }]} />
          ) : (
            <View style={styles.placeholder}>
              <Ionicons name="camera" size={50} color="#9CA3AF" />
              <Text style={styles.placeholderText}>No image selected</Text>
            </View>
          )}
        </View>

        <TouchableOpacity
          onPress={pickImage}
          activeOpacity={0.85}
          disabled={!hasGalleryPermission}
          style={{ width: '100%', marginBottom: 12 }}
        >
          <LinearGradient
            colors={["#03042c", "#302b63", "#24243e"]}
            start={{ x: 0, y: 0 }}
            end={{ x: 1, y: 0 }}
            style={styles.gradientButton}
          >
            <Text style={styles.gradientButtonText}>Choose from Gallery</Text>
          </LinearGradient>
        </TouchableOpacity>

        <TouchableOpacity
          onPress={takePhoto}
          activeOpacity={0.85}
          disabled={!hasCameraPermission}
          style={{ width: '100%', marginBottom: 12 }}
        >
          <LinearGradient
         colors={["#03042c", "#302b63", "#24243e"]}
            start={{ x: 0, y: 0 }}
            end={{ x: 1, y: 0 }}
            style={styles.gradientButton}
          >
            <Text style={styles.gradientButtonText}>Take Photo</Text>
          </LinearGradient>
        </TouchableOpacity>

        <TouchableOpacity
          onPress={handleSubmit}
          activeOpacity={0.85}
          style={{ width: '100%' }}
          disabled={loading}
        >
          <LinearGradient
            colors={["#03042c", "#302b63", "#24243e"]}
            start={{ x: 0, y: 0 }}
            end={{ x: 1, y: 0 }}
            style={styles.gradientButton}
          >
            {loading ? (
              <ActivityIndicator color="#fff" />
            ) : (
              <Text style={styles.gradientButtonText}>Submit</Text>
            )}
          </LinearGradient>
        </TouchableOpacity>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeContainer: { flex: 1, backgroundColor: 'black' },
  container: { flex: 1, padding: 24, alignItems: 'center' },
  title: { fontSize: 24, fontWeight: 'bold', color: '#fff', marginBottom: 8 },
  subtitle: { fontSize: 16, color: '#9CA3AF', textAlign: 'center', marginBottom: 24 },
  card: {
    width: '100%',
    height: 200,
    borderWidth: 1,
    borderColor: '#374151',
    borderRadius: 12,
    marginBottom: 20,
    justifyContent: 'center',
    alignItems: 'center',
    overflow: 'hidden',
    backgroundColor: '#1C2233',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.25,
    shadowRadius: 3.84,
    elevation: 5,
  },
  preview: { width: '100%', height: '100%', resizeMode: 'cover' },
  placeholder: { justifyContent: 'center', alignItems: 'center', flex: 1 },
  placeholderText: { color: '#9CA3AF', fontSize: 16, marginTop: 8 },
  gradientButton: {
    paddingVertical: 14,
    borderRadius: 12,
    alignItems: 'center',
    justifyContent: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.3,
    shadowRadius: 3,
    elevation: 3,
  },
  gradientButtonText: { color: '#fff', fontWeight: '600', fontSize: 16 },
  loanSelector: {
    width: '100%',
    marginBottom: 20,
  },
  label: {
    color: '#fff',
    fontSize: 14,
    fontWeight: '600',
    marginBottom: 8,
  },
  required: {
    color: '#FF6B6B',
  },
  pickerContainer: {
    width: '100%',
    backgroundColor: '#1C2233',
    borderRadius: 12,
    borderWidth: 1,
    borderColor: '#374151',
    padding: 16,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  pickerText: {
    color: '#fff',
    fontSize: 14,
    flex: 1,
  },
  loadingContainer: {
    width: '100%',
    padding: 20,
    alignItems: 'center',
    marginBottom: 20,
  },
  loadingText: {
    color: '#9CA3AF',
    marginTop: 8,
    fontSize: 14,
  },
  warningCard: {
    width: '100%',
    backgroundColor: '#FEF3C7',
    padding: 16,
    borderRadius: 12,
    marginBottom: 20,
    borderWidth: 1,
    borderColor: '#FCD34D',
  },
  warningText: {
    color: '#92400E',
    fontSize: 14,
    textAlign: 'center',
  },
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.7)',
    justifyContent: 'flex-end',
  },
  modalContent: {
    backgroundColor: '#1C2233',
    borderTopLeftRadius: 20,
    borderTopRightRadius: 20,
    maxHeight: '70%',
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 20,
    borderBottomWidth: 1,
    borderBottomColor: '#374151',
  },
  modalTitle: {
    color: '#fff',
    fontSize: 18,
    fontWeight: 'bold',
  },
  modalList: {
    maxHeight: 400,
  },
  loanOption: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 16,
    borderBottomWidth: 1,
    borderBottomColor: '#374151',
  },
  loanOptionSelected: {
    backgroundColor: '#374151',
  },
  loanOptionText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '600',
    flex: 1,
  },
  loanOptionAmount: {
    color: '#9CA3AF',
    fontSize: 14,
    marginRight: 8,
  },
  helperContainer: {
    marginTop: 6,
    gap: 4,
  },
  helperText: {
    color: '#9CA3AF',
    fontSize: 12,
  },
  penaltyText: {
    color: '#FF6B6B',
  },
  confirmedText: {
    color: '#22C55E',
    fontSize: 12,
    fontWeight: '600',
  },
  pendingText: {
    color: '#3B82F6',
    fontSize: 12,
    fontWeight: '500',
  },
  amountInputContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#1C2233',
    borderRadius: 12,
    borderWidth: 1,
    borderColor: '#374151',
    paddingHorizontal: 16,
  },
  currencySymbol: {
    color: '#fff',
    fontSize: 18,
    fontWeight: '600',
    marginRight: 8,
  },
  amountInput: {
    flex: 1,
    color: '#fff',
    fontSize: 18,
    paddingVertical: 16,
  },
  repaymentOption: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 16,
    borderBottomWidth: 1,
    borderBottomColor: '#374151',
  },
  repaymentOptionSelected: {
    backgroundColor: '#374151',
  },
  repaymentOptionContent: {
    flex: 1,
  },
  repaymentOptionDate: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '600',
    marginBottom: 6,
  },
  repaymentOptionDetails: {
    gap: 4,
  },
  repaymentOptionAmount: {
    color: '#9CA3AF',
    fontSize: 13,
  },
  repaymentOptionOutstanding: {
    color: '#4EFA8A',
    fontSize: 13,
    fontWeight: '600',
  },
  repaymentOptionPenalty: {
    color: '#FF6B6B',
    fontSize: 12,
  },
  repaymentOptionApproved: {
    color: '#22C55E',
    fontSize: 13,
    fontWeight: '600',
  },
  repaymentOptionFullyPaid: {
    opacity: 0.6,
    backgroundColor: '#1C2233',
  },
  repaymentOptionDateFullyPaid: {
    color: '#9CA3AF',
    textDecorationLine: 'line-through',
  },
  repaymentOptionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: 6,
  },
  statusBadges: {
    flexDirection: 'row',
    gap: 6,
    flexWrap: 'wrap',
  },
  statusBadgePaid: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    backgroundColor: 'rgba(34, 197, 94, 0.2)',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 8,
  },
  statusBadgeTextPaid: {
    color: '#22C55E',
    fontSize: 11,
    fontWeight: '600',
  },
  statusBadgePartial: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    backgroundColor: 'rgba(245, 158, 11, 0.2)',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 8,
  },
  statusBadgeTextPartial: {
    color: '#F59E0B',
    fontSize: 11,
    fontWeight: '600',
  },
  statusBadgePending: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    backgroundColor: 'rgba(59, 130, 246, 0.2)',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 8,
  },
  statusBadgeTextPending: {
    color: '#3B82F6',
    fontSize: 11,
    fontWeight: '600',
  },
  emptyRepayments: {
    padding: 20,
    alignItems: 'center',
  },
  emptyRepaymentsText: {
    color: '#9CA3AF',
    fontSize: 14,
    textAlign: 'center',
  },
});
