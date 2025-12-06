import { useRouter } from "expo-router";
import React, { useState, useMemo } from "react";
import {
  ActivityIndicator,
  Alert,
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  ScrollView,
} from "react-native";
import Slider from "@react-native-community/slider";
import { Card } from "react-native-paper";
import { LinearGradient } from "expo-linear-gradient";
import { Ionicons } from "@expo/vector-icons";
import { api } from "../../src/config/api";
import LocationPopup from "../../src/components/LocationPopup";

export default function LoanDetails() {
  const router = useRouter();
  const [loanAmount, setLoanAmount] = useState(13800);
  const [tenor, setTenor] = useState(6);
  const [loading, setLoading] = useState(false);
  const [showLocationPopup, setShowLocationPopup] = useState(false);
  const [location, setLocation] = useState<{
    latitude: number;
    longitude: number;
    address?: string;
  } | null>(null);

  const interestRate = 24; // Annual % - Must match backend default (24%)

  // Format currency ₱xx,xxx.xx
  const formatCurrency = (value: number) =>
    new Intl.NumberFormat("en-PH", {
      style: "currency",
      currency: "PHP",
    }).format(value);

  // EMI formula calculation (matches backend formula)
  // EMI = [P × R × (1+R)^N] / [(1+R)^N - 1]
  const monthlyInterest = interestRate / 12 / 100;
  const emi = useMemo(() => {
    if (monthlyInterest === 0) {
      return (loanAmount / tenor).toFixed(2);
    }
    const numerator = loanAmount * monthlyInterest * Math.pow(1 + monthlyInterest, tenor);
    const denominator = Math.pow(1 + monthlyInterest, tenor) - 1;
    return (numerator / denominator).toFixed(2);
  }, [loanAmount, tenor, monthlyInterest]);

  // Generate schedule of payments (preview - actual dates calculated by backend)
  // Note: Backend will recalculate dates when loan is created using applicationDate
  const schedule = useMemo(() => {
    const today = new Date();
    const payments = [];
    const targetDay = today.getDate();
    
    for (let i = 1; i <= tenor; i++) {
      // Use same date calculation logic as backend
      const dueDate = new Date(today.getFullYear(), today.getMonth() + i, 1);
      // Set to same day of month, or last day if month doesn't have that day
      const lastDayOfMonth = new Date(dueDate.getFullYear(), dueDate.getMonth() + 1, 0).getDate();
      dueDate.setDate(Math.min(targetDay, lastDayOfMonth));
      
      payments.push({
        dueDate: dueDate.toLocaleDateString("en-US", {
          month: "long",
          day: "numeric",
          year: "numeric",
        }),
        amount: emi,
      });
    }
    return payments;
  }, [emi, tenor]);

  const handleLocationObtained = (loc: {
    latitude: number;
    longitude: number;
    address?: string;
  }) => {
    setLocation(loc);
    console.log('📍 Location obtained:', loc);
  };

  const handleSubmitLoan = async () => {
    try {
      setLoading(true);
      
      const requestData: any = {
        amount: loanAmount,
        principalAmount: loanAmount, // Explicitly set principalAmount
        tenor: tenor.toString(), // Must be '6', '12', or '36' as string
        interestRate: interestRate / 100, // Convert 24% to 0.24 (decimal) to match backend
      };

      // Include location if available
      if (location) {
        requestData.latitude = location.latitude;
        requestData.longitude = location.longitude;
        if (location.address) {
          requestData.locationAddress = location.address;
        }
      }
      
      console.log('📤 Sending loan application:', requestData);
      
      await api.post("/loans", requestData);

      Alert.alert("✅ Success", "Loan application submitted successfully!", [
        {
          text: "OK",
          onPress: () => router.replace("/(tabs)"),
        },
      ]);
      } catch (err: any) {
        console.error("Loan submission error:", err);
        const errorData = err.response?.data;
        const message = errorData?.message || "Failed to submit loan application";
        
        // Handle existing loan error - offer to navigate to loan history
        if (errorData?.existingLoan) {
          Alert.alert(
            "⛔ Cannot Submit Application",
            message,
            [
              {
                text: "View My Loans",
                onPress: () => router.push("./loan_history"),
              },
              {
                text: "OK",
                style: "cancel",
              },
            ]
          );
        } else if (errorData?.errors && Array.isArray(errorData.errors)) {
          // Handle validation errors
          const validationErrors = errorData.errors
            .map((e: any) => {
              const path = Array.isArray(e.path) ? e.path.join('.') : e.path || 'unknown';
              return `• ${path}: ${e.message || 'Invalid value'}`;
            })
            .join('\n');
          Alert.alert("Validation Error", `${message}\n\n${validationErrors}`);
        } else {
          // Show full error response for debugging
          console.error('Full error response:', errorData);
          Alert.alert("Error", message);
        }
      } finally {
        setLoading(false);
      }
  };

  return (
    <LinearGradient
      colors={["#03042c", "#302b63", "#24243e"]}
      start={{ x: 0, y: 0 }}
      end={{ x: 1, y: 1 }}
      style={styles.gradient}
    >
      <ScrollView contentContainerStyle={styles.container}>
        {/* Header */}
        <Text style={styles.title}>Loan details</Text>
        <Text style={styles.subtitle}>
          Customise your loan amount & EMI details
        </Text>

        {/* Loan Amount */}
        <Card style={styles.card}>
          <Text style={styles.label}>Select loan amount</Text>
          <Text style={styles.amount}>{formatCurrency(loanAmount)}</Text>

          <Slider
            style={{ width: "100%" }}
            minimumValue={3500}
            maximumValue={50000}
            step={100}
            minimumTrackTintColor="#f97316"
            maximumTrackTintColor="#ddd"
            thumbTintColor="#f97316"
            value={loanAmount}
            onValueChange={(value) => setLoanAmount(value)}
          />

          <View style={styles.rangeRow}>
            <Text style={styles.rangeText}>{formatCurrency(3500)}</Text>
            <Text style={styles.rangeText}>{formatCurrency(50000)}</Text>
          </View>
        </Card>

        {/* Tenor */}
        <Text style={styles.label}>Choose your tenor</Text>
        <View style={styles.tenorRow}>
          {[6, 12, 36].map((item) => (
            <TouchableOpacity
              key={item}
              style={[styles.tenorBox, tenor === item && styles.activeTenor]}
              onPress={() => setTenor(item)}
            >
              <Text
                style={[
                  styles.tenorText,
                  tenor === item && styles.activeTenorText,
                ]}
              >
                {item} month
              </Text>
            </TouchableOpacity>
          ))}
        </View>

        {/* EMI Details */}
        <Card style={styles.card}>
          <Text style={styles.detailText}>EMI Tenure: {tenor} months</Text>
          <Text style={styles.detailText}>
            Annual Interest Rate: {interestRate}%
          </Text>
          <Text style={styles.detailText}>
            Monthly EMI: {formatCurrency(parseFloat(emi))}
          </Text>
          <Text style={styles.detailText}>
            Total Amount Payable: {formatCurrency(parseFloat(emi) * tenor)}
          </Text>
          <Text style={styles.detailText}>
            Total Interest: {formatCurrency(parseFloat(emi) * tenor - loanAmount)}
          </Text>

          <TouchableOpacity>
            <Text style={styles.link}>Loan Details View</Text>
          </TouchableOpacity>
        </Card>

        {/* Payment Schedule */}
        <Card style={styles.card}>
          <Text style={[styles.label, { marginBottom: 12 }]}>
            Schedule of Payments
          </Text>
          {schedule.map((item, idx) => (
            <View key={idx} style={styles.scheduleRow}>
              <Text style={styles.scheduleText}>{item.dueDate}</Text>
              <Text style={styles.scheduleAmount}>
                {formatCurrency(parseFloat(item.amount))}
              </Text>
            </View>
          ))}
        </Card>

        {/* Location Section */}
        <Card style={styles.card}>
          <View style={styles.locationSection}>
            <View style={styles.locationHeader}>
              <Ionicons name="location" size={20} color="#f97316" />
              <Text style={styles.label}>Application Location</Text>
            </View>
            {location ? (
              <View style={styles.locationInfo}>
                <View style={styles.locationRow}>
                  <Ionicons name="checkmark-circle" size={16} color="#4EFA8A" />
                  <Text style={styles.locationText}>
                    {location.address || `${location.latitude.toFixed(4)}, ${location.longitude.toFixed(4)}`}
                  </Text>
                </View>
                <TouchableOpacity
                  style={styles.changeLocationButton}
                  onPress={() => setShowLocationPopup(true)}
                >
                  <Text style={styles.changeLocationText}>Change Location</Text>
                </TouchableOpacity>
              </View>
            ) : (
              <TouchableOpacity
                style={styles.getLocationButton}
                onPress={() => setShowLocationPopup(true)}
              >
                <Ionicons name="location-outline" size={18} color="#fff" />
                <Text style={styles.getLocationText}>Get My Location</Text>
              </TouchableOpacity>
            )}
          </View>
        </Card>

        {/* Bottom Amount */}
        <View style={styles.footer}>
          <Text style={styles.total}>
            {formatCurrency(loanAmount)}
          </Text>

          {/* Gradient Button */}
          <TouchableOpacity
            style={styles.gradientButtonWrapper}
            onPress={handleSubmitLoan}
            disabled={loading}
          >
            <LinearGradient
              colors={["#03042c", "#302b63", "#24243e"]}
              start={{ x: 0, y: 0 }}
              end={{ x: 1, y: 1 }}
              style={styles.gradientButton}
            >
              {loading ? (
                <ActivityIndicator color="#fff" />
              ) : (
                <Text style={styles.gradientButtonText}>Submit Loan Application</Text>
              )}
            </LinearGradient>
          </TouchableOpacity>
        </View>
      </ScrollView>

      {/* Location Popup */}
      <LocationPopup
        visible={showLocationPopup}
        onClose={() => setShowLocationPopup(false)}
        onLocationObtained={handleLocationObtained}
        title="Loan Application Location"
        message="We need your location to process your loan application. This helps us verify your application and provide better service."
      />
    </LinearGradient>
  );
}

const styles = StyleSheet.create({
  gradient: { flex: 1 },
  container: {
    padding: 20,
    flexGrow: 1,
    backgroundColor: "black",
  },
  title: {
    fontSize: 20,
    fontWeight: "700",
    marginBottom: 4,
    color: "#fff",
    marginTop: 50,
  },
  subtitle: { color: "#ccc", marginBottom: 16 },
  card: {
    backgroundColor: "#1e1e2f",
    padding: 16,
    borderRadius: 12,
    elevation: 3,
    marginBottom: 16,
  },
  label: { fontWeight: "600", marginBottom: 8, fontSize: 16, color: "#fff" },
  amount: { fontSize: 22, fontWeight: "700", marginBottom: 12, color: "#fff" },
  rangeRow: {
    flexDirection: "row",
    justifyContent: "space-between",
    marginTop: 4,
  },
  rangeText: { fontSize: 12, color: "#aaa" },
  tenorRow: {
    flexDirection: "row",
    justifyContent: "space-between",
    marginVertical: 12,
  },
  tenorBox: {
    flex: 1,
    margin: 4,
    padding: 12,
    borderRadius: 10,
    backgroundColor: "#444",
    alignItems: "center",
  },
  activeTenor: { backgroundColor: "#f97316" },
  tenorText: { fontWeight: "600", color: "#ddd" },
  activeTenorText: { color: "#fff" },
  detailText: { marginVertical: 4, fontSize: 14, color: "#fff" },
  link: { color: "#f97316", marginTop: 8, fontWeight: "600" },
  scheduleRow: {
    flexDirection: "row",
    justifyContent: "space-between",
    paddingVertical: 6,
    borderBottomWidth: 0.5,
    borderBottomColor: "#444",
  },
  scheduleText: { color: "#fff", fontSize: 14 },
  scheduleAmount: { color: "#4EFA8A", fontWeight: "700", fontSize: 14 },
  footer: {
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "center",
    marginTop: 16,
  },
  total: { fontSize: 22, fontWeight: "700", color: "#fff" },
  gradientButtonWrapper: {
    borderRadius: 8,
    overflow: "hidden",
  },
  gradientButton: {
    paddingHorizontal: 24,
    paddingVertical: 12,
    borderRadius: 8,
    alignItems: "center",
  },
  gradientButtonText: {
    color: "#fff",
    fontWeight: "700",
    fontSize: 16,
  },
  locationSection: {
    gap: 12,
  },
  locationHeader: {
    flexDirection: "row",
    alignItems: "center",
    gap: 8,
  },
  locationInfo: {
    gap: 8,
  },
  locationRow: {
    flexDirection: "row",
    alignItems: "center",
    gap: 8,
  },
  locationText: {
    color: "#4EFA8A",
    fontSize: 14,
    flex: 1,
  },
  changeLocationButton: {
    alignSelf: "flex-start",
    paddingVertical: 6,
    paddingHorizontal: 12,
  },
  changeLocationText: {
    color: "#f97316",
    fontSize: 13,
    fontWeight: "600",
  },
  getLocationButton: {
    flexDirection: "row",
    alignItems: "center",
    gap: 8,
    paddingVertical: 12,
    paddingHorizontal: 16,
    backgroundColor: "#2a2a3e",
    borderRadius: 8,
    borderWidth: 1,
    borderColor: "#f97316",
    borderStyle: "dashed",
  },
  getLocationText: {
    color: "#fff",
    fontSize: 14,
    fontWeight: "600",
  },
});
