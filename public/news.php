<?php
$title = "News";

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/news/ReadNews.php';
$ReadNews = new ReadNews();
$stories  = $ReadNews->getPublishedStories();
?>
<!doctype html>
<html lang="en">
<?php include_once __DIR__ . '/partials/header.php'; ?>
<body class="min-h-screen bg-[#ebebeb] pt-24">
    <?php include_once __DIR__ . '/partials/navbar.php'; ?>

    <div class="max-w-5xl mx-auto px-6 py-12">

        <div class="mb-10">
            <h1 class="text-4xl font-bold text-gray-900">News &amp; Stories</h1>
            <p class="text-gray-500 mt-2">The latest from EveryonesParking.</p>
        </div>

        <?php if (empty($stories)): ?>
            <div class="bg-white rounded-2xl p-10 text-center text-gray-400 shadow-sm">
                No stories published yet. Check back soon.
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($stories as $story): ?>
                    <a href="/news-story.php?slug=<?= urlencode($story['slug']) ?>"
                       class="bg-white rounded-2xl shadow-[0_0_16px_rgba(0,0,0,0.08)] overflow-hidden hover:shadow-[0_0_24px_rgba(0,0,0,0.14)] transition group">
                        <?php if ($story['cover_image']): ?>
                            <img src="<?= htmlspecialchars($story['cover_image']) ?>"
                                 alt="<?= htmlspecialchars($story['title']) ?>"
                                 class="w-full h-48 object-cover group-hover:scale-[1.02] transition-transform duration-300">
                        <?php else: ?>
                            <div class="w-full h-48 bg-gradient-to-br from-[#6ae6fc]/30 to-[#060745]/10 flex items-center justify-center">
                                <i class="fa-regular fa-newspaper text-4xl text-gray-300"></i>
                            </div>
                        <?php endif; ?>
                        <div class="p-5">
                            <p class="text-xs text-gray-400 mb-2"><?= date('d M Y', strtotime($story['created_at'])) ?></p>
                            <h2 class="text-lg font-bold text-gray-900 leading-snug mb-2 group-hover:text-[#060745] transition"><?= htmlspecialchars($story['title']) ?></h2>
                            <?php if ($story['summary']): ?>
                                <p class="text-sm text-gray-500 line-clamp-3"><?= htmlspecialchars($story['summary']) ?></p>
                            <?php endif; ?>
                            <p class="text-sm font-semibold text-[#6ae6fc] mt-3">Read more &rarr;</p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>
