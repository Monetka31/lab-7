<?php
// app/Controllers/WorkspaceController.php — контроллер рабочих мест: список и просмотр места с его
// бронированиями. Написан самостоятельно по образцу BookingController (методы index и show устроены так же).

declare(strict_types=1);

namespace App\Controllers;

use App\Models\BookingRepository;
use App\Models\EmployeeRepository;
use App\Models\WorkspaceRepository;

final class WorkspaceController
{
    // GET /workspaces
    public function index(): void
    {
        // TODO 3.4 (выполнено). Список рабочих мест.
        $workspaces = (new WorkspaceRepository())->all();
        view('workspaces/index', ['title' => 'Рабочие места', 'workspaces' => $workspaces]);
    }

    // GET /workspaces/2
    public function show(string $id): void
    {
        // TODO 3.5 (выполнено). Рабочее место и список его бронирований.
        $workspace = (new WorkspaceRepository())->find((int) $id);   // 1) находим рабочее место
        if ($workspace === null) {
            notFound();                                                // 2) рабочего места нет — страница 404
        }
        $bookings = (new BookingRepository())->byWorkspace($workspace['id']); // 3) его бронирования

        view('workspaces/show', [                                    // 4) передаём данные в шаблон
            'title'     => $workspace['title'],
            'workspace' => $workspace,
            'bookings'  => $bookings,
            'employeeNames' => array_column((new EmployeeRepository())->all(), 'name', 'id'),
        ]);
    }
}
