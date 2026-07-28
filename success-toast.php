<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$toast_message = $_SESSION['success_msg'] ?? '';
if ($toast_message === '') {
    return;
}

unset($_SESSION['success_msg']);
$toast_message = htmlspecialchars($toast_message);
?>
<style>
    .success-toast {
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%) translateY(-120px);
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
        padding: 15px 25px;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        z-index: 10000;
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 500;
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        transition: transform 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        max-width: 90%;
        text-align: center;
    }
    .success-toast.show {
        transform: translateX(-50%) translateY(0);
    }
    .success-toast i {
        color: #27ae60;
        font-size: 20px;
    }
</style>

<div id="success-toast" class="success-toast">
    <i class="fa-solid fa-circle-check"></i>
    <span><?php echo $toast_message; ?></span>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toast = document.getElementById('success-toast');
        if (!toast) {
            return;
        }

        setTimeout(function () {
            toast.classList.add('show');
            setTimeout(function () {
                toast.classList.remove('show');
            }, 4000);
        }, 300);
    });
</script>
