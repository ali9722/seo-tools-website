<?php
require_once __DIR__ . '/../includes/tool-access-middleware.php';
requireToolAccess('case-converter');
include __DIR__ . '/../includes/header.php';
?>
<main class="mx-auto max-w-5xl px-6 py-12 lg:px-8">
  <section class="rounded-2xl border border-blue-100 bg-white p-8 shadow-sm">
    <h1 class="text-3xl font-bold text-slate-900">Case Converter</h1>
    <p class="mt-2 text-sm text-slate-600">Convert text to uppercase, lowercase, or title case instantly.</p>

    <textarea id="ccInput" rows="10" placeholder="Type your text..." class="mt-6 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 outline-none focus:border-primary-500 focus:ring-2 focus:ring-blue-100"></textarea>

    <div class="mt-5 flex flex-wrap gap-3">
      <button data-mode="upper" class="ccBtn rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700">UPPERCASE</button>
      <button data-mode="lower" class="ccBtn rounded-lg bg-slate-700 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">lowercase</button>
      <button data-mode="title" class="ccBtn rounded-lg bg-blue-100 px-4 py-2 text-sm font-semibold text-primary-700 hover:bg-blue-200">Title Case</button>
      <button id="ccCopy" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Copy</button>
    </div>
  </section>
</main>

<script>
  const ccInput = document.getElementById('ccInput');
  const ccButtons = document.querySelectorAll('.ccBtn');
  const ccCopy = document.getElementById('ccCopy');

  function toTitleCase(text) {
    return text.toLowerCase().replace(/\b\w/g, (ch) => ch.toUpperCase());
  }

  ccButtons.forEach((button) => {
    button.addEventListener('click', () => {
      const mode = button.getAttribute('data-mode');
      const value = ccInput.value;

      if (mode === 'upper') ccInput.value = value.toUpperCase();
      if (mode === 'lower') ccInput.value = value.toLowerCase();
      if (mode === 'title') ccInput.value = toTitleCase(value);
    });
  });

  ccCopy.addEventListener('click', async () => {
    if (!ccInput.value) return;
    await navigator.clipboard.writeText(ccInput.value);
    ccCopy.textContent = 'Copied!';
    setTimeout(() => { ccCopy.textContent = 'Copy'; }, 1200);
  });
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
