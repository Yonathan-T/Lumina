@props(['withBackground' => false])

<div id="pricing" class="relative w-full overflow-hidden">
    @if($withBackground)
    <!-- Decorative elements -->
    <div class="absolute inset-0 bg-gradient-to-br from-blue-900/30 via-transparent to-purple-900/30 -z-10"></div>
    <div class="absolute -top-20 -right-20 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-20 -left-20 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl"></div>
    @endif

    <div class="relative z-10 w-full max-w-7xl mx-auto px-6 py-8">
        <!-- Header Section -->
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="text-4xl md:text-5xl font-bold text-white mb-4">
                Pricing
            </h2>
            <p class="text-lg text-muted">
                Choose the perfect plan for your journaling journey. Upgrade or downgrade at any time.
            </p>

            <!-- Billing Toggle -->
            <div
                class="inline-flex items-center bg-white/5 backdrop-blur-sm rounded-full p-1 mt-8 border border-white/10">
                <span id="monthly-label"
                    class="px-4 py-2 text-sm font-medium rounded-full transition-all duration-200">Monthly</span>
                <div class="relative mx-2">
                    <input type="checkbox" id="billing-toggle" class="sr-only" onchange="toggleBilling()">
                    <label for="billing-toggle" class="flex items-center cursor-pointer">
                        <div class="relative">
                            <div class="block bg-white/10 w-14 h-8 rounded-full"></div>
                            <div
                                class="dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition-transform duration-300">
                            </div>
                        </div>
                    </label>
                </div>
                <span id="yearly-label" class="px-4 py-2 text-sm text-white/60">Yearly</span>
                <span id="save-badge"
                    class="absolute -top-2 right-0 bg-gradient-to-r from-blue-500 to-purple-600 text-white text-xs px-3 py-1 rounded-full font-medium transform translate-y-[-50%]">
                    Save 20%
                </span>
            </div>
        </div>

        <!-- Pricing Cards -->
        <div class="grid gap-8 md:grid-cols-3 max-w-6xl mx-auto">
            <!-- Free Plan -->
            <x-pricing-card name="Free" subtitle="For casual journaling" price="Free" yearly-price="Free"
                description="Perfect for getting started with journaling" :features="[
        'Up to 10 journal entries',
        'Basic entry organization',
        'Simple tag system',
        'Email support',
        'Basic insights',
        'Standard security'
    ]" button-text="Get Started" :popular="false" />

            <!-- Standard Plan -->
            <x-pricing-card name="Standard" subtitle="For regular journalers" price="$9.99" yearly-price="$7.99"
                description="Great for regular journaling with AI insights" :features="[
        'Unlimited journal entries',
        'Advanced organization',
        'AI mood tracking',
        '10 therapy chats/month',
        'Export options',
        'Priority support',
        'Advanced analytics',
        'Custom themes'
    ]" button-text="Start Free Trial" :popular="true" />

            <!-- Pro Plan -->
            <x-pricing-card name="Pro" subtitle="For power users" price="$19.99" yearly-price="$15.99"
                description="Complete journaling experience with premium AI" :features="[
        'Everything in Standard',
        'Unlimited AI therapy',
        'AI pattern recognition',
        'Voice-to-text',
        'Audio summaries',
        '24/7 priority support',
        'Advanced security',
        'API access',
        'Early access to features',
        'AI coach recommendations'
    ]"
                button-text="Start Free Trial" :popular="false" />
        </div>
    </div>
</div>

<script>
    function toggleBilling() {
        const toggle = document.getElementById('billing-toggle');
        const monthlyLabel = document.getElementById('monthly-label');
        const yearlyLabel = document.getElementById('yearly-label');
        const saveBadge = document.getElementById('save-badge');
        const dot = document.querySelector('.dot');
        const priceElements = document.querySelectorAll('[data-monthly-price]');

        if (toggle.checked) {
            // Yearly billing
            dot.style.transform = 'translateX(24px)';
            monthlyLabel.className = 'px-4 py-2 text-sm text-white/60';
            yearlyLabel.className = 'px-4 py-2 text-sm font-medium bg-white/10 rounded-full text-white';
            saveBadge.classList.remove('hidden');

            // Update prices
            priceElements.forEach(element => {
                const yearlyPrice = element.getAttribute('data-yearly-price');
                if (yearlyPrice !== 'Free') {
                    element.textContent = yearlyPrice;
                }
            });
        } else {
            // Monthly billing
            dot.style.transform = 'translateX(0)';
            monthlyLabel.className = 'px-4 py-2 text-sm font-medium bg-white/10 rounded-full text-white';
            yearlyLabel.className = 'px-4 py-2 text-sm text-white/60';
            saveBadge.classList.add('hidden');

            // Update prices
            priceElements.forEach(element => {
                const monthlyPrice = element.getAttribute('data-monthly-price');
                element.textContent = monthlyPrice;
            });
        }
    }
</script>