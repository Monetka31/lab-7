<?php
// Шаблон списка рабочих мест. Переменная: $workspaces
?>
<h1>Рабочие места</h1>

<div class="grid">
  <?php foreach ($workspaces as $workspace): ?>
    <div class="card">
      <h3><a href="/workspaces/<?= e($workspace['id']) ?>"><?= e($workspace['title']) ?></a></h3>
      <p><strong>Расположение:</strong> <?= e($workspace['location']) ?></p>
      <p><?= e($workspace['description']) ?></p>
    </div>
  <?php endforeach; ?>
</div>
