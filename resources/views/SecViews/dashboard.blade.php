@push('styles')
<style>
    /* Styles for the modal overlay and content */
    .welcome-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        backdrop-filter: blur(5px);
    }

    .welcome-modal-content {
        background: #1a1a2e;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        text-align: center;
        width: 90%;
        max-width: 400px;
        color: #e0e0e0;
        position: relative;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .welcome-heart {
        font-size: 3rem;
        animation: pulse 1.5s infinite ease-in-out;
        color: #ff6b6b;
        margin-bottom: 1rem;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }

    .welcome-close-btn {
        position: absolute;
        top: 15px;
        right: 15px;
        background: none;
        border: none;
        color: #e0e0e0;
        font-size: 1.5rem;
        cursor: pointer;
        opacity: 0.7;
        transition: opacity 0.2s;
        line-height: 1;
        padding: 0.5rem;
    }

    .welcome-close-btn:hover {
        opacity: 1;
    }
</style>
@endpush

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

    @push('scripts')
    <script>
        // Show welcome modal if it's the first visit
        document.addEventListener('DOMContentLoaded', function() {
            // Check if we should show the welcome modal
            const welcomeShown = localStorage.getItem('welcomeShown');
            const showWelcome = {{ session('show_welcome_modal') ? 'true' : 'false' }};
            
            if (showWelcome && !welcomeShown) {
                document.getElementById('welcomeModal').style.display = 'flex';
                localStorage.setItem('welcomeShown', 'true');
            }
        });

        function closeWelcomeModal() {
            document.getElementById('welcomeModal').style.display = 'none';
        }

        // Close when clicking outside the modal content
        document.getElementById('welcomeModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeWelcomeModal();
            }
        });
    </script>
    @endpush
</x-layout>