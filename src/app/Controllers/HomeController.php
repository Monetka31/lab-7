<?php
// app/Controllers/HomeController.php — контроллер главной страницы.

declare(strict_types=1);

namespace App\Controllers;

use App\Models\BookingRepository;
use App\Models\WorkspaceRepository;

final class HomeController
{
    // GET /
    public function index(): void
    {
        // TODO 3.1 (выполнено). Готовим данные для главной страницы и показываем шаблон app/Views/home.php.

        // 1) получаем все бронирования и все рабочие места через модели
        $bookings = (new BookingRepository())->all();
        $workspaces = (new WorkspaceRepository())->all();

        // 2) считаем бронирования по статусам циклом foreach
        $byStatus = ['new' => 0, 'confirmed' => 0, 'cancelled' => 0];
        foreach ($bookings as $booking) {
            $byStatus[$booking['status']]++;      // увеличиваем счётчик нужного статуса
        }

        // 3) передаём данные в шаблон функцией view()
        view('home', [
            'title'          => 'Главная',                  // заголовок страницы (используется в шапке)
            'total'          => count($bookings),
            'byStatus'       => $byStatus,
            'workspacesCount' => count($workspaces),
        ]);
    }
}
