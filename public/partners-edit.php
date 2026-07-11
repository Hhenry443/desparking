<?php
session_start();
$title = "Edit Partner";

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] !== true) {
    header("Location: /");
    exit;
}

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/partners/WritePartners.php';
    $WritePartners = new WritePartners();
    $WritePartners->savePartner();
    header("Location: /partners-admin.php?saved=1");
    exit;
}

// Load existing partner if editing
$partner = null;
$editId  = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int)$_GET['id'] : null;

if ($editId) {
    include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/partners/ReadPartners.php';
    $ReadPartners = new ReadPartners();
    $partner      = $ReadPartners->getPartnerById($editId);
    if (!$partner) {
        header("Location: /partners-admin.php");
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
<?php include_once __DIR__ . '/partials/header.php'; ?>
<body class="min-h-screen bg-[#ebebeb] pt-24">
    <?php include_once __DIR__ . '/partials/navbar.php'; ?>

    <div class="max-w-3xl mx-auto px-6 py-10">

        <div class="flex items-center gap-3 mb-8">
            <a href="/partners-admin.php" class="text-sm text-gray-500 hover:text-gray-800 transition">
                <i class="fa-solid fa-chevron-left text-xs"></i> Back
            </a>
            <h1 class="text-2xl font-bold text-gray-900"><?= $editId ? 'Edit Partner' : 'New Partner' ?></h1>
        </div>

        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <?php if ($editId): ?>
                <input type="hidden" name="partner_id" value="<?= $editId ?>">
            <?php endif; ?>
            <?php if (!empty($partner['logo_path'])): ?>
                <input type="hidden" name="existing_logo" value="<?= htmlspecialchars($partner['logo_path']) ?>">
            <?php endif; ?>

            <div class="bg-white rounded-2xl shadow-[0_0_16px_rgba(0,0,0,0.08)] p-6 space-y-5">

                <!-- Name -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Partner Name *</label>
                    <input type="text" name="partner_name" required
                           value="<?= htmlspecialchars($partner['partner_name'] ?? '') ?>"
                           placeholder="e.g. QPark"
                           class="w-full py-3 px-4 rounded-lg bg-gray-100 text-gray-800 text-sm border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#6ae6fc]">
                </div>

                <!-- Website URL -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Website URL</label>
                    <input type="url" name="website_url"
                           value="<?= htmlspecialchars($partner['website_url'] ?? '') ?>"
                           placeholder="https://example.com"
                           class="w-full py-3 px-4 rounded-lg bg-gray-100 text-gray-800 text-sm border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#6ae6fc]">
                    <p class="text-xs text-gray-400 mt-1">Where the logo links to when clicked. Leave blank for no link.</p>
                </div>

                <!-- Logo -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Logo <?= $editId ? '' : '*' ?></label>
                    <?php if (!empty($partner['logo_path'])): ?>
                        <div class="mb-3 flex items-center gap-3">
                            <img src="<?= htmlspecialchars($partner['logo_path']) ?>" alt="Current logo"
                                 class="h-14 w-auto max-w-[160px] object-contain border border-gray-200 rounded-lg p-2 bg-white">
                            <span class="text-xs text-gray-400">Current logo — upload a new file to replace it</span>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="logo" accept="image/jpeg,image/png,image/webp,image/gif"
                           <?= $editId ? '' : 'required' ?>
                           class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#6ae6fc] file:text-gray-900 hover:file:bg-cyan-400">
                    <p class="text-xs text-gray-400 mt-1">JPEG, PNG, WebP or GIF.</p>
                </div>

                <!-- Sort order -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Sort Order</label>
                    <input type="number" name="sort_order" min="0"
                           value="<?= (int)($partner['sort_order'] ?? 0) ?>"
                           class="w-32 py-3 px-4 rounded-lg bg-gray-100 text-gray-800 text-sm border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#6ae6fc]">
                    <p class="text-xs text-gray-400 mt-1">Lower numbers appear first in the logo marquee.</p>
                </div>

            </div>

            <!-- Save -->
            <div class="flex gap-3 pb-10">
                <button type="submit"
                        class="flex-1 py-3 bg-[#6ae6fc] text-gray-900 font-bold rounded-xl hover:bg-cyan-400 transition shadow-sm">
                    Save Partner
                </button>
                <a href="/partners-admin.php"
                   class="flex-1 py-3 bg-gray-200 text-gray-700 font-semibold rounded-xl hover:bg-gray-300 transition text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</body>
</html>
