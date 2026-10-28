<?php
// app/Models/EmployeeRepository.php — модель сотрудников (файл storage/employees.json).

declare(strict_types=1);

namespace App\Models;

final class EmployeeRepository extends JsonRepository
{
    protected string $name = 'employees';
}
