<?php
require_once 'includes/init.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    
    $otp = $_POST['otp'] ?? '';
    
    // Validate OTP (Using the requested 1235 or a secure default)
    if ($otp === '1235') {
        // Authenticate as the pre-created admin user
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = 'admin@luminary.com' AND role = 'admin'");
        $stmt->execute();
        $admin = $stmt->fetch();
        
        if ($admin) {
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['role'] = 'admin';
            setFlash("Administrative Clearance Granted. Welcome back.");
            redirect('admin/index.php');
        } else {
            $error = "Admin account not found. Please run setup_admin.php";
        }
    } else {
        $error = "Invalid Security Clearance Code.";
    }
}

$pageTitle = "Admin Security Gate";
require_once 'includes/header.php';
?>

<style>
    .gate-container {
        min-height: calc(100vh - 150px);
        display: flex;
        align-items: center;
        justify-content: center;
        background: radial-gradient(circle at center, #1a1a1a 0%, #050505 100%);
        padding: 2rem;
    }
    .gate-card {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        padding: 4rem;
        border-radius: 24px;
        width: 100%;
        max-width: 450px;
        text-align: center;
        box-shadow: 0 25px 50px rgba(0,0,0,0.5);
    }
    .gate-icon {
        width: 80px;
        height: 80px;
        background: rgba(212, 175, 55, 0.1);
        color: var(--gold);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 2rem;
        font-size: 2rem;
        border: 1px solid rgba(212, 175, 55, 0.2);
    }
    .otp-input-group {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin: 2.5rem 0;
    }
    .otp-field {
        width: 60px;
        height: 70px;
        background: rgba(0,0,0,0.3);
        border: 1px solid var(--border);
        border-radius: 12px;
        color: var(--gold);
        font-size: 2rem;
        font-weight: 800;
        text-align: center;
        transition: all 0.3s;
    }
    .otp-field:focus {
        border-color: var(--gold);
        background: rgba(212, 175, 55, 0.05);
        outline: none;
        box-shadow: 0 0 20px rgba(212, 175, 55, 0.2);
    }
    .gate-btn {
        width: 100%;
        padding: 1.25rem;
        background: var(--gold);
        color: #000;
        border: none;
        border-radius: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        cursor: pointer;
        transition: all 0.3s;
    }
    .gate-btn:hover {
        background: var(--gold-light);
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(212, 175, 55, 0.3);
    }
</style>

<div class="gate-container">
    <div class="gate-card">
        <div class="gate-icon">🛡️</div>
        <h2 style="font-size: 1.75rem; margin-bottom: 0.5rem;">Security Clearance</h2>
        <p style="color: var(--text-muted); font-size: 0.9rem;">Please enter your 4-digit administrative OTP to proceed to the command center.</p>
        
        <?php if ($error): ?>
            <div style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 1rem; border-radius: 8px; margin-top: 1.5rem; font-size: 0.85rem; border: 1px solid rgba(239, 68, 68, 0.2);">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="gateForm">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <input type="hidden" name="otp" id="finalOtp">
            <div class="otp-input-group">
                <input type="text" class="otp-field" maxlength="1" pattern="\d*" inputmode="numeric" autocomplete="one-time-code">
                <input type="text" class="otp-field" maxlength="1" pattern="\d*" inputmode="numeric">
                <input type="text" class="otp-field" maxlength="1" pattern="\d*" inputmode="numeric">
                <input type="text" class="otp-field" maxlength="1" pattern="\d*" inputmode="numeric">
            </div>
            
            <button type="submit" class="gate-btn">Verify & Proceed</button>
        </form>

        <p style="margin-top: 2rem; font-size: 0.75rem; color: var(--text-muted);">
            Unauthorized access attempts are logged and reported.
        </p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const fields = document.querySelectorAll('.otp-field');
    const finalInput = document.getElementById('finalOtp');
    const form = document.getElementById('gateForm');

    fields.forEach((field, index) => {
        field.addEventListener('input', (e) => {
            if (e.target.value.length > 0 && index < fields.length - 1) {
                fields[index + 1].focus();
            }
            updateFinal();
        });

        field.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && e.target.value.length === 0 && index > 0) {
                fields[index - 1].focus();
            }
        });
    });

    function updateFinal() {
        let val = "";
        fields.forEach(f => val += f.value);
        finalInput.value = val;
    }

    form.addEventListener('submit', (e) => {
        updateFinal();
        if (finalInput.value.length < 4) {
            e.preventDefault();
            alert("Please enter the full 4-digit code.");
        }
    });

    // Auto-focus first field
    fields[0].focus();
});
</script>

<?php require_once 'includes/footer.php'; ?>
