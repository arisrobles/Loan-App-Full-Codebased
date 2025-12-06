import { Ionicons, FontAwesome5 } from "@expo/vector-icons";
import { LinearGradient } from "expo-linear-gradient";
import { useRouter } from "expo-router";
import { StatusBar } from "expo-status-bar";
import React, { useEffect, useState } from "react";
import {
    ActivityIndicator,
    Dimensions,
    RefreshControl,
    SafeAreaView,
    ScrollView,
    StyleSheet,
    Text,
    TouchableOpacity,
    View,
} from "react-native";
import { LineChart } from "react-native-chart-kit";
import { api } from "../../src/config/api";

interface Loan {
  id: string;
  reference: string;
  principalAmount: string;
  totalPaid: string;
  status: string;
  maturityDate?: string;
  applicationDate?: string;
  repayments?: {
    id: string;
    dueDate: string;
    amountDue: string;
    amountPaid: string;
    paidAt?: string;
  }[];
}

interface UserProfile {
  fullName: string;
  email: string;
}

const Dashboard = () => {
  const router = useRouter();
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [user, setUser] = useState<UserProfile>({ fullName: "User", email: "" });
  const [loans, setLoans] = useState<Loan[]>([]);
  const [payments, setPayments] = useState<any[]>([]);
  const [loanBalance, setLoanBalance] = useState("0.00");
  const [nextDue, setNextDue] = useState<string | null>(null);
  const [notificationCount, setNotificationCount] = useState(0);
  const [loanBreakdown, setLoanBreakdown] = useState({
    totalPrincipal: "0.00",
    totalInterest: "0.00",
    totalBalance: "0.00",
  });
  const [showBreakdown, setShowBreakdown] = useState(false);
  const [analyticsData, setAnalyticsData] = useState<{ labels: string[]; data: number[] }>({
    labels: [],
    data: [],
  });

  const fetchDashboardData = async () => {
    try {
      const [profileRes, loansRes, paymentsRes, notificationsRes] = await Promise.all([
        api.get("/users/profile"),
        api.get("/loans"),
        api.get("/payments").catch(() => ({ data: { payments: [] } })), // Fetch payments, but don't fail if endpoint doesn't exist
        api.get("/notifications").catch(() => ({ data: { unreadCount: 0 } })), // Fetch notifications, but don't fail if endpoint doesn't exist
      ]);

      if (profileRes.data) {
        setUser({
          fullName: profileRes.data.fullName || "User",
          email: profileRes.data.email || "",
        });
      }

      // Store payments for status checking
      if (paymentsRes.data?.payments) {
        setPayments(paymentsRes.data.payments);
      }

      if (loansRes.data?.loans) {
        const activeLoans = loansRes.data.loans.filter(
          (loan: Loan) => loan.status !== "closed" && loan.status !== "rejected"
        );

        setLoans(activeLoans);

        // Calculate loan breakdown: Principal, Interest, and Total Balance
        let totalPrincipal = 0;
        let totalInterest = 0;
        let totalBalance = 0;

        activeLoans.forEach((loan: Loan) => {
          const principal = parseFloat(loan.principalAmount || "0");
          totalPrincipal += principal;

          if (loan.repayments && Array.isArray(loan.repayments)) {
            // Calculate from repayment schedule
            let loanTotalDue = 0; // Total amount due (principal + interest)
            let loanBalanceRemaining = 0; // Remaining balance

            loan.repayments.forEach((repayment: any) => {
              const amountDue = parseFloat(repayment.amountDue || "0");
              const amountPaid = parseFloat(repayment.amountPaid || "0");
              
              loanTotalDue += amountDue;
              
              const remaining = amountDue - amountPaid;
              if (remaining > 0) {
                loanBalanceRemaining += remaining;
              }
            });

            // Interest = Total Due - Principal
            const loanInterest = loanTotalDue - principal;
            totalInterest += loanInterest;
            totalBalance += loanBalanceRemaining;
          } else {
            // Fallback: if repayments not loaded
            const paid = parseFloat(loan.totalPaid || "0");
            // Estimate: assume 24% interest rate if not available
            const estimatedTotal = principal * 1.24; // Rough estimate
            const estimatedInterest = estimatedTotal - principal;
            totalInterest += estimatedInterest;
            totalBalance += Math.max(0, principal - paid);
          }
        });

        setLoanBreakdown({
          totalPrincipal: totalPrincipal.toFixed(2),
          totalInterest: totalInterest.toFixed(2),
          totalBalance: totalBalance.toFixed(2),
        });
        setLoanBalance(totalBalance.toFixed(2));

        // Find next due date from active loans - check repayments for actual due dates
        const allDueDates: string[] = [];
        activeLoans.forEach((loan: any) => {
          if (loan.repayments && Array.isArray(loan.repayments)) {
            loan.repayments.forEach((rep: any) => {
              if (rep.dueDate && (!rep.paidAt || rep.amountPaid < rep.amountDue)) {
                allDueDates.push(rep.dueDate);
              }
            });
          }
        });
        allDueDates.sort();
        if (allDueDates.length > 0) {
          const nextDueDate = new Date(allDueDates[0]);
          setNextDue(
            nextDueDate.toLocaleDateString("en-US", {
              month: "short",
              day: "numeric",
              year: "numeric",
            })
          );
        } else {
          // Fallback to maturity date if no repayments
          const dueDates = activeLoans
            .map((loan: Loan) => loan.maturityDate)
            .filter(Boolean)
            .sort();
          if (dueDates.length > 0) {
            const nextDueDate = new Date(dueDates[0]);
            setNextDue(
              nextDueDate.toLocaleDateString("en-US", {
                month: "short",
                day: "numeric",
                year: "numeric",
              })
            );
          }
        }
      }

      // Process analytics data from payments
      if (paymentsRes.data?.payments && Array.isArray(paymentsRes.data.payments)) {
        const payments = paymentsRes.data.payments;
        
        // Get last 6 months of payment data
        const now = new Date();
        const monthlyData: { [key: string]: number } = {};
        const monthLabels: string[] = [];
        
        // Initialize last 6 months with 0
        for (let i = 5; i >= 0; i--) {
          const date = new Date(now.getFullYear(), now.getMonth() - i, 1);
          const monthKey = `${date.getFullYear()}-${date.getMonth()}`;
          const monthLabel = date.toLocaleDateString("en-US", { month: "short" });
          monthlyData[monthKey] = 0;
          monthLabels.push(monthLabel);
        }

        // Aggregate payments by month
        payments.forEach((payment: any) => {
          if (payment.paidAt) {
            const paidDate = new Date(payment.paidAt);
            const monthKey = `${paidDate.getFullYear()}-${paidDate.getMonth()}`;
            if (monthlyData[monthKey] !== undefined) {
              monthlyData[monthKey] += parseFloat(payment.amount || "0");
            }
          }
        });

        // Also check loan repayments if payments endpoint doesn't have enough data
        if (loansRes.data?.loans && payments.length === 0) {
          loansRes.data.loans.forEach((loan: any) => {
            if (loan.repayments && Array.isArray(loan.repayments)) {
              loan.repayments.forEach((rep: any) => {
                if (rep.paidAt && rep.amountPaid) {
                  const paidDate = new Date(rep.paidAt);
                  const monthKey = `${paidDate.getFullYear()}-${paidDate.getMonth()}`;
                  if (monthlyData[monthKey] !== undefined) {
                    monthlyData[monthKey] += parseFloat(rep.amountPaid || "0");
                  }
                }
              });
            }
          });
        }

        // Convert to arrays
        const dataValues = monthLabels.map((_, index) => {
          const keys = Object.keys(monthlyData);
          return monthlyData[keys[index]] || 0;
        });

        setAnalyticsData({
          labels: monthLabels,
          data: dataValues,
        });

        // Update notification count
        if (notificationsRes.data?.unreadCount !== undefined) {
          setNotificationCount(notificationsRes.data.unreadCount);
        }
      } else {
        // If no payments, show empty chart or use loan data
        const now = new Date();
        const monthLabels: string[] = [];
        for (let i = 5; i >= 0; i--) {
          const date = new Date(now.getFullYear(), now.getMonth() - i, 1);
          monthLabels.push(date.toLocaleDateString("en-US", { month: "short" }));
        }
        setAnalyticsData({
          labels: monthLabels,
          data: [0, 0, 0, 0, 0, 0],
        });
      }
    } catch (error: any) {
      console.error("Error fetching dashboard data:", error);
      // Set default analytics data on error
      const now = new Date();
      const monthLabels: string[] = [];
      for (let i = 5; i >= 0; i--) {
        const date = new Date(now.getFullYear(), now.getMonth() - i, 1);
        monthLabels.push(date.toLocaleDateString("en-US", { month: "short" }));
      }
      setAnalyticsData({
        labels: monthLabels,
        data: [0, 0, 0, 0, 0, 0],
      });
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    fetchDashboardData();
  }, []);

  const onRefresh = () => {
    setRefreshing(true);
    fetchDashboardData();
  };
  if (loading) {
    return (
      <SafeAreaView style={styles.container}>
        <View style={{ flex: 1, justifyContent: "center", alignItems: "center" }}>
          <ActivityIndicator size="large" color="#FF6B6B" />
          <Text style={{ color: "#fff", marginTop: 16 }}>Loading dashboard...</Text>
        </View>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar style="light" />
      <ScrollView
        showsVerticalScrollIndicator={false}
        contentContainerStyle={{ paddingBottom: 100 }}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor="#FF6B6B" />
        }
      >
        {/* Header */}
        <View style={styles.header}>
          <View>
            <Text style={styles.greeting}>Welcome Back 👋</Text>
            <Text style={styles.userName}>{user.fullName}</Text>
          </View>
          <View style={styles.headerButtons}>
            <TouchableOpacity
              onPress={() => router.push('./notif')}
              style={styles.notificationButtonWrapper}
            >
              <LinearGradient
                colors={["#2E026D", "#7B2CBF", "#FF6B6B"]}
                start={{ x: 0, y: 0 }}
                end={{ x: 1, y: 1 }}
                style={styles.notificationBtn}
              >
                <Ionicons name="notifications-outline" size={22} color="#fff" />
                {notificationCount > 0 && (
                  <View style={styles.notificationBadge}>
                    <Text style={styles.notificationBadgeText}>
                      {notificationCount > 99 ? '99+' : notificationCount}
                    </Text>
                  </View>
                )}
              </LinearGradient>
            </TouchableOpacity>
            <TouchableOpacity
              onPress={() => router.push('./user_profile')}
              style={{ marginLeft: 12 }}
            >
              <LinearGradient
                colors={["#03042c", "#302b63", "#24243e"]}
                start={{ x: 0, y: 0 }}
                end={{ x: 1, y: 1 }}
                style={styles.profileBtn}
              >
                <Ionicons name="person-outline" size={22} color="#fff" />
              </LinearGradient>
            </TouchableOpacity>
          </View>
        </View>

        {/* Balance Card */}
        <LinearGradient
          colors={["#0f0c29", "#302b63", "#24243e"]}
          start={{ x: 0, y: 0 }}
          end={{ x: 1, y: 1 }}
          style={styles.balanceCard}
        >
          <View style={styles.brandingRow}>
            <Text style={styles.branding}>MasterFunds</Text>
          </View>
          <View style={styles.topRow}>
            <Text style={styles.cardTitle}>Loan Balance</Text>
          </View>
          <Text style={styles.balanceAmount}>₱{parseFloat(loanBalance).toLocaleString("en-PH", { minimumFractionDigits: 2 })}</Text>
          <Text style={styles.nextDue}>
            {nextDue ? `Next Due: ${nextDue}` : "No pending payments"}
          </Text>

          {/* Loan Breakdown - Collapsible */}
          <TouchableOpacity
            style={styles.breakdownToggle}
            onPress={() => setShowBreakdown(!showBreakdown)}
            activeOpacity={0.7}
          >
            <Text style={styles.breakdownToggleText}>
              {showBreakdown ? "Hide Details" : "Show Breakdown"}
            </Text>
            <Ionicons
              name={showBreakdown ? "chevron-up" : "chevron-down"}
              size={18}
              color="rgba(255, 255, 255, 0.7)"
            />
          </TouchableOpacity>

          {showBreakdown && (
            <View style={styles.breakdownContainer}>
              <View style={styles.breakdownRow}>
                <Text style={styles.breakdownLabel}>Principal Amount</Text>
                <Text style={styles.breakdownValue}>
                  ₱{parseFloat(loanBreakdown.totalPrincipal).toLocaleString("en-PH", { minimumFractionDigits: 2 })}
                </Text>
              </View>
              <View style={styles.breakdownRow}>
                <Text style={styles.breakdownLabel}>Total Interest</Text>
                <Text style={styles.breakdownValue}>
                  ₱{parseFloat(loanBreakdown.totalInterest).toLocaleString("en-PH", { minimumFractionDigits: 2 })}
                </Text>
              </View>
              <View style={[styles.breakdownRow, styles.breakdownTotalRow]}>
                <Text style={styles.breakdownTotalLabel}>Total Balance</Text>
                <Text style={styles.breakdownTotalValue}>
                  ₱{parseFloat(loanBreakdown.totalBalance).toLocaleString("en-PH", { minimumFractionDigits: 2 })}
                </Text>
              </View>
            </View>
          )}
        </LinearGradient>

        {/* Action Buttons */}
        <View style={styles.actionsRow}>
          <ActionButton icon="wallet" label="Apply" />
          <ActionButton icon="credit-card" label="Payment" />
          <ActionButton icon="chart-line" label="Score" />
        </View>

        {/* Transactions */}
        <View style={styles.section}>
          <View style={styles.sectionHeader}>
            <Text style={styles.sectionTitle}>Recent Transactions</Text>
            <TouchableOpacity style={styles.seeAllButton} onPress={() => router.push('./loan_history')}>
              <Text style={styles.seeAllText}>See All</Text>
              <Ionicons
                name="chevron-forward"
                size={16}
                color="#FF8008"
                style={{ marginLeft: 4 }}
              />
            </TouchableOpacity>
          </View>

          <ScrollView style={styles.transactionsList} nestedScrollEnabled>
            {loans.length > 0 ? (
              loans.slice(0, 3).map((loan) => (
                <TransactionItem
                  key={loan.id}
                  label={`Loan ${loan.reference}`}
                  amount={`₱${parseFloat(loan.principalAmount).toLocaleString("en-PH")}`}
                  date={loan.status}
                  status={loan.status}
                  applicationDate={loan.applicationDate}
                />
              ))
            ) : (
              <Text style={{ color: "#999", textAlign: "center", padding: 20 }}>
                No loans yet
              </Text>
            )}
          </ScrollView>
        </View>

        {/* Analytics */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Loan Analytics</Text>
          <View style={styles.chartContainer}>
            {analyticsData.labels.length > 0 ? (
              <ScrollView horizontal showsHorizontalScrollIndicator={false}>
                <LineChart
                  data={{
                    labels: analyticsData.labels,
                    datasets: [{ data: analyticsData.data }],
                  }}
                  width={Math.max(Dimensions.get("window").width - 40, analyticsData.labels.length * 60)}
                  height={220}
                  yAxisLabel="₱"
                  chartConfig={{
                    backgroundColor: "transparent",
                    backgroundGradientFrom: "#0B0F1A",
                    backgroundGradientTo: "#1A1A2E",
                    decimalPlaces: 0,
                    color: () => "#FF6B6B",
                    labelColor: () => "#fff",
                    propsForDots: {
                      r: "5",
                      strokeWidth: "2",
                      stroke: "#7B2CBF",
                    },
                  }}
                  bezier
                  style={styles.chartStyle}
                />
              </ScrollView>
            ) : (
              <View style={{ padding: 20, alignItems: "center" }}>
                <Text style={{ color: "#999", textAlign: "center" }}>
                  No payment data available yet
                </Text>
              </View>
            )}
          </View>
        </View>

        {/* Upcoming Payments */}
        <View style={styles.section}>
          <View style={styles.sectionHeader}>
            <Text style={styles.sectionTitle}>Upcoming Payments</Text>
            <TouchableOpacity style={styles.seeAllButton} onPress={() => router.push('./loan_history')}>
              <Text style={styles.seeAllText}>View All</Text>
              <Ionicons
                name="chevron-forward"
                size={16}
                color="#FF8008"
                style={{ marginLeft: 4 }}
              />
            </TouchableOpacity>
          </View>
          
          {(() => {
            // Collect all upcoming repayments from all active loans with payment status
            const upcomingPayments: {
              loanReference: string;
              dueDate: string;
              amountDue: string;
              loanId: string;
              repaymentId: string;
              status: 'paid' | 'confirmed' | 'partial' | 'pending' | 'available';
              confirmedAmount: number;
              pendingAmount: number;
              remainingAmount: number;
            }[] = [];

            loans.forEach((loan) => {
              if (loan.repayments && Array.isArray(loan.repayments)) {
                loan.repayments.forEach((rep: any) => {
                  const emiAmount = parseFloat(rep.amountDue || "0");
                  const amountPaid = parseFloat(rep.amountPaid || "0");
                  const penalty = parseFloat(rep.penaltyApplied || "0");
                  const totalDue = emiAmount + penalty;
                  
                  // Get payments for this repayment
                  const repPayments = payments.filter((p: any) => p.repaymentId === rep.id);
                  const approvedPayments = repPayments.filter((p: any) => p.status === 'approved');
                  const pendingPayments = repPayments.filter((p: any) => p.status === 'pending');
                  
                  const totalApprovedAmount = approvedPayments.reduce((sum: number, p: any) => {
                    return sum + parseFloat(p.amount || "0");
                  }, 0);
                  
                  const totalPendingAmount = pendingPayments.reduce((sum: number, p: any) => {
                    return sum + parseFloat(p.amount || "0");
                  }, 0);
                  
                  // Calculate actual remaining (considering approved payments and penalties)
                  // Note: amountPaid already includes penalties applied to repayment
                  const totalPaid = amountPaid + totalApprovedAmount;
                  const remainingAmount = Math.max(0, totalDue - totalPaid);
                  
                  // Determine status
                  let status: 'paid' | 'confirmed' | 'partial' | 'pending' | 'available';
                  if (totalPaid >= totalDue) {
                    status = 'paid';
                  } else if (totalApprovedAmount > 0 && remainingAmount > 0) {
                    status = 'partial';
                  } else if (totalPendingAmount > 0) {
                    status = 'pending';
                  } else if (totalApprovedAmount > 0) {
                    status = 'confirmed';
                  } else {
                    status = 'available';
                  }
                  
                  // Only include unpaid or partially paid repayments
                  if (remainingAmount > 0 || totalPendingAmount > 0) {
                    const dueDate = new Date(rep.dueDate);
                    const daysUntilDue = Math.ceil((dueDate.getTime() - new Date().getTime()) / (1000 * 60 * 60 * 24));
                    
                    // Show payments due in the next 90 days or overdue
                    if (daysUntilDue <= 90) {
                      upcomingPayments.push({
                        loanReference: loan.reference,
                        dueDate: rep.dueDate,
                        amountDue: remainingAmount.toFixed(2),
                        loanId: loan.id,
                        repaymentId: rep.id,
                        status,
                        confirmedAmount: totalApprovedAmount,
                        pendingAmount: totalPendingAmount,
                        remainingAmount,
                      });
                    }
                  }
                });
              }
            });

            // Sort by due date (earliest first)
            upcomingPayments.sort((a, b) => 
              new Date(a.dueDate).getTime() - new Date(b.dueDate).getTime()
            );

            // Show only next 3 upcoming payments
            const displayPayments = upcomingPayments.slice(0, 3);

            if (displayPayments.length > 0) {
              return (
                <View style={{ marginTop: 14 }}>
                  {displayPayments.map((payment, index) => {
                    const dueDate = new Date(payment.dueDate);
                    const daysUntilDue = Math.ceil((dueDate.getTime() - new Date().getTime()) / (1000 * 60 * 60 * 24));
                    const isOverdue = daysUntilDue < 0;
                    const isDueSoon = daysUntilDue <= 7 && daysUntilDue >= 0;

                    const getStatusInfo = () => {
                      switch (payment.status) {
                        case 'paid':
                          return { label: 'Paid', color: '#22C55E', icon: 'checkmark-circle' };
                        case 'confirmed':
                          return { label: 'Confirmed', color: '#22C55E', icon: 'checkmark-circle' };
                        case 'partial':
                          return { label: 'Partially Confirmed', color: '#F59E0B', icon: 'time' };
                        case 'pending':
                          return { label: 'Pending Approval', color: '#3B82F6', icon: 'hourglass' };
                        default:
                          return { label: 'Available', color: '#9CA3AF', icon: 'calendar-outline' };
                      }
                    };

                    const statusInfo = getStatusInfo();

                    return (
                      <View
                        key={`${payment.loanId}-${payment.dueDate}`}
                        style={[
                          styles.upcomingPaymentCard,
                          isOverdue && styles.upcomingPaymentCardOverdue,
                          isDueSoon && !isOverdue && styles.upcomingPaymentCardDueSoon,
                          payment.status === 'paid' && styles.upcomingPaymentCardPaid,
                          payment.status === 'confirmed' && styles.upcomingPaymentCardConfirmed,
                          payment.status === 'partial' && styles.upcomingPaymentCardPartial,
                          payment.status === 'pending' && styles.upcomingPaymentCardPending,
                        ]}
                      >
                        <View style={styles.upcomingPaymentRow}>
                          <View style={styles.upcomingPaymentLeft}>
                            <View style={styles.upcomingPaymentHeader}>
                            <Text style={styles.upcomingPaymentLabel}>
                              Loan {payment.loanReference}
                            </Text>
                              <View style={[styles.upcomingPaymentStatusBadge, { backgroundColor: `${statusInfo.color}20` }]}>
                                <Ionicons name={statusInfo.icon as any} size={12} color={statusInfo.color} />
                                <Text style={[styles.upcomingPaymentStatusText, { color: statusInfo.color }]}>
                                  {statusInfo.label}
                                </Text>
                              </View>
                            </View>
                            <Text style={styles.upcomingPaymentDate}>
                              {dueDate.toLocaleDateString("en-US", {
                                month: "short",
                                day: "numeric",
                                year: "numeric",
                              })}
                              {isOverdue && " • Overdue"}
                              {isDueSoon && !isOverdue && " • Due Soon"}
                            </Text>
                            <View style={styles.upcomingPaymentDetails}>
                              {payment.confirmedAmount > 0 && (
                                <Text style={styles.upcomingPaymentConfirmed}>
                                  ✓ Confirmed: ₱{payment.confirmedAmount.toLocaleString("en-PH", {
                                    minimumFractionDigits: 2,
                                  })}
                                </Text>
                              )}
                              {payment.pendingAmount > 0 && (
                                <Text style={styles.upcomingPaymentPending}>
                                  ⏳ Pending: ₱{payment.pendingAmount.toLocaleString("en-PH", {
                                    minimumFractionDigits: 2,
                                  })}
                                </Text>
                              )}
                              {payment.remainingAmount > 0 && (
                            <Text style={styles.upcomingPaymentSubtext}>
                                  Remaining: ₱{payment.remainingAmount.toLocaleString("en-PH", {
                                    minimumFractionDigits: 2,
                                  })}
                            </Text>
                              )}
                            </View>
                          </View>
                          <View style={styles.upcomingPaymentAmountContainer}>
                            {payment.status === 'paid' ? (
                              <View style={styles.paidIndicator}>
                                <Ionicons name="checkmark-circle" size={24} color="#22C55E" />
                                <Text style={styles.paidText}>Paid</Text>
                              </View>
                            ) : (
                            <Text style={[
                              styles.upcomingPaymentAmount,
                              isOverdue && styles.upcomingPaymentAmountOverdue,
                              isDueSoon && !isOverdue && styles.upcomingPaymentAmountDueSoon,
                                payment.status === 'partial' && styles.upcomingPaymentAmountPartial,
                                payment.status === 'pending' && styles.upcomingPaymentAmountPending,
                            ]}>
                              ₱{parseFloat(payment.amountDue).toLocaleString("en-PH", {
                                minimumFractionDigits: 2,
                              })}
                            </Text>
                            )}
                          </View>
                        </View>
                      </View>
                    );
                  })}
                </View>
              );
            }

            return (
              <Text style={{ color: "#999", textAlign: "center", padding: 20, marginTop: 14 }}>
                No upcoming payments
              </Text>
            );
          })()}
        </View>
      </ScrollView>
    </SafeAreaView>
  );
};

/* Components */
interface ActionButtonProps {
  icon: string;
  label: string;
  onPress?: () => void;
}

const ActionButton = ({ icon, label, onPress }: ActionButtonProps) => {
  const router = useRouter();
  
  const handlePress = () => {
    if (onPress) {
      onPress();
    } else {
      // Default navigation based on label
      if (label === "Apply") {
        router.push("./loan_request");
      } else if (label === "Payment") {
        router.push("./attatch_receipt");
      } else if (label === "Score") {
        router.push("./credit");
      }
    }
  };

  return (
    <TouchableOpacity style={{ alignItems: "center" }} onPress={handlePress}>
      <LinearGradient
        colors={["#0f0c29", "#302b63", "#24243e"] as const}
        start={{ x: 0, y: 0 }}
        end={{ x: 1, y: 1 }}
        style={styles.actionButton}
      >
        <FontAwesome5 name={icon} size={20} color="#fff" />
      </LinearGradient>
      <Text style={styles.actionLabel}>{label}</Text>
    </TouchableOpacity>
  );
};

interface TransactionItemProps {
  label: string;
  amount: string;
  date: string;
  status?: string;
  applicationDate?: string;
}

const getStatusInfo = (status: string) => {
  const statusLower = status.toLowerCase();
  switch (statusLower) {
    case 'new_application':
    case 'under_review':
      return {
        label: status === 'new_application' ? 'Pending' : 'Under Review',
        color: '#FFD700',
        bgColor: 'rgba(255, 215, 0, 0.15)',
      };
    case 'approved':
      return {
        label: 'Approved',
        color: '#10B981',
        bgColor: 'rgba(16, 185, 129, 0.15)',
      };
    case 'for_release':
    case 'disbursed':
      return {
        label: status === 'disbursed' ? 'Disbursed' : 'For Release',
        color: '#4EFA8A',
        bgColor: 'rgba(78, 250, 138, 0.15)',
      };
    case 'closed':
      return {
        label: 'Closed',
        color: '#9CA3AF',
        bgColor: 'rgba(156, 163, 175, 0.15)',
      };
    case 'rejected':
    case 'cancelled':
      return {
        label: status === 'cancelled' ? 'Cancelled' : 'Rejected',
        color: '#FF6B6B',
        bgColor: 'rgba(255, 107, 107, 0.15)',
      };
    default:
      return {
        label: status.replace(/_/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase()),
        color: '#9CA3AF',
        bgColor: 'rgba(156, 163, 175, 0.15)',
      };
  }
};

const TransactionItem = ({ label, amount, date, status, applicationDate }: TransactionItemProps) => {
  const statusInfo = status ? getStatusInfo(status) : null;
  const displayDate = applicationDate 
    ? new Date(applicationDate).toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric",
      })
    : date;

  return (
    <View style={styles.transactionItem}>
      <View style={styles.transactionLeft}>
        <View style={styles.transactionHeaderRow}>
          <Text style={styles.transactionLabel}>{label}</Text>
          {statusInfo && (
            <View style={[styles.statusBadge, { backgroundColor: statusInfo.bgColor, borderColor: statusInfo.color }]}>
              <Text style={[styles.statusBadgeText, { color: statusInfo.color }]}>
                {statusInfo.label}
              </Text>
            </View>
          )}
        </View>
        <Text style={styles.transactionDate}>{displayDate}</Text>
      </View>
      <Text
        style={[
          styles.transactionAmount,
          { color: amount.includes("-") ? "#FF6B6B" : "#4EFA8A" },
        ]}
      >
        {amount}
      </Text>
    </View>
  );
};


/* Styles */
const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: "black", paddingHorizontal: 20 },
  header: {
    marginTop: 50,
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "center",
  },
  headerButtons: {
    flexDirection: "row",
    alignItems: "center",
  },
  notificationButtonWrapper: {
    position: 'relative',
  },
  notificationBtn: {
    padding: 10,
    borderRadius: 12,
    shadowColor: "#FF6B6B",
    shadowOpacity: 0.5,
    shadowOffset: { width: 0, height: 4 },
    shadowRadius: 6,
    position: 'relative',
  },
  notificationBadge: {
    position: 'absolute',
    top: -2,
    right: -2,
    backgroundColor: '#FF6B6B',
    borderRadius: 10,
    minWidth: 20,
    height: 20,
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 2,
    borderColor: '#000',
    paddingHorizontal: 4,
  },
  notificationBadgeText: {
    color: '#fff',
    fontSize: 10,
    fontWeight: '700',
  },
  profileBtn: {
    padding: 10,
    borderRadius: 12,
    shadowColor: "#7B2CBF",
    shadowOpacity: 0.5,
    shadowOffset: { width: 0, height: 4 },
    shadowRadius: 6,
  },
  greeting: { color: "#aaa", fontSize: 15, marginBottom: 4 },
  userName: { color: "white", fontSize: 26, fontWeight: "700" },
  balanceCard: {
    borderRadius: 22,
    padding: 24,
    marginTop: 30,
    shadowColor: "#7B2CBF",
    shadowOpacity: 0.5,
    shadowOffset: { width: 0, height: 5 },
    shadowRadius: 12,
    elevation: 5,
  },
  topRow: {
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "center",
    marginTop: 4,
  },
  cardTitle: { fontSize: 16, fontWeight: "600", color: "#fff", opacity: 0.9 },
  balanceAmount: { fontSize: 36, fontWeight: "bold", marginTop: 18, color: "#fff" },
  nextDue: { fontSize: 14, marginTop: 8, color: "#f1f1f1", opacity: 0.85 },
  brandingRow: { marginBottom: 8, alignItems: "flex-end" },
  branding: { fontSize: 22, fontWeight: "700", color: "#3B82F6", letterSpacing: 1 },
  breakdownToggle: {
    flexDirection: "row",
    justifyContent: "center",
    alignItems: "center",
    marginTop: 16,
    paddingVertical: 8,
    gap: 6,
  },
  breakdownToggleText: {
    fontSize: 13,
    color: "rgba(255, 255, 255, 0.7)",
    fontWeight: "500",
  },
  breakdownContainer: {
    marginTop: 24,
    paddingTop: 20,
    borderTopWidth: 1,
    borderTopColor: "rgba(255, 255, 255, 0.1)",
  },
  breakdownRow: {
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "center",
    marginBottom: 12,
  },
  breakdownLabel: {
    fontSize: 14,
    color: "rgba(255, 255, 255, 0.7)",
    fontWeight: "500",
  },
  breakdownValue: {
    fontSize: 14,
    color: "#fff",
    fontWeight: "600",
  },
  breakdownTotalRow: {
    marginTop: 8,
    paddingTop: 12,
    borderTopWidth: 1,
    borderTopColor: "rgba(255, 255, 255, 0.15)",
  },
  breakdownTotalLabel: {
    fontSize: 16,
    color: "#fff",
    fontWeight: "700",
  },
  breakdownTotalValue: {
    fontSize: 16,
    color: "#FFD700",
    fontWeight: "700",
  },
  actionsRow: {
    flexDirection: "row",
    justifyContent: "space-around",
    alignItems: "center",
    marginTop: 36,
    paddingHorizontal: 20,
  },
  actionButton: {
    height: 62,
    width: 62,
    borderRadius: 31,
    justifyContent: "center",
    alignItems: "center",
    marginBottom: 8,
    shadowColor: "#FF6B6B",
    shadowOpacity: 0.3,
    shadowOffset: { width: 0, height: 3 },
    shadowRadius: 6,
  },
  actionLabel: { color: "#fff", fontSize: 13, textAlign: "center" },
  section: { marginTop: 42 },
  sectionHeader: { flexDirection: "row", justifyContent: "space-between", alignItems: "center" },
  sectionTitle: { color: "white", fontSize: 20, fontWeight: "600" },
  seeAllButton: { flexDirection: "row", alignItems: "center", paddingHorizontal: 12, paddingVertical: 4, borderRadius: 12 },
  seeAllText: { fontSize: 14, color: "#FF8008", fontWeight: "600" },
  transactionsList: { maxHeight: 280, marginTop: 14 },
  transactionItem: {
    backgroundColor: "rgba(255, 255, 255, 0.06)",
    padding: 18,
    borderRadius: 16,
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "center",
    marginBottom: 14,
  },
  transactionLeft: {
    flex: 1,
  },
  transactionHeaderRow: {
    flexDirection: "row",
    alignItems: "center",
    gap: 8,
    marginBottom: 4,
  },
  transactionLabel: { color: "#fff", fontSize: 15, fontWeight: "500" },
  transactionDate: { color: "#bbb", fontSize: 12, marginTop: 3 },
  transactionAmount: { fontSize: 16, fontWeight: "700" },
  statusBadge: {
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 8,
    borderWidth: 1,
  },
  statusBadgeText: {
    fontSize: 11,
    fontWeight: "600",
    textTransform: "uppercase",
    letterSpacing: 0.5,
  },
  chartContainer: { backgroundColor: "rgba(255,255,255,0.05)", borderRadius: 18, padding: 14 },
  chartStyle: { borderRadius: 16 },
  upcomingPaymentCard: {
    backgroundColor: "rgba(255, 255, 255, 0.06)",
    padding: 18,
    borderRadius: 16,
    marginBottom: 14,
    borderWidth: 1,
    borderColor: "rgba(255, 255, 255, 0.1)",
  },
  upcomingPaymentCardOverdue: {
    backgroundColor: "rgba(255, 107, 107, 0.15)",
    borderColor: "rgba(255, 107, 107, 0.3)",
  },
  upcomingPaymentCardDueSoon: {
    backgroundColor: "rgba(255, 215, 0, 0.15)",
    borderColor: "rgba(255, 215, 0, 0.3)",
  },
  upcomingPaymentRow: {
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "center",
  },
  upcomingPaymentLeft: {
    flex: 1,
  },
  upcomingPaymentLabel: {
    color: "#fff",
    fontSize: 15,
    fontWeight: "600",
    marginBottom: 4,
  },
  upcomingPaymentDate: {
    color: "rgba(255, 255, 255, 0.6)",
    fontSize: 13,
  },
  upcomingPaymentSubtext: {
    color: "rgba(255, 255, 255, 0.4)",
    fontSize: 11,
    marginTop: 2,
  },
  upcomingPaymentAmountContainer: {
    alignItems: "flex-end",
  },
  upcomingPaymentAmount: {
    fontSize: 18,
    fontWeight: "700",
    color: "#4EFA8A",
  },
  upcomingPaymentAmountOverdue: {
    color: "#FF6B6B",
  },
  upcomingPaymentAmountDueSoon: {
    color: "#FFD700",
  },
  upcomingPaymentHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 4,
  },
  upcomingPaymentStatusBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 8,
  },
  upcomingPaymentStatusText: {
    fontSize: 10,
    fontWeight: '600',
  },
  upcomingPaymentDetails: {
    marginTop: 4,
    gap: 2,
  },
  upcomingPaymentConfirmed: {
    color: '#22C55E',
    fontSize: 11,
    fontWeight: '500',
  },
  upcomingPaymentPending: {
    color: '#3B82F6',
    fontSize: 11,
    fontWeight: '500',
  },
  upcomingPaymentCardPaid: {
    backgroundColor: 'rgba(34, 197, 94, 0.15)',
    borderColor: 'rgba(34, 197, 94, 0.3)',
  },
  upcomingPaymentCardConfirmed: {
    backgroundColor: 'rgba(34, 197, 94, 0.1)',
    borderColor: 'rgba(34, 197, 94, 0.2)',
  },
  upcomingPaymentCardPartial: {
    backgroundColor: 'rgba(245, 158, 11, 0.15)',
    borderColor: 'rgba(245, 158, 11, 0.3)',
  },
  upcomingPaymentCardPending: {
    backgroundColor: 'rgba(59, 130, 246, 0.15)',
    borderColor: 'rgba(59, 130, 246, 0.3)',
  },
  upcomingPaymentAmountPartial: {
    color: '#F59E0B',
  },
  upcomingPaymentAmountPending: {
    color: '#3B82F6',
  },
  paidIndicator: {
    alignItems: 'center',
    gap: 4,
  },
  paidText: {
    color: '#22C55E',
    fontSize: 12,
    fontWeight: '600',
  },
});

export default Dashboard;
