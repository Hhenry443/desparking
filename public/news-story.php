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
$description = !empty($story['summary']) ? $story['summary'] : 'Read the latest news and updates from EveryonesParking.';
$ogType   = 'article';
$ogImage  = !empty($story['image_url']) ? $story['image_url'] : null;

$allStories   = $ReadNews->getPublishedStories();
$moreStories  = array_filter($allStories, fn($s) => $s['story_id'] !== $story['story_id']);
$moreStories  = array_slice(array_values($moreStories), 0, 3);

$currentUrl   = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
              . '://' . $_SERVER['HTTP_HOST'] . '/news-story.php?slug=' . urlencode($slug);
$canonical    = $currentUrl;
$encodedUrl   = urlencode($currentUrl);
$encodedTitle = urlencode($story['title']);
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
        <div>
            <?php foreach ($sections as $i => $section): ?>
                <?php if ($i > 0): ?>
                    <hr class="border-black my-10">
                <?php endif; ?>
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

        <!-- Share -->
        <div class="mt-14 pt-8 border-t border-gray-200">
            <p class="text-sm font-semibold text-gray-500 mb-3">Share this story</p>
            <div class="flex flex-wrap gap-3">
                <a href="https://twitter.com/intent/tweet?url=<?= $encodedUrl ?>&text=<?= $encodedTitle ?>"
                   target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-black text-white text-sm font-semibold hover:opacity-80 transition">
                    <i class="fa-brands fa-x-twitter"></i> X / Twitter
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $encodedUrl ?>"
                   target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-[#1877f2] text-white text-sm font-semibold hover:opacity-80 transition">
                    <i class="fa-brands fa-facebook"></i> Facebook
                </a>
                <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= $encodedUrl ?>"
                   target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-[#0a66c2] text-white text-sm font-semibold hover:opacity-80 transition">
                    <i class="fa-brands fa-linkedin"></i> LinkedIn
                </a>
                <a href="https://wa.me/?text=<?= $encodedTitle ?>%20<?= $encodedUrl ?>"
                   target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-[#25d366] text-white text-sm font-semibold hover:opacity-80 transition">
                    <i class="fa-brands fa-whatsapp"></i> WhatsApp
                </a>
            </div>
        </div>

        <!-- More articles -->
        <?php if (!empty($moreStories)): ?>
        <div class="mt-14">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">More stories</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <?php foreach ($moreStories as $other): ?>
                    <a href="/news-story.php?slug=<?= urlencode($other['slug']) ?>"
                       class="bg-white rounded-2xl shadow-[0_0_16px_rgba(0,0,0,0.08)] overflow-hidden hover:shadow-[0_0_24px_rgba(0,0,0,0.14)] transition group">
                        <?php if ($other['cover_image']): ?>
                            <img src="<?= htmlspecialchars($other['cover_image']) ?>"
                                 alt="<?= htmlspecialchars($other['title']) ?>"
                                 class="w-full h-36 object-cover group-hover:scale-[1.02] transition-transform duration-300">
                        <?php else: ?>
                            <div class="w-full h-36 bg-gradient-to-br from-[#6ae6fc]/30 to-[#060745]/10 flex items-center justify-center">
                                <i class="fa-regular fa-newspaper text-3xl text-gray-300"></i>
                            </div>
                        <?php endif; ?>
                        <div class="p-4">
                            <p class="text-xs text-gray-400 mb-1"><?= date('d M Y', strtotime($other['created_at'])) ?></p>
                            <h3 class="text-sm font-bold text-gray-900 leading-snug group-hover:text-[#060745] transition line-clamp-2"><?= htmlspecialchars($other['title']) ?></h3>
                            <p class="text-xs font-semibold text-[#6ae6fc] mt-2">Read more &rarr;</p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="mt-10 pt-6 border-t border-gray-200">
            <a href="/news.php" class="text-sm font-semibold text-gray-500 hover:underline">&larr; Back to all stories</a>
        </div>

    </div>

</body>

</html>