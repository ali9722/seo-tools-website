<?php
require_once __DIR__ . '/../includes/tool-access-middleware.php';
requireToolAccess('lorem-ipsum-generator');
include __DIR__ . '/../includes/header.php';
?>
<main class="mx-auto max-w-5xl px-6 py-12 lg:px-8">
  <section class="rounded-2xl border border-blue-100 bg-white p-8 shadow-sm">
    <h1 class="text-3xl font-bold text-slate-900">Lorem Ipsum Generator</h1>
    <p class="mt-2 text-sm text-slate-600">Generate placeholder paragraphs instantly with JavaScript.</p>

    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-end">
      <div>
        <label for="liCount" class="mb-2 block text-sm font-medium text-slate-700">Paragraphs</label>
        <input id="liCount" type="number" min="1" max="20" value="3" class="w-40 rounded-xl border border-slate-200 px-4 py-2 text-sm outline-none focus:border-primary-500 focus:ring-2 focus:ring-blue-100">
      </div>
      <button id="liGenerate" class="rounded-lg bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-700">Generate</button>
      <button id="liCopy" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100">Copy</button>
    </div>

    <textarea id="liOutput" rows="12" class="mt-5 w-full rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-slate-700" placeholder="Generated lorem ipsum will appear here..."></textarea>
  </section>
</main>

<script>
  const liSource = 'Lorem ipsum dolor sit amet consectetur adipiscing elit sed do eiusmod tempor incididunt ut labore et dolore magna aliqua';
  const liWords = liSource.split(' ');

  const liCount = document.getElementById('liCount');
  const liGenerate = document.getElementById('liGenerate');
  const liCopy = document.getElementById('liCopy');
  const liOutput = document.getElementById('liOutput');

  function randomSentence(minWords = 8, maxWords = 16) {
    const length = Math.floor(Math.random() * (maxWords - minWords + 1)) + minWords;
    const words = [];

    for (let i = 0; i < length; i++) {
      words.push(liWords[Math.floor(Math.random() * liWords.length)]);
    }

    const sentence = words.join(' ');
    return sentence.charAt(0).toUpperCase() + sentence.slice(1) + '.';
  }

  function buildParagraph() {
    const sentenceCount = Math.floor(Math.random() * 3) + 4;
    const sentences = [];

    for (let i = 0; i < sentenceCount; i++) {
      sentences.push(randomSentence());
    }

    return sentences.join(' ');
  }

  function generateLorem() {
    const count = Math.max(1, Math.min(20, Number(liCount.value) || 1));
    const paragraphs = [];

    for (let i = 0; i < count; i++) {
      paragraphs.push(buildParagraph());
    }

    liOutput.value = paragraphs.join('\n\n');
  }

  liGenerate.addEventListener('click', generateLorem);
  liCopy.addEventListener('click', async () => {
    if (!liOutput.value) return;
    await navigator.clipboard.writeText(liOutput.value);
    liCopy.textContent = 'Copied!';
    setTimeout(() => { liCopy.textContent = 'Copy'; }, 1200);
  });

  generateLorem();
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
