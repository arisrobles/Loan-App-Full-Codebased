import { useRouter } from "expo-router";
import React, { useState, useMemo, useEffect, useCallback, useRef } from "react";
import {
  ActivityIndicator,
  Alert,
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  ScrollView,
  SafeAreaView,
  TextInput,
  BackHandler,
  Modal,
  Dimensions,
} from "react-native";
import Slider from "@react-native-community/slider";
import { Card } from "react-native-paper";
import { LinearGradient } from "expo-linear-gradient";
import { Ionicons } from "@expo/vector-icons";
import * as DocumentPicker from "expo-document-picker";
import { api } from "../../src/config/api";
import LocationPopup from "../../src/components/LocationPopup";
import { StatusBar } from "expo-status-bar";
import { WebView } from "react-native-webview";
import AsyncStorage from "@react-native-async-storage/async-storage";
import {
  LOAN_CONFIG,
  calculateEMI,
  isValidTenor,
  isValidLoanAmount,
  getInterestRateDecimal,
  getInterestRatePercent,
} from "../../src/config/loanConfig";

type Step = 1 | 2 | 3 | 4 | 5;

interface DocumentFile {
  uri: string;
  name: string;
  type: string;
}

interface BorrowerDetails {
  // Personal Information
  fullName: string;
  firstName?: string;
  middleName?: string;
  lastName?: string;
  dateOfBirth?: string;
  age?: string;
  gender?: string;
  civilStatus: string;
  nationality?: string;
  phone?: string;
  email?: string;
  completeAddress?: string;
  houseNo?: string;
  street?: string;
  barangay?: string;
  municipality?: string;
  city?: string;
  province?: string;
  zipCode?: string;
  validIdNumber?: string;
  address: string; // Keep for backward compatibility
  
  // Employment / Source of Income
  employmentStatus?: string;
  jobTitle?: string;
  employerName?: string;
  employerAddress?: string;
  employerPhone?: string;
  yearsOfEmployment?: string;
  monthlyIncome?: string;
  payFrequency?: string;
  // Self-employed fields
  businessName?: string;
  businessType?: string;
  businessAddress?: string;
  yearsOperating?: string;
  businessIncome?: string;
  
  // Financial Information
  otherMonthlyIncome?: string;
  totalMonthlyExpenses?: string;
  hasExistingLoans?: boolean;
  existingLoanLender?: string;
  existingLoanBalance?: string;
  existingLoanMonthlyAmortization?: string;
  bankName?: string;
  bankAccountNumber?: string;
  bankAccountType?: string;
  
  // References (Character Reference)
  reference1Name?: string;
  reference1Relationship?: string;
  reference1Contact?: string;
  reference1Address?: string;
  reference2Name?: string;
  reference2Relationship?: string;
  reference2Contact?: string;
  reference2Address?: string;
  reference3Name?: string;
  reference3Relationship?: string;
  reference3Contact?: string;
  reference3Address?: string;
  
  // Additional fields from backend
  sex?: string;
  occupation?: string;
  birthday?: string;
}

interface GuarantorDetails {
  fullName: string;
  address: string;
  civilStatus: string;
}

export default function LoanApplicationWizard() {
  const router = useRouter();
  const [currentStep, setCurrentStep] = useState<Step>(1);
  
  // Step 1: Loan Details
  const [loanAmount, setLoanAmount] = useState(13800);
  const [tenor, setTenor] = useState(6);
  const [location, setLocation] = useState<{
    latitude: number;
    longitude: number;
    address?: string;
  } | null>(null);
  const [showLocationPopup, setShowLocationPopup] = useState(false);
  
  // Step 2: Documents
  const [primaryIdFront, setPrimaryIdFront] = useState<DocumentFile | null>(null);
  const [primaryIdBack, setPrimaryIdBack] = useState<DocumentFile | null>(null);
  const [secondaryIdFront, setSecondaryIdFront] = useState<DocumentFile | null>(null);
  const [secondaryIdBack, setSecondaryIdBack] = useState<DocumentFile | null>(null);
  const [signature, setSignature] = useState<DocumentFile | null>(null);
  const [photo2x2, setPhoto2x2] = useState<DocumentFile | null>(null);
  
  // Step 3: Borrower Details
  const [borrowerDetails, setBorrowerDetails] = useState<BorrowerDetails>({
    fullName: "",
    address: "",
    civilStatus: "single",
    phone: "",
    email: "",
    barangay: "", // Initialize to empty string, not undefined
    city: "", // Initialize to empty string, not undefined
  });
  const [updatingProfile, setUpdatingProfile] = useState(false);
  
  // Step 4: Agreement
  const [agreement, setAgreement] = useState<string | null>(null); // Plain text for preview
  const [agreementHtml, setAgreementHtml] = useState<string | null>(null); // HTML for viewing
  const [guarantyAgreement, setGuarantyAgreement] = useState<string | null>(null); // Plain text for preview
  const [guarantyAgreementHtml, setGuarantyAgreementHtml] = useState<string | null>(null); // HTML for viewing
  const [generatingAgreement, setGeneratingAgreement] = useState(false);
  const [city, setCity] = useState("Manila");
  // Demand letter removed from application flow - only shown for overdue payments in loan details
  
  // Word document URLs
  const [agreementWordUrl, setAgreementWordUrl] = useState<string | null>(null);
  const [guarantyAgreementWordUrl, setGuarantyAgreementWordUrl] = useState<string | null>(null);
  
  // Guarantor (optional)
  const [hasGuarantor, setHasGuarantor] = useState(false);
  const [guarantorDetails, setGuarantorDetails] = useState<GuarantorDetails>({
    fullName: "",
    address: "",
    civilStatus: "single",
  });
  
  // Step 5: Submission
  const [submitting, setSubmitting] = useState(false);
  const [loanId, setLoanId] = useState<string | null>(null);
  const [showTermsModal, setShowTermsModal] = useState(false);
  const [termsAccepted, setTermsAccepted] = useState(false);
  const [finalAgreementUrl, setFinalAgreementUrl] = useState<string | null>(null);
  const [finalGuarantyUrl, setFinalGuarantyUrl] = useState<string | null>(null);
  // finalDemandLetterUrl removed - demand letters not generated during application

  // Document viewing
  const [viewingDocument, setViewingDocument] = useState<{
    type: "loan" | "guaranty" | "demand";
    content?: string;
    wordUrl?: string;
  } | null>(null);
  
  // Agreement acknowledgment
  const [agreementViewed, setAgreementViewed] = useState(false);
  const [guarantyAgreementViewed, setGuarantyAgreementViewed] = useState(false);
  // Demand letter viewed state removed - demand letters not shown during application

  // Dropdown and picker states
  const [showNationalityDropdown, setShowNationalityDropdown] = useState(false);
  const [showEmploymentDropdown, setShowEmploymentDropdown] = useState(false);
  const [showDatePicker, setShowDatePicker] = useState(false);

  // Field error states - track which fields have errors
  const [fieldErrors, setFieldErrors] = useState<Set<string>>(new Set());

  // Get interest rate from config
  const interestRate = getInterestRatePercent(); // Annual %

  // Storage keys
  const STORAGE_KEYS = {
    LOAN_PREFERENCES: 'saved_loan_preferences',
    BORROWER_DETAILS: 'saved_borrower_details',
    GUARANTOR_DETAILS: 'saved_guarantor_details',
    LOCATION: 'saved_location',
  };

  // Save loan application data
  const saveApplicationData = async () => {
    try {
      // Save loan preferences
      const loanPreferences = {
        loanAmount,
        tenor,
        city,
      };
      await AsyncStorage.setItem(STORAGE_KEYS.LOAN_PREFERENCES, JSON.stringify(loanPreferences));

      // Save borrower details
      await AsyncStorage.setItem(STORAGE_KEYS.BORROWER_DETAILS, JSON.stringify(borrowerDetails));

      // Save guarantor details if exists
      if (hasGuarantor && guarantorDetails.fullName) {
        await AsyncStorage.setItem(STORAGE_KEYS.GUARANTOR_DETAILS, JSON.stringify({
          hasGuarantor: true,
          ...guarantorDetails,
        }));
      } else {
        await AsyncStorage.setItem(STORAGE_KEYS.GUARANTOR_DETAILS, JSON.stringify({ hasGuarantor: false }));
      }

      // Save location if exists
      if (location) {
        await AsyncStorage.setItem(STORAGE_KEYS.LOCATION, JSON.stringify(location));
      }

      console.log("✅ Application data saved for next loan");
    } catch (error) {
      console.error("Error saving application data:", error);
    }
  };

  // Load saved application data
  const loadSavedApplicationData = async () => {
    try {
      // Load loan preferences
      const savedPreferences = await AsyncStorage.getItem(STORAGE_KEYS.LOAN_PREFERENCES);
      if (savedPreferences) {
        const preferences = JSON.parse(savedPreferences);
        setLoanAmount(preferences.loanAmount || 13800);
        setTenor(preferences.tenor || 6);
        if (preferences.city) setCity(preferences.city);
      }

      // Load borrower details
      const savedBorrowerDetails = await AsyncStorage.getItem(STORAGE_KEYS.BORROWER_DETAILS);
      if (savedBorrowerDetails) {
        const details = JSON.parse(savedBorrowerDetails);
        setBorrowerDetails((prev) => ({ ...prev, ...details }));
      }

      // Load guarantor details
      const savedGuarantor = await AsyncStorage.getItem(STORAGE_KEYS.GUARANTOR_DETAILS);
      if (savedGuarantor) {
        const guarantor = JSON.parse(savedGuarantor);
        if (guarantor.hasGuarantor) {
          setHasGuarantor(true);
          setGuarantorDetails({
            fullName: guarantor.fullName || "",
            address: guarantor.address || "",
            civilStatus: guarantor.civilStatus || "single",
          });
        }
      }

      // Load location
      const savedLocation = await AsyncStorage.getItem(STORAGE_KEYS.LOCATION);
      if (savedLocation) {
        const loc = JSON.parse(savedLocation);
        setLocation(loc);
      }

      console.log("✅ Saved application data loaded");
    } catch (error) {
      console.error("Error loading saved application data:", error);
    }
  };

  // Clear saved application data
  const clearSavedData = async () => {
    try {
      await AsyncStorage.multiRemove([
        STORAGE_KEYS.LOAN_PREFERENCES,
        STORAGE_KEYS.BORROWER_DETAILS,
        STORAGE_KEYS.GUARANTOR_DETAILS,
        STORAGE_KEYS.LOCATION,
      ]);
      Alert.alert("Success", "Saved application data has been cleared.");
      console.log("✅ Saved application data cleared");
    } catch (error) {
      console.error("Error clearing saved data:", error);
      Alert.alert("Error", "Failed to clear saved data.");
    }
  };

  // Helper function to clear field error
  const clearFieldError = (fieldName: string) => {
    if (fieldErrors.has(fieldName)) {
      const newErrors = new Set(fieldErrors);
      newErrors.delete(fieldName);
      setFieldErrors(newErrors);
    }
  };

  // Load saved data on component mount
  useEffect(() => {
    loadSavedApplicationData();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  // Auto-save data when borrower details change (debounced)
  useEffect(() => {
    const timer = setTimeout(() => {
      if (borrowerDetails.fullName || borrowerDetails.email || borrowerDetails.phone) {
        saveApplicationData();
      }
    }, 2000); // Save after 2 seconds of no changes

    return () => clearTimeout(timer);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [borrowerDetails, loanAmount, tenor, hasGuarantor, guarantorDetails, city]);

  // Save data after successful submission
  useEffect(() => {
    if (loanId) {
      saveApplicationData();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [loanId]);

  // Reset document viewed flags when regenerated
  useEffect(() => {
    setAgreementViewed(false);
  }, [agreement, agreementHtml]);
  
  useEffect(() => {
    setGuarantyAgreementViewed(false);
  }, [guarantyAgreement, guarantyAgreementHtml]);

  // Demand letter viewing state removed - demand letters not shown during application

  // Generate HTML for document viewer (PDF-like display)
  const generateDocumentHTML = (content: string, title: string): string => {
    // Convert plain text to HTML with proper formatting
    const htmlContent = content
      .split('\n')
      .map((line) => {
        // Preserve empty lines
        if (line.trim() === '') return '<br/>';
        // Format headers (all caps lines)
        if (line === line.toUpperCase() && line.length > 5 && !line.includes('(')) {
          return `<h2 style="font-size: 16px !important; font-weight: bold; margin-top: 20px; margin-bottom: 10px; text-align: center;">${line}</h2>`;
        }
        // Format signature lines
        if (line.includes('________________') || line.includes('___')) {
          return `<p style="margin: 10px 0; text-align: center; font-size: 12px !important;">${line}</p>`;
        }
        // Regular paragraphs
        return `<p style="margin: 8px 0; line-height: 1.6; font-size: 12px !important; text-align: justify;">${line}</p>`;
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
            h1, h2, h3 {
              color: #ffffff;
              margin-top: 20px;
              margin-bottom: 10px;
            }
            h1 {
              text-align: center;
              border-bottom: 2px solid #4EFA8A;
              padding-bottom: 10px;
            }
            h2 {
              color: #ffffff;
              border-bottom: 2px solid #4EFA8A;
              padding-bottom: 10px;
              text-align: center;
            }
            p {
              color: #e0e0e0;
              margin: 8px 0;
              line-height: 1.6;
              font-size: 12px;
              text-align: justify;
            }
            strong, b {
              color: #ffffff;
              font-weight: bold;
            }
            @media print {
              body {
                background-color: white;
                color: black;
              }
              h1, h2, h3 {
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
          <h1>${title}</h1>
          ${htmlContent}
        </body>
      </html>
    `;
  };

  const civilStatusOptions = [
    { value: "single", label: "Single" },
    { value: "married", label: "Married" },
    { value: "widowed", label: "Widowed" },
    { value: "divorced", label: "Divorced" },
    { value: "separated", label: "Separated" },
  ];

  const genderOptions = [
    { value: "Male", label: "Male" },
    { value: "Female", label: "Female" },
    { value: "Prefer not to say", label: "Prefer not to say" },
  ];

  const employmentStatusOptions = [
    { value: "employed", label: "Employed" },
    { value: "self-employed", label: "Self-employed" },
    { value: "ofw", label: "OFW" },
    { value: "freelancer", label: "Freelancer" },
    { value: "unemployed", label: "Unemployed (with other income source)" },
  ];

  const payFrequencyOptions = [
    { value: "monthly", label: "Monthly" },
    { value: "semi-monthly", label: "Semi-monthly" },
    { value: "weekly", label: "Weekly" },
  ];

  const nationalityOptions = [
    { value: "Filipino", label: "Filipino" },
    { value: "American", label: "American" },
    { value: "Chinese", label: "Chinese" },
    { value: "Japanese", label: "Japanese" },
    { value: "Korean", label: "Korean" },
    { value: "Other", label: "Other" },
  ];

  // Format currency
  const formatCurrency = (value: number) =>
    new Intl.NumberFormat("en-PH", {
      style: "currency",
      currency: "PHP",
    }).format(value);

  // Calculate EMI using centralized function (returns number)
  const emiValue = useMemo(() => {
    return calculateEMI(loanAmount, tenor);
  }, [loanAmount, tenor]);

  // Check if step is complete
  // Step 1: Loan Details
  const renderStep1 = () => (
    <View>
      {/* Saved Data Info Banner */}
      <Card style={[styles.card, styles.savedDataBanner]}>
        <View style={styles.savedDataBannerContent}>
          <Ionicons name="information-circle-outline" size={20} color="#4EFA8A" />
          <Text style={styles.savedDataBannerText}>
            Your information is automatically saved and will be pre-filled for your next loan application.
          </Text>
          <TouchableOpacity
            onPress={() => {
              Alert.alert(
                "Clear Saved Data",
                "Are you sure you want to clear all saved application data? This will remove your saved information for future loan applications.",
                [
                  { text: "Cancel", style: "cancel" },
                  {
                    text: "Clear",
                    style: "destructive",
                    onPress: clearSavedData,
                  },
                ]
              );
            }}
            style={styles.clearSavedDataButton}
          >
            <Ionicons name="trash-outline" size={16} color="#9CA3AF" />
          </TouchableOpacity>
        </View>
      </Card>

      <Text style={styles.stepTitle}>Loan Details</Text>
      <Text style={styles.stepSubtitle}>Select your loan amount and payment terms</Text>

      <Card style={styles.card}>
        <Text style={styles.label}>Select loan amount</Text>
        <Text style={styles.amount}>{formatCurrency(loanAmount)}</Text>
        <Slider
          style={{ width: "100%" }}
          minimumValue={LOAN_CONFIG.AMOUNT.MIN}
          maximumValue={LOAN_CONFIG.AMOUNT.MAX}
          step={100}
          minimumTrackTintColor="#f97316"
          maximumTrackTintColor="#ddd"
          thumbTintColor="#f97316"
          value={loanAmount}
          onValueChange={setLoanAmount}
        />
        <View style={styles.rangeRow}>
          <Text style={styles.rangeText}>{formatCurrency(LOAN_CONFIG.AMOUNT.MIN)}</Text>
          <Text style={styles.rangeText}>{formatCurrency(LOAN_CONFIG.AMOUNT.MAX)}</Text>
        </View>
      </Card>

      <Text style={styles.label}>Choose your tenor (1-18 months)</Text>
      <ScrollView 
        showsVerticalScrollIndicator={false}
        contentContainerStyle={styles.tenorScrollContainer}
      >
        <View style={styles.tenorGrid}>
          {LOAN_CONFIG.TENOR.OPTIONS.map((item, index) => {
            // Remove right margin for every 6th item (end of row)
            const isLastInRow = (index + 1) % 6 === 0;
            return (
          <TouchableOpacity
            key={item}
              style={[
                styles.tenorBox, 
                tenor === item && styles.activeTenor,
                isLastInRow && styles.tenorBoxLastInRow
              ]}
            onPress={() => setTenor(item)}
          >
            <Text
              style={[
                styles.tenorText,
                tenor === item && styles.activeTenorText,
              ]}
            >
                {item}
            </Text>
          </TouchableOpacity>
          );
        })}
      </View>
      </ScrollView>

      <Card style={styles.card}>
        <Text style={styles.detailText}>EMI Tenure: {tenor} months</Text>
        <Text style={styles.detailText}>Annual Interest Rate: {interestRate}%</Text>
        <Text style={styles.detailText}>
          Monthly EMI: {formatCurrency(emiValue)}
        </Text>
        <Text style={styles.detailText}>
          Total Amount Payable: {formatCurrency(emiValue * tenor)}
        </Text>
      </Card>

      <Card style={styles.card}>
        <View style={styles.locationHeader}>
          <Ionicons name="location" size={20} color="#f97316" />
          <Text style={styles.label}>Application Location *</Text>
          <Text style={styles.requiredIndicator}>(Required)</Text>
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
          <View>
            <TouchableOpacity
              style={styles.getLocationButton}
              onPress={() => setShowLocationPopup(true)}
            >
              <Ionicons name="location-outline" size={18} color="#fff" />
              <Text style={styles.getLocationText}>Get My Location</Text>
            </TouchableOpacity>
            <View style={styles.locationWarning}>
              <Ionicons name="warning-outline" size={16} color="#f97316" />
              <Text style={styles.locationWarningText}>
                Location is required to proceed with your loan application.
              </Text>
            </View>
          </View>
        )}
      </Card>
    </View>
  );

  // Step 2: Documents
  const handlePickDocument = async (
    setter: (doc: DocumentFile | null) => void
  ) => {
    try {
      const result = await DocumentPicker.getDocumentAsync({
        type: ["image/jpeg", "image/png", "image/jpg", "application/pdf"],
        copyToCacheDirectory: true,
      });

      if (!result.canceled && result.assets && result.assets.length > 0) {
        const asset = result.assets[0];
        
        // Validate file type
        const allowedTypes = ["image/jpeg", "image/png", "image/jpg", "application/pdf"];
        const fileType = asset.mimeType || "application/pdf";
        
        if (!allowedTypes.includes(fileType)) {
          Alert.alert(
            "Invalid File Type",
            "Please select a JPG, PNG, or PDF file. Other file types are not supported.",
            [{ text: "OK" }]
          );
          return;
        }
        
        setter({
          uri: asset.uri,
          name: asset.name || "document",
          type: fileType,
        });
      }
    } catch (error) {
      console.error("Error picking document:", error);
      Alert.alert("Error", "Failed to pick document");
    }
  };


  // Upload documents (called during final submission - ONLY when Submit button is clicked)
  const uploadDocuments = async (loanIdParam: string): Promise<boolean> => {
    if (!primaryIdFront || !primaryIdBack || !secondaryIdFront || !secondaryIdBack || !signature || !photo2x2) {
      Alert.alert("Missing Documents", "Please upload all required documents: Primary ID (front & back), Secondary ID (front & back), Signature, and 2x2 Photo");
      return false;
    }

    if (!loanIdParam) {
      Alert.alert("Error", "Loan ID is required to upload documents");
      return false;
    }

    try {
      console.log("📤 Uploading documents during final submission...");

      // Upload Primary ID Front
      const primaryFrontFormData = new FormData();
      primaryFrontFormData.append("file", {
        uri: primaryIdFront.uri,
        name: primaryIdFront.name || "primary_id_front.pdf",
        type: primaryIdFront.type || "application/pdf",
      } as any);
      primaryFrontFormData.append("documentType", "PRIMARY_ID");
      primaryFrontFormData.append("loanId", loanIdParam);

      console.log("📤 Uploading Primary ID Front...");
      await api.post("/documents/upload", primaryFrontFormData, {
        headers: { 'Content-Type': 'multipart/form-data' },
        timeout: 60000,
      });

      // Upload Primary ID Back
      const primaryBackFormData = new FormData();
      primaryBackFormData.append("file", {
        uri: primaryIdBack.uri,
        name: primaryIdBack.name || "primary_id_back.pdf",
        type: primaryIdBack.type || "application/pdf",
      } as any);
      primaryBackFormData.append("documentType", "PRIMARY_ID");
      primaryBackFormData.append("loanId", loanIdParam);

      console.log("📤 Uploading Primary ID Back...");
      await api.post("/documents/upload", primaryBackFormData, {
        headers: { 'Content-Type': 'multipart/form-data' },
        timeout: 60000,
      });

      // Upload Secondary ID Front
      const secondaryFrontFormData = new FormData();
      secondaryFrontFormData.append("file", {
        uri: secondaryIdFront.uri,
        name: secondaryIdFront.name || "secondary_id_front.pdf",
        type: secondaryIdFront.type || "application/pdf",
      } as any);
      secondaryFrontFormData.append("documentType", "SECONDARY_ID");
      secondaryFrontFormData.append("loanId", loanIdParam);

      console.log("📤 Uploading Secondary ID Front...");
      await api.post("/documents/upload", secondaryFrontFormData, {
        headers: { 'Content-Type': 'multipart/form-data' },
        timeout: 60000,
      });

      // Upload Secondary ID Back
      const secondaryBackFormData = new FormData();
      secondaryBackFormData.append("file", {
        uri: secondaryIdBack.uri,
        name: secondaryIdBack.name || "secondary_id_back.pdf",
        type: secondaryIdBack.type || "application/pdf",
      } as any);
      secondaryBackFormData.append("documentType", "SECONDARY_ID");
      secondaryBackFormData.append("loanId", loanIdParam);

      console.log("📤 Uploading Secondary ID Back...");
      await api.post("/documents/upload", secondaryBackFormData, {
        headers: { 'Content-Type': 'multipart/form-data' },
        timeout: 60000,
      });

      // Upload Signature
      const signatureFormData = new FormData();
      signatureFormData.append("file", {
        uri: signature.uri,
        name: signature.name || "signature.pdf",
        type: signature.type || "application/pdf",
      } as any);
      signatureFormData.append("documentType", "SIGNATURE");
      signatureFormData.append("loanId", loanIdParam);

      console.log("📤 Uploading Signature...");
      await api.post("/documents/upload", signatureFormData, {
        headers: { 'Content-Type': 'multipart/form-data' },
        timeout: 60000,
      });

      // Upload 2x2 Photo
      const photo2x2FormData = new FormData();
      photo2x2FormData.append("file", {
        uri: photo2x2.uri,
        name: photo2x2.name || "photo_2x2.jpg",
        type: photo2x2.type || "image/jpeg",
      } as any);
      photo2x2FormData.append("documentType", "PHOTO_2X2");
      photo2x2FormData.append("loanId", loanIdParam);

      console.log("📤 Uploading 2x2 Photo...");
      await api.post("/documents/upload", photo2x2FormData, {
        headers: { 'Content-Type': 'multipart/form-data' },
        timeout: 60000,
      });

      console.log("✅ All documents uploaded successfully");

      return true;
    } catch (error: any) {
      console.error("❌ Error uploading documents:", error);
      let errorMessage = "Failed to upload documents";
      
      if (error.code === 'ECONNABORTED' || error.message?.includes('timeout')) {
        errorMessage = "Upload timeout. Please check your internet connection and try again.";
      } else if (error.code === 'ERR_NETWORK' || error.message === 'Network Error' || !error.response) {
        errorMessage = `Network error. Please ensure the backend server is running on port 8080.`;
      } else if (error.response?.data?.message) {
        errorMessage = error.response.data.message;
      } else if (error.message) {
        errorMessage = error.message;
      }
      
      Alert.alert("Upload Error", errorMessage);
      return false;
    }
  };

  const renderStep2 = () => (
    <View>
      <View style={styles.stepHeader}>
        <Ionicons name="document-text-outline" size={32} color="#4EFA8A" />
        <Text style={styles.stepTitle}>Required Documents</Text>
      </View>
      <Text style={styles.stepSubtitle}>
        Upload both front and back of your valid IDs. Documents will be uploaded when you submit your application.
      </Text>

      {/* Primary ID */}
      <Card style={styles.card}>
        <View>
          <View style={styles.sectionHeader}>
            <Ionicons name="id-card-outline" size={20} color="#4EFA8A" />
            <Text style={styles.sectionTitle}>Primary ID *</Text>
          </View>
          <Text style={styles.helperText}>
            (e.g., Driver&apos;s License, Passport, National ID)
          </Text>
          
          <View style={styles.uploadRow}>
            <View style={styles.uploadColumn}>
              <Text style={styles.uploadLabel}>Front *</Text>
              <TouchableOpacity
                style={[styles.uploadButton, primaryIdFront && styles.uploadButtonSuccess]}
                onPress={() => handlePickDocument(setPrimaryIdFront)}
              >
                <Ionicons
                  name={primaryIdFront ? "checkmark-circle" : "camera-outline"}
                  size={24}
                  color={primaryIdFront ? "#4EFA8A" : "#fff"}
                />
                <Text style={styles.uploadButtonText}>
                  {primaryIdFront ? primaryIdFront.name : "Upload Front"}
                </Text>
              </TouchableOpacity>
              {primaryIdFront && (
                <TouchableOpacity
                  style={styles.removeButton}
                  onPress={() => setPrimaryIdFront(null)}
                >
                  <Text style={styles.removeButtonText}>Remove</Text>
                </TouchableOpacity>
              )}
            </View>

            <View style={styles.uploadColumn}>
              <Text style={styles.uploadLabel}>Back *</Text>
              <TouchableOpacity
                style={[styles.uploadButton, primaryIdBack && styles.uploadButtonSuccess]}
                onPress={() => handlePickDocument(setPrimaryIdBack)}
              >
                <Ionicons
                  name={primaryIdBack ? "checkmark-circle" : "camera-outline"}
                  size={24}
                  color={primaryIdBack ? "#4EFA8A" : "#fff"}
                />
                <Text style={styles.uploadButtonText}>
                  {primaryIdBack ? primaryIdBack.name : "Upload Back"}
                </Text>
              </TouchableOpacity>
              {primaryIdBack && (
                <TouchableOpacity
                  style={styles.removeButton}
                  onPress={() => setPrimaryIdBack(null)}
                >
                  <Text style={styles.removeButtonText}>Remove</Text>
                </TouchableOpacity>
              )}
            </View>
          </View>
        </View>
      </Card>

      {/* Secondary ID */}
      <Card style={styles.card}>
        <View>
          <View style={styles.sectionHeader}>
            <Ionicons name="card-outline" size={20} color="#4EFA8A" />
            <Text style={styles.sectionTitle}>Secondary ID *</Text>
          </View>
          <Text style={styles.helperText}>
            (e.g., TIN, SSS, PhilHealth, Postal ID)
          </Text>
          
          <View style={styles.uploadRow}>
            <View style={styles.uploadColumn}>
              <Text style={styles.uploadLabel}>Front *</Text>
              <TouchableOpacity
                style={[styles.uploadButton, secondaryIdFront && styles.uploadButtonSuccess]}
                onPress={() => handlePickDocument(setSecondaryIdFront)}
              >
                <Ionicons
                  name={secondaryIdFront ? "checkmark-circle" : "camera-outline"}
                  size={24}
                  color={secondaryIdFront ? "#4EFA8A" : "#fff"}
                />
                <Text style={styles.uploadButtonText}>
                  {secondaryIdFront ? secondaryIdFront.name : "Upload Front"}
                </Text>
              </TouchableOpacity>
              {secondaryIdFront && (
                <TouchableOpacity
                  style={styles.removeButton}
                  onPress={() => setSecondaryIdFront(null)}
                >
                  <Text style={styles.removeButtonText}>Remove</Text>
                </TouchableOpacity>
              )}
            </View>

            <View style={styles.uploadColumn}>
              <Text style={styles.uploadLabel}>Back *</Text>
              <TouchableOpacity
                style={[styles.uploadButton, secondaryIdBack && styles.uploadButtonSuccess]}
                onPress={() => handlePickDocument(setSecondaryIdBack)}
              >
                <Ionicons
                  name={secondaryIdBack ? "checkmark-circle" : "camera-outline"}
                  size={24}
                  color={secondaryIdBack ? "#4EFA8A" : "#fff"}
                />
                <Text style={styles.uploadButtonText}>
                  {secondaryIdBack ? secondaryIdBack.name : "Upload Back"}
                </Text>
              </TouchableOpacity>
              {secondaryIdBack && (
                <TouchableOpacity
                  style={styles.removeButton}
                  onPress={() => setSecondaryIdBack(null)}
                >
                  <Text style={styles.removeButtonText}>Remove</Text>
                </TouchableOpacity>
              )}
            </View>
          </View>
        </View>
      </Card>

    </View>
  );

  // Step 3: Borrower Details
  const profileLoadedRef = useRef(false);
  useEffect(() => {
    // Load profile once on first entry to Step 3; avoid overwriting in-progress edits when navigating back
    if (currentStep === 3 && !profileLoadedRef.current) {
      fetchBorrowerProfile();
    }
  }, [currentStep]);

  const fetchBorrowerProfile = async () => {
    try {
      const res = await api.get("/users/profile");
      if (res.data) {
        setBorrowerDetails((prev) => ({
          ...prev,
          fullName: res.data.fullName ?? prev.fullName ?? "",
          address: res.data.address ?? prev.address ?? "",
          completeAddress: res.data.address ?? prev.completeAddress ?? prev.address,
          civilStatus: res.data.civilStatus ?? prev.civilStatus ?? "single",
          phone: res.data.phone ?? prev.phone ?? "",
          email: res.data.email ?? prev.email ?? "",
          gender: res.data.sex ?? prev.gender ?? "",
          occupation: res.data.occupation ?? prev.occupation ?? "",
          monthlyIncome: res.data.monthlyIncome ?? prev.monthlyIncome ?? "",
          birthday: res.data.birthday ?? prev.birthday ?? "",
          hasExistingLoans: prev.hasExistingLoans ?? false,
          // Preserve barangay and city from form (not stored in backend)
          barangay: prev.barangay ?? "",
          city: prev.city ?? "",
        }));
        profileLoadedRef.current = true;
      }
    } catch (error) {
      console.error("Error fetching profile:", error);
    }
  };


  const renderStep3 = () => {
    const isEmployed = borrowerDetails.employmentStatus === "employed";
    const isSelfEmployed = borrowerDetails.employmentStatus === "self-employed";
    const isOFW = borrowerDetails.employmentStatus === "ofw";
    const isFreelancer = borrowerDetails.employmentStatus === "freelancer";

    return (
      <View>
        <Text style={styles.stepTitle}>Borrower Information</Text>
        <Text style={styles.stepSubtitle}>
          Please provide complete and accurate information for your loan application
        </Text>

        {/* 1. Personal Information */}
        <Card style={styles.card}>
          <View style={styles.sectionHeader}>
            <Ionicons name="person-outline" size={20} color="#4EFA8A" />
            <Text style={styles.sectionTitle}>Personal Information</Text>
          </View>

          <View style={styles.inputGroup}>
            <View style={styles.labelRow}>
              <Ionicons name="person-outline" size={16} color="#9CA3AF" />
              <Text style={styles.label}>Full Name (First, Middle, Last) *</Text>
            </View>
            <View style={styles.inputContainer}>
              <TextInput
                style={[
                  styles.input,
                  fieldErrors.has("fullName") && styles.inputError
                ]}
                value={borrowerDetails.fullName}
                onChangeText={(text) => {
                  setBorrowerDetails({ ...borrowerDetails, fullName: text });
                  // Clear error when user starts typing
                  if (fieldErrors.has("fullName")) {
                    const newErrors = new Set(fieldErrors);
                    newErrors.delete("fullName");
                    setFieldErrors(newErrors);
                  }
                }}
                placeholder="Enter your complete legal name"
                placeholderTextColor="#6B7280"
              />
              {fieldErrors.has("fullName") && (
                <Text style={styles.errorText}>Full Name is required</Text>
              )}
            </View>
          </View>

          <View style={styles.inputGroup}>
            <View style={styles.labelRow}>
              <Ionicons name="calendar-outline" size={16} color="#9CA3AF" />
              <Text style={styles.label}>Date of Birth *</Text>
            </View>
            <View>
              <TouchableOpacity
                style={[
                  styles.dropdownButton,
                  fieldErrors.has("dateOfBirth") && styles.dropdownButtonError
                ]}
                onPress={() => setShowDatePicker(true)}
              >
                <Text style={[styles.dropdownButtonText, !borrowerDetails.dateOfBirth && styles.placeholderText]}>
                  {borrowerDetails.dateOfBirth || "Select Date of Birth"}
                </Text>
                <Ionicons name="calendar-outline" size={20} color="#9CA3AF" />
              </TouchableOpacity>
              {fieldErrors.has("dateOfBirth") && (
                <Text style={styles.errorText}>Date of Birth is required</Text>
              )}
            </View>
          </View>

          {/* Date Picker Modal */}
          <Modal
            visible={showDatePicker}
            transparent={true}
            animationType="slide"
            onRequestClose={() => setShowDatePicker(false)}
          >
            <TouchableOpacity 
              style={styles.modalOverlay}
              activeOpacity={1}
              onPress={() => setShowDatePicker(false)}
            >
              <TouchableOpacity activeOpacity={1} onPress={(e) => e.stopPropagation()}>
                <View style={styles.datePickerModal}>
                  <View style={styles.modalHeader}>
                    <Text style={styles.modalTitle}>Select Date of Birth</Text>
                    <TouchableOpacity onPress={() => setShowDatePicker(false)}>
                      <Ionicons name="close" size={24} color="#fff" />
                    </TouchableOpacity>
                  </View>
                  <View style={styles.datePickerContainer}>
                    {/* Year Picker */}
                    <View style={styles.datePickerColumn}>
                      <Text style={styles.datePickerLabel}>Year</Text>
                      <ScrollView style={styles.datePickerScroll} showsVerticalScrollIndicator={false}>
                        {Array.from({ length: 100 }, (_, i) => new Date().getFullYear() - 18 - i).map((year) => {
                          const currentDate = borrowerDetails.dateOfBirth ? borrowerDetails.dateOfBirth.split('/') : ['', '', ''];
                          const isSelected = currentDate[2] === year.toString();
                          return (
                            <TouchableOpacity
                              key={year}
                              style={[
                                styles.datePickerOption,
                                isSelected && styles.datePickerOptionSelected,
                              ]}
                              onPress={() => {
                                const newDate = `${currentDate[0] || '01'}/${currentDate[1] || '01'}/${year}`;
                                setBorrowerDetails({ ...borrowerDetails, dateOfBirth: newDate });
                                clearFieldError("dateOfBirth");
                              }}
                            >
                              <Text style={[
                                styles.datePickerOptionText,
                                isSelected && styles.datePickerOptionTextSelected,
                              ]}>
                                {year}
                              </Text>
                            </TouchableOpacity>
                          );
                        })}
                      </ScrollView>
                    </View>
                    {/* Month Picker */}
                    <View style={styles.datePickerColumn}>
                      <Text style={styles.datePickerLabel}>Month</Text>
                      <ScrollView style={styles.datePickerScroll} showsVerticalScrollIndicator={false}>
                        {['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'].map((month) => {
                          const currentDate = borrowerDetails.dateOfBirth ? borrowerDetails.dateOfBirth.split('/') : ['', '', ''];
                          const isSelected = currentDate[0] === month;
                          return (
                            <TouchableOpacity
                              key={month}
                              style={[
                                styles.datePickerOption,
                                isSelected && styles.datePickerOptionSelected,
                              ]}
                              onPress={() => {
                                const newDate = `${month}/${currentDate[1] || '01'}/${currentDate[2] || new Date().getFullYear() - 18}`;
                                setBorrowerDetails({ ...borrowerDetails, dateOfBirth: newDate });
                                clearFieldError("dateOfBirth");
                              }}
                            >
                              <Text style={[
                                styles.datePickerOptionText,
                                isSelected && styles.datePickerOptionTextSelected,
                              ]}>
                                {new Date(2000, parseInt(month) - 1).toLocaleString('default', { month: 'short' })}
                              </Text>
                            </TouchableOpacity>
                          );
                        })}
                      </ScrollView>
                    </View>
                    {/* Day Picker */}
                    <View style={styles.datePickerColumn}>
                      <Text style={styles.datePickerLabel}>Day</Text>
                      <ScrollView style={styles.datePickerScroll} showsVerticalScrollIndicator={false}>
                        {Array.from({ length: 31 }, (_, i) => String(i + 1).padStart(2, '0')).map((day) => {
                          const currentDate = borrowerDetails.dateOfBirth ? borrowerDetails.dateOfBirth.split('/') : ['01', '', ''];
                          const isSelected = currentDate[1] === day;
                          return (
                            <TouchableOpacity
                              key={day}
                              style={[
                                styles.datePickerOption,
                                isSelected && styles.datePickerOptionSelected,
                              ]}
                              onPress={() => {
                                const newDate = `${currentDate[0] || '01'}/${day}/${currentDate[2] || new Date().getFullYear() - 18}`;
                                setBorrowerDetails({ ...borrowerDetails, dateOfBirth: newDate });
                                clearFieldError("dateOfBirth");
                              }}
                            >
                              <Text style={[
                                styles.datePickerOptionText,
                                isSelected && styles.datePickerOptionTextSelected,
                              ]}>
                                {day}
                              </Text>
                            </TouchableOpacity>
                          );
                        })}
                      </ScrollView>
                    </View>
                  </View>
                  <TouchableOpacity
                    style={styles.datePickerConfirmButton}
                    onPress={() => setShowDatePicker(false)}
                  >
                    <Text style={styles.datePickerConfirmText}>Confirm</Text>
                  </TouchableOpacity>
                </View>
              </TouchableOpacity>
            </TouchableOpacity>
          </Modal>

          <View style={styles.inputGroup}>
            <View style={styles.labelRow}>
              <Ionicons name="time-outline" size={16} color="#9CA3AF" />
              <Text style={styles.label}>Age</Text>
            </View>
            <View style={styles.inputContainer}>
              <TextInput
                style={styles.input}
                value={borrowerDetails.age}
                onChangeText={(text) =>
                  setBorrowerDetails({ ...borrowerDetails, age: text })
                }
                placeholder="Enter your age"
                placeholderTextColor="#6B7280"
                keyboardType="numeric"
                maxLength={3}
              />
            </View>
          </View>

          <View style={styles.inputGroup}>
            <View style={styles.labelRow}>
              <Ionicons name="male-female-outline" size={16} color="#9CA3AF" />
              <Text style={styles.label}>Gender *</Text>
            </View>
            <View style={styles.checkboxContainer}>
              {genderOptions.map((option) => (
                <TouchableOpacity
                  key={option.value}
                  style={styles.checkboxRow}
                  onPress={() => {
                    setBorrowerDetails({
                      ...borrowerDetails,
                      gender: option.value,
                    });
                    clearFieldError("gender");
                  }}
                >
                  <View style={[
                    styles.checkbox,
                    borrowerDetails.gender === option.value && styles.checkboxChecked,
                  ]}>
                    {borrowerDetails.gender === option.value && (
                      <Ionicons name="checkmark" size={16} color="#fff" />
                    )}
                  </View>
                  <Text style={styles.checkboxLabel}>{option.label}</Text>
                </TouchableOpacity>
              ))}
            </View>
            {fieldErrors.has("gender") && (
              <Text style={styles.errorText}>Gender is required</Text>
            )}
          </View>

          <View style={styles.inputGroup}>
            <View style={styles.labelRow}>
              <Ionicons name="heart-outline" size={16} color="#9CA3AF" />
              <Text style={styles.label}>Civil Status *</Text>
            </View>
            <View style={styles.checkboxContainer}>
              {civilStatusOptions.map((option) => (
                <TouchableOpacity
                  key={option.value}
                  style={styles.checkboxRow}
                  onPress={() => {
                    setBorrowerDetails({
                      ...borrowerDetails,
                      civilStatus: option.value,
                    });
                    clearFieldError("civilStatus");
                  }}
                >
                  <View style={[
                    styles.checkbox,
                    borrowerDetails.civilStatus === option.value && styles.checkboxChecked,
                  ]}>
                    {borrowerDetails.civilStatus === option.value && (
                      <Ionicons name="checkmark" size={16} color="#fff" />
                    )}
                  </View>
                  <Text style={styles.checkboxLabel}>{option.label}</Text>
                </TouchableOpacity>
              ))}
            </View>
            {fieldErrors.has("civilStatus") && (
              <Text style={styles.errorText}>Civil Status is required</Text>
            )}
          </View>

          <View style={styles.inputGroup}>
            <View style={styles.labelRow}>
              <Ionicons name="flag-outline" size={16} color="#9CA3AF" />
              <Text style={styles.label}>Nationality *</Text>
            </View>
            <TouchableOpacity
              style={[
                styles.dropdownButton,
                fieldErrors.has("nationality") && styles.dropdownButtonError
              ]}
              onPress={() => setShowNationalityDropdown(!showNationalityDropdown)}
            >
              <Text style={[styles.dropdownButtonText, !borrowerDetails.nationality && styles.placeholderText]}>
                {borrowerDetails.nationality 
                  ? nationalityOptions.find(opt => opt.value === borrowerDetails.nationality)?.label 
                  : "Select Nationality"}
              </Text>
              <Ionicons 
                name={showNationalityDropdown ? "chevron-up" : "chevron-down"} 
                size={20} 
                color="#9CA3AF" 
              />
            </TouchableOpacity>
            {showNationalityDropdown && (
              <View style={styles.dropdownList}>
                {nationalityOptions.map((option) => (
                  <TouchableOpacity
                    key={option.value}
                    style={[
                      styles.dropdownItem,
                      borrowerDetails.nationality === option.value && styles.dropdownItemSelected,
                    ]}
                    onPress={() => {
                      setBorrowerDetails({ ...borrowerDetails, nationality: option.value });
                      setShowNationalityDropdown(false);
                      clearFieldError("nationality");
                    }}
                  >
                    <Text style={[
                      styles.dropdownItemText,
                      borrowerDetails.nationality === option.value && styles.dropdownItemTextSelected,
                    ]}>
                      {option.label}
                    </Text>
                    {borrowerDetails.nationality === option.value && (
                      <Ionicons name="checkmark" size={18} color="#4EFA8A" />
                    )}
                  </TouchableOpacity>
                ))}
              </View>
            )}
            {fieldErrors.has("nationality") && (
              <Text style={styles.errorText}>Nationality is required</Text>
            )}
          </View>

          <View style={styles.inputGroup}>
            <View style={styles.labelRow}>
              <Ionicons name="call-outline" size={16} color="#9CA3AF" />
              <Text style={styles.label}>Contact Number (Mobile) *</Text>
            </View>
            <Text style={styles.helperText}>11-digit mobile number starting with 09</Text>
            <View style={styles.inputContainer}>
              <TextInput
                style={[
                  styles.input,
                  fieldErrors.has("phone") && styles.inputError
                ]}
                value={borrowerDetails.phone}
                onChangeText={(text) => {
                  setBorrowerDetails({ ...borrowerDetails, phone: text });
                  clearFieldError("phone");
                }}
                placeholder="09XX XXX XXXX"
                placeholderTextColor="#6B7280"
                keyboardType="phone-pad"
                maxLength={11}
              />
              {fieldErrors.has("phone") && (
                <Text style={styles.errorText}>Contact Number is required</Text>
              )}
            </View>
          </View>

          <View style={styles.inputGroup}>
            <View style={styles.labelRow}>
              <Ionicons name="mail-outline" size={16} color="#9CA3AF" />
              <Text style={styles.label}>Email Address *</Text>
            </View>
            <View style={styles.inputContainer}>
              <TextInput
                style={[
                  styles.input,
                  fieldErrors.has("email") && styles.inputError
                ]}
                value={borrowerDetails.email}
                onChangeText={(text) => {
                  setBorrowerDetails({ ...borrowerDetails, email: text });
                  clearFieldError("email");
                }}
                placeholder="your.email@example.com"
                placeholderTextColor="#6B7280"
                keyboardType="email-address"
                autoCapitalize="none"
              />
              {fieldErrors.has("email") && (
                <Text style={styles.errorText}>Email Address is required</Text>
              )}
            </View>
          </View>

          <View style={styles.addressSection}>
            <Text style={styles.sectionSubtitle}>Complete Address Details</Text>
            
            <View style={styles.inputGroup}>
              <View style={styles.labelRow}>
                <Ionicons name="home-outline" size={16} color="#9CA3AF" />
                <Text style={styles.label}>House No./Street *</Text>
              </View>
              <View style={styles.inputContainer}>
                <TextInput
                  style={[
                    styles.input,
                    fieldErrors.has("houseNo") && styles.inputError
                  ]}
                  value={borrowerDetails.houseNo}
                  onChangeText={(text) => {
                    setBorrowerDetails({ ...borrowerDetails, houseNo: text });
                    clearFieldError("houseNo");
                  }}
                  placeholder="House/Unit No., Street Name"
                  placeholderTextColor="#6B7280"
                />
                {fieldErrors.has("houseNo") && (
                  <Text style={styles.errorText}>House Number/Street is required</Text>
                )}
              </View>
            </View>

            <View style={styles.inputGroup}>
              <View style={styles.labelRow}>
                <Ionicons name="location-outline" size={16} color="#9CA3AF" />
                <Text style={styles.label}>Barangay *</Text>
              </View>
              <View style={styles.inputContainer}>
                <TextInput
                  style={[
                    styles.input,
                    fieldErrors.has("barangay") && styles.inputError
                  ]}
                  value={borrowerDetails.barangay}
                  onChangeText={(text) => {
                    setBorrowerDetails({ ...borrowerDetails, barangay: text });
                    clearFieldError("barangay");
                  }}
                  placeholder="Enter barangay"
                  placeholderTextColor="#6B7280"
                />
                {fieldErrors.has("barangay") && (
                  <Text style={styles.errorText}>Barangay is required</Text>
                )}
              </View>
            </View>

            <View style={styles.inputGroup}>
              <View style={styles.labelRow}>
                <Ionicons name="business-outline" size={16} color="#9CA3AF" />
                <Text style={styles.label}>Municipality/City *</Text>
              </View>
              <View style={styles.inputContainer}>
                <TextInput
                  style={[
                    styles.input,
                    fieldErrors.has("city") && styles.inputError
                  ]}
                  value={borrowerDetails.city}
                  onChangeText={(text) => {
                    setBorrowerDetails({ ...borrowerDetails, city: text });
                    clearFieldError("city");
                  }}
                  placeholder="Enter municipality or city"
                  placeholderTextColor="#6B7280"
                />
                {fieldErrors.has("city") && (
                  <Text style={styles.errorText}>Municipality/City is required</Text>
                )}
              </View>
            </View>

            <View style={styles.inputGroup}>
              <View style={styles.labelRow}>
                <Ionicons name="map-outline" size={16} color="#9CA3AF" />
                <Text style={styles.label}>Province *</Text>
              </View>
              <View style={styles.inputContainer}>
                <TextInput
                  style={[
                    styles.input,
                    fieldErrors.has("province") && styles.inputError
                  ]}
                  value={borrowerDetails.province}
                  onChangeText={(text) => {
                    setBorrowerDetails({ ...borrowerDetails, province: text });
                    clearFieldError("province");
                  }}
                  placeholder="Enter province"
                  placeholderTextColor="#6B7280"
                />
                {fieldErrors.has("province") && (
                  <Text style={styles.errorText}>Province is required</Text>
                )}
              </View>
            </View>

            <View style={styles.inputGroup}>
              <View style={styles.labelRow}>
                <Ionicons name="mail-outline" size={16} color="#9CA3AF" />
                <Text style={styles.label}>ZIP Code</Text>
              </View>
              <View style={styles.inputContainer}>
                <TextInput
                  style={styles.input}
                  value={borrowerDetails.zipCode}
                  onChangeText={(text) =>
                    setBorrowerDetails({ ...borrowerDetails, zipCode: text })
                  }
                  placeholder="Enter ZIP code"
                  placeholderTextColor="#6B7280"
                  keyboardType="numeric"
                  maxLength={4}
                />
              </View>
            </View>

            <View style={styles.inputGroup}>
              <View style={styles.labelRow}>
                <Ionicons name="document-text-outline" size={16} color="#9CA3AF" />
                <Text style={styles.label}>Complete Address *</Text>
              </View>
              <Text style={styles.helperText}>Full address will be auto-generated from above fields</Text>
              <View style={styles.inputContainer}>
                <TextInput
                  style={[styles.input, styles.textArea]}
                  value={borrowerDetails.completeAddress || borrowerDetails.address}
                  onChangeText={(text) =>
                    setBorrowerDetails({ 
                      ...borrowerDetails, 
                      completeAddress: text,
                      address: text, // Keep for backward compatibility
                    })
                  }
                  placeholder="Full address (auto-filled from above fields)"
                  placeholderTextColor="#6B7280"
                  multiline
                  numberOfLines={3}
                />
              </View>
            </View>
          </View>

          <View style={styles.inputGroup}>
            <View style={styles.labelRow}>
              <Ionicons name="id-card-outline" size={16} color="#9CA3AF" />
              <Text style={styles.label}>Valid ID Number *</Text>
            </View>
            <Text style={styles.helperText}>Enter the ID number from your uploaded Primary ID</Text>
            <View style={styles.inputContainer}>
              <TextInput
                style={[
                  styles.input,
                  fieldErrors.has("validIdNumber") && styles.inputError
                ]}
                value={borrowerDetails.validIdNumber}
                onChangeText={(text) => {
                  setBorrowerDetails({ ...borrowerDetails, validIdNumber: text });
                  clearFieldError("validIdNumber");
                }}
                placeholder="Enter your ID number"
                placeholderTextColor="#6B7280"
              />
              {fieldErrors.has("validIdNumber") && (
                <Text style={styles.errorText}>Valid ID Number is required</Text>
              )}
            </View>
          </View>
        </Card>

        {/* 2. Employment / Source of Income */}
        <Card style={styles.card}>
          <View style={styles.sectionHeader}>
            <Ionicons name="briefcase-outline" size={20} color="#4EFA8A" />
            <Text style={styles.sectionTitle}>Employment / Source of Income</Text>
          </View>

          <View style={styles.inputGroup}>
            <View style={styles.labelRow}>
              <Ionicons name="briefcase-outline" size={16} color="#9CA3AF" />
              <Text style={styles.label}>Employment Status *</Text>
            </View>
            <Text style={styles.helperText}>Select your current employment status</Text>
            <TouchableOpacity
              style={[
                styles.dropdownButton,
                fieldErrors.has("employmentStatus") && styles.dropdownButtonError
              ]}
              onPress={() => setShowEmploymentDropdown(!showEmploymentDropdown)}
            >
              <Text style={[styles.dropdownButtonText, !borrowerDetails.employmentStatus && styles.placeholderText]}>
                {borrowerDetails.employmentStatus 
                  ? employmentStatusOptions.find(opt => opt.value === borrowerDetails.employmentStatus)?.label 
                  : "Select Employment Status"}
              </Text>
              <Ionicons 
                name={showEmploymentDropdown ? "chevron-up" : "chevron-down"} 
                size={20} 
                color="#9CA3AF" 
              />
            </TouchableOpacity>
            {showEmploymentDropdown && (
              <View style={styles.dropdownList}>
                {employmentStatusOptions.map((option) => (
                  <TouchableOpacity
                    key={option.value}
                    style={[
                      styles.dropdownItem,
                      borrowerDetails.employmentStatus === option.value && styles.dropdownItemSelected,
                    ]}
                    onPress={() => {
                      setBorrowerDetails({ ...borrowerDetails, employmentStatus: option.value });
                      setShowEmploymentDropdown(false);
                      clearFieldError("employmentStatus");
                    }}
                  >
                    <Text style={[
                      styles.dropdownItemText,
                      borrowerDetails.employmentStatus === option.value && styles.dropdownItemTextSelected,
                    ]}>
                      {option.label}
                    </Text>
                    {borrowerDetails.employmentStatus === option.value && (
                      <Ionicons name="checkmark" size={18} color="#4EFA8A" />
                    )}
                  </TouchableOpacity>
                ))}
              </View>
            )}
            {fieldErrors.has("employmentStatus") && (
              <Text style={styles.errorText}>Employment Status is required</Text>
            )}
          </View>

          {/* Employed Fields */}
          {(isEmployed || isOFW || isFreelancer) && (
            <View>
              <View style={styles.inputGroup}>
                <View style={styles.labelRow}>
                  <Ionicons name="person-outline" size={16} color="#9CA3AF" />
                  <Text style={styles.label}>Job Title / Position *</Text>
                </View>
                <View style={styles.inputContainer}>
                  <TextInput
                    style={[
                      styles.input,
                      fieldErrors.has("jobTitle") && styles.inputError
                    ]}
                    value={borrowerDetails.jobTitle}
                    onChangeText={(text) => {
                      setBorrowerDetails({ ...borrowerDetails, jobTitle: text });
                      clearFieldError("jobTitle");
                    }}
                    placeholder="Enter your job title or position"
                    placeholderTextColor="#6B7280"
                  />
                  {fieldErrors.has("jobTitle") && (
                    <Text style={styles.errorText}>Job Title/Position is required</Text>
                  )}
                </View>
              </View>

              <View style={styles.inputGroup}>
                <View style={styles.labelRow}>
                  <Ionicons name="business-outline" size={16} color="#9CA3AF" />
                  <Text style={styles.label}>Employer / Business Name *</Text>
                </View>
                <View style={styles.inputContainer}>
                  <TextInput
                    style={[
                      styles.input,
                      fieldErrors.has("employerName") && styles.inputError
                    ]}
                    value={borrowerDetails.employerName}
                    onChangeText={(text) => {
                      setBorrowerDetails({ ...borrowerDetails, employerName: text });
                      clearFieldError("employerName");
                    }}
                    placeholder="Enter employer or company name"
                    placeholderTextColor="#6B7280"
                  />
                  {fieldErrors.has("employerName") && (
                    <Text style={styles.errorText}>Employer/Business Name is required</Text>
                  )}
                </View>
              </View>

              <View style={styles.inputGroup}>
                <View style={styles.labelRow}>
                  <Ionicons name="location-outline" size={16} color="#9CA3AF" />
                  <Text style={styles.label}>Employer Address</Text>
                </View>
                <View style={styles.inputContainer}>
                  <TextInput
                    style={[styles.input, styles.textArea]}
                    value={borrowerDetails.employerAddress}
                    onChangeText={(text) =>
                      setBorrowerDetails({ ...borrowerDetails, employerAddress: text })
                    }
                    placeholder="Enter employer's complete address"
                    placeholderTextColor="#6B7280"
                    multiline
                    numberOfLines={2}
                  />
                </View>
              </View>

              <View style={styles.inputGroup}>
                <View style={styles.labelRow}>
                  <Ionicons name="call-outline" size={16} color="#9CA3AF" />
                  <Text style={styles.label}>Employer Phone Number</Text>
                </View>
                <View style={styles.inputContainer}>
                  <TextInput
                    style={styles.input}
                    value={borrowerDetails.employerPhone}
                    onChangeText={(text) =>
                      setBorrowerDetails({ ...borrowerDetails, employerPhone: text })
                    }
                    placeholder="Enter employer's contact number"
                    placeholderTextColor="#6B7280"
                    keyboardType="phone-pad"
                  />
                </View>
              </View>

              <View style={styles.inputGroup}>
                <View style={styles.labelRow}>
                  <Ionicons name="time-outline" size={16} color="#9CA3AF" />
                  <Text style={styles.label}>Years of Employment</Text>
                </View>
                <View style={styles.inputContainer}>
                  <TextInput
                    style={styles.input}
                    value={borrowerDetails.yearsOfEmployment}
                    onChangeText={(text) =>
                      setBorrowerDetails({ ...borrowerDetails, yearsOfEmployment: text })
                    }
                    placeholder="e.g., 2 years, 6 months"
                    placeholderTextColor="#6B7280"
                  />
                </View>
              </View>
            </View>
          )}

          {/* Self-employed Fields */}
          {isSelfEmployed && (
            <View>
              <View style={styles.inputGroup}>
                <View style={styles.labelRow}>
                  <Ionicons name="storefront-outline" size={16} color="#9CA3AF" />
                  <Text style={styles.label}>Business Name *</Text>
                </View>
                <View style={styles.inputContainer}>
                  <TextInput
                    style={[
                      styles.input,
                      fieldErrors.has("businessName") && styles.inputError
                    ]}
                    value={borrowerDetails.businessName}
                    onChangeText={(text) => {
                      setBorrowerDetails({ ...borrowerDetails, businessName: text });
                      clearFieldError("businessName");
                    }}
                    placeholder="Enter your business name"
                    placeholderTextColor="#6B7280"
                  />
                  {fieldErrors.has("businessName") && (
                    <Text style={styles.errorText}>Business Name is required</Text>
                  )}
                </View>
              </View>

              <View style={styles.inputGroup}>
                <View style={styles.labelRow}>
                  <Ionicons name="grid-outline" size={16} color="#9CA3AF" />
                  <Text style={styles.label}>Business Type</Text>
                </View>
                <View style={styles.inputContainer}>
                  <TextInput
                    style={styles.input}
                    value={borrowerDetails.businessType}
                    onChangeText={(text) =>
                      setBorrowerDetails({ ...borrowerDetails, businessType: text })
                    }
                    placeholder="e.g., Retail, Services, Manufacturing"
                    placeholderTextColor="#6B7280"
                  />
                </View>
              </View>

              <View style={styles.inputGroup}>
                <View style={styles.labelRow}>
                  <Ionicons name="location-outline" size={16} color="#9CA3AF" />
                  <Text style={styles.label}>Business Address</Text>
                </View>
                <View style={styles.inputContainer}>
                  <TextInput
                    style={[styles.input, styles.textArea]}
                    value={borrowerDetails.businessAddress}
                    onChangeText={(text) =>
                      setBorrowerDetails({ ...borrowerDetails, businessAddress: text })
                    }
                    placeholder="Enter business complete address"
                    placeholderTextColor="#6B7280"
                    multiline
                    numberOfLines={2}
                  />
                </View>
              </View>

              <View style={styles.inputGroup}>
                <View style={styles.labelRow}>
                  <Ionicons name="time-outline" size={16} color="#9CA3AF" />
                  <Text style={styles.label}>Years Operating</Text>
                </View>
                <View style={styles.inputContainer}>
                  <TextInput
                    style={styles.input}
                    value={borrowerDetails.yearsOperating}
                    onChangeText={(text) =>
                      setBorrowerDetails({ ...borrowerDetails, yearsOperating: text })
                    }
                    placeholder="e.g., 3 years"
                    placeholderTextColor="#6B7280"
                  />
                </View>
              </View>

              <View style={styles.inputGroup}>
                <View style={styles.labelRow}>
                  <Ionicons name="cash-outline" size={16} color="#9CA3AF" />
                  <Text style={styles.label}>Monthly / Annual Business Income</Text>
                </View>
                <View style={styles.inputContainer}>
                  <TextInput
                    style={styles.input}
                    value={borrowerDetails.businessIncome}
                    onChangeText={(text) =>
                      setBorrowerDetails({ ...borrowerDetails, businessIncome: text })
                    }
                    placeholder="Enter monthly or annual income"
                    placeholderTextColor="#6B7280"
                    keyboardType="numeric"
                  />
                </View>
              </View>
            </View>
          )}

          {/* Common Income Fields */}
          <View style={styles.inputGroup}>
            <View style={styles.labelRow}>
              <Ionicons name="cash-outline" size={16} color="#9CA3AF" />
              <Text style={styles.label}>Monthly Income (PHP) *</Text>
            </View>
            <Text style={styles.helperText}>Your total monthly income from all sources</Text>
            <View style={styles.inputContainer}>
              <TextInput
                style={[
                  styles.input,
                  fieldErrors.has("monthlyIncome") && styles.inputError
                ]}
                value={borrowerDetails.monthlyIncome}
                onChangeText={(text) => {
                  setBorrowerDetails({ ...borrowerDetails, monthlyIncome: text });
                  clearFieldError("monthlyIncome");
                }}
                placeholder="Enter your monthly income"
                placeholderTextColor="#6B7280"
                keyboardType="numeric"
              />
              {fieldErrors.has("monthlyIncome") && (
                <Text style={styles.errorText}>Monthly Income is required</Text>
              )}
            </View>
          </View>

          {(isEmployed || isOFW || isFreelancer) && (
            <View style={styles.inputGroup}>
              <View style={styles.labelRow}>
                <Ionicons name="calendar-outline" size={16} color="#9CA3AF" />
                <Text style={styles.label}>Pay Frequency</Text>
              </View>
              <View style={styles.optionsRow}>
                {payFrequencyOptions.map((option) => (
                  <TouchableOpacity
                    key={option.value}
                    style={[
                      styles.optionButton,
                      borrowerDetails.payFrequency === option.value &&
                        styles.optionButtonActive,
                    ]}
                    onPress={() =>
                      setBorrowerDetails({
                        ...borrowerDetails,
                        payFrequency: option.value,
                      })
                    }
                  >
                    <Text
                      style={[
                        styles.optionText,
                        borrowerDetails.payFrequency === option.value &&
                          styles.optionTextActive,
                      ]}
                    >
                      {option.label}
                    </Text>
                  </TouchableOpacity>
                ))}
              </View>
            </View>
          )}
        </Card>

        {/* 3. Financial Information */}
        <Card style={styles.card}>
          <View style={styles.sectionHeader}>
            <Ionicons name="wallet-outline" size={20} color="#4EFA8A" />
            <Text style={styles.sectionTitle}>Financial Information</Text>
          </View>

          <View style={styles.inputGroup}>
            <View style={styles.labelRow}>
              <Ionicons name="add-circle-outline" size={16} color="#9CA3AF" />
              <Text style={styles.label}>Other Monthly Income (Optional)</Text>
            </View>
            <Text style={styles.helperText}>Additional income from other sources (rental, investments, etc.)</Text>
            <View style={styles.inputContainer}>
              <TextInput
                style={styles.input}
                value={borrowerDetails.otherMonthlyIncome}
                onChangeText={(text) =>
                  setBorrowerDetails({ ...borrowerDetails, otherMonthlyIncome: text })
                }
                placeholder="Enter other sources of monthly income"
                placeholderTextColor="#6B7280"
                keyboardType="numeric"
              />
            </View>
          </View>

          <View style={styles.inputGroup}>
            <View style={styles.labelRow}>
              <Ionicons name="remove-circle-outline" size={16} color="#9CA3AF" />
              <Text style={styles.label}>Total Expenses per Month (PHP)</Text>
            </View>
            <Text style={styles.helperText}>Your total monthly expenses (bills, food, transportation, etc.)</Text>
            <View style={styles.inputContainer}>
              <TextInput
                style={styles.input}
                value={borrowerDetails.totalMonthlyExpenses}
                onChangeText={(text) =>
                  setBorrowerDetails({ ...borrowerDetails, totalMonthlyExpenses: text })
                }
                placeholder="Enter your total monthly expenses"
                placeholderTextColor="#6B7280"
                keyboardType="numeric"
              />
            </View>
          </View>

          <View style={styles.inputGroup}>
            <View style={styles.checkboxRow}>
              <TouchableOpacity
                style={styles.checkbox}
                onPress={() =>
                  setBorrowerDetails({
                    ...borrowerDetails,
                    hasExistingLoans: !borrowerDetails.hasExistingLoans,
                  })
                }
              >
                {borrowerDetails.hasExistingLoans && (
                  <Ionicons name="checkmark" size={20} color="#4EFA8A" />
                )}
              </TouchableOpacity>
              <Text style={styles.label}>Do you have existing loans?</Text>
            </View>
          </View>

          {borrowerDetails.hasExistingLoans && (
            <View>
              <View style={styles.inputGroup}>
                <View style={styles.labelRow}>
                  <Ionicons name="business-outline" size={16} color="#9CA3AF" />
                  <Text style={styles.label}>Lender Name</Text>
                </View>
                <View style={styles.inputContainer}>
                  <TextInput
                    style={styles.input}
                    value={borrowerDetails.existingLoanLender}
                    onChangeText={(text) =>
                      setBorrowerDetails({ ...borrowerDetails, existingLoanLender: text })
                    }
                    placeholder="Enter lender name"
                    placeholderTextColor="#6B7280"
                  />
                </View>
              </View>

              <View style={styles.inputGroup}>
                <View style={styles.labelRow}>
                  <Ionicons name="cash-outline" size={16} color="#9CA3AF" />
                  <Text style={styles.label}>Remaining Balance (PHP)</Text>
                </View>
                <View style={styles.inputContainer}>
                  <TextInput
                    style={styles.input}
                    value={borrowerDetails.existingLoanBalance}
                    onChangeText={(text) =>
                      setBorrowerDetails({ ...borrowerDetails, existingLoanBalance: text })
                    }
                    placeholder="Enter remaining balance"
                    placeholderTextColor="#6B7280"
                    keyboardType="numeric"
                  />
                </View>
              </View>

              <View style={styles.inputGroup}>
                <View style={styles.labelRow}>
                  <Ionicons name="calendar-outline" size={16} color="#9CA3AF" />
                  <Text style={styles.label}>Monthly Amortization (PHP)</Text>
                </View>
                <View style={styles.inputContainer}>
                  <TextInput
                    style={styles.input}
                    value={borrowerDetails.existingLoanMonthlyAmortization}
                    onChangeText={(text) =>
                      setBorrowerDetails({
                        ...borrowerDetails,
                        existingLoanMonthlyAmortization: text,
                      })
                    }
                    placeholder="Enter monthly amortization"
                    placeholderTextColor="#6B7280"
                    keyboardType="numeric"
                  />
                </View>
              </View>
            </View>
          )}

        </Card>

        {/* 4. Character References */}
        <Card style={styles.card}>
          <View style={styles.sectionHeader}>
            <Ionicons name="people-outline" size={20} color="#4EFA8A" />
            <Text style={styles.sectionTitle}>Character References</Text>
            <Text style={styles.helperText}>(At least 1 required, up to 3)</Text>
          </View>

          {/* Reference 1 */}
          <View style={styles.inputGroup}>
            <Text style={styles.label}>Reference 1 - Full Name *</Text>
            <View style={styles.inputContainer}>
              <TextInput
                style={[
                  styles.input,
                  fieldErrors.has("reference1Name") && styles.inputError
                ]}
                value={borrowerDetails.reference1Name}
                onChangeText={(text) => {
                  setBorrowerDetails({ ...borrowerDetails, reference1Name: text });
                  clearFieldError("reference1Name");
                }}
                placeholder="Enter reference full name"
                placeholderTextColor="#9CA3AF"
              />
              {fieldErrors.has("reference1Name") && (
                <Text style={styles.errorText}>Reference 1 - Full Name is required</Text>
              )}
            </View>
          </View>

          <View style={styles.inputGroup}>
            <Text style={styles.label}>Reference 1 - Relationship *</Text>
            <View style={styles.inputContainer}>
              <TextInput
                style={[
                  styles.input,
                  fieldErrors.has("reference1Relationship") && styles.inputError
                ]}
                value={borrowerDetails.reference1Relationship}
                onChangeText={(text) => {
                  setBorrowerDetails({ ...borrowerDetails, reference1Relationship: text });
                  clearFieldError("reference1Relationship");
                }}
                placeholder="e.g., Spouse, Parent, Sibling, Friend, Colleague"
                placeholderTextColor="#9CA3AF"
              />
              {fieldErrors.has("reference1Relationship") && (
                <Text style={styles.errorText}>Reference 1 - Relationship is required</Text>
              )}
            </View>
          </View>

          <View style={styles.inputGroup}>
            <Text style={styles.label}>Reference 1 - Contact Number *</Text>
            <View style={styles.inputContainer}>
              <TextInput
                style={[
                  styles.input,
                  fieldErrors.has("reference1Contact") && styles.inputError
                ]}
                value={borrowerDetails.reference1Contact}
                onChangeText={(text) => {
                  setBorrowerDetails({ ...borrowerDetails, reference1Contact: text });
                  clearFieldError("reference1Contact");
                }}
                placeholder="09XX XXX XXXX"
                placeholderTextColor="#9CA3AF"
                keyboardType="phone-pad"
              />
              {fieldErrors.has("reference1Contact") && (
                <Text style={styles.errorText}>Reference 1 - Contact Number is required</Text>
              )}
            </View>
          </View>

          <View style={styles.inputGroup}>
            <Text style={styles.label}>Reference 1 - Address</Text>
            <TextInput
              style={[styles.input, styles.textArea]}
              value={borrowerDetails.reference1Address}
              onChangeText={(text) =>
                setBorrowerDetails({ ...borrowerDetails, reference1Address: text })
              }
              placeholder="Enter reference address"
              placeholderTextColor="#9CA3AF"
              multiline
              numberOfLines={2}
            />
          </View>

          {/* Reference 2 */}
          <View style={styles.inputGroup}>
            <Text style={styles.label}>Reference 2 - Full Name (Optional)</Text>
            <TextInput
              style={styles.input}
              value={borrowerDetails.reference2Name}
              onChangeText={(text) =>
                setBorrowerDetails({ ...borrowerDetails, reference2Name: text })
              }
              placeholder="Enter reference full name"
              placeholderTextColor="#9CA3AF"
            />
          </View>

          <View style={styles.inputGroup}>
            <Text style={styles.label}>Reference 2 - Relationship</Text>
            <TextInput
              style={styles.input}
              value={borrowerDetails.reference2Relationship}
              onChangeText={(text) =>
                setBorrowerDetails({ ...borrowerDetails, reference2Relationship: text })
              }
              placeholder="e.g., Spouse, Parent, Sibling, Friend"
              placeholderTextColor="#9CA3AF"
            />
          </View>

          <View style={styles.inputGroup}>
            <Text style={styles.label}>Reference 2 - Contact Number</Text>
            <TextInput
              style={styles.input}
              value={borrowerDetails.reference2Contact}
              onChangeText={(text) =>
                setBorrowerDetails({ ...borrowerDetails, reference2Contact: text })
              }
              placeholder="09XX XXX XXXX"
              placeholderTextColor="#9CA3AF"
              keyboardType="phone-pad"
            />
          </View>

          <View style={styles.inputGroup}>
            <Text style={styles.label}>Reference 2 - Address</Text>
            <TextInput
              style={[styles.input, styles.textArea]}
              value={borrowerDetails.reference2Address}
              onChangeText={(text) =>
                setBorrowerDetails({ ...borrowerDetails, reference2Address: text })
              }
              placeholder="Enter reference address"
              placeholderTextColor="#9CA3AF"
              multiline
              numberOfLines={2}
            />
          </View>

          {/* Reference 3 */}
          <View style={styles.inputGroup}>
            <Text style={styles.label}>Reference 3 - Full Name (Optional)</Text>
            <TextInput
              style={styles.input}
              value={borrowerDetails.reference3Name}
              onChangeText={(text) =>
                setBorrowerDetails({ ...borrowerDetails, reference3Name: text })
              }
              placeholder="Enter reference full name"
              placeholderTextColor="#9CA3AF"
            />
          </View>

          <View style={styles.inputGroup}>
            <Text style={styles.label}>Reference 3 - Relationship</Text>
            <TextInput
              style={styles.input}
              value={borrowerDetails.reference3Relationship}
              onChangeText={(text) =>
                setBorrowerDetails({ ...borrowerDetails, reference3Relationship: text })
              }
              placeholder="e.g., Spouse, Parent, Sibling, Friend"
              placeholderTextColor="#9CA3AF"
            />
          </View>

          <View style={styles.inputGroup}>
            <Text style={styles.label}>Reference 3 - Contact Number</Text>
            <TextInput
              style={styles.input}
              value={borrowerDetails.reference3Contact}
              onChangeText={(text) =>
                setBorrowerDetails({ ...borrowerDetails, reference3Contact: text })
              }
              placeholder="09XX XXX XXXX"
              placeholderTextColor="#9CA3AF"
              keyboardType="phone-pad"
            />
          </View>

          <View style={styles.inputGroup}>
            <Text style={styles.label}>Reference 3 - Address</Text>
            <TextInput
              style={[styles.input, styles.textArea]}
              value={borrowerDetails.reference3Address}
              onChangeText={(text) =>
                setBorrowerDetails({ ...borrowerDetails, reference3Address: text })
              }
              placeholder="Enter reference address"
              placeholderTextColor="#9CA3AF"
              multiline
              numberOfLines={2}
            />
          </View>
        </Card>

        {/* 5. Optional Guarantor Section */}
        <Card style={styles.card}>
          <View style={styles.sectionHeader}>
            <Ionicons name="shield-outline" size={20} color="#4EFA8A" />
            <Text style={styles.sectionTitle}>Guarantor (Optional)</Text>
          </View>

          <View style={styles.inputGroup}>
            <View style={styles.checkboxRow}>
              <TouchableOpacity
                style={styles.checkbox}
                onPress={() => setHasGuarantor(!hasGuarantor)}
              >
                {hasGuarantor && <Ionicons name="checkmark" size={20} color="#4EFA8A" />}
              </TouchableOpacity>
              <Text style={styles.label}>I have a guarantor for this loan</Text>
            </View>
          </View>

          {hasGuarantor && (
            <View>
              <View style={styles.inputGroup}>
                <Text style={styles.label}>Guarantor Full Name *</Text>
                <View style={styles.inputContainer}>
                  <TextInput
                    style={[
                      styles.input,
                      fieldErrors.has("guarantorFullName") && styles.inputError
                    ]}
                    value={guarantorDetails.fullName}
                    onChangeText={(text) => {
                      setGuarantorDetails({ ...guarantorDetails, fullName: text });
                      clearFieldError("guarantorFullName");
                    }}
                    placeholder="Enter guarantor's full name"
                    placeholderTextColor="#9CA3AF"
                  />
                  {fieldErrors.has("guarantorFullName") && (
                    <Text style={styles.errorText}>Guarantor Full Name is required</Text>
                  )}
                </View>
              </View>

              <View style={styles.inputGroup}>
                <Text style={styles.label}>Guarantor Address *</Text>
                <View style={styles.inputContainer}>
                  <TextInput
                    style={[
                      styles.input,
                      styles.textArea,
                      fieldErrors.has("guarantorAddress") && styles.inputError
                    ]}
                    value={guarantorDetails.address}
                    onChangeText={(text) => {
                      setGuarantorDetails({ ...guarantorDetails, address: text });
                      clearFieldError("guarantorAddress");
                    }}
                    placeholder="Enter guarantor's complete address"
                    placeholderTextColor="#9CA3AF"
                    multiline
                    numberOfLines={3}
                  />
                  {fieldErrors.has("guarantorAddress") && (
                    <Text style={styles.errorText}>Guarantor Address is required</Text>
                  )}
                </View>
              </View>

              <View style={styles.inputGroup}>
                <View style={styles.labelRow}>
                  <Ionicons name="heart-outline" size={16} color="#9CA3AF" />
                  <Text style={styles.label}>Guarantor Civil Status *</Text>
                </View>
                <View style={styles.checkboxContainer}>
                  {civilStatusOptions.map((option) => (
                    <TouchableOpacity
                      key={option.value}
                      style={styles.checkboxRow}
                      onPress={() => {
                        setGuarantorDetails({
                          ...guarantorDetails,
                          civilStatus: option.value,
                        });
                        clearFieldError("guarantorCivilStatus");
                      }}
                    >
                      <View style={[
                        styles.checkbox,
                        guarantorDetails.civilStatus === option.value && styles.checkboxChecked,
                      ]}>
                        {guarantorDetails.civilStatus === option.value && (
                          <Ionicons name="checkmark" size={16} color="#fff" />
                        )}
                      </View>
                      <Text style={styles.checkboxLabel}>{option.label}</Text>
                    </TouchableOpacity>
                  ))}
                </View>
              </View>
            </View>
          )}
        </Card>

        {/* 6. Signature & Photo */}
        <Card style={styles.card}>
          <View style={styles.sectionHeader}>
            <Ionicons name="document-attach-outline" size={20} color="#4EFA8A" />
            <Text style={styles.sectionTitle}>Signature & Photo</Text>
          </View>

          {/* Signature */}
          <View style={styles.inputGroup}>
            <View style={styles.labelRow}>
              <Ionicons name="create-outline" size={16} color="#9CA3AF" />
              <Text style={styles.label}>Signature *</Text>
            </View>
            <Text style={styles.helperText}>
              Upload a clear image of your signature (JPG, PNG, or PDF)
            </Text>
            <View style={styles.uploadSingle}>
              <TouchableOpacity
                style={[styles.uploadButton, signature && styles.uploadButtonSuccess]}
                onPress={() => handlePickDocument(setSignature)}
              >
                <Ionicons
                  name={signature ? "checkmark-circle" : "create-outline"}
                  size={24}
                  color={signature ? "#4EFA8A" : "#fff"}
                />
                <Text style={styles.uploadButtonText}>
                  {signature ? signature.name : "Upload Signature"}
                </Text>
              </TouchableOpacity>
              {signature && (
                <TouchableOpacity
                  style={styles.removeButton}
                  onPress={() => setSignature(null)}
                >
                  <Text style={styles.removeButtonText}>Remove</Text>
                </TouchableOpacity>
              )}
            </View>
            {fieldErrors.has("signature") && (
              <Text style={styles.errorText}>Signature is required</Text>
            )}
          </View>

          {/* 2x2 Photo */}
          <View style={styles.inputGroup}>
            <View style={styles.labelRow}>
              <Ionicons name="camera-outline" size={16} color="#9CA3AF" />
              <Text style={styles.label}>2x2 Photo *</Text>
            </View>
            <Text style={styles.helperText}>
              Upload a recent 2x2 ID photo (JPG or PNG, max 5MB)
            </Text>
            <View style={styles.uploadSingle}>
              <TouchableOpacity
                style={[styles.uploadButton, photo2x2 && styles.uploadButtonSuccess]}
                onPress={() => handlePickDocument(setPhoto2x2)}
              >
                <Ionicons
                  name={photo2x2 ? "checkmark-circle" : "camera-outline"}
                  size={24}
                  color={photo2x2 ? "#4EFA8A" : "#fff"}
                />
                <Text style={styles.uploadButtonText}>
                  {photo2x2 ? photo2x2.name : "Upload 2x2 Photo"}
                </Text>
              </TouchableOpacity>
              {photo2x2 && (
                <TouchableOpacity
                  style={styles.removeButton}
                  onPress={() => setPhoto2x2(null)}
                >
                  <Text style={styles.removeButtonText}>Remove</Text>
                </TouchableOpacity>
              )}
            </View>
            {fieldErrors.has("photo2x2") && (
              <Text style={styles.errorText}>2x2 Photo is required</Text>
            )}
          </View>
        </Card>
      </View>
    );
  };

  // Step 4: Agreement Generation (PREVIEW ONLY - no loan created)

  const handleGenerateAgreement = async () => {
    try {
      setGeneratingAgreement(true);
      
      // Validate borrower details are complete
      if (!borrowerDetails.fullName || !borrowerDetails.address) {
        Alert.alert(
          "Incomplete Information",
          "Please complete your borrower information in Step 3 before generating agreements.",
          [
            {
              text: "Go to Step 3",
              onPress: () => setCurrentStep(3),
            },
            { text: "OK" },
          ]
        );
        return;
      }
      
      // Generate loan agreement PREVIEW (no loan created yet)
      const agreementRes = await api.post("/legal/agreement/preview", {
        loanAmount,
        tenor,
        interestRate: getInterestRateDecimal(), // Get from config as decimal
        borrower: {
          fullName: borrowerDetails.fullName,
          address: borrowerDetails.address,
          civilStatus: borrowerDetails.civilStatus,
          email: borrowerDetails.email,
          phone: borrowerDetails.phone,
        },
        city,
        penaltyRate: 0.10,
        applicationDate: new Date().toISOString(),
        // Optional fields with defaults
        loanPurpose: 'personal use',
        paymentPlace: 'the Lender\'s office',
        venueCity: city,
      });

      if (agreementRes.data?.data) {
        // Store HTML content separately for viewing
        if (agreementRes.data.data.htmlContent) {
          setAgreementHtml(agreementRes.data.data.htmlContent);
        }
        // Store plain text for preview (always use text version for preview)
        if (agreementRes.data.data.agreement) {
          setAgreement(agreementRes.data.data.agreement);
        } else if (agreementRes.data.data.htmlContent) {
          // Extract plain text from HTML if no text version available
          const textContent = agreementRes.data.data.htmlContent
            .replace(/<[^>]*>/g, '') // Remove HTML tags
            .replace(/&nbsp;/g, ' ')
            .replace(/&amp;/g, '&')
            .replace(/&lt;/g, '<')
            .replace(/&gt;/g, '>')
            .trim();
          setAgreement(textContent.substring(0, 200) + '...'); // Limit preview length
        }
        if (agreementRes.data.data.wordDocumentUrl) {
          // Get full URL with API base
          const apiBase = api.defaults.baseURL?.replace('/api/v1', '') || 'http://localhost:8080';
          setAgreementWordUrl(`${apiBase}${agreementRes.data.data.wordDocumentUrl}`);
        }
      }

      // Generate guaranty agreement preview if guarantor is provided
      if (hasGuarantor && guarantorDetails.fullName && guarantorDetails.address) {
        try {
          const guarantyRes = await api.post("/legal/guaranty-agreement/preview", {
            loanAmount,
            borrower: {
              fullName: borrowerDetails.fullName,
              address: borrowerDetails.address,
              civilStatus: borrowerDetails.civilStatus,
            },
            guarantor: guarantorDetails,
            city,
            applicationDate: new Date().toISOString(),
          });

          if (guarantyRes.data?.data) {
            // Store HTML content separately for viewing
            if (guarantyRes.data.data.htmlContent) {
              setGuarantyAgreementHtml(guarantyRes.data.data.htmlContent);
            }
            // Store plain text for preview
            if (guarantyRes.data.data.agreement) {
              setGuarantyAgreement(guarantyRes.data.data.agreement);
            } else if (guarantyRes.data.data.htmlContent) {
              // Extract plain text from HTML if no text version available
              const textContent = guarantyRes.data.data.htmlContent
                .replace(/<[^>]*>/g, '') // Remove HTML tags
                .replace(/&nbsp;/g, ' ')
                .replace(/&amp;/g, '&')
                .replace(/&lt;/g, '<')
                .replace(/&gt;/g, '>')
                .trim();
              setGuarantyAgreement(textContent.substring(0, 200) + '...'); // Limit preview length
            }
            if (guarantyRes.data.data.wordDocumentUrl) {
              // Get full URL with API base
              const apiBase = api.defaults.baseURL?.replace('/api/v1', '') || 'http://localhost:8080';
              setGuarantyAgreementWordUrl(`${apiBase}${guarantyRes.data.data.wordDocumentUrl}`);
            }
          }
        } catch (guarantyError: any) {
          console.error("Error generating guaranty agreement preview:", guarantyError);
          // Don't fail the whole process if guaranty agreement fails
        }
      }

      // NOTE: Demand letter is NOT generated during application
      // Demand letters are only for when borrowers have defaulted on payments
      // They should only be generated when there are actual overdue payments
      // (See loan_details.tsx for demand letter generation for existing loans)

      Alert.alert(
        "✅ Success", 
        "Agreement(s) generated successfully! Please review the documents before proceeding.",
        [{ text: "OK" }]
      );
      // Don't auto-advance - user must view agreements first
    } catch (error: any) {
      console.error("Error generating agreement preview:", error);
      const errorData = error.response?.data;
      
      if (errorData?.missingFields) {
        Alert.alert(
          "Incomplete Information",
          `Please update your profile with: ${Object.entries(errorData.missingFields)
            .filter(([_, missing]) => missing)
            .map(([field]) => field)
            .join(", ")}`,
          [
            {
              text: "Go to Profile",
              onPress: () => setCurrentStep(3),
            },
            { text: "OK" },
          ]
        );
      } else {
        Alert.alert("Error", errorData?.message || "Failed to generate agreement preview");
      }
    } finally {
      setGeneratingAgreement(false);
    }
  };

  const renderStep4 = () => (
    <View>
      <Text style={styles.stepTitle}>Generate Loan Agreement</Text>
      <Text style={styles.stepSubtitle}>
        Review and generate your legal loan agreement
      </Text>

      <Card style={styles.card}>
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
          disabled={generatingAgreement}
          style={styles.actionButton}
        >
          <LinearGradient
            colors={["#03042c", "#302b63", "#24243e"]}
            start={{ x: 0, y: 0 }}
            end={{ x: 1, y: 1 }}
            style={styles.gradientButton}
          >
            {generatingAgreement ? (
              <ActivityIndicator color="#fff" />
            ) : (
              <View style={{ flexDirection: 'row', alignItems: 'center' }}>
                <Ionicons name="document-text-outline" size={20} color="#fff" />
                <Text style={[styles.buttonText, { marginLeft: 8 }]}>Generate Agreement</Text>
              </View>
            )}
          </LinearGradient>
        </TouchableOpacity>

        {agreement && (
          <View style={styles.agreementPreview}>
            <Text style={styles.agreementLabel}>Loan Agreement</Text>
            <Text style={styles.agreementText} numberOfLines={8}>
              {agreement}
            </Text>
            <TouchableOpacity
              style={styles.viewDocumentButton}
              onPress={() => {
                const wordUrl = agreementWordUrl 
                  ? agreementWordUrl.replace(/^https?:\/\/[^/]+/, '') // Remove base URL, keep path
                  : undefined;
                setViewingDocument({ 
                  type: "loan", 
                  content: agreementHtml || agreement, // Use HTML if available, otherwise text
                  wordUrl: wordUrl || undefined
                });
                setAgreementViewed(true); // Mark as viewed when opened
              }}
            >
              <Ionicons name="eye-outline" size={18} color="#4EFA8A" />
              <Text style={styles.viewDocumentText}>View Full Document</Text>
            </TouchableOpacity>
            {agreementViewed && (
              <View style={styles.acknowledgmentRow}>
                <Ionicons name="checkmark-circle" size={20} color="#4EFA8A" />
                <Text style={styles.acknowledgmentText}>Agreement reviewed</Text>
              </View>
            )}
          </View>
        )}

        {/* Guaranty Agreement - only shown if borrower has a guarantor */}
        {hasGuarantor && guarantyAgreement && (
          <View style={styles.agreementPreview}>
            <Text style={styles.agreementLabel}>Guaranty Agreement</Text>
            <Text style={styles.agreementText} numberOfLines={8}>
              {guarantyAgreement}
            </Text>
            <TouchableOpacity
              style={styles.viewDocumentButton}
              onPress={() => {
                const wordUrl = guarantyAgreementWordUrl 
                  ? guarantyAgreementWordUrl.replace(/^https?:\/\/[^/]+/, '') // Remove base URL, keep path
                  : undefined;
                setViewingDocument({ 
                  type: "guaranty", 
                  content: guarantyAgreementHtml || guarantyAgreement, // Use HTML if available, otherwise text
                  wordUrl: wordUrl || undefined
                });
                setGuarantyAgreementViewed(true); // Mark as viewed when opened
              }}
            >
              <Ionicons name="eye-outline" size={18} color="#4EFA8A" />
              <Text style={styles.viewDocumentText}>View Full Document</Text>
            </TouchableOpacity>
            {guarantyAgreementViewed && (
              <View style={styles.acknowledgmentRow}>
                <Ionicons name="checkmark-circle" size={20} color="#4EFA8A" />
                <Text style={styles.acknowledgmentText}>Agreement reviewed</Text>
              </View>
            )}
          </View>
        )}

        {/* Demand letter is NOT shown during application - it's only for overdue payments */}

        {agreement && (
          <View style={styles.importantNotice}>
            <Ionicons name="information-circle" size={20} color="#f97316" />
            <Text style={styles.importantNoticeText}>
              Please review the agreement(s) above before proceeding. You must view the full document(s) to continue.
            </Text>
          </View>
        )}
      </Card>
    </View>
  );

  // Step 5: Final Submission - ONLY NOW everything is submitted
  const handleFinalSubmit = async (skipTermsCheck: boolean = false) => {
    // Always show terms and conditions modal first (unless explicitly skipped)
    if (!skipTermsCheck) {
      // Reset terms acceptance each time modal is shown
      setTermsAccepted(false);
      setShowTermsModal(true);
      return;
    }

    // Validate all required data
    if (!agreement) {
      Alert.alert(
        "Agreement Required",
        "Please go back to Step 4 and generate the loan agreement first.",
        [
          {
            text: "Go to Step 4",
            onPress: () => setCurrentStep(4),
          },
          { text: "Cancel", style: "cancel" },
        ]
      );
      return;
    }

    if (!agreementViewed) {
      Alert.alert(
        "Review Required",
        "Please view the Loan Agreement document in Step 4 before submitting.",
        [
          {
            text: "Go to Step 4",
            onPress: () => setCurrentStep(4),
          },
          { text: "Cancel", style: "cancel" },
        ]
      );
      return;
    }

    if (hasGuarantor && guarantyAgreement && !guarantyAgreementViewed) {
      Alert.alert(
        "Review Required",
        "Please view the Guaranty Agreement document in Step 4 before submitting.",
        [
          {
            text: "Go to Step 4",
            onPress: () => setCurrentStep(4),
          },
          { text: "Cancel", style: "cancel" },
        ]
      );
      return;
    }

    // Validate documents are selected
    if (!primaryIdFront || !primaryIdBack || !secondaryIdFront || !secondaryIdBack || !signature || !photo2x2) {
      Alert.alert(
        "Missing Documents",
        "Please go back to Step 2 and Step 3 to upload all required documents: Primary ID (front & back), Secondary ID (front & back), Signature, and 2x2 Photo.",
        [
          {
            text: "Go to Documents",
            onPress: () => setCurrentStep(2),
          },
          { text: "Cancel", style: "cancel" },
        ]
      );
      return;
    }

    Alert.alert(
      "Submit Application",
      "Are you sure you want to submit your loan application? This will create the loan, upload documents, and finalize your application. This action cannot be undone.",
      [
        { text: "Cancel", style: "cancel" },
        {
          text: "Submit",
          onPress: async () => {
            try {
              setSubmitting(true);

              // Step 1: Ensure profile is fully saved with all data before creating loan
              console.log("💾 Step 0: Saving complete profile data...");
              const completeProfileData: any = {
                fullName: borrowerDetails.fullName,
                email: borrowerDetails.email,
                phone: borrowerDetails.phone,
                address: borrowerDetails.completeAddress || borrowerDetails.address,
                sex: borrowerDetails.gender, // Map gender to sex
                civilStatus: borrowerDetails.civilStatus,
                occupation: borrowerDetails.jobTitle || borrowerDetails.occupation,
                monthlyIncome: borrowerDetails.monthlyIncome ? parseFloat(borrowerDetails.monthlyIncome) : undefined,
                birthday: borrowerDetails.dateOfBirth || borrowerDetails.birthday, // Map dateOfBirth to birthday
              };
              
              // Remove undefined values
              Object.keys(completeProfileData).forEach(key => {
                if (completeProfileData[key] === undefined || completeProfileData[key] === '') {
                  delete completeProfileData[key];
                }
              });
              
              try {
                await api.put("/users/profile", completeProfileData);
                console.log("✅ Complete profile data saved");
              } catch (profileError: any) {
                console.warn("⚠️ Profile update warning:", profileError.response?.data?.message || profileError.message);
                // Continue even if profile update has issues - loan creation will validate required fields
              }

              // Step 1: Create the loan in the database
              console.log("📝 Step 1: Creating loan...");
              const requestData: any = {
                amount: loanAmount,
                principalAmount: loanAmount,
                tenor: tenor.toString(),
                interestRate: getInterestRateDecimal(), // Get from config as decimal
              };

              if (location) {
                requestData.latitude = location.latitude;
                requestData.longitude = location.longitude;
                if (location.address) {
                  requestData.locationAddress = location.address;
                }
              }

              // Add guarantor data if provided
              if (hasGuarantor && guarantorDetails.fullName && guarantorDetails.address) {
                requestData.guarantor = {
                  fullName: guarantorDetails.fullName,
                  address: guarantorDetails.address,
                  civilStatus: guarantorDetails.civilStatus || undefined,
                };
              }

              const loanRes = await api.post("/loans", requestData);
              const createdLoanId = loanRes.data?.data?.id;
              
              if (!createdLoanId) {
                throw new Error("Failed to create loan");
              }
              
              setLoanId(createdLoanId);
              console.log("✅ Loan created:", createdLoanId);

              // Step 2: Upload documents
              console.log("📤 Step 2: Uploading documents...");
              const documentsUploaded = await uploadDocuments(createdLoanId);
              if (!documentsUploaded) {
                setSubmitting(false);
                return; // Error already shown in uploadDocuments
              }
              console.log("✅ Documents uploaded");

              // Step 3: Generate and save final agreements (with loanId)
              console.log("📄 Step 3: Generating final agreements...");
              
              // Generate loan agreement (final version - saved to database)
              try {
                const agreementRes = await api.post("/legal/agreement", {
                  loanId: createdLoanId,
                  city,
                  penaltyRate: 0.10,
                });
                if (agreementRes.data?.data?.wordDocumentUrl) {
                  const apiBase = api.defaults.baseURL?.replace('/api/v1', '') || 'http://localhost:8080';
                  setFinalAgreementUrl(`${apiBase}${agreementRes.data.data.wordDocumentUrl}`);
                }
                console.log("✅ Loan agreement saved");
              } catch (agreementError: any) {
                console.error("Error saving loan agreement:", agreementError);
                // Continue even if agreement save fails
              }

              // Generate guaranty agreement if provided
              if (hasGuarantor && guarantorDetails.fullName && guarantorDetails.address) {
                try {
                  const guarantyRes = await api.post("/legal/guaranty-agreement", {
                    loanId: createdLoanId,
                    guarantor: guarantorDetails,
                    city,
                  });
                  if (guarantyRes.data?.data?.wordDocumentUrl) {
                    const apiBase = api.defaults.baseURL?.replace('/api/v1', '') || 'http://localhost:8080';
                    setFinalGuarantyUrl(`${apiBase}${guarantyRes.data.data.wordDocumentUrl}`);
                  }
                  console.log("✅ Guaranty agreement saved");
                } catch (guarantyError: any) {
                  console.error("Error saving guaranty agreement:", guarantyError);
                  // Continue even if guaranty agreement save fails
                }
              }

              // NOTE: Demand letter is NOT generated during application submission
              // Demand letters are only generated when there are actual overdue payments
              // They will be generated on-demand from the loan details page when needed

              // Step 4: Application is complete!
              // Navigate to success screen
              router.push({
                pathname: "/(tabs)/loan_application_success" as any,
                params: {
                  loanId: createdLoanId.toString(),
                  loanAmount: loanAmount.toString(),
                  tenor: tenor.toString(),
                  agreementUrl: finalAgreementUrl || "",
                  guarantyUrl: finalGuarantyUrl || "",
                },
              });
            } catch (error: any) {
              console.error("❌ Error during final submission:", error);
              const errorMessage = error.response?.data?.message || error.message || "Failed to submit application";
              Alert.alert("Submission Error", errorMessage, [{ text: "OK" }]);
            } finally {
              setSubmitting(false);
            }
          },
        },
      ]
    );
  };

  const renderStep5 = () => (
    <View>
      <Text style={styles.stepTitle}>Review & Submit</Text>
      <Text style={styles.stepSubtitle}>
        Review your application details and submit
      </Text>

      <Card style={styles.card}>
        <Text style={styles.reviewTitle}>Loan Details</Text>
        <Text style={styles.reviewText}>Amount: {formatCurrency(loanAmount)}</Text>
        <Text style={styles.reviewText}>Tenor: {tenor} months</Text>
        <Text style={styles.reviewText}>Monthly EMI: {formatCurrency(emiValue)}</Text>
      </Card>

      <Card style={styles.card}>
        <Text style={styles.reviewTitle}>Documents</Text>
        <View style={styles.reviewRow}>
          <Ionicons name="checkmark-circle" size={20} color="#4EFA8A" />
          <Text style={styles.reviewText}>Primary ID Front: {primaryIdFront?.name || "Uploaded"}</Text>
        </View>
        <View style={styles.reviewRow}>
          <Ionicons name="checkmark-circle" size={20} color="#4EFA8A" />
          <Text style={styles.reviewText}>Primary ID Back: {primaryIdBack?.name || "Uploaded"}</Text>
        </View>
        <View style={styles.reviewRow}>
          <Ionicons name="checkmark-circle" size={20} color="#4EFA8A" />
          <Text style={styles.reviewText}>Secondary ID Front: {secondaryIdFront?.name || "Uploaded"}</Text>
        </View>
        <View style={styles.reviewRow}>
          <Ionicons name="checkmark-circle" size={20} color="#4EFA8A" />
          <Text style={styles.reviewText}>Secondary ID Back: {secondaryIdBack?.name || "Uploaded"}</Text>
        </View>
      </Card>

      <Card style={styles.card}>
        <Text style={styles.reviewTitle}>Borrower Information</Text>
        <Text style={styles.reviewText}>Name: {borrowerDetails.fullName}</Text>
        <Text style={styles.reviewText}>Address: {borrowerDetails.address}</Text>
        <Text style={styles.reviewText}>Civil Status: {borrowerDetails.civilStatus}</Text>
      </Card>

      {agreement && (
        <Card style={styles.card}>
          <View style={styles.agreementPreview}>
            <Text style={styles.agreementLabel}>Loan Agreement</Text>
            <Text style={styles.agreementText} numberOfLines={8}>
              {agreement}
            </Text>
            <TouchableOpacity
              style={styles.viewDocumentButton}
              onPress={() => {
                const wordUrl = agreementWordUrl 
                  ? agreementWordUrl.replace(/^https?:\/\/[^/]+/, '') // Remove base URL, keep path
                  : undefined;
                setViewingDocument({ 
                  type: "loan", 
                  content: agreementHtml || agreement, // Use HTML if available, otherwise text
                  wordUrl: wordUrl || undefined
                });
              }}
            >
              <Ionicons name="eye-outline" size={18} color="#4EFA8A" />
              <Text style={styles.viewDocumentText}>View Full Document</Text>
            </TouchableOpacity>
            {agreementViewed && (
              <View style={styles.acknowledgmentRow}>
                <Ionicons name="checkmark-circle" size={20} color="#4EFA8A" />
                <Text style={styles.acknowledgmentText}>Agreement reviewed</Text>
              </View>
            )}
          </View>
        </Card>
      )}

      {/* Guaranty Agreement - only shown if borrower has a guarantor */}
      {hasGuarantor && guarantyAgreement && (
        <Card style={styles.card}>
          <View style={styles.agreementPreview}>
            <Text style={styles.agreementLabel}>Guaranty Agreement</Text>
            <Text style={styles.agreementText} numberOfLines={8}>
              {guarantyAgreement}
            </Text>
            <TouchableOpacity
              style={styles.viewDocumentButton}
              onPress={() => {
                const wordUrl = guarantyAgreementWordUrl 
                  ? guarantyAgreementWordUrl.replace(/^https?:\/\/[^/]+/, '') // Remove base URL, keep path
                  : undefined;
                setViewingDocument({ 
                  type: "guaranty", 
                  content: guarantyAgreementHtml || guarantyAgreement, // Use HTML if available, otherwise text
                  wordUrl: wordUrl || undefined
                });
              }}
            >
              <Ionicons name="eye-outline" size={18} color="#4EFA8A" />
              <Text style={styles.viewDocumentText}>View Full Document</Text>
            </TouchableOpacity>
            {guarantyAgreementViewed && (
              <View style={styles.acknowledgmentRow}>
                <Ionicons name="checkmark-circle" size={20} color="#4EFA8A" />
                <Text style={styles.acknowledgmentText}>Agreement reviewed</Text>
              </View>
            )}
          </View>
        </Card>
      )}

      {/* Demand letter is NOT shown during application - it's only for overdue payments */}

      {hasGuarantor && guarantorDetails.fullName && (
        <Card style={styles.card}>
          <Text style={styles.reviewTitle}>Guarantor Information</Text>
          <Text style={styles.reviewText}>Name: {guarantorDetails.fullName}</Text>
          <Text style={styles.reviewText}>Address: {guarantorDetails.address}</Text>
          <Text style={styles.reviewText}>Civil Status: {guarantorDetails.civilStatus}</Text>
        </Card>
      )}

      <TouchableOpacity
        onPress={() => handleFinalSubmit()}
        disabled={submitting}
        style={styles.submitButton}
      >
        <LinearGradient
          colors={["#03042c", "#302b63", "#24243e"]}
          start={{ x: 0, y: 0 }}
          end={{ x: 1, y: 1 }}
          style={styles.gradientButton}
        >
          {submitting ? (
            <ActivityIndicator color="#fff" />
          ) : (
            <Text style={styles.buttonText}>Submit Application</Text>
          )}
        </LinearGradient>
      </TouchableOpacity>
    </View>
  );

  // Terms and Conditions Modal
  const renderTermsModal = () => (
    <Modal
      visible={showTermsModal}
      transparent={true}
      animationType="slide"
      onRequestClose={() => setShowTermsModal(false)}
    >
      <View style={styles.termsModalOverlay}>
        <View style={styles.termsModalContent}>
          <View style={styles.termsModalHeader}>
            <Text style={styles.termsModalTitle}>Terms and Conditions</Text>
            <TouchableOpacity
              onPress={() => {
                setShowTermsModal(false);
                setTermsAccepted(false);
              }}
              style={styles.termsModalCloseButton}
            >
              <Ionicons name="close" size={24} color="#fff" />
            </TouchableOpacity>
          </View>
          
          <ScrollView 
            style={styles.termsModalScrollView} 
            contentContainerStyle={styles.termsModalScrollContent}
            showsVerticalScrollIndicator={true}
          >
            <Text style={styles.termsSectionTitle}>Data Collection and Privacy</Text>
            <Text style={styles.termsText}>
              By submitting this loan application, you acknowledge and agree to the following:
            </Text>
            
            <Text style={styles.termsSectionTitle}>1. Data Collection</Text>
            <Text style={styles.termsText}>
              You consent to the collection, processing, and storage of your personal information, including but not limited to:
            </Text>
            <Text style={styles.termsBulletPoint}>
              • Personal identification details (name, date of birth, gender, civil status, nationality)
            </Text>
            <Text style={styles.termsBulletPoint}>
              • Contact information (phone number, email address, complete address)
            </Text>
            <Text style={styles.termsBulletPoint}>
              • Employment and financial information (employment status, income, expenses, existing loans)
            </Text>
            <Text style={styles.termsBulletPoint}>
              • Valid identification documents (Primary ID, Secondary ID)
            </Text>
            <Text style={styles.termsBulletPoint}>
              • Character references and guarantor information (if applicable)
            </Text>
            <Text style={styles.termsBulletPoint}>
              • Location data (geographical coordinates and address)
            </Text>

            <Text style={styles.termsSectionTitle}>2. Data Usage</Text>
            <Text style={styles.termsText}>
              Your information will be used for:
            </Text>
            <Text style={styles.termsBulletPoint}>
              • Processing and evaluating your loan application
            </Text>
            <Text style={styles.termsBulletPoint}>
              • Credit assessment and risk analysis
            </Text>
            <Text style={styles.termsBulletPoint}>
              • Generating legal documents (Loan Agreement, Guaranty Agreement, Demand Letter)
            </Text>
            <Text style={styles.termsBulletPoint}>
              • Communication regarding your loan application and account
            </Text>
            <Text style={styles.termsBulletPoint}>
              • Compliance with legal and regulatory requirements
            </Text>

            <Text style={styles.termsSectionTitle}>3. Data Sharing</Text>
            <Text style={styles.termsText}>
              Your information may be shared with:
            </Text>
            <Text style={styles.termsBulletPoint}>
              • Credit bureaus and credit reporting agencies
            </Text>
            <Text style={styles.termsBulletPoint}>
              • Legal and regulatory authorities when required by law
            </Text>
            <Text style={styles.termsBulletPoint}>
              • Service providers and partners involved in loan processing
            </Text>

            <Text style={styles.termsSectionTitle}>4. Data Security</Text>
            <Text style={styles.termsText}>
              We implement appropriate security measures to protect your personal information from unauthorized access, alteration, disclosure, or destruction.
            </Text>

            <Text style={styles.termsSectionTitle}>5. Consent and Authorization</Text>
            <Text style={styles.termsText}>
              By submitting this application, you:
            </Text>
            <Text style={styles.termsBulletPoint}>
              • Confirm that all information provided is true, accurate, and complete
            </Text>
            <Text style={styles.termsBulletPoint}>
              • Authorize us to verify the information provided through any means
            </Text>
            <Text style={styles.termsBulletPoint}>
              • Consent to the processing of your personal data as described above
            </Text>
            <Text style={styles.termsBulletPoint}>
              • Agree to be bound by the terms and conditions of the loan agreement
            </Text>
            <Text style={styles.termsBulletPoint}>
              • Understand that false or misleading information may result in application rejection or legal action
            </Text>

            <Text style={styles.termsSectionTitle}>6. Right to Withdraw</Text>
            <Text style={styles.termsText}>
              You have the right to withdraw your consent at any time, subject to our legal obligations and the terms of any existing loan agreement.
            </Text>

            <Text style={styles.termsSectionTitle}>7. Contact Information</Text>
            <Text style={styles.termsText}>
              For questions or concerns regarding data privacy, please contact us through the app&apos;s support feature or via email.
            </Text>

            <View style={styles.termsCheckboxContainer}>
              <TouchableOpacity
                style={[styles.termsCheckbox, termsAccepted && styles.termsCheckboxChecked]}
                onPress={() => setTermsAccepted(!termsAccepted)}
              >
                {termsAccepted && (
                  <Ionicons name="checkmark" size={20} color="#fff" />
                )}
              </TouchableOpacity>
              <Text style={styles.termsCheckboxLabel}>
                I have read, understood, and agree to the Terms and Conditions, Privacy Policy, and Data Collection practices described above.
              </Text>
            </View>
          </ScrollView>

          <View style={styles.termsModalFooter}>
            <TouchableOpacity
              style={[styles.termsButton, styles.termsButtonCancel]}
              onPress={() => {
                setShowTermsModal(false);
                setTermsAccepted(false);
              }}
            >
              <Ionicons name="close-circle-outline" size={18} color="#fff" />
              <Text style={styles.termsButtonTextCancel}>Cancel</Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={[
                styles.termsButton,
                styles.termsButtonAgree,
                !termsAccepted && styles.termsButtonDisabled
              ]}
              onPress={() => {
                if (termsAccepted) {
                  setShowTermsModal(false);
                  // Call handleFinalSubmit with skipTermsCheck flag
                  setTimeout(() => {
                    handleFinalSubmit(true);
                  }, 100);
                } else {
                  Alert.alert(
                    "Agreement Required",
                    "Please read and accept the Terms and Conditions to proceed with your loan application.",
                    [{ text: "OK" }]
                  );
                }
              }}
              disabled={!termsAccepted}
            >
              <Ionicons 
                name={termsAccepted ? "checkmark-circle" : "checkmark-circle-outline"} 
                size={18} 
                color={termsAccepted ? "#03042c" : "#9CA3AF"} 
              />
              <Text style={[
                styles.termsButtonTextAgree,
                !termsAccepted && styles.termsButtonTextDisabled
              ]}>
                {termsAccepted ? "I Agree" : "Accept Terms to Continue"}
              </Text>
            </TouchableOpacity>
          </View>
        </View>
      </View>
    </Modal>
  );

  const renderCurrentStep = () => {
    switch (currentStep) {
      case 1:
        return renderStep1();
      case 2:
        return renderStep2();
      case 3:
        return renderStep3();
      case 4:
        return renderStep4();
      case 5:
        return renderStep5();
      default:
        return renderStep1();
    }
  };

  const canGoBack = useCallback(() => {
    return currentStep > 1;
  }, [currentStep]);

  const handleNext = async () => {
    if (currentStep >= 5) {
      return;
    }

    // Validate each step with appropriate error messages
    switch (currentStep) {
      case 1:
        const missingStep1: string[] = [];
        
        if (!isValidLoanAmount(loanAmount)) {
          missingStep1.push(`Loan Amount (must be between ₱${LOAN_CONFIG.AMOUNT.MIN.toLocaleString()} and ₱${LOAN_CONFIG.AMOUNT.MAX.toLocaleString()})`);
        }
        if (!isValidTenor(tenor)) {
          missingStep1.push(`Loan Term (Tenor must be between ${LOAN_CONFIG.TENOR.MIN} and ${LOAN_CONFIG.TENOR.MAX} months)`);
        }
        if (!location) {
          missingStep1.push("Application Location");
        }

        if (missingStep1.length > 0) {
          const fieldsList = missingStep1.map((field, index) => `${index + 1}. ${field}`).join('\n');
          Alert.alert(
            "Fields Required",
            `Please complete the following required fields:\n\n${fieldsList}`,
            [{ text: "OK" }]
          );
          return;
        }
        break;

      case 2:
        const missingStep2: string[] = [];
        
        if (!primaryIdFront) missingStep2.push("Primary ID - Front Side");
        if (!primaryIdBack) missingStep2.push("Primary ID - Back Side");
        if (!secondaryIdFront) missingStep2.push("Secondary ID - Front Side");
        if (!secondaryIdBack) missingStep2.push("Secondary ID - Back Side");

        if (missingStep2.length > 0) {
          const fieldsList = missingStep2.map((field, index) => `${index + 1}. ${field}`).join('\n');
          Alert.alert(
            "Fields Required",
            `Please upload the following required documents:\n\n${fieldsList}`,
            [{ text: "OK" }]
          );
          return;
        }
        break;

      case 3:
        // Collect all missing required fields and set error states
        const errorFields = new Set<string>();
        const missingFields: string[] = [];

        // Validate Personal Information
        if (!borrowerDetails.fullName) {
          errorFields.add("fullName");
          missingFields.push("Full Name");
        }
        if (!borrowerDetails.dateOfBirth) {
          errorFields.add("dateOfBirth");
          missingFields.push("Date of Birth");
        }
        if (!borrowerDetails.gender) {
          errorFields.add("gender");
          missingFields.push("Gender");
        }
        if (!borrowerDetails.civilStatus) {
          errorFields.add("civilStatus");
          missingFields.push("Civil Status");
        }
        if (!borrowerDetails.nationality) {
          errorFields.add("nationality");
          missingFields.push("Nationality");
        }
        if (!borrowerDetails.phone) {
          errorFields.add("phone");
          missingFields.push("Contact Number");
        }
        if (!borrowerDetails.email) {
          errorFields.add("email");
          missingFields.push("Email Address");
        }
        if (!borrowerDetails.houseNo) {
          errorFields.add("houseNo");
          missingFields.push("House Number/Street");
        }
        if (!borrowerDetails.barangay) {
          errorFields.add("barangay");
          missingFields.push("Barangay");
        }
        if (!borrowerDetails.city) {
          errorFields.add("city");
          missingFields.push("Municipality/City");
        }
        if (!borrowerDetails.province) {
          errorFields.add("province");
          missingFields.push("Province");
        }
        if (!borrowerDetails.validIdNumber) {
          errorFields.add("validIdNumber");
          missingFields.push("Valid ID Number");
        }

        // Validate Employment Information
        if (!borrowerDetails.employmentStatus) {
          errorFields.add("employmentStatus");
          missingFields.push("Employment Status");
        }
        if (!borrowerDetails.monthlyIncome) {
          errorFields.add("monthlyIncome");
          missingFields.push("Monthly Income");
        }
        
        const isEmployed = borrowerDetails.employmentStatus === "employed" || 
                           borrowerDetails.employmentStatus === "ofw" || 
                           borrowerDetails.employmentStatus === "freelancer";
        const isSelfEmployed = borrowerDetails.employmentStatus === "self-employed";
        
        if (isEmployed) {
          if (!borrowerDetails.jobTitle) {
            errorFields.add("jobTitle");
            missingFields.push("Job Title/Position");
          }
          if (!borrowerDetails.employerName) {
            errorFields.add("employerName");
            missingFields.push("Employer/Business Name");
          }
        }
        if (isSelfEmployed && !borrowerDetails.businessName) {
          errorFields.add("businessName");
          missingFields.push("Business Name");
        }

        // Validate References
        if (!borrowerDetails.reference1Name) {
          errorFields.add("reference1Name");
          missingFields.push("Reference 1 - Full Name");
        }
        if (!borrowerDetails.reference1Relationship) {
          errorFields.add("reference1Relationship");
          missingFields.push("Reference 1 - Relationship");
        }
        if (!borrowerDetails.reference1Contact) {
          errorFields.add("reference1Contact");
          missingFields.push("Reference 1 - Contact Number");
        }

        // Validate Guarantor if provided
        if (hasGuarantor) {
          if (!guarantorDetails.fullName) {
            errorFields.add("guarantorFullName");
            missingFields.push("Guarantor - Full Name");
          }
          if (!guarantorDetails.address) {
            errorFields.add("guarantorAddress");
            missingFields.push("Guarantor - Address");
          }
          if (!guarantorDetails.civilStatus) {
            errorFields.add("guarantorCivilStatus");
            missingFields.push("Guarantor - Civil Status");
          }
        }

        // Validate Signature and Photo
        if (!signature) {
          errorFields.add("signature");
          missingFields.push("Signature");
        }
        if (!photo2x2) {
          errorFields.add("photo2x2");
          missingFields.push("2x2 Photo");
        }

        // Set error states and show alert
        if (missingFields.length > 0) {
          setFieldErrors(errorFields);
          const fieldsList = missingFields.map((field, index) => `${index + 1}. ${field}`).join('\n');
          Alert.alert(
            "Fields Required",
            `Please complete the following required fields:\n\n${fieldsList}`,
            [{ text: "OK" }]
          );
          return;
        } else {
          // Clear errors if validation passes
          setFieldErrors(new Set());
        }

        // Save profile before proceeding - map frontend fields to backend fields
        try {
          setUpdatingProfile(true);
          
          // Prepare profile data with all fields that can be saved
          const profileData: any = {
            fullName: borrowerDetails.fullName,
            email: borrowerDetails.email,
            phone: borrowerDetails.phone,
            address: borrowerDetails.completeAddress || borrowerDetails.address,
            sex: borrowerDetails.gender, // Map gender to sex
            civilStatus: borrowerDetails.civilStatus,
            occupation: borrowerDetails.jobTitle || borrowerDetails.occupation,
            monthlyIncome: borrowerDetails.monthlyIncome ? parseFloat(borrowerDetails.monthlyIncome) : undefined,
            birthday: borrowerDetails.dateOfBirth || borrowerDetails.birthday, // Map dateOfBirth to birthday
          };
          
          // Remove undefined values
          Object.keys(profileData).forEach(key => {
            if (profileData[key] === undefined || profileData[key] === '') {
              delete profileData[key];
            }
          });
          
          console.log("💾 Saving profile data:", profileData);
          await api.put("/users/profile", profileData);
          console.log("✅ Profile saved successfully");
          
          // Profile saved successfully, proceed to next step
          setCurrentStep(4);
        } catch (error: any) {
          console.error("❌ Error saving profile:", error);
          Alert.alert("Error", error.response?.data?.message || "Failed to save profile. Please try again.");
          return;
        } finally {
          setUpdatingProfile(false);
        }
        return; // Exit early since we already handled the step transition

      case 4:
        // Step 4 validation is handled by isStepComplete
        // No validation needed, proceed to next step
        break;
    }

    // For steps that passed validation, move to next step
    setCurrentStep((prev) => (prev + 1) as Step);
  };

  // Check if there's any progress/data entered
  const hasProgress = useCallback(() => {
    return (
      loanAmount > 0 ||
      primaryIdFront !== null ||
      primaryIdBack !== null ||
      secondaryIdFront !== null ||
      secondaryIdBack !== null ||
      borrowerDetails.fullName !== "" ||
      borrowerDetails.address !== "" ||
      agreement !== null ||
      loanId !== null
    );
  }, [loanAmount, primaryIdFront, primaryIdBack, secondaryIdFront, secondaryIdBack, borrowerDetails, agreement, loanId]);

  // Handle exit confirmation
  const handleExitConfirmation = useCallback(() => {
    const progress = hasProgress();
    if (!progress) {
      // No progress, exit immediately
      router.back();
      return;
    }

    Alert.alert(
      "Exit Application?",
      "Are you sure you want to exit? Your progress will be lost and you'll need to start over.",
      [
        {
          text: "Continue Application",
          style: "cancel",
        },
        {
          text: "Exit",
          style: "destructive",
          onPress: () => {
            router.back();
          },
        },
      ]
    );
  }, [hasProgress, router]);

  const handleBack = useCallback(() => {
    if (canGoBack()) {
      setCurrentStep((prev) => (prev - 1) as Step);
    } else {
      // If on first step, show exit confirmation
      handleExitConfirmation();
    }
  }, [canGoBack, handleExitConfirmation]);

  // Handle Android back button
  useEffect(() => {
    const backHandler = BackHandler.addEventListener("hardwareBackPress", () => {
      if (currentStep > 1) {
        // If not on first step, go back to previous step
        handleBack();
        return true; // Prevent default back behavior
      } else {
        // If on first step, show exit confirmation
        handleExitConfirmation();
        return true; // Prevent default back behavior
      }
    });

    return () => backHandler.remove();
  }, [currentStep, handleBack, handleExitConfirmation]);

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar style="light" />
      <LinearGradient
        colors={["#03042c", "#302b63", "#24243e"]}
        start={{ x: 0, y: 0 }}
        end={{ x: 1, y: 1 }}
        style={styles.gradient}
      >
        {/* Progress Indicator */}
        <View style={styles.progressContainer}>
          <View style={[
            styles.progressStep,
            currentStep >= 1 && styles.progressStepActive,
            currentStep === 1 && styles.progressStepCurrent,
          ]}>
            <Text style={[
              styles.progressStepText,
              currentStep >= 1 && styles.progressStepTextActive,
              currentStep === 1 && styles.progressStepTextCurrent,
            ]}>
              1
            </Text>
          </View>
          <View style={[
            styles.progressLine,
            currentStep > 1 && styles.progressLineActive,
          ]} />
          <View style={[
            styles.progressStep,
            currentStep >= 2 && styles.progressStepActive,
            currentStep === 2 && styles.progressStepCurrent,
          ]}>
            <Text style={[
              styles.progressStepText,
              currentStep >= 2 && styles.progressStepTextActive,
              currentStep === 2 && styles.progressStepTextCurrent,
            ]}>
              2
            </Text>
          </View>
          <View style={[
            styles.progressLine,
            currentStep > 2 && styles.progressLineActive,
          ]} />
          <View style={[
            styles.progressStep,
            currentStep >= 3 && styles.progressStepActive,
            currentStep === 3 && styles.progressStepCurrent,
          ]}>
            <Text style={[
              styles.progressStepText,
              currentStep >= 3 && styles.progressStepTextActive,
              currentStep === 3 && styles.progressStepTextCurrent,
            ]}>
              3
            </Text>
          </View>
          <View style={[
            styles.progressLine,
            currentStep > 3 && styles.progressLineActive,
          ]} />
          <View style={[
            styles.progressStep,
            currentStep >= 4 && styles.progressStepActive,
            currentStep === 4 && styles.progressStepCurrent,
          ]}>
            <Text style={[
              styles.progressStepText,
              currentStep >= 4 && styles.progressStepTextActive,
              currentStep === 4 && styles.progressStepTextCurrent,
            ]}>
              4
            </Text>
          </View>
          <View style={[
            styles.progressLine,
            currentStep > 4 && styles.progressLineActive,
          ]} />
          <View style={[
            styles.progressStep,
            currentStep >= 5 && styles.progressStepActive,
            currentStep === 5 && styles.progressStepCurrent,
          ]}>
            <Text style={[
              styles.progressStepText,
              currentStep >= 5 && styles.progressStepTextActive,
              currentStep === 5 && styles.progressStepTextCurrent,
            ]}>
              5
            </Text>
          </View>
        </View>

        <ScrollView contentContainerStyle={styles.scrollContent}>
          {renderCurrentStep()}
        </ScrollView>

        {/* Navigation Buttons */}
        <View style={styles.navigation}>
          {canGoBack() && (
            <TouchableOpacity
              style={styles.navButton}
              onPress={handleBack}
            >
              <Ionicons name="arrow-back" size={20} color="#fff" />
              <Text style={styles.navButtonText}>Back</Text>
            </TouchableOpacity>
          )}
           {currentStep < 5 && (
             <TouchableOpacity
               style={[
                 styles.navButton, 
                 styles.navButtonPrimary,
                 (updatingProfile || submitting) && styles.navButtonDisabled
               ]}
               onPress={handleNext}
               disabled={updatingProfile || submitting}
             >
               {updatingProfile || submitting ? (
                 <ActivityIndicator color="#fff" size="small" />
               ) : (
                 <View style={{ flexDirection: 'row', alignItems: 'center', gap: 8 }}>
                   <Text style={styles.navButtonText}>
                     {currentStep === 4 ? "Review & Submit" : "Next"}
                   </Text>
                   <Ionicons name="arrow-forward" size={20} color="#fff" />
                 </View>
               )}
             </TouchableOpacity>
           )}
        </View>
      </LinearGradient>

      <LocationPopup
        visible={showLocationPopup}
        onClose={() => setShowLocationPopup(false)}
        onLocationObtained={(loc) => {
          setLocation(loc);
          setShowLocationPopup(false);
        }}
        title="Loan Application Location"
        message="We need your location to process your loan application."
      />

      {/* Terms and Conditions Modal */}
      {renderTermsModal()}

      {/* Document Viewer Modal */}
      <Modal
        visible={viewingDocument !== null}
        transparent={true}
        animationType="slide"
        onRequestClose={() => setViewingDocument(null)}
      >
        <View style={styles.modalContainer}>
          <View style={styles.modalContent}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>
                {viewingDocument?.type === "loan" 
                  ? "Loan Agreement" 
                  : viewingDocument?.type === "guaranty"
                  ? "Guaranty Agreement"
                  : "Final Demand Letter"}
              </Text>
              <TouchableOpacity
                onPress={() => setViewingDocument(null)}
                style={styles.modalCloseButton}
              >
                <Ionicons name="close" size={28} color="#fff" />
              </TouchableOpacity>
            </View>
            <WebView
              source={{
                html: viewingDocument?.content || generateDocumentHTML(
                  "",
                  viewingDocument?.type === "loan" 
                    ? "Loan Agreement" 
                    : viewingDocument?.type === "guaranty"
                    ? "Guaranty Agreement"
                    : "Final Demand Letter"
                ),
              }}
              style={styles.modalWebView}
              showsVerticalScrollIndicator={true}
            />
          </View>
        </View>
      </Modal>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: "black" },
  gradient: { flex: 1 },
  progressContainer: {
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "space-between",
    paddingHorizontal: 20,
    paddingTop: 60,
    paddingBottom: 20,
    flexWrap: "nowrap",
  },
  progressStep: {
    width: 36,
    height: 36,
    borderRadius: 18,
    backgroundColor: "#374151",
    justifyContent: "center",
    alignItems: "center",
    flexShrink: 0,
  },
  progressStepActive: {
    backgroundColor: "#f97316",
  },
  progressStepCurrent: {
    width: 48,
    height: 48,
    borderRadius: 24,
    transform: [{ scale: 1.15 }],
  },
  progressStepText: {
    color: "#9CA3AF",
    fontSize: 14,
    fontWeight: "600",
  },
  progressStepTextActive: {
    color: "#fff",
  },
  progressStepTextCurrent: {
    fontSize: 18,
    fontWeight: "700",
  },
  progressLine: {
    flex: 1,
    height: 2,
    backgroundColor: "#374151",
    marginHorizontal: 6,
    minWidth: 20,
  },
  progressLineActive: {
    backgroundColor: "#f97316",
  },
  scrollContent: {
    padding: 20,
    paddingBottom: 100,
  },
  stepHeader: {
    flexDirection: "row",
    alignItems: "center",
    gap: 12,
    marginBottom: 8,
  },
  stepTitle: {
    fontSize: 24,
    fontWeight: "700",
    color: "#fff",
  },
  stepSubtitle: {
    fontSize: 14,
    color: "#9CA3AF",
    marginBottom: 24,
  },
  card: {
    backgroundColor: "#1e1e2f",
    padding: 20,
    borderRadius: 14,
    marginBottom: 20,
    borderWidth: 1,
    borderColor: "#2a2a3e",
    shadowColor: "#000",
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 2,
  },
  label: {
    fontWeight: "600",
    fontSize: 15,
    color: "#E5E7EB",
    letterSpacing: 0.2,
  },
  amount: {
    fontSize: 22,
    fontWeight: "700",
    marginBottom: 12,
    color: "#fff",
  },
  rangeRow: {
    flexDirection: "row",
    justifyContent: "space-between",
    marginTop: 4,
  },
  rangeText: { fontSize: 12, color: "#aaa" },
  tenorScrollContainer: {
    paddingVertical: 8,
  },
  tenorGrid: {
    flexDirection: "row",
    flexWrap: "wrap",
    justifyContent: "flex-start",
    width: "100%",
    paddingHorizontal: 0,
  },
  tenorBox: {
    width: (Dimensions.get("window").width - 40) / 6 - 6.67, // Screen width minus padding (20 each side) divided by 6, minus margin space
    marginRight: 8,
    marginBottom: 8,
    padding: 10,
    borderRadius: 10,
    backgroundColor: "#444",
    alignItems: "center",
    justifyContent: "center",
    aspectRatio: 1,
  },
  tenorBoxLastInRow: {
    marginRight: 0, // Remove right margin for last item in each row
  },
  activeTenor: { backgroundColor: "#f97316" },
  tenorText: { 
    fontWeight: "700", 
    fontSize: 18,
    color: "#ddd" 
  },
  activeTenorText: { color: "#fff" },
  tenorMonthText: {
    fontSize: 10,
    color: "#aaa",
    marginTop: 2,
  },
  activeTenorMonthText: { color: "#fff" },
  detailText: { marginVertical: 4, fontSize: 14, color: "#fff" },
  locationHeader: {
    flexDirection: "row",
    alignItems: "center",
    gap: 8,
    marginBottom: 12,
  },
  sectionHeader: {
    flexDirection: "row",
    alignItems: "center",
    gap: 10,
    marginBottom: 20,
    paddingBottom: 14,
    borderBottomWidth: 1.5,
    borderBottomColor: "#374151",
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: "700",
    color: "#fff",
  },
  locationInfo: { gap: 8 },
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
  locationWarning: {
    flexDirection: "row",
    alignItems: "center",
    marginTop: 12,
    padding: 12,
    backgroundColor: "rgba(249, 115, 22, 0.1)",
    borderRadius: 8,
    borderWidth: 1,
    borderColor: "#f97316",
    gap: 8,
  },
  locationWarningText: {
    flex: 1,
    color: "#f97316",
    fontSize: 13,
    lineHeight: 18,
  },
  requiredIndicator: {
    color: "#f97316",
    fontSize: 12,
    fontWeight: "600",
    marginLeft: 4,
  },
  uploadRow: {
    flexDirection: "row",
    gap: 12,
    marginTop: 12,
  },
  uploadColumn: {
    flex: 1,
  },
  uploadSingle: {
    width: "100%",
    marginTop: 12,
  },
  uploadLabel: {
    fontSize: 13,
    color: "#9CA3AF",
    marginBottom: 8,
    fontWeight: "500",
  },
  uploadButton: {
    flexDirection: "row",
    alignItems: "center",
    gap: 12,
    padding: 16,
    backgroundColor: "#2a2a3e",
    borderRadius: 10,
    borderWidth: 1.5,
    borderColor: "#374151",
    minHeight: 56,
  },
  uploadButtonSuccess: {
    borderColor: "#4EFA8A",
    backgroundColor: "rgba(78, 250, 138, 0.1)",
  },
  uploadButtonText: {
    color: "#fff",
    fontSize: 16,
    fontWeight: "500",
    flex: 1,
  },
  removeButton: {
    marginTop: 8,
    alignSelf: "flex-start",
  },
  removeButtonText: {
    color: "#FF6B6B",
    fontSize: 14,
    fontWeight: "600",
  },
  helperText: {
    fontSize: 12,
    color: "#9CA3AF",
    marginBottom: 10,
    marginTop: 2,
    lineHeight: 16,
  },
  inputGroup: { 
    marginBottom: 20,
  },
  inputContainer: {
    position: 'relative',
  },
  input: {
    backgroundColor: "#2a2a3e",
    padding: 14,
    paddingLeft: 16,
    borderRadius: 10,
    borderWidth: 1.5,
    borderColor: "#374151",
    color: "#fff",
    fontSize: 15,
    minHeight: 48,
  },
  inputError: {
    borderColor: "#FF6B6B",
    borderWidth: 2,
    backgroundColor: "rgba(255, 107, 107, 0.05)",
  },
  errorText: {
    color: "#FF6B6B",
    fontSize: 12,
    marginTop: 6,
    marginLeft: 4,
    fontWeight: "500",
  },
  dropdownButtonError: {
    borderColor: "#FF6B6B",
    borderWidth: 2,
    backgroundColor: "rgba(255, 107, 107, 0.05)",
  },
  optionButtonError: {
    borderColor: "#FF6B6B",
    borderWidth: 2,
  },
  labelRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    marginBottom: 8,
  },
  sectionSubtitle: {
    fontSize: 13,
    color: "#9CA3AF",
    marginBottom: 16,
    fontStyle: 'italic',
  },
  addressSection: {
    marginTop: 8,
    paddingTop: 16,
    borderTopWidth: 1,
    borderTopColor: "#374151",
  },
  textArea: {
    minHeight: 90,
    textAlignVertical: "top",
    paddingTop: 14,
  },
  optionsRow: {
    flexDirection: "row",
    flexWrap: "wrap",
    gap: 8,
  },
  optionButton: {
    paddingVertical: 10,
    paddingHorizontal: 18,
    borderRadius: 10,
    backgroundColor: "#2a2a3e",
    borderWidth: 1.5,
    borderColor: "#374151",
    minWidth: 100,
    alignItems: 'center',
  },
  optionButtonActive: {
    backgroundColor: "#f97316",
    borderColor: "#f97316",
    shadowColor: "#f97316",
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.3,
    shadowRadius: 4,
    elevation: 3,
  },
  optionText: {
    color: "#9CA3AF",
    fontSize: 14,
    fontWeight: "500",
  },
  optionTextActive: {
    color: "#fff",
    fontWeight: "700",
  },
  actionButton: {
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
  agreementPreview: {
    marginTop: 16,
    padding: 12,
    backgroundColor: "#2a2a3e",
    borderRadius: 8,
  },
  agreementLabel: {
    fontSize: 16,
    fontWeight: "bold",
    color: "#fff",
    marginBottom: 8,
  },
  checkboxContainer: {
    gap: 12,
  },
  checkboxContainerError: {
    padding: 8,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: "#FF6B6B",
    backgroundColor: "rgba(255, 107, 107, 0.05)",
  },
  checkboxRow: {
    flexDirection: "row",
    alignItems: "center",
    paddingVertical: 8,
  },
  checkbox: {
    width: 22,
    height: 22,
    borderWidth: 2,
    borderColor: "#4EFA8A",
    borderRadius: 5,
    marginRight: 12,
    justifyContent: "center",
    alignItems: "center",
    backgroundColor: "transparent",
  },
  checkboxChecked: {
    backgroundColor: "#4EFA8A",
    borderColor: "#4EFA8A",
  },
  checkboxLabel: {
    fontSize: 15,
    color: "#E5E7EB",
    fontWeight: "500",
  },
  dropdownButton: {
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "space-between",
    backgroundColor: "#2a2a3e",
    padding: 14,
    paddingLeft: 16,
    borderRadius: 10,
    borderWidth: 1.5,
    borderColor: "#374151",
    minHeight: 48,
  },
  dropdownButtonText: {
    color: "#fff",
    fontSize: 15,
    flex: 1,
  },
  placeholderText: {
    color: "#6B7280",
  },
  dropdownList: {
    marginTop: 8,
    backgroundColor: "#2a2a3e",
    borderRadius: 10,
    borderWidth: 1.5,
    borderColor: "#374151",
    maxHeight: 200,
    overflow: "hidden",
  },
  dropdownItem: {
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "space-between",
    padding: 14,
    paddingLeft: 16,
    borderBottomWidth: 1,
    borderBottomColor: "#374151",
  },
  dropdownItemSelected: {
    backgroundColor: "rgba(78, 250, 138, 0.1)",
  },
  dropdownItemText: {
    color: "#E5E7EB",
    fontSize: 15,
    flex: 1,
  },
  dropdownItemTextSelected: {
    color: "#4EFA8A",
    fontWeight: "600",
  },
  modalOverlay: {
    flex: 1,
    backgroundColor: "rgba(0, 0, 0, 0.7)",
    justifyContent: "center",
    alignItems: "center",
  },
  datePickerModal: {
    width: "90%",
    maxHeight: "80%",
    backgroundColor: "#1e1e2f",
    borderRadius: 14,
    borderWidth: 1,
    borderColor: "#374151",
    overflow: "hidden",
  },
  datePickerContainer: {
    flexDirection: "row",
    height: 300,
    padding: 16,
  },
  datePickerColumn: {
    flex: 1,
    marginHorizontal: 4,
  },
  datePickerLabel: {
    fontSize: 12,
    color: "#9CA3AF",
    marginBottom: 8,
    textAlign: "center",
    fontWeight: "600",
  },
  datePickerScroll: {
    flex: 1,
  },
  datePickerOption: {
    padding: 12,
    marginVertical: 2,
    borderRadius: 8,
    backgroundColor: "#2a2a3e",
    alignItems: "center",
  },
  datePickerOptionSelected: {
    backgroundColor: "#4EFA8A",
  },
  datePickerOptionText: {
    color: "#E5E7EB",
    fontSize: 14,
  },
  datePickerOptionTextSelected: {
    color: "#03042c",
    fontWeight: "700",
  },
  datePickerConfirmButton: {
    backgroundColor: "#4EFA8A",
    padding: 16,
    alignItems: "center",
    borderTopWidth: 1,
    borderTopColor: "#374151",
  },
  datePickerConfirmText: {
    color: "#03042c",
    fontSize: 16,
    fontWeight: "700",
  },
  viewDocumentButton: {
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "center",
    marginTop: 12,
    paddingVertical: 10,
    paddingHorizontal: 16,
    backgroundColor: "rgba(78, 250, 138, 0.1)",
    borderRadius: 8,
    borderWidth: 1,
    borderColor: "#4EFA8A",
    gap: 8,
  },
  viewDocumentText: {
    color: "#4EFA8A",
    fontSize: 14,
    fontWeight: "600",
  },
  modalContainer: {
    flex: 1,
    backgroundColor: "rgba(0, 0, 0, 0.9)",
    justifyContent: "center",
    alignItems: "center",
  },
  modalContent: {
    width: "95%",
    height: "90%",
    backgroundColor: "#1a1a2e",
    borderRadius: 12,
    overflow: "hidden",
  },
  modalHeader: {
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "center",
    padding: 20,
    borderBottomWidth: 1,
    borderBottomColor: "#374151",
    backgroundColor: "#03042c",
  },
  modalTitle: {
    fontSize: 20,
    fontWeight: "bold",
    color: "#fff",
  },
  modalDownloadButton: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    paddingHorizontal: 12,
    paddingVertical: 8,
    backgroundColor: 'rgba(78, 250, 138, 0.1)',
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#4EFA8A',
  },
  modalDownloadText: {
    color: '#4EFA8A',
    fontSize: 14,
    fontWeight: '600',
  },
  modalCloseButton: {
    padding: 4,
  },
  modalLoadingContainer: {
    flex: 1,
    justifyContent: "center",
    alignItems: "center",
    backgroundColor: "#1a1a2e",
  },
  modalLoadingText: {
    color: "#fff",
    marginTop: 12,
    fontSize: 14,
  },
  modalWebView: {
    flex: 1,
    backgroundColor: "#1a1a2e",
  },
  modalScrollView: {
    flex: 1,
  },
  modalScrollContent: {
    padding: 20,
  },
  modalDocumentText: {
    color: "#fff",
    fontSize: 12,
    lineHeight: 20,
    fontFamily: "monospace",
  },
  acknowledgmentRow: {
    flexDirection: "row",
    alignItems: "center",
    marginTop: 12,
    gap: 8,
  },
  acknowledgmentText: {
    color: "#4EFA8A",
    fontSize: 14,
    fontWeight: "600",
  },
  importantNotice: {
    flexDirection: "row",
    alignItems: "flex-start",
    marginTop: 16,
    padding: 12,
    backgroundColor: "rgba(249, 115, 22, 0.1)",
    borderRadius: 8,
    borderWidth: 1,
    borderColor: "#f97316",
    gap: 8,
  },
  importantNoticeText: {
    flex: 1,
    color: "#f97316",
    fontSize: 13,
    lineHeight: 18,
  },
  agreementText: {
    color: "#fff",
    fontSize: 12,
    lineHeight: 18,
  },
  reviewTitle: {
    fontSize: 18,
    fontWeight: "700",
    color: "#fff",
    marginBottom: 12,
  },
  reviewText: {
    fontSize: 14,
    color: "#fff",
    marginBottom: 8,
  },
  reviewRow: {
    flexDirection: "row",
    alignItems: "center",
    gap: 8,
    marginBottom: 8,
  },
  submitButton: {
    marginTop: 24,
    borderRadius: 8,
    overflow: "hidden",
  },
  navigation: {
    flexDirection: "row",
    justifyContent: "space-between",
    padding: 20,
    backgroundColor: "rgba(0, 0, 0, 0.5)",
  },
  navButton: {
    flexDirection: "row",
    alignItems: "center",
    gap: 8,
    paddingVertical: 12,
    paddingHorizontal: 20,
    borderRadius: 8,
    backgroundColor: "#374151",
  },
  navButtonPrimary: {
    backgroundColor: "#f97316",
  },
  navButtonDisabled: {
    opacity: 0.5,
  },
  navButtonText: {
    color: "#fff",
    fontSize: 16,
    fontWeight: "600",
  },
  termsModalOverlay: {
    flex: 1,
    backgroundColor: "rgba(0, 0, 0, 0.8)",
    justifyContent: "center",
    alignItems: "center",
  },
  termsModalContent: {
    width: "90%",
    height: "85%",
    maxHeight: "85%",
    backgroundColor: "#1e1e2f",
    borderRadius: 16,
    borderWidth: 1,
    borderColor: "#374151",
    overflow: "hidden",
    flexDirection: "column",
  },
  termsModalHeader: {
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "center",
    padding: 20,
    borderBottomWidth: 1,
    borderBottomColor: "#374151",
    backgroundColor: "#2a2a3e",
  },
  termsModalTitle: {
    fontSize: 20,
    fontWeight: "700",
    color: "#fff",
  },
  termsModalCloseButton: {
    padding: 4,
  },
  termsModalScrollView: {
    flex: 1,
    backgroundColor: "#1e1e2f",
  },
  termsModalScrollContent: {
    padding: 20,
    paddingBottom: 40,
  },
  termsSectionTitle: {
    fontSize: 16,
    fontWeight: "700",
    color: "#4EFA8A",
    marginTop: 20,
    marginBottom: 12,
  },
  termsText: {
    fontSize: 14,
    color: "#ffffff",
    lineHeight: 22,
    marginBottom: 12,
    textAlign: "justify",
  },
  termsBulletPoint: {
    fontSize: 14,
    color: "#ffffff",
    lineHeight: 22,
    marginBottom: 10,
    marginLeft: 16,
    paddingLeft: 8,
  },
  termsCheckboxContainer: {
    flexDirection: "row",
    alignItems: "flex-start",
    marginTop: 24,
    marginBottom: 16,
    padding: 16,
    backgroundColor: "rgba(78, 250, 138, 0.1)",
    borderRadius: 8,
    borderWidth: 1,
    borderColor: "#4EFA8A",
  },
  termsCheckbox: {
    width: 24,
    height: 24,
    borderRadius: 6,
    borderWidth: 2,
    borderColor: "#4EFA8A",
    backgroundColor: "transparent",
    justifyContent: "center",
    alignItems: "center",
    marginRight: 12,
    marginTop: 2,
  },
  termsCheckboxChecked: {
    backgroundColor: "#4EFA8A",
  },
  termsCheckboxLabel: {
    flex: 1,
    fontSize: 13,
    color: "#E5E7EB",
    lineHeight: 20,
  },
  termsModalFooter: {
    flexDirection: "row",
    gap: 10,
    padding: 16,
    borderTopWidth: 1,
    borderTopColor: "#374151",
    backgroundColor: "#2a2a3e",
  },
  termsButton: {
    flex: 1,
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "center",
    gap: 6,
    paddingVertical: 12,
    paddingHorizontal: 16,
    borderRadius: 10,
    minHeight: 44,
    shadowColor: "#000",
    shadowOffset: {
      width: 0,
      height: 2,
    },
    shadowOpacity: 0.1,
    shadowRadius: 3,
    elevation: 3,
  },
  termsButtonCancel: {
    backgroundColor: "#374151",
    borderWidth: 1.5,
    borderColor: "#4B5563",
  },
  termsButtonAgree: {
    backgroundColor: "#4EFA8A",
    borderWidth: 1.5,
    borderColor: "#4EFA8A",
  },
  termsButtonDisabled: {
    backgroundColor: "#1F2937",
    borderColor: "#374151",
    opacity: 0.6,
  },
  termsButtonTextCancel: {
    color: "#fff",
    fontSize: 15,
    fontWeight: "600",
    letterSpacing: 0.3,
  },
  termsButtonTextAgree: {
    color: "#03042c",
    fontSize: 15,
    fontWeight: "700",
    letterSpacing: 0.3,
  },
  termsButtonTextDisabled: {
    color: "#9CA3AF",
  },
  savedDataBanner: {
    backgroundColor: "rgba(78, 250, 138, 0.1)",
    borderWidth: 1,
    borderColor: "#4EFA8A",
    marginBottom: 16,
  },
  savedDataBannerContent: {
    flexDirection: "row",
    alignItems: "center",
    gap: 10,
  },
  savedDataBannerText: {
    flex: 1,
    fontSize: 12,
    color: "#E5E7EB",
    lineHeight: 18,
  },
  clearSavedDataButton: {
    padding: 4,
  },
});

