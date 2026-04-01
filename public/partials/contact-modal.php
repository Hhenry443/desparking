<!-- CONTACT MODAL -->
<div id="contact-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6 relative">

        <button onclick="closeContactModal()"
            class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition cursor-pointer"
            aria-label="Close">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <h2 class="text-xl font-bold text-[#060745] mb-1">Contact Us</h2>
        <p class="text-sm text-gray-500 mb-5">Send us a message and we'll get back to you.</p>

        <div id="contact-success" class="hidden mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded-lg text-sm">
            Message sent! We'll be in touch soon.
        </div>
        <div id="contact-error" class="hidden mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded-lg text-sm"></div>

        <form id="contact-form" class="space-y-4" novalidate>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input type="text" name="name" required
                    class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-2 focus:ring-[#6ae6fc] focus:outline-none"
                    placeholder="John Smith">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" required
                    class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-2 focus:ring-[#6ae6fc] focus:outline-none"
                    placeholder="you@example.com">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                <textarea name="message" required rows="4"
                    class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-2 focus:ring-[#6ae6fc] focus:outline-none resize-none"
                    placeholder="How can we help?"></textarea>
            </div>
            <button type="submit" id="contact-submit"
                class="w-full bg-[#6ae6fc] hover:bg-[#5ad0e0] text-[#060745] font-bold py-2.5 rounded-lg transition cursor-pointer">
                Send Message
            </button>
        </form>
    </div>
</div>

<script>
    function closeContactModal() {
        document.getElementById('contact-modal').classList.add('hidden');
        document.getElementById('contact-form').reset();
        document.getElementById('contact-success').classList.add('hidden');
        document.getElementById('contact-error').classList.add('hidden');
    }

    document.getElementById('contact-modal').addEventListener('click', function(e) {
        if (e.target === this) closeContactModal();
    });

    document.getElementById('contact-form').addEventListener('submit', async function(e) {
        e.preventDefault();

        const btn = document.getElementById('contact-submit');
        const errorBox = document.getElementById('contact-error');
        const successBox = document.getElementById('contact-success');

        errorBox.classList.add('hidden');
        successBox.classList.add('hidden');
        btn.disabled = true;
        btn.textContent = 'Sending…';

        try {
            const res = await fetch('/php/api/index.php?id=contactEnquiry', {
                method: 'POST',
                body: new FormData(this),
            });
            const json = await res.json();

            if (res.ok) {
                this.reset();
                successBox.classList.remove('hidden');
            } else {
                errorBox.textContent = json.feedback || 'Something went wrong. Please try again.';
                errorBox.classList.remove('hidden');
            }
        } catch {
            errorBox.textContent = 'Network error. Please try again.';
            errorBox.classList.remove('hidden');
        }

        btn.disabled = false;
        btn.textContent = 'Send Message';
    });
</script>
