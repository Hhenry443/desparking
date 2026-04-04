<?php

$title = "FAQ";

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once $_SERVER['DOCUMENT_ROOT'] . "/php/api/faq/ReadFaq.php";
$ReadFaq = new ReadFaq();
$faqs    = $ReadFaq->getAllFaqs();
?>
<!doctype html>
<html>

<?php include_once __DIR__ . '/partials/header.php'; ?>


<body class="min-h-screen bg-white">
    <?php include_once __DIR__ . '/partials/navbar.php'; ?>

    <!-- HERO / SECTION 1 -->
    <section id="section-1" class="relative bg-white overflow-hidden pt-28 lg:pt-48 pb-12 lg:pb-32">

        <!-- Inner content -->
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 items-start bg-[url('/images/desparking-business-header.jpg')] bg-cover bg-center rounded-lg ">

            <!-- LEFT BOX -->
            <div class="bg-white w-full h-full px-6 py-12">
                <h1 class="text-4xl font-bold text-gray-800 mb-6">Frequently Asked Questions</h1>
                <div class="w-32 h-1 bg-[#6ae6fc] mb-6"></div>
            </div>

            <!-- RIGHT BOX -->
            <div class="w-full h-full">
                <div class="w-1/4 h-1/2 bg-white"></div>
            </div>
        </div>
    </section>

    <section id="section-2" class="relative bg-white overflow-hidden pt-16 pb-16">
        <div class="max-w-7xl mx-auto px-6 mb-6">
            <input
                type="text"
                id="faq-search"
                placeholder="Search for a question..."
                class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#6ae6fc]">
        </div>

        <div class="max-w-7xl mx-auto px-6 mb-10">

            <!-- Tabs -->
            <div class="flex gap-8 border-b border-gray-200">

                <button onclick="setCategory('general')"
                    class="faq-tab pb-4 text-gray-500 font-medium border-b-2 border-transparent hover:text-black hover:cursor-pointer transition "
                    data-tab="general">
                    General
                </button>

                <button onclick="setCategory('owners')"
                    class="faq-tab pb-4 text-gray-500 font-medium border-b-2 border-transparent hover:text-black hover:cursor-pointer transition"
                    data-tab="owners">
                    Space Owners
                </button>

                <button onclick="setCategory('drivers')"
                    class="faq-tab pb-4 text-gray-500 font-medium border-b-2 border-transparent hover:text-black hover:cursor-pointer transition"
                    data-tab="drivers">
                    Drivers
                </button>

            </div>

            <!-- Optional helper text -->
            <p id="faq-placeholder" class="text-gray-500 mt-6">
                Select a category to view questions
            </p>

        </div>

        <div class="max-w-7xl mx-auto px-6 flex flex-col items-start">
            <div class="space-y-6" id="faq">
                <?php foreach ($faqs as $faq) : ?>
                    <!-- FAQ Item -->
                    <div class="faq-item border-b border-[#6ae6fc] pb-4 transition-opacity duration-200" data-category="<?= $faq['category'] ?>">
                        <button
                            class="faq-toggle w-full flex justify-between items-center text-left text-lg font-semibold text-gray-900 py-4">
                            <?= $faq['question'] ?>
                            <svg
                                class="faq-icon w-5 h-5 transition-transform duration-300 origin-center"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div class="faq-content max-h-0 overflow-hidden transition-all duration-300 text-gray-600">
                            <p class="pb-2">
                                <?= nl2br($faq['answer']) ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>

            </div>
        </div>
    </section>

    <script>
        document.querySelectorAll('.faq-toggle').forEach(button => {
            button.addEventListener('click', () => {
                const item = button.parentElement;
                const content = item.querySelector('.faq-content');
                const icon = button.querySelector('.faq-icon');

                // Close others (accordion style)
                document.querySelectorAll('.faq-item').forEach(other => {
                    if (other !== item) {
                        other.querySelector('.faq-content').style.maxHeight = null;
                        other.querySelector('.faq-icon').classList.remove('rotate-180');
                    }
                });

                // Toggle current
                if (content.style.maxHeight) {
                    content.style.maxHeight = null;
                    icon.classList.remove('rotate-180');
                } else {
                    content.style.maxHeight = content.scrollHeight + "px";
                    icon.classList.add('rotate-180');
                }
            });
        });
    </script>

    <script>
        const faqs = Array.from(document.querySelectorAll('.faq-item'));
        const tabs = document.querySelectorAll('.faq-tab');
        const searchInput = document.getElementById('faq-search');

        let currentCategory = null;
        let isSearching = false;

        function updateView() {
            const query = searchInput.value.toLowerCase().trim();

            faqs.forEach(faq => {
                const question = faq.querySelector('.faq-toggle').innerText.toLowerCase();
                const answer = faq.querySelector('.faq-content').innerText.toLowerCase();

                const matchesSearch = query === '' || question.includes(query) || answer.includes(query);
                const matchesCategory = query ? true : (!currentCategory || faq.dataset.category === currentCategory);

                const shouldShow = matchesSearch && matchesCategory;

                faq.style.display = shouldShow ? 'block' : 'none';

                // collapse if hidden
                if (!shouldShow) {
                    faq.querySelector('.faq-content').style.maxHeight = null;
                    faq.querySelector('.faq-icon').classList.remove('rotate-180');
                }
            });

            // placeholder
            document.getElementById('faq-placeholder').style.display =
                faqs.some(f => f.style.display === 'block') ? 'none' : 'block';

            // tab styling
            tabs.forEach(tab => {
                if (tab.dataset.tab === currentCategory) {
                    tab.classList.add('text-black', 'border-black');
                    tab.classList.remove('text-gray-500', 'border-transparent');
                } else {
                    tab.classList.remove('text-black', 'border-black');
                    tab.classList.add('text-gray-500', 'border-transparent');
                }
            });
        }

        // ---- TAB CLICK ----
        function setCategory(category) {
            currentCategory = category;
            updateView();
        }

        // ---- SEARCH ----
        searchInput.addEventListener('input', () => {
            updateView();
        });

        // ---- INIT ----
        faqs.forEach(faq => faq.style.display = 'none');

        // ---- PREVIEW GENERATION ----
        function populatePreviews() {
            const categories = {
                general: document.getElementById('preview-general'),
                owners: document.getElementById('preview-owners'),
                drivers: document.getElementById('preview-drivers'),
            };

            Object.keys(categories).forEach(cat => {
                const items = faqs.filter(f => f.dataset.category === cat).slice(0, 3);

                items.forEach(item => {
                    const q = item.querySelector('.faq-toggle').innerText;

                    const el = document.createElement('div');
                    el.className = "text-sm text-gray-700 cursor-pointer hover:text-blue-500";
                    el.innerText = q;

                    el.onclick = () => {
                        setCategory(cat);

                        setTimeout(() => {
                            item.querySelector('.faq-toggle').click();
                        }, 200);
                    };
                    categories[cat].appendChild(el);
                });
            });
        }

        // init
        populatePreviews();
    </script>

    <?php include_once __DIR__ . '/partials/footer.php'; ?>

</body>

</html>