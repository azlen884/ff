<?php
// User Panel Footer - Distinct Layout
$settings = getSiteSettings();
?>
    </div><!-- End App Body Container -->

    <!-- User Panel Dedicated Footer (Distinct from Landing & Admin) -->
    <footer class="mt-auto border-t border-slate-800/80 bg-[#0A0E18] text-slate-400 py-6 px-4 lg:px-8">
        <div class="max-w-7xl mx-auto w-full flex flex-col sm:flex-row items-center justify-between text-xs gap-4">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>FF Service Node: <strong>Direct UID Server Online</strong></span>
                <span class="text-slate-600">|</span>
                <span>Delivery SLA: <strong>&lt; 2 Minutes</strong></span>
            </div>
            <div class="flex items-center gap-6 text-slate-400">
                <a href="/uids" class="hover:text-rose-400 transition-colors">Saved UIDs</a>
                <a href="/orders" class="hover:text-rose-400 transition-colors">Order Logs</a>
                <a href="/profile" class="hover:text-rose-400 transition-colors">Security Settings</a>
                <span class="text-slate-500">UID Top-Up Verified</span>
            </div>
        </div>
    </footer>

    <!-- Plain JavaScript Interactive Logic -->
    <script src="/js/app.js"></script>
</body>
</html>
