import React, { useState } from 'react';
import {
    ActivityIndicator,
    Alert,
    Keyboard,
    KeyboardAvoidingView,
    Platform,
    SafeAreaView,
    StyleSheet,
    Text,
    TextInput,
    TouchableOpacity,
    TouchableWithoutFeedback,
    View
} from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { useRouter } from 'expo-router';
import { api } from '../../src/config/api';

export default function ForgotPasswordScreen() {
    const router = useRouter();
    const [email, setEmail] = useState('');
    const [otp, setOtp] = useState('');
    const [newPassword, setNewPassword] = useState('');

    // existing code had loading, we keep it
    const [loading, setLoading] = useState(false);
    const [step, setStep] = useState<1 | 2>(1); // 1: Email, 2: OTP + New Password

    const handleSendOtp = async () => {
        if (!email) {
            Alert.alert('Missing field', 'Please enter your email address');
            return;
        }

        try {
            setLoading(true);
            const res = await api.post('/auth/forgot-password', { email });

            if (res.data.success) {
                Alert.alert('OTP Sent', res.data.message);
                setStep(2);
            } else {
                Alert.alert('Error', res.data.message || 'Failed to send OTP');
            }
        } catch (err: any) {
            const msg = err.response?.data?.message || 'Failed to send reset email. Please try again.';
            Alert.alert('Error', msg);
        } finally {
            setLoading(false);
        }
    };

    const handleResetPassword = async () => {
        if (!otp || !newPassword) {
            Alert.alert('Missing fields', 'Please enter OTP and new password');
            return;
        }

        try {
            setLoading(true);
            const res = await api.post('/auth/reset-password', {
                email,
                otp,
                newPassword
            });

            if (res.data.success) {
                Alert.alert(
                    'Success',
                    'Your password has been reset successfully.',
                    [
                        {
                            text: 'Login Now',
                            onPress: () => router.dismissTo('/(tabs)/login'),
                        },
                    ]
                );
            } else {
                Alert.alert('Error', res.data.message || 'Failed to reset password');
            }
        } catch (err: any) {
            const msg = err.response?.data?.message || 'Failed to reset password. Check your OTP.';
            Alert.alert('Error', msg);
        } finally {
            setLoading(false);
        }
    };

    return (
        <SafeAreaView style={styles.safeContainer}>
            <TouchableWithoutFeedback onPress={Keyboard.dismiss}>
                <KeyboardAvoidingView
                    behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
                    style={styles.container}
                >
                    <View style={styles.headerContainer}>
                        <Text style={styles.title}>Recovery</Text>
                        <Text style={styles.subtitle}>
                            {step === 1
                                ? 'Enter your email to receive an OTP'
                                : 'Enter the OTP and your new password'}
                        </Text>
                    </View>

                    <View style={styles.form}>
                        {step === 1 && (
                            <TextInput
                                style={styles.input}
                                placeholder="Email Address"
                                placeholderTextColor="#9CA3AF"
                                onChangeText={setEmail}
                                value={email}
                                autoCapitalize="none"
                                keyboardType="email-address"
                            />
                        )}

                        {step === 2 && (
                            <>
                                <TextInput
                                    style={styles.input}
                                    placeholder="Enter 6-digit OTP"
                                    placeholderTextColor="#9CA3AF"
                                    onChangeText={setOtp}
                                    value={otp}
                                    keyboardType="number-pad"
                                    maxLength={6}
                                />
                                <TextInput
                                    style={styles.input}
                                    placeholder="New Password"
                                    placeholderTextColor="#9CA3AF"
                                    onChangeText={setNewPassword}
                                    value={newPassword}
                                    secureTextEntry
                                />
                            </>
                        )}

                        <TouchableOpacity
                            activeOpacity={0.85}
                            onPress={step === 1 ? handleSendOtp : handleResetPassword}
                            disabled={loading}
                        >
                            <LinearGradient
                                colors={["#03042c", "#302b63", "#24243e"]}
                                start={{ x: 0, y: 0 }}
                                end={{ x: 1, y: 1 }}
                                style={styles.button}
                            >
                                {loading ? (
                                    <ActivityIndicator color="#fff" />
                                ) : (
                                    <Text style={styles.buttonText}>
                                        {step === 1 ? 'Send OTP' : 'Reset Password'}
                                    </Text>
                                )}
                            </LinearGradient>
                        </TouchableOpacity>

                        <TouchableOpacity
                            style={styles.backContainer}
                            onPress={() => {
                                if (step === 2) setStep(1);
                                else router.back();
                            }}
                        >
                            <Text style={styles.link}>
                                {step === 2 ? 'Change Email' : 'Back to Login'}
                            </Text>
                        </TouchableOpacity>
                    </View>
                </KeyboardAvoidingView>
            </TouchableWithoutFeedback>
        </SafeAreaView>
    );
}

const styles = StyleSheet.create({
    safeContainer: {
        flex: 1,
        backgroundColor: 'black',
    },
    container: {
        flex: 1,
        paddingHorizontal: 24,
        justifyContent: 'center',
    },
    headerContainer: {
        marginBottom: 48,
    },
    title: {
        fontSize: 32,
        fontWeight: '800',
        color: 'white',
        marginBottom: 8,
        textAlign: 'center',
    },
    subtitle: {
        fontSize: 16,
        color: 'white',
        textAlign: 'center',
    },
    form: {
        width: '100%',
    },
    input: {
        backgroundColor: '#1C2233',
        padding: 16,
        borderRadius: 12,
        borderWidth: 1,
        borderColor: '#374151',
        marginBottom: 16,
        fontSize: 16,
        color: '#ffffff',
    },
    button: {
        paddingVertical: 16,
        borderRadius: 12,
        alignItems: 'center',
        shadowColor: '#ED80E9',
        shadowOpacity: 0.2,
        shadowRadius: 6,
        shadowOffset: { width: 0, height: 4 },
        elevation: 3,
    },
    buttonText: {
        color: '#ffffff',
        fontSize: 18,
        fontWeight: '600',
    },
    backContainer: {
        marginTop: 24,
        alignItems: 'center',
    },
    link: {
        fontSize: 14,
        color: '#9CA3AF',
    },
});
