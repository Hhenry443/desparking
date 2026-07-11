<?php
session_start();
$title = "Partners CMS";

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] !== true) {
    header("Location: /");
    exit;
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_partner_id'])) {
    include_once $_SERVER['DOCUMENT_ROOT'] . '/php/models/Partners.php';
    $partners = new Partners();
    $partners->deletePartner((int)$_POST['delete_partner_id']);
    header("Location: /partners-admin.php?deleted=1");
    exit;
}

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/partners/ReadPartners.php';
$ReadPartners = new ReadPartners();
$partners     = $ReadPartners->getAllPartners();
?>
<!doctype html>
<html lang="en">
<?php include_once __DIR__ . '/partials/header.php'; ?>
<body class="min-h-screen bg-[#ebebeb] pt-24">
    <?php include_once __DIR__ . '/partials/navbar.php'; ?>

    <div class="max-w-5xl mx-auto px-6 py-10">

        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Partners CMS</h1>
                <p class="text-sm text-gray-500 mt-1">Manage the partner logos shown on the Partners page.</p>
            </div>
            <a href="/partners-edit.php"
               class="px-5 py-2.5 bg-[#6ae6fc] text-gray-900 text-sm font-bold rounded-xl hover:bg-cyan-400 transition shadow-sm">
                + New Partner
            </a>
        </div>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-xl text-sm">Partner deleted.</div>
        <?php endif; ?>
        <?php if (isset($_GET['saved'])): ?>
            <div class="mb-6 p-4 bg-emerald-50 text-emerald-700 rounded-xl text-sm">Partner saved.</div>
        <?php endif; ?>

        <?php if (empty($partners)): ?>
            <div class="bg-white rounded-2xl p-10 text-center text-gray-400 shadow-sm">
                No partners yet. Click <strong>+ New Partner</strong> to get started.
            </div>
        <?php else: ?>
            <div class="bg-white rounded-2xl shadow-[0_0_16px_rgba(0,0,0,0.08)] overflow-hidden">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="text-xs text-gray-500 uppercase tracking-wide bg-gray-50 border-b border-gray-100">
                            <th class="p-4 text-left">Logo</th>
                            <th class="p-4 text-left">Name</th>
                            <th class="p-4 text-left">Website</th>
                            <th class="p-4 text-left">Order</th>
                            <th class="p-4 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($partners as $partner): ?>
                            <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                                <td class="p-4">
                                    <img src="<?= htmlspecialchars($partner['logo_path']) ?>" alt="<?= htmlspecialchars($partner['partner_name']) ?>"
                                         class="h-10 w-auto max-w-[120px] object-contain">
                                </td>
                                <td class="p-4 font-semibold text-sm text-gray-800">
                                    <?= htmlspecialchars($partner['partner_name']) ?>
                                </td>
                                <td class="p-4 text-sm text-gray-500 max-w-xs truncate">
                                    <?php if ($partner['website_url']): ?>
                                        <a href="<?= htmlspecialchars($partner['website_url']) ?>" target="_blank" rel="noopener noreferrer"
                                           class="text-[#060745] hover:underline"><?= htmlspecialchars($partner['website_url']) ?></a>
                                    <?php else: ?>
                                        <span class="text-gray-300">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-sm text-gray-500">
                                    <?= (int)$partner['sort_order'] ?>
                                </td>
                                <td class="p-4">
                                    <div class="flex gap-2">
                                        <a href="/partners-edit.php?id=<?= $partner['partner_id'] ?>"
                                           class="px-3 py-1.5 text-xs font-semibold bg-[#6ae6fc] text-gray-900 rounded-lg hover:bg-cyan-400 transition">
                                            Edit
                                        </a>
                                        <form method="POST" onsubmit="return confirm('Delete this partner permanently?')">
                                            <input type="hidden" name="delete_partner_id" value="<?= $partner['partner_id'] ?>">
                                            <button type="submit"
                                                class="px-3 py-1.5 text-xs font-semibold bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>
