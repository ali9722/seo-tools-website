<?php
require_once __DIR__ . '/../includes/tool-access-middleware.php';
requireToolAccess('remove-extra-spaces');
include __DIR__ . '/../includes/header.php';
?>
<main class="mx-auto max-w-5xl px-6 py-12 lg:px-8">
  <section class="rounded-2xl border border-blue-100 bg-white p-8 shadow-sm">
    <h1 class="text-3xl font-bold text-slate-900">Remove Extra Spaces</h1>
    <p class="mt-2 text-sm text-slate-600">Clean up text by removing repeated spaces and trimming each line.</p>

    <textarea id="rsInput" rows="8" placeholder="Paste text with inconsistent spacing..." class="mt-6 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 outline-none focus:border-primary-500 focus:ring-2 focus:ring-blue-100"></textarea>

    <div class="mt-4 flex flex-wrap gap-3">
      <button id="rsClean" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700">Clean Text</button>
      <button id="rsCopy" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Copy</button>
    </div>

    <textarea id="rsOutput" rows="8" readonly placeholder="Cleaned output..." class="mt-5 w-full rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-slate-700"></textarea>
  </section>
</main>

<script>
  const rsInput = document.getElementById('rsInput');
  const rsClean = document.getElementById('rsClean');
  const rsCopy = document.getElementById('rsCopy');
  const rsOutput = document.getElementById('rsOutput');

  function cleanSpaces(text) {
    return text
      .split(/\r\n|\r|\n/)
      .map((line) => line.replace(/\s+/g, ' ').trim())
      .join('\n')
      .trim();
  }

  rsClean.addEventListener('click', () => {
    rsOutput.value = cleanSpaces(rsInput.value);
  });

  rsCopy.addEventListener('click', async () => {
    if (!rsOutput.value) return;
    await navigator.clipboard.writeText(rsOutput.value);
    rsCopy.textContent = 'Copied!';
    setTimeout(() => { rsCopy.textContent = 'Copy'; }, 1200);
  });
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
