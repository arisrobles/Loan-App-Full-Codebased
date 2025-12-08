import { useRouter, useLocalSearchParams } from "expo-router";
import React, { useState, useEffect } from "react";
import {
  ActivityIndicator,
  Alert,
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  ScrollView,
  TextInput,
  SafeAreaView,
} from "react-native";
import { Card } from "react-native-paper";
import { LinearGradient } from "expo-linear-gradient";
import { Ionicons } from "@expo/vector-icons";
import { api } from "../../src/config/api";
import { StatusBar } from "expo-status-bar";

interface BorrowerDetails {
  fullName: string;
  address: string;
  civilStatus: string;
  phone?: string;
  email?: string;
}

export default function LoanApplicationDetails() {
  const router = useRouter();
  const params = useLocalSearchParams();
  const loanId = params.loanId as string;

  const [loading, setLoading] = useState(false);
  const [generating, setGenerating] = useState(false);
  const [borrowerDetails, setBorrowerDetails] = useState<BorrowerDetails>({
    fullName: "",
    address: "",
    civilStatus: "single",
    phone: "",
    email: "",
  });
  const [agreement, setAgreement] = useState<string | null>(null);
  const [city, setCity] = useState("Manila");

  const civilStatusOptions = [
    { value: "single", label: "Single" },
    { value: "married", label: "Married" },
    { value: "widowed", label: "Widowed" },
    { value: "divorced", label: "Divorced" },
    { value: "separated", label: "Separated" },
  ];

  useEffect(() => {
    fetchBorrowerProfile();
  }, []);

  const fetchBorrowerProfile = async () => {
    try {
      const res = await api.get("/users/profile");
      if (res.data) {
        setBorrowerDetails({
          fullName: res.data.fullName || "",
          address: res.data.address || "",
          civilStatus: res.data.civilStatus || "single",
          phone: res.data.phone || "",
          email: res.data.email || "",
        });
      }
    } catch (error) {
      console.error("Error fetching profile:", error);
    }
  };

  const handleUpdateProfile = async () => {
    if (!borrowerDetails.fullName || !borrowerDetails.address) {
      Alert.alert("Missing Information", "Please fill in your full name and address.");
      return;
    }

    try {
      setLoading(true);
      await api.put("/users/profile", {
        fullName: borrowerDetails.fullName,
        address: borrowerDetails.address,
        civilStatus: borrowerDetails.civilStatus,
        phone: borrowerDetails.phone,
        email: borrowerDetails.email,
      });
      Alert.alert("✅ Success", "Profile updated successfully!");
    } catch (error: any) {
      console.error("Error updating profile:", error);
      Alert.alert("Error", error.response?.data?.message || "Failed to update profile");
    } finally {
      setLoading(false);
    }
  };

  const handleGenerateAgreement = async () => {
    if (!borrowerDetails.fullName || !borrowerDetails.address) {
      Alert.alert("Missing Information", "Please update your profile with full name and address first.");
      return;
    }

    try {
      setGenerating(true);
      const res = await api.post("/legal/agreement", {
        loanId,
        city,
        penaltyRate: 0.10, // 10% penalty
      });

      if (res.data?.data?.agreement) {
        setAgreement(res.data.data.agreement);
        Alert.alert("✅ Success", "Loan agreement generated successfully!");
      }
    } catch (error: any) {
      console.error("Error generating agreement:", error);
      const errorData = error.response?.data;
      if (errorData?.missingFields) {
        Alert.alert(
          "Incomplete Information",
          `Please update your profile with: ${Object.entries(errorData.missingFields)
            .filter(([_, missing]) => missing)
            .map(([field]) => field)
            .join(", ")}`
        );
      } else {
        Alert.alert("Error", errorData?.message || "Failed to generate agreement");
      }
    } finally {
      setGenerating(false);
    }
  };

  const handleDownloadAgreement = () => {
    if (!agreement) return;

    // For now, we'll show the agreement in an alert or navigate to a view screen
    // In a production app, you'd use a library like react-native-fs or expo-file-system
    Alert.alert(
      "Agreement Generated",
      "The loan agreement has been generated. You can view it in your documents section.",
      [
        {
          text: "View Documents",
          onPress: () => router.push("/(tabs)/loan_history"),
        },
        { text: "OK" },
      ]
    );
  };

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar style="light" />
      <ScrollView contentContainerStyle={styles.scrollContent}>
        <View style={styles.header}>
          <TouchableOpacity onPress={() => router.back()} style={styles.backButton}>
            <Ionicons name="arrow-back" size={24} color="#fff" />
          </TouchableOpacity>
          <Text style={styles.title}>Loan Application Details</Text>
          <View style={{ width: 40 }} />
        </View>

        <Text style={styles.subtitle}>
          Complete your information to generate the legal loan agreement
        </Text>

        {/* Borrower Information Form */}
        <Card style={styles.card}>
          <Text style={styles.cardTitle}>Borrower Information</Text>

          <View style={styles.inputGroup}>
            <Text style={styles.label}>Full Name *</Text>
            <TextInput
              style={styles.input}
              value={borrowerDetails.fullName}
              onChangeText={(text) =>
                setBorrowerDetails({ ...borrowerDetails, fullName: text })
              }
              placeholder="Enter your full name"
              placeholderTextColor="#9CA3AF"
            />
          </View>

          <View style={styles.inputGroup}>
            <Text style={styles.label}>Address *</Text>
            <TextInput
              style={[styles.input, styles.textArea]}
              value={borrowerDetails.address}
              onChangeText={(text) =>
                setBorrowerDetails({ ...borrowerDetails, address: text })
              }
              placeholder="Enter your complete address"
              placeholderTextColor="#9CA3AF"
              multiline
              numberOfLines={3}
            />
          </View>

          <View style={styles.inputGroup}>
            <Text style={styles.label}>Civil Status *</Text>
            <View style={styles.optionsRow}>
              {civilStatusOptions.map((option) => (
                <TouchableOpacity
                  key={option.value}
                  style={[
                    styles.optionButton,
                    borrowerDetails.civilStatus === option.value && styles.optionButtonActive,
                  ]}
                  onPress={() =>
                    setBorrowerDetails({ ...borrowerDetails, civilStatus: option.value })
                  }
                >
                  <Text
                    style={[
                      styles.optionText,
                      borrowerDetails.civilStatus === option.value && styles.optionTextActive,
                    ]}
                  >
                    {option.label}
                  </Text>
                </TouchableOpacity>
              ))}
            </View>
          </View>

          <View style={styles.inputGroup}>
            <Text style={styles.label}>Phone Number</Text>
            <TextInput
              style={styles.input}
              value={borrowerDetails.phone}
              onChangeText={(text) =>
                setBorrowerDetails({ ...borrowerDetails, phone: text })
              }
              placeholder="Enter your phone number"
              placeholderTextColor="#9CA3AF"
              keyboardType="phone-pad"
            />
          </View>

          <View style={styles.inputGroup}>
            <Text style={styles.label}>Email</Text>
            <TextInput
              style={styles.input}
              value={borrowerDetails.email}
              onChangeText={(text) =>
                setBorrowerDetails({ ...borrowerDetails, email: text })
              }
              placeholder="Enter your email"
              placeholderTextColor="#9CA3AF"
              keyboardType="email-address"
              autoCapitalize="none"
            />
          </View>

          <TouchableOpacity
            onPress={handleUpdateProfile}
            disabled={loading}
            style={styles.updateButton}
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
                <Text style={styles.buttonText}>Update Profile</Text>
              )}
            </LinearGradient>
          </TouchableOpacity>
        </Card>

        {/* Agreement Generation */}
        <Card style={styles.card}>
          <Text style={styles.cardTitle}>Generate Loan Agreement</Text>
          <Text style={styles.cardDescription}>
            After updating your profile, generate the legal loan agreement document.
          </Text>

          <View style={styles.inputGroup}>
            <Text style={styles.label}>City</Text>
            <TextInput
              style={styles.input}
              value={city}
              onChangeText={setCity}
              placeholder="Enter city (e.g., Manila)"
              placeholderTextColor="#9CA3AF"
            />
          </View>

          <TouchableOpacity
            onPress={handleGenerateAgreement}
            disabled={generating || !borrowerDetails.fullName || !borrowerDetails.address}
            style={styles.generateButton}
          >
            <LinearGradient
              colors={["#03042c", "#302b63", "#24243e"]}
              start={{ x: 0, y: 0 }}
              end={{ x: 1, y: 1 }}
              style={styles.gradientButton}
            >
              {generating ? (
                <ActivityIndicator color="#fff" />
              ) : (
                <>
                  <Ionicons name="document-text-outline" size={20} color="#fff" />
                  <Text style={styles.buttonText}>Generate Agreement</Text>
                </>
              )}
            </LinearGradient>
          </TouchableOpacity>

          {agreement && (
            <TouchableOpacity
              onPress={handleDownloadAgreement}
              style={styles.downloadButton}
            >
              <Ionicons name="download-outline" size={20} color="#4EFA8A" />
              <Text style={styles.downloadText}>View Generated Agreement</Text>
            </TouchableOpacity>
          )}
        </Card>
      </ScrollView>
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
  header: {
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "space-between",
    marginTop: 20,
    marginBottom: 16,
  },
  backButton: {
    padding: 8,
  },
  title: {
    fontSize: 24,
    fontWeight: "700",
    color: "#fff",
  },
  subtitle: {
    fontSize: 14,
    color: "#9CA3AF",
    marginBottom: 24,
  },
  card: {
    backgroundColor: "#1e1e2f",
    padding: 20,
    borderRadius: 12,
    marginBottom: 16,
  },
  cardTitle: {
    fontSize: 18,
    fontWeight: "700",
    color: "#fff",
    marginBottom: 8,
  },
  cardDescription: {
    fontSize: 14,
    color: "#9CA3AF",
    marginBottom: 16,
  },
  inputGroup: {
    marginBottom: 16,
  },
  label: {
    fontSize: 14,
    fontWeight: "600",
    color: "#fff",
    marginBottom: 8,
  },
  input: {
    backgroundColor: "#2a2a3e",
    padding: 12,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: "#374151",
    color: "#fff",
    fontSize: 16,
  },
  textArea: {
    minHeight: 80,
    textAlignVertical: "top",
  },
  optionsRow: {
    flexDirection: "row",
    flexWrap: "wrap",
    gap: 8,
  },
  optionButton: {
    paddingVertical: 8,
    paddingHorizontal: 16,
    borderRadius: 8,
    backgroundColor: "#2a2a3e",
    borderWidth: 1,
    borderColor: "#374151",
  },
  optionButtonActive: {
    backgroundColor: "#f97316",
    borderColor: "#f97316",
  },
  optionText: {
    color: "#9CA3AF",
    fontSize: 14,
    fontWeight: "500",
  },
  optionTextActive: {
    color: "#fff",
    fontWeight: "600",
  },
  updateButton: {
    marginTop: 8,
    borderRadius: 8,
    overflow: "hidden",
  },
  generateButton: {
    marginTop: 8,
    borderRadius: 8,
    overflow: "hidden",
  },
  gradientButton: {
    paddingVertical: 14,
    paddingHorizontal: 24,
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "center",
    gap: 8,
  },
  buttonText: {
    color: "#fff",
    fontSize: 16,
    fontWeight: "600",
  },
  downloadButton: {
    marginTop: 16,
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "center",
    paddingVertical: 12,
    borderRadius: 8,
    backgroundColor: "#4EFA8A20",
    borderWidth: 1,
    borderColor: "#4EFA8A",
    gap: 8,
  },
  downloadText: {
    color: "#4EFA8A",
    fontSize: 16,
    fontWeight: "600",
  },
});

