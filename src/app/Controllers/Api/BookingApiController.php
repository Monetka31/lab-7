<?php
// app/Controllers/Api/BookingApiController.php — REST API ресурса «бронирования» (/api/bookings).

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\BookingRepository;
use App\Models\EmployeeRepository;
use App\Models\WorkspaceRepository;

final class BookingApiController
{
    private BookingRepository $bookings;
    private WorkspaceRepository $workspaces;
    private EmployeeRepository $employees;

    public function __construct()
    {
        $this->bookings = new BookingRepository();
        $this->workspaces = new WorkspaceRepository();
        $this->employees = new EmployeeRepository();
    }

    // GET /api/bookings  или  GET /api/bookings?status=confirmed&workspace_id=2&employee_id=1&date=2026-10-12
    public function index(): void
    {
        $bookings = $this->bookings->all();

        $status = $_GET['status'] ?? '';
        if ($status !== '') {
            $bookings = array_values(array_filter($bookings, fn(array $b): bool => $b['status'] === $status));
        }
        $workspaceId = $_GET['workspace_id'] ?? '';
        if ($workspaceId !== '') {
            $bookings = array_values(array_filter($bookings, fn(array $b): bool => $b['workspace_id'] === (int) $workspaceId));
        }
        $employeeId = $_GET['employee_id'] ?? '';
        if ($employeeId !== '') {
            $bookings = array_values(array_filter($bookings, fn(array $b): bool => $b['employee_id'] === (int) $employeeId));
        }
        $date = $_GET['date'] ?? '';
        if ($date !== '') {
            $bookings = array_values(array_filter($bookings, fn(array $b): bool => $b['booking_date'] === $date));
        }

        json_response($bookings);
    }

    // GET /api/bookings/5
    public function show(string $id): void
    {
        $booking = $this->bookings->find((int) $id);
        if ($booking === null) {
            json_error('Бронирование не найдено', 404);
        }
        json_response($booking);
    }

    // POST /api/bookings
    public function store(): void
    {
        $data = json_input();
        $data += ['status' => 'new', 'time_slot' => 'full_day'];   // значения по умолчанию
        $errors = $this->validate($data);
        if ($errors) {
            json_error('Ошибка проверки данных', 422, $errors);
        }

        $new = $this->clean($data);
        $this->checkFree($new);                                    // место должно быть свободно (иначе 409)

        $booking = $this->bookings->add($new);
        header('Location: /api/bookings/' . $booking['id']);
        json_response($booking, 201);
    }

    // PUT или PATCH /api/bookings/5
    public function update(string $id): void
    {
        $booking = $this->bookings->find((int) $id);
        if ($booking === null) {
            json_error('Бронирование не найдено', 404);
        }

        $data = array_merge($booking, json_input());               // старые значения + присланные
        $errors = $this->validate($data);
        if ($errors) {
            json_error('Ошибка проверки данных', 422, $errors);
        }

        $new = $this->clean($data);
        $this->checkFree($new, $booking['id']);                    // своё бронирование не считается помехой

        json_response($this->bookings->update($booking['id'], $new));
    }

    // DELETE /api/bookings/5
    public function destroy(string $id): void
    {
        if (!$this->bookings->delete((int) $id)) {
            json_error('Бронирование не найдено', 404);
        }
        no_content();
    }

    // Если место уже занято в это время — ответ 409 Conflict (отменённые бронирования не мешают)
    private function checkFree(array $booking, ?int $exceptId = null): void
    {
        if ($booking['status'] === 'cancelled') {
            return;
        }
        $other = $this->bookings->findConflict($booking['workspace_id'], $booking['booking_date'], $booking['time_slot'], $exceptId);
        if ($other !== null) {
            json_error('Рабочее место уже занято в это время (бронирование №' . $other['id'] . ')', 409);
        }
    }

    // Оставляет только известные поля (лишние, присланные клиентом, отбрасываются)
    private function clean(array $data): array
    {
        return [
            'workspace_id' => (int) $data['workspace_id'],
            'employee_id'  => (int) $data['employee_id'],
            'status'       => $data['status'],
            'time_slot'    => $data['time_slot'],
            'booking_date' => $data['booking_date'],
        ];
    }

    // Проверка данных: массив ошибок «поле => сообщение»
    private function validate(array $data): array
    {
        $errors = [];
        if (!is_int($data['workspace_id'] ?? null) || $this->workspaces->find($data['workspace_id']) === null) {
            $errors['workspace_id'] = 'Рабочего места с таким номером нет';
        }
        if (!is_int($data['employee_id'] ?? null) || $this->employees->find($data['employee_id']) === null) {
            $errors['employee_id'] = 'Сотрудника с таким номером нет';
        }
        if (!in_array($data['status'] ?? null, ['new', 'confirmed', 'cancelled'], true)) {
            $errors['status'] = 'Статус: new, confirmed или cancelled';
        }
        if (!in_array($data['time_slot'] ?? null, ['morning', 'afternoon', 'full_day'], true)) {
            $errors['time_slot'] = 'Время: morning, afternoon или full_day';
        }
        $date = $data['booking_date'] ?? null;
        $dateOk = is_string($date)
            && preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $m)
            && checkdate((int) $m[2], (int) $m[3], (int) $m[1]);
        if (!$dateOk) {
            $errors['booking_date'] = 'Дата в формате ГГГГ-ММ-ДД';
        }
        return $errors;
    }
}
