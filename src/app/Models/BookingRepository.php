<?php
// app/Models/BookingRepository.php — модель бронирований (файл storage/bookings.json).
// Операции all, find, add, update, delete унаследованы от JsonRepository.

declare(strict_types=1);

namespace App\Models;

final class BookingRepository extends JsonRepository
{
    protected string $name = 'bookings';                   // файл storage/bookings.json

    // Бронирования одного рабочего места
    public function byWorkspace(int $workspaceId): array
    {
        return array_values(array_filter(
            $this->all(),
            fn(array $booking): bool => $booking['workspace_id'] === $workspaceId
        ));
    }

    // Бронирования одного сотрудника
    public function byEmployee(int $employeeId): array
    {
        return array_values(array_filter(
            $this->all(),
            fn(array $booking): bool => $booking['employee_id'] === $employeeId
        ));
    }

    // Ищет бронирование, которое мешает занять место $workspaceId в день $date на время $slot.
    // Отменённые бронирования не мешают. $exceptId — своё бронирование (при изменении его не считаем).
    // Время пересекается, если интервалы одинаковые или хотя бы один из них «весь день».
    public function findConflict(int $workspaceId, string $date, string $slot, ?int $exceptId = null): ?array
    {
        foreach ($this->all() as $booking) {
            if ($booking['id'] === $exceptId || $booking['status'] === 'cancelled') {
                continue;
            }
            if ($booking['workspace_id'] !== $workspaceId || $booking['booking_date'] !== $date) {
                continue;
            }
            if ($booking['time_slot'] === $slot || $booking['time_slot'] === 'full_day' || $slot === 'full_day') {
                return $booking;
            }
        }
        return null;
    }
}
