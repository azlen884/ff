<?php
// Admin Panel Footer - Distinct Admin Layout
$settings = getSiteSettings();
?>
    </div><!-- End Admin Body Container -->

    <!-- Admin Dedicated Footer (Distinct from Landing & User) -->
    <footer class="mt-auto border-t border-slate-800/80 bg-[#0B0F19] text-slate-500 py-4 px-4 lg:px-8 text-xs">
        <div class="w-full flex flex-col sm:flex-row items-center justify-between gap-3">
            <div>
                <span><?= htmlspecialchars($settings['site_name'] ?? 'FF Panel Store') ?> Admin Console • V1 Basic Manual Workflow Engine</span>
            </div>
            <div class="flex items-center gap-4 text-slate-400">
                <span>Server Time: <?= date('Y-m-d H:i:s') ?> UTC</span>
                <span>•</span>
                <span>Active Admin Session</span>
            </div>
        </div>
    </footer>

    <script src="/js/app.js"></script>
</body>
</html>
