<?php
// app/Models/WorkspaceRepository.php — модель рабочих мест (файл storage/workspaces.json).

declare(strict_types=1);

namespace App\Models;

final class WorkspaceRepository extends JsonRepository
{
    protected string $name = 'workspaces';
}
