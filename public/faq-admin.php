<?php
session_start();
$title = "FAQ CMS";

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] !== true) {
    header("Location: /");
    exit;
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_faq_id'])) {
    include_once $_SERVER['DOCUMENT_ROOT'] . '/php/models/Faq.php';
    $faq = new Faq();
    $faq->deleteFaq((int)$_POST['delete_faq_id']);
    header("Location: /faq-admin.php?deleted=1");
    exit;
}

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/faq/ReadFaq.php';
$ReadFaq = new ReadFaq();
$faqs    = $ReadFaq->getAllFaqs();

$categoryLabels = ['general' => 'General', 'owners' => 'Space Owners', 'drivers' => 'Drivers'];
?>
<!doctype html>
<html lang="en">
<?php include_once __DIR__ . '/partials/header.php'; ?>
<body class="min-h-screen bg-[#ebebeb] pt-24">
    <?php include_once __DIR__ . '/partials/navbar.php'; ?>

    <div class="max-w-5xl mx-auto px-6 py-10">

        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">FAQ CMS</h1>
                <p class="text-sm text-gray-500 mt-1">Manage frequently asked questions.</p>
            </div>
            <a href="/faq-edit.php"
               class="px-5 py-2.5 bg-[#6ae6fc] text-gray-900 text-sm font-bold rounded-xl hover:bg-cyan-400 transition shadow-sm">
                + New FAQ
            </a>
        </div>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-xl text-sm">FAQ deleted.</div>
        <?php endif; ?>
        <?php if (isset($_GET['saved'])): ?>
            <div class="mb-6 p-4 bg-emerald-50 text-emerald-700 rounded-xl text-sm">FAQ saved.</div>
        <?php endif; ?>

        <?php if (empty($faqs)): ?>
            <div class="bg-white rounded-2xl p-10 text-center text-gray-400 shadow-sm">
                No FAQs yet. Click <strong>+ New FAQ</strong> to get started.
            </div>
        <?php else: ?>
            <div class="bg-white rounded-2xl shadow-[0_0_16px_rgba(0,0,0,0.08)] overflow-hidden">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="text-xs text-gray-500 uppercase tracking-wide bg-gray-50 border-b border-gray-100">
                            <th class="p-4 text-left">Question</th>
                            <th class="p-4 text-left">Category</th>
                            <th class="p-4 text-left">Order</th>
                            <th class="p-4 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($faqs as $faq): ?>
                            <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                                <td class="p-4 font-semibold text-sm text-gray-800 max-w-sm">
                                    <?= htmlspecialchars($faq['question']) ?>
                                </td>
                                <td class="p-4">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-cyan-100 text-cyan-700">
                                        <?= htmlspecialchars($categoryLabels[$faq['category']] ?? $faq['category']) ?>
                                    </span>
                                </td>
                                <td class="p-4 text-sm text-gray-500">
                                    <?= (int)$faq['sort_order'] ?>
                                </td>
                                <td class="p-4">
                                    <div class="flex gap-2">
                                        <a href="/faq-edit.php?id=<?= $faq['faq_id'] ?>"
                                           class="px-3 py-1.5 text-xs font-semibold bg-[#6ae6fc] text-gray-900 rounded-lg hover:bg-cyan-400 transition">
                                            Edit
                                        </a>
                                        <form method="POST" onsubmit="return confirm('Delete this FAQ permanently?')">
                                            <input type="hidden" name="delete_faq_id" value="<?= $faq['faq_id'] ?>">
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
