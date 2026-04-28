<x-layouts.app :title="__('Privacy Policy - Editorial Standards Platform')">
    <x-slot:header>true</x-slot:header>

    <div class="bg-gray-50 py-16 dark:bg-gray-950">
        <div class="container mx-auto max-w-4xl px-4">
            {{-- Header --}}
            <div class="mb-12 text-center">
                <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white sm:text-5xl">{{ __('Privacy Policy') }}</h1>
                <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">{{ __('Last updated:') }} {{ date('d/m/Y') }}</p>
            </div>

            {{-- Content --}}
            <div class="prose prose-indigo max-w-none rounded-2xl bg-white p-8 shadow-sm dark:prose-invert dark:bg-gray-900 sm:p-12">
                <p class="lead">{{ __('At Editorial Standards Platform (ESP), we take the privacy of our users and the security of their data very seriously. This policy describes how we collect, use and protect your information.') }}</p>

                <h2 class="text-2xl font-bold mt-8 mb-4">1. {{ __('Information We Collect') }}</h2>
                <p>{{ __('We collect information necessary to provide our editorial evaluation and visibility services:') }}</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li><strong>{{ __('Registration Information:') }}</strong> {{ __('Name, email address, institutional affiliation and position.') }}</li>
                    <li><strong>{{ __('Publication Information:') }}</strong> {{ __('Journal and book metadata (titles, ISSN/ISBN, URLs, editorial policies).') }}</li>
                    <li><strong>{{ __('Browsing Data:') }}</strong> {{ __('IP address, browser type and platform interactions to improve user experience.') }}</li>
                </ul>

                <h2 class="text-2xl font-bold mt-8 mb-4">2. {{ __('Use of Information') }}</h2>
                <p>{{ __('We use the collected data for the following purposes:') }}</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li>{{ __('Manage your account and process evaluation requests.') }}</li>
                    <li>{{ __('Display public information from journals and books in our global directory.') }}</li>
                    <li>{{ __('Harvest metadata via OAI-PMH for technical bibliometric analysis.') }}</li>
                    <li>{{ __('Send important communications related to the status of your evaluation or service updates.') }}</li>
                    <li>{{ __('Improve the security and functionality of the Platform.') }}</li>
                </ul>

                <h2 class="text-2xl font-bold mt-8 mb-4">3. {{ __('Data Sharing') }}</h2>
                <p>{{ __('Editorial Standards Platform does not sell or rent your personal data to third parties. Your data may be shared only in the following cases:') }}</p>
                <ul class="list-disc pl-6 space-y-2">
                    <li><strong>{{ __('Public Information:') }}</strong> {{ __('Journal data and their technical ratings are public by the nature of the editorial transparency service.') }}</li>
                    <li><strong>{{ __('Service Providers:') }}</strong> {{ __('With third parties that help us operate the platform (e.g. payment processors, hosting services), under strict confidentiality agreements.') }}</li>
                    <li><strong>{{ __('Legal Requirements:') }}</strong> {{ __('If required by law or to protect the rights of the Platform.') }}</li>
                </ul>

                <h2 class="text-2xl font-bold mt-8 mb-4">4. {{ __('Data Security') }}</h2>
                <p>{{ __('We implement technical and organizational security measures to protect your information against loss, theft or unauthorized access. This includes the use of encryption protocols and state-of-the-art firewalls.') }}</p>

                <h2 class="text-2xl font-bold mt-8 mb-4">5. {{ __('Your Rights') }}</h2>
                <p>{{ __('You have the right to access, rectify or delete your personal data stored on our platform. You can manage most of this information directly from your user panel or by contacting us if you wish to permanently close your account.') }}</p>

                <h2 class="text-2xl font-bold mt-8 mb-4">6. {{ __('Cookies') }}</h2>
                <p>{{ __('We use cookies to keep your session active and analyze site traffic. You can configure your browser to reject cookies, although this may affect the functionality of some areas of the Platform.') }}</p>

                <h2 class="text-2xl font-bold mt-8 mb-4">7. {{ __('Changes to this Policy') }}</h2>
                <p>{{ __('We reserve the right to update this Privacy Policy to reflect changes in our practices or legal requirements. We will notify you of any significant changes through your registered email.') }}</p>

                <div class="mt-12 border-t border-gray-100 pt-8 dark:border-gray-800">
                    <p class="text-sm text-gray-500">{{ __('For any questions about the processing of your data, please contact us through our') }} <a href="/contact" class="text-indigo-600 hover:underline dark:text-indigo-400">{{ __('contact page') }}</a>.</p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
