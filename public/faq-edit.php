<?php
session_start();
$title = "Edit FAQ";

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] !== true) {
    header("Location: /");
    exit;
}

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/faq/WriteFaq.php';
    $WriteFaq = new WriteFaq();
    $WriteFaq->saveFaq();
    header("Location: /faq-admin.php?saved=1");
    exit;
}

// Load existing FAQ if editing
$faq    = null;
$editId = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int)$_GET['id'] : null;

if ($editId) {
    include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/faq/ReadFaq.php';
    $ReadFaq = new ReadFaq();
    $faq     = $ReadFaq->getFaqById($editId);
    if (!$faq) {
        header("Location: /faq-admin.php");
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
            <a href="/faq-admin.php" class="text-sm text-gray-500 hover:text-gray-800 transition">
                <i class="fa-solid fa-chevron-left text-xs"></i> Back
            </a>
            <h1 class="text-2xl font-bold text-gray-900"><?= $editId ? 'Edit FAQ' : 'New FAQ' ?></h1>
        </div>

        <form method="POST" class="space-y-6">
            <?php if ($editId): ?>
                <input type="hidden" name="faq_id" value="<?= $editId ?>">
            <?php endif; ?>

            <div class="bg-white rounded-2xl shadow-[0_0_16px_rgba(0,0,0,0.08)] p-6 space-y-5">

                <!-- Question -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Question *</label>
                    <input type="text" name="question" required
                           value="<?= htmlspecialchars($faq['question'] ?? '') ?>"
                           placeholder="Enter the question"
                           class="w-full py-3 px-4 rounded-lg bg-gray-100 text-gray-800 text-sm border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#6ae6fc]">
                </div>

                <!-- Answer -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Answer *</label>
                    <textarea name="answer" rows="8" required
                              placeholder="Enter the answer"
                              class="w-full py-3 px-4 rounded-lg bg-gray-100 text-gray-800 text-sm border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#6ae6fc]"><?= htmlspecialchars($faq['answer'] ?? '') ?></textarea>
                </div>

                <!-- Category -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Category</label>
                    <select name="category"
                            class="w-full py-3 px-4 rounded-lg bg-gray-100 text-gray-800 text-sm border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#6ae6fc]">
                        <option value="general"  <?= ($faq['category'] ?? 'general') === 'general'  ? 'selected' : '' ?>>General</option>
                        <option value="owners"   <?= ($faq['category'] ?? '') === 'owners'   ? 'selected' : '' ?>>Space Owners</option>
                        <option value="drivers"  <?= ($faq['category'] ?? '') === 'drivers'  ? 'selected' : '' ?>>Drivers</option>
                    </select>
                </div>

                <!-- Sort order -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Sort Order</label>
                    <input type="number" name="sort_order" min="0"
                           value="<?= (int)($faq['sort_order'] ?? 0) ?>"
                           class="w-32 py-3 px-4 rounded-lg bg-gray-100 text-gray-800 text-sm border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#6ae6fc]">
                    <p class="text-xs text-gray-400 mt-1">Lower numbers appear first within each category.</p>
                </div>

            </div>

            <!-- Save -->
            <div class="flex gap-3 pb-10">
                <button type="submit"
                        class="flex-1 py-3 bg-[#6ae6fc] text-gray-900 font-bold rounded-xl hover:bg-cyan-400 transition shadow-sm">
                    Save FAQ
                </button>
                <a href="/faq-admin.php"
                   class="flex-1 py-3 bg-gray-200 text-gray-700 font-semibold rounded-xl hover:bg-gray-300 transition text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</body>
</html>
