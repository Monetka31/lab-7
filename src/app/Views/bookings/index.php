<?php
// Шаблон списка бронирований. Переменные: $bookings (бронирования), $status (выбранный фильтр), $employeeNames (ФИО по номеру)
$statusNames = ['new' => 'Ожидает подтверждения', 'confirmed' => 'Подтверждено', 'cancelled' => 'Отменено'];
$timeSlotNames = ['morning' => 'Утро', 'afternoon' => 'День', 'full_day' => 'Весь день'];
?>
<h1>Бронирования</h1>

<p class="filters">
  Показать:
  <a href="/bookings" class="<?= $status === '' ? 'active' : '' ?>">все</a>
  <?php foreach ($statusNames as $code => $name): ?>
    <a href="/bookings?status=<?= e($code) ?>" class="<?= $status === $code ? 'active' : '' ?>"><?= e($name) ?></a>
  <?php endforeach; ?>
</p>

<?php if (count($bookings) === 0): ?>
  <p class="empty">Бронирования не найдены.</p>
<?php else: ?>
  <table>
    <thead>
      <tr><th>№</th><th>Сотрудник</th><th>Статус</th><th>Время</th><th>Дата</th></tr>
    </thead>
    <tbody>
      <?php foreach ($bookings as $booking): ?>
        <tr>
          <td><?= e($booking['id']) ?></td>
          <td><a href="/bookings/<?= e($booking['id']) ?>"><?= e($employeeNames[$booking['employee_id']] ?? '—') ?></a></td>
          <td><span class="badge badge-<?= e($booking['status']) ?>"><?= e($statusNames[$booking['status']] ?? $booking['status']) ?></span></td>
          <td><?= e($timeSlotNames[$booking['time_slot']] ?? $booking['time_slot']) ?></td>
          <td><?= e($booking['booking_date'] ?? '—') ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
