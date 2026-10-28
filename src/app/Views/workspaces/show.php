<?php
// Шаблон просмотра рабочего места. Переменные: $workspace (место), $bookings (его бронирования), $employeeNames (ФИО по номеру)
$statusNames = ['new' => 'Ожидает подтверждения', 'confirmed' => 'Подтверждено', 'cancelled' => 'Отменено'];
?>
<p><a href="/workspaces">← Ко всем рабочим местам</a></p>

<div class="card">
  <h1><?= e($workspace['title']) ?></h1>
  <p><strong>Расположение:</strong> <?= e($workspace['location']) ?></p>
  <p><?= e($workspace['description']) ?></p>
</div>

<h2>Бронирования места (<?= count($bookings) ?>)</h2>

<?php if (count($bookings) === 0): ?>
  <p class="empty">Для этого места пока нет бронирований.</p>
<?php else: ?>
  <ul class="list">
    <?php foreach ($bookings as $booking): ?>
      <li>
        <a href="/bookings/<?= e($booking['id']) ?>"><?= e($employeeNames[$booking['employee_id']] ?? '—') ?></a>
        <span class="badge badge-<?= e($booking['status']) ?>"><?= e($statusNames[$booking['status']] ?? $booking['status']) ?></span>
      </li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>
