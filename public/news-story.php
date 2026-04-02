<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/news/ReadNews.php';
$ReadNews = new ReadNews();

$slug  = $_GET['slug'] ?? '';
$story = $slug ? $ReadNews->getStoryBySlug($slug) : null;

if (!$story) {
    header("Location: /news.php");
    exit;
}

$sections = $ReadNews->getSections((int)$story['story_id']);
$title    = $story['title'];
?>
<!doctype html>
<html lang="en">
<?php include_once __DIR__ . '/partials/header.php'; ?>
<body class="min-h-screen bg-[#ebebeb] pt-24">
    <?php include_once __DIR__ . '/partials/navbar.php'; ?>

    <div class="max-w-3xl mx-auto px-6 py-12">

        <!-- Back -->
        <a href="/news.php" class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-500 hover:text-gray-800 mb-8 transition">
            <i class="fa-solid fa-chevron-left text-xs"></i> All stories
        </a>

        <!-- Hero -->
        <?php if ($story['cover_image']): ?>
            <img src="<?= htmlspecialchars($story['cover_image']) ?>"
                 alt="<?= htmlspecialchars($story['title']) ?>"
                 class="w-full h-72 object-cover rounded-2xl mb-8 shadow-sm">
        <?php endif; ?>

        <p class="text-xs text-gray-400 mb-3"><?= date('d F Y', strtotime($story['created_at'])) ?></p>
        <h1 class="text-4xl font-bold text-gray-900 leading-tight mb-4"><?= htmlspecialchars($story['title']) ?></h1>

        <?php if ($story['summary']): ?>
            <p class="text-lg text-gray-500 leading-relaxed mb-10 border-l-4 border-[#6ae6fc] pl-4">
                <?= htmlspecialchars($story['summary']) ?>
            </p>
        <?php endif; ?>

        <!-- Sections -->
        <div class="space-y-10">
            <?php foreach ($sections as $section): ?>
                <div>
                    <?php if ($section['heading']): ?>
                        <h2 class="text-2xl font-bold text-gray-900 mb-3"><?= htmlspecialchars($section['heading']) ?></h2>
                    <?php endif; ?>

                    <?php if ($section['image_path']): ?>
                        <img src="<?= htmlspecialchars($section['image_path']) ?>"
                             alt=""
                             class="w-full rounded-2xl mb-4 shadow-sm object-cover max-h-96">
                    <?php endif; ?>

                    <?php if ($section['body']): ?>
                        <p class="text-gray-600 leading-relaxed text-base whitespace-pre-line"><?= htmlspecialchars($section['body']) ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-14 pt-8 border-t border-gray-200">
            <a href="/news.php" class="text-sm font-semibold text-[#6ae6fc] hover:underline">&larr; Back to all stories</a>
        </div>

    </div>

</body>
</html>
