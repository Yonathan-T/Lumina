<style>
    /* Styles for the modal overlay and content */
    :root {
        --dark-bg: #1A1A2E;
        --modal-bg: rgba(26, 26, 46, 0.9);
        --text-color: #E0E0E0;
        --accent-blue: #4E60F7;
        --accent-purple: #8A4EF7;
    }

    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .modal-content {
        background: var(--modal-bg);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        text-align: center;
        width: 90%;
        max-width: 400px;
        color: var(--text-color);
        position: relative;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .heart-icon {
        font-size: 3rem;
        animation: pulse 1.5s infinite ease-in-out;
    }

    @keyframes pulse {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.1);
        }
    }

    .modal-content h3 {
        font-size: 1.8rem;
        margin-top: 15px;
        margin-bottom: 10px;
        font-weight: 600;
    }

    .modal-content p {
        font-size: 1rem;
        line-height: 1.5;
        margin-bottom: 25px;
        color: #B0B0B0;
    }

    .dashboard-button {
        display: inline-block;
        padding: 12px 25px;
        border-radius: 8px;
        text-decoration: none;
        color: #fff;
        font-weight: bold;
        background: linear-gradient(135deg, var(--accent-blue) 0%, var(--accent-purple) 100%);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .dashboard-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
    }

    .support-link {
        color: var(--accent-blue);
        text-decoration: none;
        font-weight: bold;
        margin-top: 20px;
        display: block;
        transition: color 0.2s;
    }

    .support-link:hover {
        color: var(--accent-purple);
    }
</style>

<div class="modal-overlay" id="welcome-modal">
    <div class="modal-content">
        <div class="heart-icon">💖</div>
        <h3>Thank You!</h3>
        <p>You now have access to all Lumina's features!</p>
        <a href="{{ route('dashboard') }}" class="dashboard-button">
            Go to Dashboard
        </a>
        <a href="#" class="support-link">
            Need help? Visit our Support Center.
        </a>
    </div>
</div>

<script>
    // Optional: JavaScript to show/hide the modal
    // You can control its visibility with a Blade conditional if you prefer.
    const modal = document.getElementById('welcome-modal');

    // Example of a simple way to show the modal
    // modal.style.display = 'flex';

    // Example of a way to hide the modal on click outside
    modal.addEventListener('click', (e) => {
        if (e.target.id === 'welcome-modal') {
            modal.style.display = 'none';
        }
    });
</script>