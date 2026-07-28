    <!-- ================= ADMIN FOOTER ================= -->
    <footer style="text-align: center; padding: 30px; font-size: 13px; color: #888; background: #fff; border-top: 1px solid #eee; margin-top: 50px;">
        © 2026 Delight Dinning Console. All Rights Reserved.
    </footer>

    <div id="admin-new-order-toast" style="position: fixed; bottom: 24px; right: 24px; max-width: 360px; background: #fff; border: 1px solid #ffe08a; border-left: 4px solid #25d366; border-radius: 12px; box-shadow: 0 12px 30px rgba(0,0,0,0.12); padding: 16px 18px; z-index: 9999; display: none;">
        <div style="display: flex; align-items: flex-start; gap: 12px;">
            <i class="fa-brands fa-whatsapp" style="color: #25d366; font-size: 22px; margin-top: 2px;"></i>
            <div style="flex: 1;">
                <strong id="admin-new-order-title" style="display: block; color: #1F2937; margin-bottom: 6px;">New order received</strong>
                <p id="admin-new-order-text" style="margin: 0 0 12px; font-size: 13px; color: #6b7280; line-height: 1.5;"></p>
                <a id="admin-new-order-send" href="#" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; background: #25d366; color: #fff; text-decoration: none; padding: 8px 14px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                    <i class="fa-brands fa-whatsapp"></i> Send Bill via WhatsApp
                </a>
            </div>
            <button type="button" id="admin-new-order-close" style="border: none; background: transparent; color: #9ca3af; cursor: pointer; font-size: 18px; line-height: 1;">&times;</button>
        </div>
    </div>

    <script>
        (function () {
            const toast = document.getElementById('admin-new-order-toast');
            const title = document.getElementById('admin-new-order-title');
            const text = document.getElementById('admin-new-order-text');
            const sendLink = document.getElementById('admin-new-order-send');
            const closeBtn = document.getElementById('admin-new-order-close');
            let lastSeenOrderId = <?php echo (int) ($latest_order_id ?? 0); ?>;

            function hideToast() {
                if (toast) {
                    toast.style.display = 'none';
                }
            }

            if (closeBtn) {
                closeBtn.addEventListener('click', hideToast);
            }

            function checkNewOrders() {
                fetch('check-new-orders.php?last_seen_id=' + encodeURIComponent(lastSeenOrderId), {
                    credentials: 'same-origin'
                })
                    .then(function (response) { return response.json(); })
                    .then(function (data) {
                        if (!data.orders || !data.orders.length) {
                            return;
                        }

                        const latest = data.orders[data.orders.length - 1];
                        lastSeenOrderId = latest.id;

                        if (title) {
                            title.textContent = 'New order ' + latest.order_number;
                        }
                        if (text) {
                            text.textContent = latest.customer_name + ' placed an order for Rs. ' + latest.total_price + '. Send the order confirmation & bill to their WhatsApp.';
                        }
                        if (sendLink) {
                            sendLink.href = latest.send_url;
                        }
                        if (toast) {
                            toast.style.display = 'block';
                        }
                    })
                    .catch(function () {
                        // Ignore polling errors silently.
                    });
            }

            setInterval(checkNewOrders, 15000);

            <?php if (!empty($auto_open_wa)): ?>
            window.open(<?php echo json_encode($auto_open_wa); ?>, '_blank');
            <?php endif; ?>
        })();
    </script>

</body>
</html>
