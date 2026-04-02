<?php
require_once __DIR__ . '/../includes/tool-access-middleware.php';
requireToolAccess('word-counter');
include __DIR__ . '/../includes/header.php';
?>
<main class="mx-auto max-w-5xl px-6 py-12 lg:px-8">
  <section class="rounded-2xl border border-blue-100 bg-white p-8 shadow-sm">
    <h1 class="text-3xl font-bold text-slate-900">Word Counter</h1>
    <p class="mt-2 text-sm text-slate-600">Count words, characters, and lines instantly as you type.</p>

    <textarea id="wcInput" rows="12" placeholder="Paste or type your text here..." class="mt-6 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 outline-none focus:border-primary-500 focus:ring-2 focus:ring-blue-100"></textarea>

    <div class="mt-6 grid gap-4 sm:grid-cols-3">
      <div class="rounded-xl bg-blue-50 p-4 text-center">
        <p class="text-xs uppercase tracking-wide text-slate-600">Words</p>
        <p id="wcWords" class="mt-1 text-2xl font-bold text-primary-700">0</p>
      </div>
      <div class="rounded-xl bg-blue-50 p-4 text-center">
        <p class="text-xs uppercase tracking-wide text-slate-600">Characters</p>
        <p id="wcChars" class="mt-1 text-2xl font-bold text-primary-700">0</p>
      </div>
      <div class="rounded-xl bg-blue-50 p-4 text-center">
        <p class="text-xs uppercase tracking-wide text-slate-600">Lines</p>
        <p id="wcLines" class="mt-1 text-2xl font-bold text-primary-700">0</p>
      </div>
    </div>
  </section>
</main>

<script>
  const wcInput = document.getElementById('wcInput');
  const wcWords = document.getElementById('wcWords');
  const wcChars = document.getElementById('wcChars');
  const wcLines = document.getElementById('wcLines');

  function updateWordCounter() {
    const text = wcInput.value;
    const words = text.trim() ? text.trim().split(/\s+/).length : 0;
    const chars = text.length;
    const lines = text === '' ? 0 : text.split(/\r\n|\r|\n/).length;

    wcWords.textContent = words;
    wcChars.textContent = chars;
    wcLines.textContent = lines;
  }

  wcInput.addEventListener('input', updateWordCounter);
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
