<?php
// Шаблон просмотра одного бронирования. Переменные: $booking (бронирование), $workspace (его рабочее место), $employee (сотрудник)
$statusNames = ['new' => 'Ожидает подтверждения', 'confirmed' => 'Подтверждено', 'cancelled' => 'Отменено'];
$timeSlotNames = ['morning' => 'Утро', 'afternoon' => 'День', 'full_day' => 'Весь день'];
?>
<p><a href="/bookings">← К списку бронирований</a></p>

<div class="card">
  <h1>Бронирование №<?= e($booking['id']) ?></h1>
  <dl class="meta">
    <dt>Сотрудник</dt>       <dd><?= e($employee['name'] ?? '—') ?></dd>
    <dt>Рабочее место</dt>   <dd><a href="/workspaces/<?= e($workspace['id']) ?>"><?= e($workspace['title']) ?></a></dd>
    <dt>Статус</dt>          <dd><span class="badge badge-<?= e($booking['status']) ?>"><?= e($statusNames[$booking['status']] ?? $booking['status']) ?></span></dd>
    <dt>Время</dt>           <dd><?= e($timeSlotNames[$booking['time_slot']] ?? $booking['time_slot']) ?></dd>
    <dt>Дата</dt>            <dd><?= e($booking['booking_date'] ?? '—') ?></dd>
  </dl>
</div>
