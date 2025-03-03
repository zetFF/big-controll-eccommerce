@extends('layouts.app')

@section('title', 'Privacy Policy')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">Privacy Policy</h1>
            
            <div class="prose prose-blue max-w-none">
                <p class="text-gray-500">Last updated: {{ date('F d, Y') }}</p>

                <h2>1. Information We Collect</h2>
                <p>
                    We collect information that you provide directly to us, including:
                </p>
                <ul>
                    <li>Name and contact information</li>
                    <li>Account credentials</li>
                    <li>Payment information</li>
                    <li>Communications with us</li>
                </ul>

                <h2>2. How We Use Your Information</h2>
                <p>
                    We use the information we collect to:
                </p>
                <ul>
                    <li>Provide and maintain our services</li>
                    <li>Process your transactions</li>
                    <li>Send you technical notices and support messages</li>
                    <li>Communicate with you about products, services, and events</li>
                </ul>

                <h2>3. Information Sharing</h2>
                <p>
                    We do not sell or rent your personal information to third parties. We may share your information with:
                </p>
                <ul>
                    <li>Service providers who assist in our operations</li>
                    <li>Professional advisers</li>
                    <li>Law enforcement when required by law</li>
                </ul>

                <h2>4. Security</h2>
                <p>
                    We implement appropriate technical and organizational measures to protect your personal information.
                </p>

                <h2>5. Your Rights</h2>
                <p>
                    You have the right to:
                </p>
                <ul>
                    <li>Access your personal information</li>
                    <li>Correct inaccurate information</li>
                    <li>Request deletion of your information</li>
                    <li>Object to processing of your information</li>
                </ul>

                <h2>6. Contact Us</h2>
                <p>
                    If you have questions about this Privacy Policy, please contact us at:
                    <br>
                    Email: privacy@example.com
                    <br>
                    Address: Your Company Address
                </p>
            </div>
        </div>
    </div>
</div>
@endsection 