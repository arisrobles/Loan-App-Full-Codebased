import React, { useState } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, SafeAreaView, ScrollView } from 'react-native';
import { StatusBar } from 'expo-status-bar';
import { Ionicons } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import { useRouter, useLocalSearchParams } from 'expo-router';

type LegalContentType = 'menu' | 'terms' | 'privacy' | 'agreement';

const LegalInfoPage = () => {
  const router = useRouter();
  const params = useLocalSearchParams();
  const [contentType, setContentType] = useState<LegalContentType>(
    (params.type as LegalContentType) || 'menu'
  );

  const handleView = (type: LegalContentType) => {
    setContentType(type);
  };

  const handleBack = () => {
    if (contentType !== 'menu') {
      setContentType('menu');
    } else {
      router.back();
    }
  };

  const GradientButton = ({ text, onPress, iconName }: { text: string; onPress: () => void; iconName: string }) => (
    <TouchableOpacity onPress={onPress} activeOpacity={0.85}>
      <LinearGradient
        colors={['#03042c', '#302b63', '#24243e']}
        start={{ x: 0, y: 0 }}
        end={{ x: 1, y: 0 }}
        style={styles.item}
      >
        <Text style={styles.itemText}>{text}</Text>
        <Ionicons name={iconName as any} size={24} color="#fff" />
      </LinearGradient>
    </TouchableOpacity>
  );

  const renderContent = () => {
    switch (contentType) {
      case 'terms':
        return <TermsContent />;
      case 'privacy':
        return <PrivacyContent />;
      case 'agreement':
        return <AgreementContent />;
      default:
        return (
          <>
            <GradientButton
              text="Terms & Conditions"
              onPress={() => handleView('terms')}
              iconName="chevron-forward"
            />
            <GradientButton
              text="Privacy Policy"
              onPress={() => handleView('privacy')}
              iconName="chevron-forward"
            />
            <GradientButton
              text="Loan Agreement / Consent"
              onPress={() => handleView('agreement')}
              iconName="chevron-forward"
            />
          </>
        );
    }
  };

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
        <TouchableOpacity onPress={handleBack} style={styles.backButton}>
          <Ionicons name="arrow-back" size={24} color="#fff" />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>
          {contentType === 'menu' ? 'Legal / Info' : contentType === 'terms' ? 'Terms & Conditions' : contentType === 'privacy' ? 'Privacy Policy' : 'Loan Agreement'}
        </Text>
        <View style={{ width: 40 }} />
      </LinearGradient>

      {contentType === 'menu' ? (
        <View style={styles.menuContainer}>
          {renderContent()}
        </View>
      ) : (
        <ScrollView style={styles.scrollView} contentContainerStyle={styles.scrollContent}>
          {renderContent()}
        </ScrollView>
      )}
    </SafeAreaView>
  );
};

const TermsContent = () => (
  <View style={styles.contentContainer}>
    <Text style={styles.contentTitle}>Terms & Conditions</Text>
    <Text style={styles.contentDate}>Last Updated: January 2024</Text>
    
    <Text style={styles.sectionTitle}>1. Acceptance of Terms</Text>
    <Text style={styles.contentText}>
      By accessing and using the MasterFunds mobile application, you accept and agree to be bound by the terms and provision of this agreement.
    </Text>

    <Text style={styles.sectionTitle}>2. Loan Application</Text>
    <Text style={styles.contentText}>
      When you apply for a loan through MasterFunds, you agree to provide accurate and complete information. We reserve the right to approve or reject any loan application at our sole discretion.
    </Text>

    <Text style={styles.sectionTitle}>3. Interest Rates and Fees</Text>
    <Text style={styles.contentText}>
      Interest rates and fees are disclosed at the time of loan application. By accepting a loan, you agree to the terms including interest rates, penalties, and repayment schedules.
    </Text>

    <Text style={styles.sectionTitle}>4. Repayment Obligations</Text>
    <Text style={styles.contentText}>
      You are responsible for making timely payments according to your repayment schedule. Late payments may incur penalties as specified in your loan agreement.
    </Text>

    <Text style={styles.sectionTitle}>5. Payment Submission</Text>
    <Text style={styles.contentText}>
      Payments submitted through the app are subject to admin approval. Payments are not considered complete until approved by MasterFunds administrators.
    </Text>

    <Text style={styles.sectionTitle}>6. Account Security</Text>
    <Text style={styles.contentText}>
      You are responsible for maintaining the confidentiality of your account credentials. Notify us immediately of any unauthorized access to your account.
    </Text>

    <Text style={styles.sectionTitle}>7. Limitation of Liability</Text>
    <Text style={styles.contentText}>
      MasterFunds shall not be liable for any indirect, incidental, special, or consequential damages arising from the use of this application.
    </Text>

    <Text style={styles.sectionTitle}>8. Changes to Terms</Text>
    <Text style={styles.contentText}>
      We reserve the right to modify these terms at any time. Continued use of the application after changes constitutes acceptance of the new terms.
    </Text>
  </View>
);

const PrivacyContent = () => (
  <View style={styles.contentContainer}>
    <Text style={styles.contentTitle}>Privacy Policy</Text>
    <Text style={styles.contentDate}>Last Updated: January 2024</Text>
    
    <Text style={styles.sectionTitle}>1. Information We Collect</Text>
    <Text style={styles.contentText}>
      We collect information you provide directly to us, including personal identification information, financial information, and documents submitted during loan applications.
    </Text>

    <Text style={styles.sectionTitle}>2. How We Use Your Information</Text>
    <Text style={styles.contentText}>
      We use your information to process loan applications, manage your account, process payments, send notifications, and comply with legal obligations.
    </Text>

    <Text style={styles.sectionTitle}>3. Information Sharing</Text>
    <Text style={styles.contentText}>
      We do not sell your personal information. We may share information with service providers, legal authorities when required, and with your consent.
    </Text>

    <Text style={styles.sectionTitle}>4. Data Security</Text>
    <Text style={styles.contentText}>
      We implement appropriate security measures to protect your personal information. However, no method of transmission over the internet is 100% secure.
    </Text>

    <Text style={styles.sectionTitle}>5. Your Rights</Text>
    <Text style={styles.contentText}>
      You have the right to access, update, or delete your personal information. Contact us to exercise these rights.
    </Text>

    <Text style={styles.sectionTitle}>6. Cookies and Tracking</Text>
    <Text style={styles.contentText}>
      Our application may use cookies and similar technologies to enhance your experience and analyze usage patterns.
    </Text>

    <Text style={styles.sectionTitle}>7. Children's Privacy</Text>
    <Text style={styles.contentText}>
      Our services are not intended for individuals under 18 years of age. We do not knowingly collect information from children.
    </Text>

    <Text style={styles.sectionTitle}>8. Contact Us</Text>
    <Text style={styles.contentText}>
      For questions about this Privacy Policy, please contact us at support@masterfunds.com
    </Text>
  </View>
);

const AgreementContent = () => (
  <View style={styles.contentContainer}>
    <Text style={styles.contentTitle}>Loan Agreement & Consent</Text>
    <Text style={styles.contentDate}>Last Updated: January 2024</Text>
    
    <Text style={styles.sectionTitle}>Loan Agreement</Text>
    <Text style={styles.contentText}>
      By applying for a loan through MasterFunds, you agree to the following terms:
    </Text>

    <Text style={styles.subsectionTitle}>1. Loan Terms</Text>
    <Text style={styles.contentText}>
      • Loan amount, interest rate, and repayment schedule will be specified in your loan approval notification{'\n'}
      • You agree to repay the loan according to the agreed schedule{'\n'}
      • Late payments may incur penalties as specified
    </Text>

    <Text style={styles.subsectionTitle}>2. Repayment</Text>
    <Text style={styles.contentText}>
      • Payments must be made on or before the due date{'\n'}
      • You may make payments through the MasterFunds mobile application{'\n'}
      • All payments are subject to admin approval
    </Text>

    <Text style={styles.subsectionTitle}>3. Default</Text>
    <Text style={styles.contentText}>
      • Failure to make payments may result in default{'\n'}
      • Default may affect your credit score and future loan eligibility{'\n'}
      • We reserve the right to take legal action to recover outstanding amounts
    </Text>

    <Text style={styles.sectionTitle}>Consent to Data Processing</Text>
    <Text style={styles.contentText}>
      By using MasterFunds services, you consent to:
    </Text>
    <Text style={styles.contentText}>
      • Collection and processing of your personal and financial information{'\n'}
      • Credit checks and background verification{'\n'}
      • Communication via email, SMS, and in-app notifications{'\n'}
      • Sharing information with credit bureaus and regulatory authorities as required by law
    </Text>

    <Text style={styles.sectionTitle}>Acknowledgement</Text>
    <Text style={styles.contentText}>
      I acknowledge that I have read, understood, and agree to be bound by the terms of this Loan Agreement and Consent. I understand that this is a legally binding agreement.
    </Text>
  </View>
);

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: 'black',
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
    fontSize: 20,
    fontWeight: '700',
    color: '#fff',
  },
  menuContainer: {
    padding: 20,
  },
  item: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 18,
    borderRadius: 16,
    marginBottom: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.06,
    shadowRadius: 6,
    elevation: 3,
  },
  itemText: {
    color: '#ffffff',
    fontSize: 16,
    fontWeight: '600',
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    padding: 20,
    paddingBottom: 40,
  },
  contentContainer: {
    backgroundColor: '#1C2233',
    borderRadius: 12,
    padding: 20,
    borderWidth: 1,
    borderColor: '#374151',
  },
  contentTitle: {
    fontSize: 24,
    fontWeight: '700',
    color: '#fff',
    marginBottom: 8,
  },
  contentDate: {
    fontSize: 14,
    color: '#9CA3AF',
    marginBottom: 24,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: '#ED80E9',
    marginTop: 20,
    marginBottom: 12,
  },
  subsectionTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: '#D1D5DB',
    marginTop: 16,
    marginBottom: 8,
  },
  contentText: {
    fontSize: 15,
    color: '#D1D5DB',
    lineHeight: 24,
    marginBottom: 12,
  },
});

export default LegalInfoPage;
