<x-layout :showNav="false" :showSidebar="true">
    <section class="p-6" id="mainContent">
        @livewire('dashboard.dashboard-stats')
    </section>

    <div class="welcome-modal-overlay" id="welcomeModal" style="display: none;">
        <div class="welcome-modal-content">
            <button class="welcome-close-btn" onclick="closeWelcomeModal()">&times;</button>
            <div class="welcome-heart">❤️</div>
            <h2 class="text-2xl font-bold mb-4">Welcome to {{ config('app.name') }}!</h2>
            <p class="mb-4">Thank you for joining us. We're excited to have you on board!</p>
            <p class="text-sm text-gray-400">Check your email to verify your account and set your password.</p>
        </div>
    </div>

    <script>
        function initWelcomeModal() {
            const modal = document.getElementById('welcomeModal');
            if (!modal) {
                return;
            }

            const welcomeShown = localStorage.getItem('welcomeShown');
            const showWelcome = {{ session('show_welcome_modal') ? 'true' : 'false' }};
            
            if (showWelcome && !welcomeShown) {
                modal.style.display = 'flex';
                localStorage.setItem('welcomeShown', 'true');
            }
        }

        function closeWelcomeModal() {
            const modal = document.getElementById('welcomeModal');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        document.addEventListener('click', function(e) {
            const modal = document.getElementById('welcomeModal');
            if (modal && e.target === modal) {
                closeWelcomeModal();
            }
        });

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initWelcomeModal, { once: true });
        } else {
            initWelcomeModal();
        }

        document.addEventListener('livewire:navigated', initWelcomeModal);
    </script>
</x-layout>
