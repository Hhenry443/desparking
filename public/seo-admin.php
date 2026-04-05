<?php
session_start();
$title   = "SEO Manager";
$noIndex = true;

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] !== true) {
    header("Location: /");
    exit;
}

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/models/Seo.php';
$seo = new Seo();

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    $slug        = trim($_POST['page_slug'] ?? '');
    $seoTitle    = trim($_POST['seo_title'] ?? '');
    $description = trim($_POST['seo_description'] ?? '');
    $ogImage     = trim($_POST['og_image'] ?? '');

    if ($slug && $seoTitle && $description) {
        $seo->upsertPage($slug, $seoTitle, $description, $ogImage);
        header("Location: /seo-admin.php?saved=1");
        exit;
    }
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $seo->deletePage((int)$_POST['delete_id']);
    header("Location: /seo-admin.php?deleted=1");
    exit;
}

$pages = $seo->getAllPages();

// Known pages for the quick-add dropdown
$knownPages = [
    '/'                 => 'Home',
    '/map.php'          => 'Find Parking (Map)',
    '/monthly.php'      => 'Long-Term Parking',
    '/how-we-work.php'  => 'How We Work',
    '/why-rent.php'     => 'Why Rent Your Space',
    '/business.php'     => 'Business Solutions',
    '/hospitality.php'  => 'Hospitality Solutions',
    '/event.php'        => 'Event Solutions',
    '/news.php'         => 'News',
    '/about.php'        => 'About Us',
    '/faq.php'          => 'FAQ',
    '/partners.php'     => 'Partners',
];
?>
<!doctype html>
<html lang="en">
<?php include_once __DIR__ . '/partials/header.php'; ?>
<body class="min-h-screen bg-[#ebebeb] pt-24">
    <?php include_once __DIR__ . '/partials/navbar.php'; ?>

    <div class="max-w-5xl mx-auto px-6 py-10">

        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">SEO Manager</h1>
                <p class="text-sm text-gray-500 mt-1">Override title, description, and OG image per page. Overrides take priority over page defaults.</p>
            </div>
            <a href="/admin.php" class="text-sm text-gray-500 hover:text-gray-800 transition">
                <i class="fa-solid fa-chevron-left text-xs"></i> Admin
            </a>
        </div>

        <?php if (isset($_GET['saved'])): ?>
            <div class="mb-6 p-4 bg-emerald-50 text-emerald-700 rounded-xl text-sm">SEO settings saved.</div>
        <?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?>
            <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-xl text-sm">Override removed.</div>
        <?php endif; ?>

        <!-- Add / Edit form -->
        <div class="bg-white rounded-2xl shadow-[0_0_16px_rgba(0,0,0,0.08)] p-6 mb-8">
            <h2 class="text-base font-bold text-gray-800 mb-5">Add or Update a Page Override</h2>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="save">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Page slug -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Page Slug *</label>
                        <input list="known-pages" type="text" name="page_slug" required
                               placeholder="/map.php or /news-story.php?slug=my-article"
                               class="w-full py-3 px-4 rounded-lg bg-gray-100 text-gray-800 text-sm border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#6ae6fc]">
                        <datalist id="known-pages">
                            <?php foreach ($knownPages as $slug => $label): ?>
                                <option value="<?= htmlspecialchars($slug) ?>"><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </datalist>
                        <p class="text-xs text-gray-400 mt-1">Use the exact URL path, e.g. <code>/about.php</code></p>
                    </div>

                    <!-- SEO title -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">SEO Title * <span class="font-normal text-gray-400">(50–60 chars ideal)</span></label>
                        <input type="text" name="seo_title" maxlength="120" required
                               placeholder="EveryonesParking - Find Cheap Parking Near You"
                               class="w-full py-3 px-4 rounded-lg bg-gray-100 text-gray-800 text-sm border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#6ae6fc]">
                    </div>
                </div>

                <!-- Meta description -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Meta Description * <span class="font-normal text-gray-400">(150–160 chars ideal)</span></label>
                    <textarea name="seo_description" rows="3" maxlength="320" required
                              placeholder="A concise description of this page shown in Google search results."
                              class="w-full py-3 px-4 rounded-lg bg-gray-100 text-gray-800 text-sm border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#6ae6fc]"></textarea>
                    <p class="text-xs text-gray-400 mt-1"><span id="desc-count">0</span> / 320 characters</p>
                </div>

                <!-- OG Image -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">OG Image URL <span class="font-normal text-gray-400">(optional — used for social sharing previews)</span></label>
                    <input type="url" name="og_image"
                           placeholder="https://everyonesparking.com.au/images/og-home.png"
                           class="w-full py-3 px-4 rounded-lg bg-gray-100 text-gray-800 text-sm border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#6ae6fc]">
                </div>

                <div>
                    <button type="submit"
                            class="px-6 py-2.5 bg-[#6ae6fc] text-gray-900 text-sm font-bold rounded-xl hover:bg-cyan-400 transition shadow-sm">
                        Save Override
                    </button>
                </div>
            </form>
        </div>

        <!-- Existing overrides table -->
        <?php if (empty($pages)): ?>
            <div class="bg-white rounded-2xl p-10 text-center text-gray-400 shadow-sm">
                No overrides yet. Use the form above to add one.
            </div>
        <?php else: ?>
            <div class="bg-white rounded-2xl shadow-[0_0_16px_rgba(0,0,0,0.08)] overflow-hidden">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="text-xs text-gray-500 uppercase tracking-wide bg-gray-50 border-b border-gray-100">
                            <th class="p-4 text-left">Page</th>
                            <th class="p-4 text-left">SEO Title</th>
                            <th class="p-4 text-left">Description</th>
                            <th class="p-4 text-left">OG Image</th>
                            <th class="p-4 text-left">Updated</th>
                            <th class="p-4"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pages as $page): ?>
                            <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                                <td class="p-4 text-sm font-mono text-gray-700"><?= htmlspecialchars($page['page_slug']) ?></td>
                                <td class="p-4 text-sm text-gray-800 max-w-[180px] truncate"><?= htmlspecialchars($page['seo_title']) ?></td>
                                <td class="p-4 text-sm text-gray-500 max-w-[220px] truncate"><?= htmlspecialchars($page['seo_description']) ?></td>
                                <td class="p-4 text-sm text-gray-400">
                                    <?php if ($page['og_image']): ?>
                                        <a href="<?= htmlspecialchars($page['og_image']) ?>" target="_blank" class="text-[#6ae6fc] hover:underline text-xs">View</a>
                                    <?php else: ?>
                                        <span class="text-gray-300">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-xs text-gray-400"><?= htmlspecialchars(date('d M Y', strtotime($page['updated_at']))) ?></td>
                                <td class="p-4 text-right">
                                    <form method="POST" onsubmit="return confirm('Remove this override?')">
                                        <input type="hidden" name="delete_id" value="<?= (int)$page['id'] ?>">
                                        <button type="submit"
                                                class="text-xs text-red-400 hover:text-red-600 font-medium transition">
                                            Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>

    <script>
        const descTextarea = document.querySelector('textarea[name="seo_description"]');
        const descCount = document.getElementById('desc-count');
        if (descTextarea && descCount) {
            descTextarea.addEventListener('input', () => {
                descCount.textContent = descTextarea.value.length;
            });
        }
    </script>
</body>
</html>
