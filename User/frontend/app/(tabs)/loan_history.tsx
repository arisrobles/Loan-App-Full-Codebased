import React, { useEffect, useState } from "react";
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
  Alert,
} from "react-native";
import { LinearGradient } from "expo-linear-gradient";
import { Ionicons } from "@expo/vector-icons";
import { useRouter } from "expo-router";
import { api } from "../../src/config/api";

interface Payment {
  id: string;
  amount: string; // Backend returns 'amount', not 'amountPaid'
  paidAt?: string;
  loanId: string;
  loanReference?: string; // Backend returns 'loanReference', not nested 'loan.reference'
  dueDate?: string;
  penaltyApplied?: string;
}

interface Receipt {
  id: string;
  fileName: string;
  fileUrl: string;
  uploadedAt: string;
  loanId?: string;
  loanReference?: string;
}

export default function LoanHistoryScreen() {
  const router = useRouter();
  const [payments, setPayments] = useState<Payment[]>([]);
  const [loans, setLoans] = useState<any[]>([]);
  const [receipts, setReceipts] = useState<Receipt[]>([]);
  const [loading, setLoading] = useState(true);
  const [selectedReceipt, setSelectedReceipt] = useState<Receipt | null>(null);
  const [imageModalVisible, setImageModalVisible] = useState(false);
  const [cancellingLoanId, setCancellingLoanId] = useState<string | null>(null);

  useEffect(() => {
    fetchHistory();
  }, []);

  const fetchHistory = async () => {
    try {
      const [paymentsRes, loansRes, receiptsRes] = await Promise.all([
        api.get("/payments"),
        api.get("/loans"),
        api.get("/documents"),
      ]);

      if (paymentsRes.data?.payments) {
        setPayments(paymentsRes.data.payments);
      }

      if (loansRes.data?.loans) {
        setLoans(loansRes.data.loans);
      }

      if (receiptsRes.data?.data) {
        // Filter only RECEIPT type documents
        const receiptDocs = receiptsRes.data.data.filter(
          (doc: any) => doc.documentType === 'RECEIPT'
        );
        setReceipts(receiptDocs);
      }
    } catch (error) {
      console.error("Error fetching history:", error);
    } finally {
      setLoading(false);
    }
  };

  const getReceiptImageUrl = (fileUrl: string) => {
    // Construct full URL - fileUrl is like "/uploads/filename.jpg"
    // Need to prepend the API base URL
    // Extract base URL from api instance
    const baseURL = api.defaults.baseURL?.replace('/api/v1', '') || 'http://192.168.8.107:8080';
    return `${baseURL}${fileUrl}`;
  };

  const openReceiptModal = (receipt: Receipt) => {
    setSelectedReceipt(receipt);
    setImageModalVisible(true);
  };

  const formatDate = (dateString?: string) => {
    if (!dateString) return "N/A";
    return new Date(dateString).toLocaleDateString("en-US", {
      month: "short",
      day: "numeric",
      year: "numeric",
    });
  };

  const handleCancelLoan = (loanId: string, loanReference: string) => {
    Alert.alert(
      "Cancel Loan Application",
      `Are you sure you want to cancel loan application ${loanReference}? This action cannot be undone.`,
      [
        {
          text: "No",
          style: "cancel",
        },
        {
          text: "Yes, Cancel",
          style: "destructive",
          onPress: async () => {
            try {
              setCancellingLoanId(loanId);
              const res = await api.patch(`/loans/${loanId}/cancel`);
              if (res.data?.success) {
                Alert.alert("✅ Success", "Loan application cancelled successfully", [
                  {
                    text: "OK",
                    onPress: () => {
                      fetchHistory(); // Refresh the list
                    },
                  },
                ]);
              }
            } catch (error: any) {
              console.error("Cancel loan error:", error);
              const message = error.response?.data?.message || "Failed to cancel loan application";
              Alert.alert("Error", message);
            } finally {
              setCancellingLoanId(null);
            }
          },
        },
      ]
    );
  };

  const canCancelLoan = (status: string) => {
    return status === "new_application" || status === "under_review";
  };

  if (loading) {
    return (
      <SafeAreaView style={styles.container}>
        <View style={{ flex: 1, justifyContent: "center", alignItems: "center" }}>
          <ActivityIndicator size="large" color="#FF6B6B" />
          <Text style={{ color: "#fff", marginTop: 16 }}>Loading history...</Text>
        </View>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.container}>
      <ScrollView contentContainerStyle={styles.scrollContent}>
        <Text style={styles.title}>Loan History</Text>
        <Text style={styles.subtitle}>Your transaction and loan history</Text>

        {/* Loans Section */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Your Loans</Text>
          {loans.length > 0 ? (
            loans.map((loan) => (
              <TouchableOpacity
                key={loan.id}
                onPress={() => router.push({
                  pathname: '/(tabs)/loan_details',
                  params: { loanId: loan.id }
                })}
                activeOpacity={0.8}
              >
                <LinearGradient
                colors={["#03042c", "#302b63", "#24243e"]}
                start={{ x: 0, y: 0 }}
                end={{ x: 1, y: 1 }}
                style={styles.card}
              >
                <View style={styles.cardHeader}>
                  <Text style={styles.cardTitle}>{loan.reference}</Text>
                    <View style={styles.cardHeaderRight}>
                  <Text
                    style={[
                      styles.statusBadge,
                      {
                        color:
                          loan.status === "approved" || loan.status === "disbursed"
                            ? "#4EFA8A"
                            : loan.status === "rejected" || loan.status === "cancelled"
                            ? "#FF6B6B"
                            : "#FFD700",
                      },
                    ]}
                  >
                    {loan.status === "cancelled" ? "Cancelled" : loan.status}
                  </Text>
                      <Ionicons name="chevron-forward" size={20} color="#9CA3AF" style={{ marginLeft: 8 }} />
                    </View>
                </View>
                <Text style={styles.cardAmount}>
                  ₱{parseFloat(loan.principalAmount || "0").toLocaleString("en-PH", {
                    minimumFractionDigits: 2,
                  })}
                </Text>
                <Text style={styles.cardDate}>
                  Applied: {formatDate(loan.applicationDate)}
                </Text>
                  {loan.status === "disbursed" && loan.repayments && (
                    <Text style={styles.repaymentInfo}>
                      {loan.repayments.filter((r: any) => parseFloat(r.amountPaid) >= parseFloat(r.amountDue)).length} / {loan.repayments.length} payments completed
                    </Text>
                  )}
                {canCancelLoan(loan.status) && (
                  <TouchableOpacity
                    style={styles.cancelButton}
                      onPress={(e) => {
                        e.stopPropagation();
                        handleCancelLoan(loan.id, loan.reference);
                      }}
                    disabled={cancellingLoanId === loan.id}
                  >
                    {cancellingLoanId === loan.id ? (
                      <ActivityIndicator size="small" color="#FF6B6B" />
                    ) : (
                      <>
                        <Ionicons name="close-circle-outline" size={18} color="#FF6B6B" />
                        <Text style={styles.cancelButtonText}>Cancel Application</Text>
                      </>
                    )}
                  </TouchableOpacity>
                )}
              </LinearGradient>
              </TouchableOpacity>
            ))
          ) : (
            <Text style={styles.emptyText}>No loans yet</Text>
          )}
        </View>

        {/* Payments Section */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Payment History</Text>
          {payments.length > 0 ? (
            payments.map((payment) => (
              <View key={payment.id} style={styles.paymentCard}>
                <View style={styles.paymentRow}>
                  <Ionicons name="checkmark-circle" size={24} color="#4EFA8A" />
                  <View style={styles.paymentInfo}>
                    <Text style={styles.paymentLabel}>
                      Payment for Loan {payment.loanReference || payment.loanId}
                    </Text>
                    <Text style={styles.paymentDate}>
                      {payment.paidAt ? formatDate(payment.paidAt) : formatDate(payment.dueDate)}
                    </Text>
                  </View>
                  <Text style={styles.paymentAmount}>
                    ₱{parseFloat(payment.amount || "0").toLocaleString("en-PH", {
                      minimumFractionDigits: 2,
                    })}
                  </Text>
                </View>
              </View>
            ))
          ) : (
            <Text style={styles.emptyText}>No payments yet</Text>
          )}
        </View>

        {/* Receipts Section */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Uploaded Receipts</Text>
          {receipts.length > 0 ? (
            receipts.map((receipt) => (
              <TouchableOpacity
                key={receipt.id}
                onPress={() => openReceiptModal(receipt)}
                style={styles.receiptCard}
              >
                <View style={styles.receiptRow}>
                  <Ionicons name="receipt" size={24} color="#FFD700" />
                  <View style={styles.receiptInfo}>
                    <Text style={styles.receiptLabel}>{receipt.fileName}</Text>
                    <Text style={styles.receiptDate}>
                      {receipt.loanReference ? `Loan: ${receipt.loanReference} • ` : ''}
                      {formatDate(receipt.uploadedAt)}
                    </Text>
                  </View>
                  <Ionicons name="chevron-forward" size={20} color="#9CA3AF" />
                </View>
              </TouchableOpacity>
            ))
          ) : (
            <Text style={styles.emptyText}>No receipts uploaded yet</Text>
          )}
        </View>
      </ScrollView>

      {/* Receipt Image Modal */}
      <Modal
        visible={imageModalVisible}
        transparent={true}
        animationType="fade"
        onRequestClose={() => setImageModalVisible(false)}
      >
        <View style={styles.modalContainer}>
          <View style={styles.modalContent}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>
                {selectedReceipt?.fileName || 'Receipt'}
              </Text>
              <TouchableOpacity
                onPress={() => setImageModalVisible(false)}
                style={styles.closeButton}
              >
                <Ionicons name="close" size={28} color="#fff" />
              </TouchableOpacity>
            </View>
            {selectedReceipt && (
              <Image
                source={{ uri: getReceiptImageUrl(selectedReceipt.fileUrl) }}
                style={styles.modalImage}
                resizeMode="contain"
              />
            )}
            <View style={styles.modalFooter}>
              <Text style={styles.modalFooterText}>
                {selectedReceipt?.loanReference
                  ? `Loan: ${selectedReceipt.loanReference}`
                  : 'General Receipt'}
              </Text>
              <Text style={styles.modalFooterText}>
                Uploaded: {selectedReceipt ? formatDate(selectedReceipt.uploadedAt) : 'N/A'}
              </Text>
            </View>
          </View>
        </View>
      </Modal>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: "black",
  },
  scrollContent: {
    padding: 20,
    paddingBottom: 40,
  },
  title: {
    fontSize: 28,
    fontWeight: "700",
    color: "#fff",
    marginBottom: 8,
    marginTop: 20,
  },
  subtitle: {
    fontSize: 14,
    color: "#9CA3AF",
    marginBottom: 24,
  },
  section: {
    marginBottom: 32,
  },
  sectionTitle: {
    fontSize: 20,
    fontWeight: "600",
    color: "#fff",
    marginBottom: 16,
  },
  card: {
    borderRadius: 16,
    padding: 20,
    marginBottom: 12,
    shadowColor: "#000",
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.3,
    shadowRadius: 6,
    elevation: 4,
  },
  cardHeader: {
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "center",
    marginBottom: 8,
  },
  cardHeaderRight: {
    flexDirection: "row",
    alignItems: "center",
  },
  cardTitle: {
    fontSize: 18,
    fontWeight: "600",
    color: "#fff",
  },
  statusBadge: {
    fontSize: 12,
    fontWeight: "600",
    textTransform: "capitalize",
  },
  cardAmount: {
    fontSize: 24,
    fontWeight: "700",
    color: "#fff",
    marginTop: 8,
  },
  cardDate: {
    fontSize: 14,
    color: "#9CA3AF",
    marginTop: 8,
  },
  repaymentInfo: {
    fontSize: 13,
    color: "#4EFA8A",
    marginTop: 8,
    fontWeight: "500",
  },
  cancelButton: {
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "center",
    marginTop: 12,
    paddingVertical: 10,
    paddingHorizontal: 16,
    backgroundColor: "rgba(255, 107, 107, 0.15)",
    borderRadius: 8,
    borderWidth: 1,
    borderColor: "rgba(255, 107, 107, 0.3)",
  },
  cancelButtonText: {
    color: "#FF6B6B",
    fontSize: 14,
    fontWeight: "600",
    marginLeft: 6,
  },
  paymentCard: {
    backgroundColor: "rgba(255, 255, 255, 0.06)",
    borderRadius: 12,
    padding: 16,
    marginBottom: 12,
  },
  paymentRow: {
    flexDirection: "row",
    alignItems: "center",
  },
  paymentInfo: {
    flex: 1,
    marginLeft: 12,
  },
  paymentLabel: {
    fontSize: 16,
    fontWeight: "500",
    color: "#fff",
  },
  paymentDate: {
    fontSize: 12,
    color: "#9CA3AF",
    marginTop: 4,
  },
  paymentAmount: {
    fontSize: 18,
    fontWeight: "700",
    color: "#4EFA8A",
  },
  emptyText: {
    color: "#9CA3AF",
    textAlign: "center",
    padding: 20,
    fontSize: 16,
  },
  receiptCard: {
    backgroundColor: "rgba(255, 255, 255, 0.06)",
    borderRadius: 12,
    padding: 16,
    marginBottom: 12,
  },
  receiptRow: {
    flexDirection: "row",
    alignItems: "center",
  },
  receiptInfo: {
    flex: 1,
    marginLeft: 12,
  },
  receiptLabel: {
    fontSize: 16,
    fontWeight: "500",
    color: "#fff",
  },
  receiptDate: {
    fontSize: 12,
    color: "#9CA3AF",
    marginTop: 4,
  },
  modalContainer: {
    flex: 1,
    backgroundColor: "rgba(0, 0, 0, 0.9)",
    justifyContent: "center",
    alignItems: "center",
  },
  modalContent: {
    width: "90%",
    maxHeight: "90%",
    backgroundColor: "#1A1A2E",
    borderRadius: 16,
    padding: 20,
  },
  modalHeader: {
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "center",
    marginBottom: 16,
  },
  modalTitle: {
    fontSize: 18,
    fontWeight: "600",
    color: "#fff",
    flex: 1,
  },
  closeButton: {
    padding: 4,
  },
  modalImage: {
    width: "100%",
    height: 400,
    borderRadius: 12,
    backgroundColor: "#000",
  },
  modalFooter: {
    marginTop: 16,
    paddingTop: 16,
    borderTopWidth: 1,
    borderTopColor: "rgba(255, 255, 255, 0.1)",
  },
  modalFooterText: {
    fontSize: 14,
    color: "#9CA3AF",
    marginBottom: 4,
  },
});
