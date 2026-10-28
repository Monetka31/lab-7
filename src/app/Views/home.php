<?php
// Шаблон главной страницы. Переменные приходят из HomeController::index():
//   $total (всего бронирований), $byStatus (массив «статус => количество»), $workspacesCount
?>
<h1>Главная</h1>

<div class="stats">
  <div class="card">
    <div class="stat-value"><?= e($total) ?></div>
    <div class="stat-label">всего бронирований</div>
  </div>
  <div class="card">
    <div class="stat-value"><?= e($byStatus['new']) ?></div>
    <div class="stat-label">ожидают подтверждения</div>
  </div>
  <div class="card">
    <div class="stat-value"><?= e($byStatus['confirmed']) ?></div>
    <div class="stat-label">подтверждено</div>
  </div>
  <div class="card">
    <div class="stat-value"><?= e($byStatus['cancelled']) ?></div>
    <div class="stat-label">отменено</div>
  </div>
</div>

<p>В системе <strong><?= e($workspacesCount) ?></strong> рабочих места. Перейдите к <a href="/bookings">списку бронирований</a> или <a href="/workspaces">рабочим местам</a>.</p>
