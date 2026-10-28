<?php
// routes/web.php — таблица маршрутов приложения: «какой адрес — какой контроллер и метод».
// Переменная $router создаётся в public/index.php.

use App\Controllers\Api\BookingApiController;
use App\Controllers\Api\EmployeeApiController;
use App\Controllers\Api\WorkspaceApiController;
use App\Controllers\HomeController;
use App\Controllers\WorkspaceController;
use App\Controllers\BookingController;

// Маршруты сайта (из лабораторной работы №6).
// ВАЖНО: /bookings/create зарегистрирован РАНЬШЕ /bookings/{id}, иначе слово
// «create» было бы принято за значение {id}.

$router->get('/', [HomeController::class, 'index']);

$router->get('/bookings', [BookingController::class, 'index']);
$router->get('/bookings/create', [BookingController::class, 'create']);
$router->post('/bookings', [BookingController::class, 'store']);
$router->get('/bookings/{id}', [BookingController::class, 'show']);

$router->get('/workspaces', [WorkspaceController::class, 'index']);
$router->get('/workspaces/{id}', [WorkspaceController::class, 'show']);

// ---------- REST API (все адреса начинаются с /api, ответы в формате JSON) ----------
// PUT и PATCH направляются в один и тот же метод update().
// Вложенные адреса (/api/workspaces/{id}/bookings) можно писать в любом порядке:
// шаблон {id} не захватывает символ «/».

$router->get('/api/bookings', [BookingApiController::class, 'index']);
$router->post('/api/bookings', [BookingApiController::class, 'store']);
$router->get('/api/bookings/{id}', [BookingApiController::class, 'show']);
$router->put('/api/bookings/{id}', [BookingApiController::class, 'update']);
$router->patch('/api/bookings/{id}', [BookingApiController::class, 'update']);
$router->delete('/api/bookings/{id}', [BookingApiController::class, 'destroy']);

$router->get('/api/workspaces', [WorkspaceApiController::class, 'index']);
$router->post('/api/workspaces', [WorkspaceApiController::class, 'store']);
$router->get('/api/workspaces/{id}', [WorkspaceApiController::class, 'show']);
$router->put('/api/workspaces/{id}', [WorkspaceApiController::class, 'update']);
$router->patch('/api/workspaces/{id}', [WorkspaceApiController::class, 'update']);
$router->delete('/api/workspaces/{id}', [WorkspaceApiController::class, 'destroy']);
$router->get('/api/workspaces/{id}/bookings', [WorkspaceApiController::class, 'bookings']);

$router->get('/api/employees', [EmployeeApiController::class, 'index']);
$router->post('/api/employees', [EmployeeApiController::class, 'store']);
$router->get('/api/employees/{id}', [EmployeeApiController::class, 'show']);
$router->put('/api/employees/{id}', [EmployeeApiController::class, 'update']);
$router->patch('/api/employees/{id}', [EmployeeApiController::class, 'update']);
$router->delete('/api/employees/{id}', [EmployeeApiController::class, 'destroy']);
$router->get('/api/employees/{id}/bookings', [EmployeeApiController::class, 'bookings']);
