<?php
// app/Controllers/BookingController.php — контроллер бронирований: список, просмотр, создание.

declare(strict_types=1);

namespace App\Controllers;

use App\Models\BookingRepository;
use App\Models\EmployeeRepository;
use App\Models\WorkspaceRepository;

final class BookingController
{
    private BookingRepository $bookings;
    private WorkspaceRepository $workspaces;
    private EmployeeRepository $employees;

    // Конструктор вызывается при создании объекта: подготавливаем модели для всех методов
    public function __construct()
    {
        $this->bookings = new BookingRepository();
        $this->workspaces = new WorkspaceRepository();
        $this->employees = new EmployeeRepository();
    }

    // GET /bookings  или  GET /bookings?status=confirmed
    public function index(): void
    {
        // TODO 3.2 (выполнено). Список бронирований с необязательным фильтром по статусу.
        $status = $_GET['status'] ?? '';     // параметр из адреса после «?»; если его нет — пустая строка
        $bookings = $this->bookings->all();
        if ($status !== '') {                // фильтр по статусу
            $bookings = array_values(array_filter($bookings, fn(array $b): bool => $b['status'] === $status));
        }
        view('bookings/index', [
            'title'         => 'Бронирования',
            'bookings'      => $bookings,
            'status'        => $status,
            'employeeNames' => array_column($this->employees->all(), 'name', 'id'),   // «номер => ФИО»
        ]);
    }

    // GET /bookings/5   (значение {id} из маршрута приходит в параметр $id)
    public function show(string $id): void
    {
        // TODO 3.3 (выполнено). Показываем одно бронирование; если его нет — 404.
        $booking = $this->bookings->find((int) $id);    // (int) превращает строку '5' в число 5
        if ($booking === null) {
            notFound();                                  // бронирования нет: готовая функция покажет страницу 404
        }
        view('bookings/show', [
            'title'     => 'Бронирование №' . $booking['id'],
            'booking'   => $booking,
            'workspace' => $this->workspaces->find($booking['workspace_id']),
            'employee'  => $this->employees->find($booking['employee_id']),
        ]);
    }

    // GET /bookings/create — форма создания (образец: разберите, как устроен метод)
    public function create(): void
    {
        view('bookings/create', [
            'title'      => 'Новое бронирование',
            'workspaces' => $this->workspaces->all(),
            'employees'  => $this->employees->all(),
            'errors'     => [],
            'old'        => [],
        ]);
    }

    // POST /bookings — обработка формы (образец обработки формы; комментарии объясняют, что происходит)
    public function store(): void
    {
        // Данные формы приходят в массиве $_POST
        $employeeId = (int) ($_POST['employee_id'] ?? 0);
        $workspaceId = (int) ($_POST['workspace_id'] ?? 0);
        $timeSlot = $_POST['time_slot'] ?? 'full_day';
        $bookingDate = $_POST['booking_date'] ?? '';

        // Проверка данных на сервере (клиентской проверке доверять нельзя)
        $errors = [];
        if ($this->employees->find($employeeId) === null) {
            $errors['employee_id'] = 'Выберите сотрудника из списка';
        }
        if ($this->workspaces->find($workspaceId) === null) {
            $errors['workspace_id'] = 'Выберите рабочее место из списка';
        }
        if (!in_array($timeSlot, ['morning', 'afternoon', 'full_day'], true)) {
            $errors['time_slot'] = 'Недопустимый интервал времени';
        }
        if ($bookingDate === '') {
            $errors['booking_date'] = 'Укажите дату бронирования';
        }
        // Место не должно быть занято на это же время
        if (!$errors && $this->bookings->findConflict($workspaceId, $bookingDate, $timeSlot) !== null) {
            $errors['booking_date'] = 'Это рабочее место уже занято в выбранное время';
        }

        if ($errors) {
            http_response_code(422);              // 422: данные не прошли проверку
            view('bookings/create', [
                'title'      => 'Новое бронирование',
                'workspaces' => $this->workspaces->all(),
                'employees'  => $this->employees->all(),
                'errors'     => $errors,
                'old'        => $_POST,           // вернуть в форму то, что ввёл пользователь
            ]);
            return;
        }

        $this->bookings->add([
            'workspace_id'  => $workspaceId,
            'employee_id'   => $employeeId,
            'status'        => 'new',
            'time_slot'     => $timeSlot,
            'booking_date'  => $bookingDate,
        ]);

        redirect('/bookings');                    // после успешной отправки формы — переход на список
    }
}
