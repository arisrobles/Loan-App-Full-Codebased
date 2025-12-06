import React, { useState } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, SafeAreaView, ScrollView, Alert } from 'react-native';
import { StatusBar } from 'expo-status-bar';
import * as DocumentPicker from 'expo-document-picker';
import { LinearGradient } from 'expo-linear-gradient';

const UploadFiles = () => {
  const [id1, setId1] = useState(null);
  const [id2, setId2] = useState(null);
  const [agreementFile, setAgreementFile] = useState(null);

  const handleUploadID1 = async () => {
    const result = await DocumentPicker.getDocumentAsync({ type: '*/*' });
    if (result.type === 'success') setId1(result.name);
  };

  const handleUploadID2 = async () => {
    const result = await DocumentPicker.getDocumentAsync({ type: '*/*' });
    if (result.type === 'success') setId2(result.name);
  };

  const handleUploadAgreement = async () => {
    const result = await DocumentPicker.getDocumentAsync({ type: '*/*' });
    if (result.type === 'success') setAgreementFile(result.name);
  };

  const handleSave = () => {
    if (!id1 || !id2 || !agreementFile) {
      Alert.alert('Error', 'Please upload 2 valid IDs and the agreement.');
      return;
    }
    Alert.alert('Success', 'Files uploaded successfully!');
  };

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar style="light" />
      <ScrollView contentContainerStyle={{ paddingBottom: 40 }}>
        <Text style={styles.headerTitle}>Upload Files</Text>

        {/* Valid ID 1 */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Primary ID</Text>
          <TouchableOpacity onPress={handleUploadID1}>
            <LinearGradient 
               colors={["#03042c", "#302b63", "#24243e"]}
              start={{ x: 0, y: 0 }}
              end={{ x: 1, y: 1 }}
              style={styles.uploadButton}
            >
              <Text style={styles.uploadButtonText}>
                {id1 ? id1 : 'Upload ID #1'}
              </Text>
            </LinearGradient>
          </TouchableOpacity>
        </View>

        {/* Valid ID 2 */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Secondary ID</Text>
          <TouchableOpacity onPress={handleUploadID2}>
            <LinearGradient 
              colors={["#03042c", "#302b63", "#24243e"]}
              start={{ x: 0, y: 0 }}
              end={{ x: 1, y: 1 }}
              style={styles.uploadButton}
            >
              <Text style={styles.uploadButtonText}>
                {id2 ? id2 : 'Upload ID #2'}
              </Text>
            </LinearGradient>
          </TouchableOpacity>
        </View>

        {/* Agreement */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Agreement</Text>
          <TouchableOpacity onPress={handleUploadAgreement}>
            <LinearGradient 
               colors={["#03042c", "#302b63", "#24243e"]}
              start={{ x: 0, y: 0 }}
              end={{ x: 1, y: 1 }}
              style={styles.uploadButton}
            >
              <Text style={styles.uploadButtonText}>
                {agreementFile ? agreementFile : 'Upload Agreement'}
              </Text>
            </LinearGradient>
          </TouchableOpacity>
        </View>

        {/* Submit */}
        <TouchableOpacity onPress={handleSave}>
          <LinearGradient 
            colors={["#03042c", "#302b63", "#24243e"]}
            start={{ x: 0, y: 0 }}
            end={{ x: 1, y: 1 }}
            style={styles.saveButton}
          >
            <Text style={styles.saveButtonText}>Submit</Text>
          </LinearGradient>
        </TouchableOpacity>
      </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: { 
    flex: 1, 
    backgroundColor: 'black', 
    paddingHorizontal: 20 
  },
  headerTitle: { 
    fontSize: 24, 
    fontWeight: '700', 
    color: 'white', 
    marginTop: 40, 
    marginBottom: 20 
  },
  section: { 
    marginBottom: 30 
  },
  sectionTitle: { 
    fontSize: 18, 
    fontWeight: '600', 
    color: 'white', 
    marginBottom: 12 
  },
  uploadButton: { 
    paddingVertical: 14, 
    borderRadius: 12, 
    alignItems: 'center',
    shadowColor: '#FF6B6B',
    shadowOpacity: 0.3,
    shadowOffset: { width: 0, height: 3 },
    shadowRadius: 6,
  },
  uploadButtonText: { 
    color: 'white', 
    fontWeight: '600', 
    textAlign: 'center' 
  },
  saveButton: { 
    paddingVertical: 16, 
    borderRadius: 16, 
    alignItems: 'center', 
    marginTop: 20,
    shadowColor: '#FF6B6B',
    shadowOpacity: 0.4,
    shadowOffset: { width: 0, height: 4 },
    shadowRadius: 8,
  },
  saveButtonText: { 
    color: 'white', 
    fontWeight: '700', 
    fontSize: 16 
  },
});

export default UploadFiles;
