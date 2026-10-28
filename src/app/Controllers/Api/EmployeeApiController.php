<?php
// app/Controllers/Api/EmployeeApiController.php — REST API ресурса «сотрудники» (/api/employees).

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\BookingRepository;
use App\Models\EmployeeRepository;

final class EmployeeApiController
{
    private EmployeeRepository $employees;
    private BookingRepository $bookings;

    public function __construct()
    {
        $this->employees = new EmployeeRepository();
        $this->bookings = new BookingRepository();
    }

    // GET /api/employees  или  GET /api/employees?department=it
    public function index(): void
    {
        $employees = $this->employees->all();
        $department = $_GET['department'] ?? '';
        if ($department !== '') {
            $employees = array_values(array_filter($employees, fn(array $e): bool => $e['department'] === $department));
        }
        json_response($employees);
    }

    // GET /api/employees/2
    public function show(string $id): void
    {
        $employee = $this->employees->find((int) $id);
        if ($employee === null) {
            json_error('Сотрудник не найден', 404);
        }
        json_response($employee);
    }

    // GET /api/employees/2/bookings — вложенный ресурс: бронирования сотрудника
    public function bookings(string $id): void
    {
        if ($this->employees->find((int) $id) === null) {
            json_error('Сотрудник не найден', 404);
        }
        json_response($this->bookings->byEmployee((int) $id));
    }

    // POST /api/employees
    public function store(): void
    {
        $data = json_input();
        $errors = $this->validate($data);
        if ($errors) {
            json_error('Ошибка проверки данных', 422, $errors);
        }

        $employee = $this->employees->add($this->clean($data));
        header('Location: /api/employees/' . $employee['id']);
        json_response($employee, 201);
    }

    // PUT или PATCH /api/employees/2
    public function update(string $id): void
    {
        $employee = $this->employees->find((int) $id);
        if ($employee === null) {
            json_error('Сотрудник не найден', 404);
        }

        $data = array_merge($employee, json_input());
        $errors = $this->validate($data);
        if ($errors) {
            json_error('Ошибка проверки данных', 422, $errors);
        }

        json_response($this->employees->update($employee['id'], $this->clean($data)));
    }

    // DELETE /api/employees/2 — сотрудника, у которого есть бронирования, удалять нельзя (409)
    public function destroy(string $id): void
    {
        if ($this->employees->find((int) $id) === null) {
            json_error('Сотрудник не найден', 404);
        }
        if ($this->bookings->byEmployee((int) $id)) {
            json_error('Нельзя удалить сотрудника: у него есть бронирования', 409);
        }
        $this->employees->delete((int) $id);
        no_content();
    }

    private function clean(array $data): array
    {
        return [
            'name'       => trim($data['name']),
            'email'      => trim($data['email']),
            'department' => $data['department'],
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];
        if (!is_string($data['name'] ?? null) || mb_strlen(trim($data['name'])) < 2) {
            $errors['name'] = 'Имя — не короче 2 символов';
        }
        if (!is_string($data['email'] ?? null) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Некорректный адрес электронной почты';
        }
        if (!in_array($data['department'] ?? null, ['it', 'sales', 'hr', 'finance'], true)) {
            $errors['department'] = 'Отдел: it, sales, hr или finance';
        }
        return $errors;
    }
}
