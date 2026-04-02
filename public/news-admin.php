<?php
session_start();
$title = "News CMS";

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] !== true) {
    header("Location: /");
    exit;
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_story_id'])) {
    include_once $_SERVER['DOCUMENT_ROOT'] . '/php/models/News.php';
    $news = new News();
    $news->deleteStory((int)$_POST['delete_story_id']);
    header("Location: /news-admin.php?deleted=1");
    exit;
}

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/news/ReadNews.php';
$ReadNews = new ReadNews();
$stories  = $ReadNews->getAllStories();
?>
<!doctype html>
<html lang="en">
<?php include_once __DIR__ . '/partials/header.php'; ?>
<body class="min-h-screen bg-[#ebebeb] pt-24">
    <?php include_once __DIR__ . '/partials/navbar.php'; ?>

    <div class="max-w-5xl mx-auto px-6 py-10">

        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">News CMS</h1>
                <p class="text-sm text-gray-500 mt-1">Manage published stories and drafts.</p>
            </div>
            <a href="/news-edit.php"
               class="px-5 py-2.5 bg-[#6ae6fc] text-gray-900 text-sm font-bold rounded-xl hover:bg-cyan-400 transition shadow-sm">
                + New Story
            </a>
        </div>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-xl text-sm">Story deleted.</div>
        <?php endif; ?>
        <?php if (isset($_GET['saved'])): ?>
            <div class="mb-6 p-4 bg-emerald-50 text-emerald-700 rounded-xl text-sm">Story saved.</div>
        <?php endif; ?>

        <?php if (empty($stories)): ?>
            <div class="bg-white rounded-2xl p-10 text-center text-gray-400 shadow-sm">
                No stories yet. Click <strong>+ New Story</strong> to get started.
            </div>
        <?php else: ?>
            <div class="bg-white rounded-2xl shadow-[0_0_16px_rgba(0,0,0,0.08)] overflow-hidden">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="text-xs text-gray-500 uppercase tracking-wide bg-gray-50 border-b border-gray-100">
                            <th class="p-4 text-left">Title</th>
                            <th class="p-4 text-left">Status</th>
                            <th class="p-4 text-left">Date</th>
                            <th class="p-4 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stories as $story): ?>
                            <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                                <td class="p-4 font-semibold text-sm text-gray-800">
                                    <?= htmlspecialchars($story['title']) ?>
                                </td>
                                <td class="p-4">
                                    <?php if ($story['status'] === 'published'): ?>
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">Published</span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">Draft</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-sm text-gray-500">
                                    <?= date('d M Y', strtotime($story['created_at'])) ?>
                                </td>
                                <td class="p-4">
                                    <div class="flex gap-2">
                                        <a href="/news-edit.php?id=<?= $story['story_id'] ?>"
                                           class="px-3 py-1.5 text-xs font-semibold bg-[#6ae6fc] text-gray-900 rounded-lg hover:bg-cyan-400 transition">
                                            Edit
                                        </a>
                                        <?php if ($story['status'] === 'published'): ?>
                                            <a href="/news-story.php?slug=<?= urlencode($story['slug']) ?>" target="_blank"
                                               class="px-3 py-1.5 text-xs font-semibold bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition">
                                                View
                                            </a>
                                        <?php endif; ?>
                                        <form method="POST" onsubmit="return confirm('Delete this story permanently?')">
                                            <input type="hidden" name="delete_story_id" value="<?= $story['story_id'] ?>">
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
