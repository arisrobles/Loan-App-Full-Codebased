import { useRouter, useLocalSearchParams } from "expo-router";
import React, { useState } from "react";
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  ScrollView,
  SafeAreaView,
  ActivityIndicator,
  Linking,
} from "react-native";
import { LinearGradient } from "expo-linear-gradient";
import { Ionicons } from "@expo/vector-icons";
import * as FileSystem from "expo-file-system";
import * as Sharing from "expo-sharing";
import { StatusBar } from "expo-status-bar";

export default function LoanApplicationSuccess() {
  const router = useRouter();
  const params = useLocalSearchParams<{
    loanId?: string;
    loanAmount?: string;
    tenor?: string;
    agreementUrl?: string;
    guarantyUrl?: string;
  }>();

  const [downloadingDoc, setDownloadingDoc] = useState<string | null>(null);

  const loanId = params.loanId || null;
  const loanAmount = params.loanAmount ? parseFloat(params.loanAmount) : 0;
  const tenor = params.tenor ? parseInt(params.tenor) : 0;
  const finalAgreementUrl = params.agreementUrl || null;
  const finalGuarantyUrl = params.guarantyUrl || null;

  const handleDownloadDocument = async (url: string, documentName: string) => {
    if (!url) return;

    try {
      setDownloadingDoc(documentName.toLowerCase().includes("loan") ? "agreement" : "guaranty");

      // Check if sharing is available
      const isAvailable = await Sharing.isAvailableAsync();
      if (!isAvailable) {
        // Fallback: Open in browser
        const canOpen = await Linking.canOpenURL(url);
        if (canOpen) {
          await Linking.openURL(url);
        }
        return;
      }

      // Download the file
      const fileUri = FileSystem.documentDirectory + `${documentName.replace(/\s+/g, "_")}.pdf`;
      const downloadResult = await FileSystem.downloadAsync(url, fileUri);

      if (downloadResult.status === 200) {
        // Share the file
        await Sharing.shareAsync(downloadResult.uri, {
          mimeType: "application/pdf",
          dialogTitle: `Share ${documentName}`,
        });
      } else {
        throw new Error("Download failed");
      }
    } catch (error: any) {
      console.error(`Error downloading ${documentName}:`, error);
      // Fallback: Try to open in browser
      try {
        const canOpen = await Linking.canOpenURL(url);
        if (canOpen) {
          await Linking.openURL(url);
        }
      } catch (linkError) {
        console.error("Error opening URL:", linkError);
      }
    } finally {
      setDownloadingDoc(null);
    }
  };

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar style="light" />
      <LinearGradient
        colors={["#03042c", "#1a1a2e", "#16213e"]}
        style={styles.gradient}
      >
        <ScrollView
          style={styles.scrollView}
          contentContainerStyle={styles.scrollContent}
          showsVerticalScrollIndicator={true}
        >
          {/* Success Icon */}
          <View style={styles.successIconContainer}>
            <View style={styles.successIconCircle}>
              <Ionicons name="checkmark-circle" size={100} color="#4EFA8A" />
            </View>
          </View>

          {/* Success Title */}
          <Text style={styles.successTitle}>Application Submitted Successfully!</Text>

          {/* Success Message */}
          <Text style={styles.successMessage}>
            Your loan application has been received and is now being processed. We will review your application and notify you of the status soon.
          </Text>

          {/* Loan Details Card */}
          <View style={styles.successDetailsCard}>
            {loanId && (
              <View style={styles.successDetailRow}>
                <Ionicons name="document-text-outline" size={20} color="#4EFA8A" />
                <Text style={styles.successDetailLabel}>Application ID:</Text>
                <Text style={styles.successDetailValue}>{loanId}</Text>
              </View>
            )}
            <View style={styles.successDetailRow}>
              <Ionicons name="cash-outline" size={20} color="#4EFA8A" />
              <Text style={styles.successDetailLabel}>Loan Amount:</Text>
              <Text style={styles.successDetailValue}>
                ₱{loanAmount.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
              </Text>
            </View>
            <View style={styles.successDetailRow}>
              <Ionicons name="calendar-outline" size={20} color="#4EFA8A" />
              <Text style={styles.successDetailLabel}>Payment Term:</Text>
              <Text style={styles.successDetailValue}>{tenor} {tenor === 1 ? "month" : "months"}</Text>
            </View>
          </View>

          {/* Download Documents Section */}
          {(finalAgreementUrl || finalGuarantyUrl) && (
            <View style={styles.successDownloadsSection}>
              <Text style={styles.successDownloadsTitle}>Download Documents</Text>
              <Text style={styles.successDownloadsSubtitle}>
                Save copies of your loan documents for your records
              </Text>
              
              {/* Loan Agreement Download */}
              {finalAgreementUrl && (
                <TouchableOpacity
                  style={styles.successDownloadButton}
                  onPress={() => handleDownloadDocument(finalAgreementUrl, "Loan Agreement")}
                  disabled={downloadingDoc === "agreement"}
                >
                  {downloadingDoc === "agreement" ? (
                    <ActivityIndicator size="small" color="#4EFA8A" />
                  ) : (
                    <Ionicons name="download-outline" size={20} color="#4EFA8A" />
                  )}
                  <Text style={styles.successDownloadButtonText}>Loan Agreement</Text>
                  <Ionicons name="document-text-outline" size={18} color="#9CA3AF" />
                </TouchableOpacity>
              )}

              {/* Guaranty Agreement Download */}
              {finalGuarantyUrl && (
                <TouchableOpacity
                  style={styles.successDownloadButton}
                  onPress={() => handleDownloadDocument(finalGuarantyUrl, "Guaranty Agreement")}
                  disabled={downloadingDoc === "guaranty"}
                >
                  {downloadingDoc === "guaranty" ? (
                    <ActivityIndicator size="small" color="#4EFA8A" />
                  ) : (
                    <Ionicons name="download-outline" size={20} color="#4EFA8A" />
                  )}
                  <Text style={styles.successDownloadButtonText}>Guaranty Agreement</Text>
                  <Ionicons name="document-text-outline" size={18} color="#9CA3AF" />
                </TouchableOpacity>
              )}
            </View>
          )}

          {/* Next Steps */}
          <View style={styles.successNextSteps}>
            <Text style={styles.successNextStepsTitle}>What&apos;s Next?</Text>
            <View style={styles.successNextStepItem}>
              <Ionicons name="checkmark-circle" size={16} color="#4EFA8A" />
              <Text style={styles.successNextStepText}>Application submitted and documents uploaded</Text>
            </View>
            <View style={styles.successNextStepItem}>
              <Ionicons name="time-outline" size={16} color="#9CA3AF" />
              <Text style={styles.successNextStepText}>Awaiting lender review and approval</Text>
            </View>
            <View style={styles.successNextStepItem}>
              <Ionicons name="notifications-outline" size={16} color="#9CA3AF" />
              <Text style={styles.successNextStepText}>You will receive a notification once reviewed</Text>
            </View>
          </View>
        </ScrollView>

        {/* Action Button - Fixed at bottom */}
        <View style={styles.buttonContainer}>
          <TouchableOpacity
            style={styles.successButton}
            onPress={() => {
              router.replace("/(tabs)");
            }}
          >
            <LinearGradient
              colors={["#4EFA8A", "#30D158"]}
              start={{ x: 0, y: 0 }}
              end={{ x: 1, y: 1 }}
              style={styles.successButtonGradient}
            >
              <Ionicons name="checkmark-circle" size={20} color="#03042c" />
              <Text style={styles.successButtonText}>View My Loans</Text>
            </LinearGradient>
          </TouchableOpacity>
        </View>
      </LinearGradient>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: "#000",
  },
  gradient: {
    flex: 1,
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    padding: 24,
    paddingBottom: 100, // Space for fixed button
    alignItems: "center",
  },
  successIconContainer: {
    marginTop: 40,
    marginBottom: 30,
  },
  successIconCircle: {
    width: 140,
    height: 140,
    borderRadius: 70,
    backgroundColor: "rgba(78, 250, 138, 0.1)",
    justifyContent: "center",
    alignItems: "center",
    borderWidth: 3,
    borderColor: "#4EFA8A",
  },
  successTitle: {
    fontSize: 28,
    fontWeight: "700",
    color: "#fff",
    textAlign: "center",
    marginBottom: 16,
    letterSpacing: 0.5,
    paddingHorizontal: 20,
  },
  successMessage: {
    fontSize: 16,
    color: "#E5E7EB",
    textAlign: "center",
    lineHeight: 24,
    marginBottom: 32,
    paddingHorizontal: 20,
  },
  successDetailsCard: {
    width: "100%",
    backgroundColor: "#2a2a3e",
    borderRadius: 16,
    padding: 20,
    marginBottom: 24,
    borderWidth: 1,
    borderColor: "#374151",
  },
  successDetailRow: {
    flexDirection: "row",
    alignItems: "center",
    marginBottom: 16,
    gap: 12,
  },
  successDetailLabel: {
    fontSize: 15,
    color: "#9CA3AF",
    fontWeight: "500",
    flex: 1,
  },
  successDetailValue: {
    fontSize: 15,
    color: "#fff",
    fontWeight: "600",
  },
  successDownloadsSection: {
    width: "100%",
    marginBottom: 32,
  },
  successDownloadsTitle: {
    fontSize: 18,
    fontWeight: "700",
    color: "#4EFA8A",
    marginBottom: 8,
  },
  successDownloadsSubtitle: {
    fontSize: 13,
    color: "#9CA3AF",
    marginBottom: 20,
    lineHeight: 20,
  },
  successDownloadButton: {
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "space-between",
    backgroundColor: "#2a2a3e",
    borderRadius: 12,
    padding: 16,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: "#374151",
    gap: 12,
  },
  successDownloadButtonText: {
    flex: 1,
    fontSize: 15,
    color: "#fff",
    fontWeight: "600",
    marginLeft: 8,
  },
  successNextSteps: {
    width: "100%",
    marginBottom: 24,
  },
  successNextStepsTitle: {
    fontSize: 18,
    fontWeight: "700",
    color: "#4EFA8A",
    marginBottom: 16,
  },
  successNextStepItem: {
    flexDirection: "row",
    alignItems: "flex-start",
    marginBottom: 12,
    gap: 12,
  },
  successNextStepText: {
    flex: 1,
    fontSize: 14,
    color: "#E5E7EB",
    lineHeight: 22,
  },
  buttonContainer: {
    padding: 24,
    paddingBottom: 40,
    backgroundColor: "transparent",
  },
  successButton: {
    width: "100%",
    borderRadius: 14,
    overflow: "hidden",
  },
  successButtonGradient: {
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "center",
    gap: 10,
    paddingVertical: 18,
    paddingHorizontal: 24,
  },
  successButtonText: {
    color: "#03042c",
    fontSize: 17,
    fontWeight: "700",
    letterSpacing: 0.3,
  },
});

