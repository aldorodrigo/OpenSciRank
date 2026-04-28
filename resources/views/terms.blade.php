<x-layouts.app :title="__('Terms of Use - Editorial Standards Platform')">
    <x-slot:header>true</x-slot:header>

    <div class="bg-gray-50 py-16 dark:bg-gray-950">
        <div class="container mx-auto max-w-4xl px-4">
            {{-- Header --}}
            <div class="mb-12 text-center">
                <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white sm:text-5xl">{{ __('Terms of Use') }}</h1>
                <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">{{ __('Last updated:') }} {{ date('d/m/Y') }}</p>
            </div>

            {{-- Content --}}
            <div class="prose prose-indigo max-w-none rounded-2xl bg-white p-8 shadow-sm dark:prose-invert dark:bg-gray-900 sm:p-12">
                <p class="lead">{{ __('Welcome to Editorial Standards Platform (ESP). By using our website and services, you agree to comply with the following terms and conditions.') }}</p>

                <h2 class="text-2xl font-bold mt-8 mb-4">1. {{ __('Acceptance of Terms') }}</h2>
                <p>{{ __('By accessing or using the Editorial Standards Platform (hereinafter, "the Platform"), you agree to be legally bound by these Terms of Use and our Privacy Policy. If you do not agree with any part of these terms, you may not access the Platform or use our services.') }}</p>

                <h2 class="text-2xl font-bold mt-8 mb-4">2. {{ __('Description of Service') }}</h2>
                <p>{{ __('ESP is a global platform designed for editorial evaluation and visibility of scientific journals and academic books. Services include, among others, indexing in our directory, independent technical evaluation based on Open Science criteria and issuance of editorial quality seals.') }}</p>

                <h2 class="text-2xl font-bold mt-8 mb-4">3. {{ __('User Registration and Responsibilities') }}</h2>
                <ul class="list-disc pl-6 space-y-2">
                    <li>{{ __('To access certain functionalities, such as requesting an evaluation, registration as a user is required.') }}</li>
                    <li>{{ __('You are responsible for maintaining the confidentiality of your account and password.') }}</li>
                    <li>{{ __('You guarantee that all information provided during registration and evaluation request is truthful, accurate and up to date.') }}</li>
                    <li>{{ __('The use of false identities or impersonation of editors or institutions is prohibited.') }}</li>
                </ul>

                <h2 class="text-2xl font-bold mt-8 mb-4">4. {{ __('Intellectual Property') }}</h2>
                <p>{{ __('All content on the Platform, including texts, logos, graphics, icons, images and software, is the property of Editorial Standards Platform or its licensors and is protected by international intellectual property laws.') }}</p>
                <p>{{ __('Editors retain rights over their journal information and metadata, but grant ESP a license to display and process such information for evaluation and directory display purposes.') }}</p>

                <h2 class="text-2xl font-bold mt-8 mb-4">5. {{ __('Evaluation Process and Seals') }}</h2>
                <p>{{ __('Obtaining an editorial quality seal from ESP is subject to compliance with specific technical criteria evaluated by our independent team. ESP reserves the right to withdraw or revoke any seal if unethical editorial practices are detected or if the publication ceases to meet the required standards.') }}</p>

                <h2 class="text-2xl font-bold mt-8 mb-4">6. {{ __('Limitation of Liability') }}</h2>
                <p>{{ __('ESP provides its services "as is" and does not guarantee that the platform is free of errors or interruptions. In no event shall ESP be liable for indirect, incidental or consequential damages arising from the use or inability to use the platform.') }}</p>
                <p>{{ __('ESP is not responsible for decisions made by third parties (authors, accreditation agencies, institutions) based on the ratings or seals granted by the Platform.') }}</p>

                <h2 class="text-2xl font-bold mt-8 mb-4">7. {{ __('Modifications') }}</h2>
                <p>{{ __('We reserve the right to modify these Terms of Use at any time. Changes will take effect immediately after publication on the website. Continued use of the Platform after such modifications constitutes your acceptance of the new terms.') }}</p>

                <h2 class="text-2xl font-bold mt-8 mb-4">8. {{ __('Applicable Law and Jurisdiction') }}</h2>
                <p>{{ __('These terms are governed by international laws of electronic commerce and intellectual property. Any dispute related to these terms will be subject to the exclusive jurisdiction of the competent courts defined by the Platform administration.') }}</p>

                <div class="mt-12 border-t border-gray-100 pt-8 dark:border-gray-800">
                    <p class="text-sm text-gray-500">{{ __('If you have any questions about these Terms of Use, please contact us through our') }} <a href="/contact" class="text-indigo-600 hover:underline dark:text-indigo-400">{{ __('contact page') }}</a>.</p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
