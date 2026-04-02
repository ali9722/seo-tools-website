<?php
require_once __DIR__ . '/../includes/tool-access-middleware.php';
requireToolAccess('reverse-text');
include __DIR__ . '/../includes/header.php';
?>
<main class="mx-auto max-w-5xl px-6 py-12 lg:px-8">
  <section class="rounded-2xl border border-blue-100 bg-white p-8 shadow-sm">
    <h1 class="text-3xl font-bold text-slate-900">Reverse Text</h1>
    <p class="mt-2 text-sm text-slate-600">Reverse text, words, or line order live without page reload.</p>

    <textarea id="rtInput" rows="8" placeholder="Enter text to reverse..." class="mt-6 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 outline-none focus:border-primary-500 focus:ring-2 focus:ring-blue-100"></textarea>

    <div class="mt-4 flex flex-wrap gap-3">
      <button data-reverse="chars" class="rtBtn rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700">Reverse Characters</button>
      <button data-reverse="words" class="rtBtn rounded-lg bg-slate-700 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Reverse Words</button>
      <button data-reverse="lines" class="rtBtn rounded-lg bg-blue-100 px-4 py-2 text-sm font-semibold text-primary-700 hover:bg-blue-200">Reverse Lines</button>
    </div>

    <textarea id="rtOutput" rows="8" readonly placeholder="Reversed result..." class="mt-5 w-full rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-slate-700"></textarea>
  </section>
</main>

<script>
  const rtInput = document.getElementById('rtInput');
  const rtOutput = document.getElementById('rtOutput');
  const rtButtons = document.querySelectorAll('.rtBtn');

  function reverseChars(text) {
    return [...text].reverse().join('');
  }

  function reverseWords(text) {
    return text.split(/\s+/).filter(Boolean).reverse().join(' ');
  }

  function reverseLines(text) {
    return text.split(/\r\n|\r|\n/).reverse().join('\n');
  }

  rtButtons.forEach((button) => {
    button.addEventListener('click', () => {
      const type = button.getAttribute('data-reverse');
      const input = rtInput.value;
      if (type === 'chars') rtOutput.value = reverseChars(input);
      if (type === 'words') rtOutput.value = reverseWords(input);
      if (type === 'lines') rtOutput.value = reverseLines(input);
    });
  });
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
