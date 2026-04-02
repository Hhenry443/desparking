<?php
session_start();
$title = "Edit Story";

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] !== true) {
    header("Location: /");
    exit;
}

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/news/WriteNews.php';
    $WriteNews = new WriteNews();
    $id = $WriteNews->saveStory();
    header("Location: /news-admin.php?saved=1");
    exit;
}

// Load existing story if editing
$story    = null;
$sections = [];
$editId   = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int)$_GET['id'] : null;

if ($editId) {
    include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/news/ReadNews.php';
    $ReadNews = new ReadNews();
    $story    = $ReadNews->getStoryById($editId);
    if (!$story) {
        header("Location: /news-admin.php");
        exit;
    }
    $sections = $ReadNews->getSections($editId);
}
?>
<!doctype html>
<html lang="en">
<?php include_once __DIR__ . '/partials/header.php'; ?>
<body class="min-h-screen bg-[#ebebeb] pt-24">
    <?php include_once __DIR__ . '/partials/navbar.php'; ?>

    <div class="max-w-3xl mx-auto px-6 py-10">

        <div class="flex items-center gap-3 mb-8">
            <a href="/news-admin.php" class="text-sm text-gray-500 hover:text-gray-800 transition">
                <i class="fa-solid fa-chevron-left text-xs"></i> Back
            </a>
            <h1 class="text-2xl font-bold text-gray-900"><?= $editId ? 'Edit Story' : 'New Story' ?></h1>
        </div>

        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <?php if ($editId): ?>
                <input type="hidden" name="story_id" value="<?= $editId ?>">
            <?php endif; ?>

            <div class="bg-white rounded-2xl shadow-[0_0_16px_rgba(0,0,0,0.08)] p-6 space-y-5">
                <h2 class="text-base font-bold text-gray-800">Story Details</h2>

                <!-- Title -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Title *</label>
                    <input type="text" name="title" required
                           value="<?= htmlspecialchars($story['title'] ?? '') ?>"
                           placeholder="Story headline"
                           class="w-full py-3 px-4 rounded-lg bg-gray-100 text-gray-800 text-sm border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#6ae6fc]">
                </div>

                <!-- Summary -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Summary / Excerpt</label>
                    <textarea name="summary" rows="3"
                              placeholder="A short description shown on the listing page"
                              class="w-full py-3 px-4 rounded-lg bg-gray-100 text-gray-800 text-sm border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#6ae6fc]"><?= htmlspecialchars($story['summary'] ?? '') ?></textarea>
                </div>

                <!-- Cover image -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Cover Image</label>
                    <?php if (!empty($story['cover_image'])): ?>
                        <img src="<?= htmlspecialchars($story['cover_image']) ?>" class="h-32 rounded-xl object-cover mb-2">
                        <input type="hidden" name="existing_cover" value="<?= htmlspecialchars($story['cover_image']) ?>">
                    <?php endif; ?>
                    <input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp"
                           class="text-sm text-gray-600">
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Status</label>
                    <select name="status"
                            class="w-full py-3 px-4 rounded-lg bg-gray-100 text-gray-800 text-sm border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#6ae6fc]">
                        <option value="draft"     <?= ($story['status'] ?? 'draft') === 'draft'     ? 'selected' : '' ?>>Draft</option>
                        <option value="published" <?= ($story['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                    </select>
                </div>
            </div>

            <!-- Sections -->
            <div class="bg-white rounded-2xl shadow-[0_0_16px_rgba(0,0,0,0.08)] p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-base font-bold text-gray-800">Sections</h2>
                    <button type="button" onclick="addSection()"
                            class="text-sm font-semibold text-[#6ae6fc] hover:underline">
                        + Add section
                    </button>
                </div>

                <div id="sections-list" class="space-y-6">
                    <?php if (empty($sections)): ?>
                        <!-- JS will add a blank section via addSection() on load -->
                    <?php else: ?>
                        <?php foreach ($sections as $i => $s): ?>
                            <div class="section-block border border-gray-100 rounded-xl p-4 space-y-3 bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wide">Section <?= $i + 1 ?></span>
                                    <button type="button" onclick="removeSection(this)"
                                            class="text-xs text-red-400 hover:text-red-600 font-semibold transition">Remove</button>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Heading</label>
                                    <input type="text" name="section_heading[]"
                                           value="<?= htmlspecialchars($s['heading'] ?? '') ?>"
                                           placeholder="Section heading (optional)"
                                           class="w-full py-2 px-3 rounded-lg bg-white text-gray-800 text-sm border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#6ae6fc]">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Body text</label>
                                    <textarea name="section_body[]" rows="5"
                                              placeholder="Section content"
                                              class="w-full py-2 px-3 rounded-lg bg-white text-gray-800 text-sm border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#6ae6fc]"><?= htmlspecialchars($s['body'] ?? '') ?></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Image</label>
                                    <?php if (!empty($s['image_path'])): ?>
                                        <img src="<?= htmlspecialchars($s['image_path']) ?>" class="h-24 rounded-xl object-cover mb-2">
                                        <input type="hidden" name="section_existing_image[]" value="<?= htmlspecialchars($s['image_path']) ?>">
                                    <?php else: ?>
                                        <input type="hidden" name="section_existing_image[]" value="">
                                    <?php endif; ?>
                                    <input type="file" name="section_image[]" accept="image/jpeg,image/png,image/webp"
                                           class="text-sm text-gray-600">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Save -->
            <div class="flex gap-3 pb-10">
                <button type="submit"
                        class="flex-1 py-3 bg-[#6ae6fc] text-gray-900 font-bold rounded-xl hover:bg-cyan-400 transition shadow-sm">
                    Save Story
                </button>
                <a href="/news-admin.php"
                   class="flex-1 py-3 bg-gray-200 text-gray-700 font-semibold rounded-xl hover:bg-gray-300 transition text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <script>
        let sectionCount = <?= count($sections) ?>;

        function addSection() {
            sectionCount++;
            const list = document.getElementById('sections-list');
            const div  = document.createElement('div');
            div.className = 'section-block border border-gray-100 rounded-xl p-4 space-y-3 bg-gray-50';
            div.innerHTML = `
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wide">Section ${sectionCount}</span>
                    <button type="button" onclick="removeSection(this)" class="text-xs text-red-400 hover:text-red-600 font-semibold transition">Remove</button>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Heading</label>
                    <input type="text" name="section_heading[]" placeholder="Section heading (optional)"
                           class="w-full py-2 px-3 rounded-lg bg-white text-gray-800 text-sm border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#6ae6fc]">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Body text</label>
                    <textarea name="section_body[]" rows="5" placeholder="Section content"
                              class="w-full py-2 px-3 rounded-lg bg-white text-gray-800 text-sm border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#6ae6fc]"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Image</label>
                    <input type="hidden" name="section_existing_image[]" value="">
                    <input type="file" name="section_image[]" accept="image/jpeg,image/png,image/webp"
                           class="text-sm text-gray-600">
                </div>
            `;
            list.appendChild(div);
        }

        function removeSection(btn) {
            btn.closest('.section-block').remove();
        }

        // Start with one blank section if creating a new story
        if (sectionCount === 0) addSection();
    </script>
</body>
</html>
