@extends('layouts.app')

@php
  $pageTitle = $type === 'menu' ? 'Legal Information' : ($type === 'terms' ? 'Terms & Conditions' : ($type === 'privacy' ? 'Privacy Policy' : 'Loan Agreement'));
@endphp

@section('content')

{{-- HEADER --}}
<div class="mb-6 rounded-2xl bg-gradient-to-r from-slate-900 via-slate-900 to-slate-800 text-white shadow-xl">
  <div class="p-6 flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold mb-2">{{ $pageTitle }}</h1>
      <p class="text-slate-300 text-sm">Legal documents and policies</p>
    </div>
    @if($type !== 'menu')
      <a href="{{ route('legal.index') }}" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 rounded-lg text-sm font-semibold">
        ← Back to Legal
      </a>
    @endif
  </div>
</div>

@if($type === 'menu')
  {{-- Legal Menu --}}
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <a href="{{ route('legal.index', ['type' => 'terms']) }}" class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 hover:shadow-md transition-shadow">
      <div class="flex items-center gap-4 mb-4">
        <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
          <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
        </div>
        <h3 class="text-lg font-semibold text-slate-900">Terms & Conditions</h3>
      </div>
      <p class="text-sm text-slate-600">Read our terms of service and conditions for using MasterFunds.</p>
    </a>

    <a href="{{ route('legal.index', ['type' => 'privacy']) }}" class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 hover:shadow-md transition-shadow">
      <div class="flex items-center gap-4 mb-4">
        <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center">
          <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
          </svg>
        </div>
        <h3 class="text-lg font-semibold text-slate-900">Privacy Policy</h3>
      </div>
      <p class="text-sm text-slate-600">Learn how we collect, use, and protect your data.</p>
    </a>

    <a href="{{ route('legal.index', ['type' => 'agreement']) }}" class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 hover:shadow-md transition-shadow">
      <div class="flex items-center gap-4 mb-4">
        <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center">
          <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
        </div>
        <h3 class="text-lg font-semibold text-slate-900">Loan Agreement</h3>
      </div>
      <p class="text-sm text-slate-600">Standard loan agreement terms and borrower consent.</p>
    </a>
  </div>
@else
  {{-- Legal Content --}}
  <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8">
    @if($type === 'terms')
      <div class="prose max-w-none">
        <h2 class="text-2xl font-bold text-slate-900 mb-2">Terms & Conditions</h2>
        <p class="text-sm text-slate-500 mb-6">Last Updated: January 2024</p>

        <h3 class="text-xl font-semibold text-slate-900 mt-6 mb-3">1. Acceptance of Terms</h3>
        <p class="text-slate-700 leading-relaxed mb-4">
          By accessing and using the MasterFunds Admin Panel, you accept and agree to be bound by the terms and provision of this agreement. If you do not agree to these terms, you should not use this system.
        </p>

        <h3 class="text-xl font-semibold text-slate-900 mt-6 mb-3">2. System Access</h3>
        <p class="text-slate-700 leading-relaxed mb-4">
          Access to the MasterFunds Admin Panel is restricted to authorized personnel only. You are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account.
        </p>

        <h3 class="text-xl font-semibold text-slate-900 mt-6 mb-3">3. Loan Management</h3>
        <p class="text-slate-700 leading-relaxed mb-4">
          When managing loans through MasterFunds, you agree to process loans in accordance with applicable laws and regulations. All loan decisions and approvals are subject to your organization's policies and procedures.
        </p>

        <h3 class="text-xl font-semibold text-slate-900 mt-6 mb-3">4. Payment Processing</h3>
        <p class="text-slate-700 leading-relaxed mb-4">
          Payment approvals and rejections must be made in good faith and in accordance with the loan agreement terms. You are responsible for verifying payment authenticity before approval.
        </p>

        <h3 class="text-xl font-semibold text-slate-900 mt-6 mb-3">5. Data Accuracy</h3>
        <p class="text-slate-700 leading-relaxed mb-4">
          You are responsible for ensuring the accuracy of all data entered into the system. MasterFunds is not liable for errors resulting from incorrect data entry.
        </p>

        <h3 class="text-xl font-semibold text-slate-900 mt-6 mb-3">6. System Availability</h3>
        <p class="text-slate-700 leading-relaxed mb-4">
          While we strive to maintain system availability, MasterFunds does not guarantee uninterrupted access. We reserve the right to perform maintenance and updates that may temporarily affect system availability.
        </p>

        <h3 class="text-xl font-semibold text-slate-900 mt-6 mb-3">7. Limitation of Liability</h3>
        <p class="text-slate-700 leading-relaxed mb-4">
          MasterFunds shall not be liable for any indirect, incidental, special, or consequential damages arising from the use of this system. Our liability is limited to the maximum extent permitted by law.
        </p>

        <h3 class="text-xl font-semibold text-slate-900 mt-6 mb-3">8. Changes to Terms</h3>
        <p class="text-slate-700 leading-relaxed mb-4">
          We reserve the right to modify these terms at any time. Continued use of the system after changes constitutes acceptance of the new terms. You will be notified of significant changes.
        </p>
      </div>

    @elseif($type === 'privacy')
      <div class="prose max-w-none">
        <h2 class="text-2xl font-bold text-slate-900 mb-2">Privacy Policy</h2>
        <p class="text-sm text-slate-500 mb-6">Last Updated: January 2024</p>

        <h3 class="text-xl font-semibold text-slate-900 mt-6 mb-3">1. Information We Collect</h3>
        <p class="text-slate-700 leading-relaxed mb-4">
          MasterFunds collects information necessary for loan management operations, including borrower personal information, financial data, loan details, payment records, and system usage logs.
        </p>

        <h3 class="text-xl font-semibold text-slate-900 mt-6 mb-3">2. How We Use Your Information</h3>
        <p class="text-slate-700 leading-relaxed mb-4">
          We use collected information to process loans, manage payments, generate reports, comply with legal obligations, and improve system functionality. Data is used solely for legitimate business purposes.
        </p>

        <h3 class="text-xl font-semibold text-slate-900 mt-6 mb-3">3. Data Security</h3>
        <p class="text-slate-700 leading-relaxed mb-4">
          We implement appropriate technical and organizational security measures to protect personal information. This includes encryption, access controls, regular security audits, and staff training.
        </p>

        <h3 class="text-xl font-semibold text-slate-900 mt-6 mb-3">4. Information Sharing</h3>
        <p class="text-slate-700 leading-relaxed mb-4">
          We do not sell personal information. Data may be shared with service providers, legal authorities when required by law, and with your explicit consent. All sharing is done in compliance with applicable data protection laws.
        </p>

        <h3 class="text-xl font-semibold text-slate-900 mt-6 mb-3">5. Data Retention</h3>
        <p class="text-slate-700 leading-relaxed mb-4">
          We retain personal information for as long as necessary to fulfill the purposes outlined in this policy, comply with legal obligations, resolve disputes, and enforce agreements.
        </p>

        <h3 class="text-xl font-semibold text-slate-900 mt-6 mb-3">6. Your Rights</h3>
        <p class="text-slate-700 leading-relaxed mb-4">
          You have the right to access, update, correct, or delete personal information. You may also request data portability or object to certain processing activities. Contact us to exercise these rights.
        </p>

        <h3 class="text-xl font-semibold text-slate-900 mt-6 mb-3">7. Cookies and Tracking</h3>
        <p class="text-slate-700 leading-relaxed mb-4">
          Our system may use cookies and similar technologies to enhance functionality, analyze usage patterns, and maintain session state. You can control cookie preferences through your browser settings.
        </p>

        <h3 class="text-xl font-semibold text-slate-900 mt-6 mb-3">8. Contact Us</h3>
        <p class="text-slate-700 leading-relaxed mb-4">
          For questions about this Privacy Policy or to exercise your rights, please contact us at support@masterfunds.com or through the Help & Support page.
        </p>
      </div>

    @elseif($type === 'agreement')
      <div class="prose max-w-none">
        <h2 class="text-2xl font-bold text-slate-900 mb-2">Loan Agreement & Consent</h2>
        <p class="text-sm text-slate-500 mb-6">Last Updated: January 2024</p>

        <h3 class="text-xl font-semibold text-slate-900 mt-6 mb-3">Loan Agreement Terms</h3>
        <p class="text-slate-700 leading-relaxed mb-4">
          By processing loans through MasterFunds, you acknowledge and agree to the following standard loan agreement terms:
        </p>

        <h4 class="text-lg font-semibold text-slate-900 mt-4 mb-2">1. Loan Terms</h4>
        <ul class="list-disc list-inside text-slate-700 space-y-2 mb-4">
          <li>Loan amount, interest rate, and repayment schedule are specified in the loan approval</li>
          <li>Borrowers agree to repay the loan according to the agreed schedule</li>
          <li>Late payments may incur penalties as specified in the loan agreement</li>
          <li>Loan terms are binding once the loan is disbursed</li>
        </ul>

        <h4 class="text-lg font-semibold text-slate-900 mt-4 mb-2">2. Repayment Obligations</h4>
        <ul class="list-disc list-inside text-slate-700 space-y-2 mb-4">
          <li>Payments must be made on or before the due date</li>
          <li>Borrowers may make payments through the MasterFunds mobile application</li>
          <li>All payments are subject to admin approval</li>
          <li>Partial payments are accepted and applied to the oldest unpaid repayment</li>
        </ul>

        <h4 class="text-lg font-semibold text-slate-900 mt-4 mb-2">3. Default and Consequences</h4>
        <ul class="list-disc list-inside text-slate-700 space-y-2 mb-4">
          <li>Failure to make payments may result in loan default</li>
          <li>Default may affect borrower credit score and future loan eligibility</li>
          <li>We reserve the right to take legal action to recover outstanding amounts</li>
          <li>Collection activities will be conducted in accordance with applicable laws</li>
        </ul>

        <h3 class="text-xl font-semibold text-slate-900 mt-6 mb-3">Consent to Data Processing</h3>
        <p class="text-slate-700 leading-relaxed mb-4">
          By using MasterFunds services, borrowers consent to:
        </p>
        <ul class="list-disc list-inside text-slate-700 space-y-2 mb-4">
          <li>Collection and processing of personal and financial information</li>
          <li>Credit checks and background verification</li>
          <li>Communication via email, SMS, and in-app notifications</li>
          <li>Sharing information with credit bureaus and regulatory authorities as required by law</li>
          <li>Data retention for the duration of the loan and as required by law</li>
        </ul>

        <h3 class="text-xl font-semibold text-slate-900 mt-6 mb-3">Acknowledgement</h3>
        <p class="text-slate-700 leading-relaxed mb-4">
          I acknowledge that I have read, understood, and agree to be bound by the terms of this Loan Agreement and Consent. I understand that this is a legally binding agreement and that all loan terms will be clearly communicated to borrowers before loan disbursement.
        </p>
      </div>
    @endif
  </div>
@endif

@endsection

