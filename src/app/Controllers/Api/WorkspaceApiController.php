<?php
// app/Controllers/Api/WorkspaceApiController.php — REST API ресурса «рабочие места» (/api/workspaces).

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\BookingRepository;
use App\Models\WorkspaceRepository;

final class WorkspaceApiController
{
    private WorkspaceRepository $workspaces;
    private BookingRepository $bookings;

    public function __construct()
    {
        $this->workspaces = new WorkspaceRepository();
        $this->bookings = new BookingRepository();
    }

    // GET /api/workspaces
    public function index(): void
    {
        json_response($this->workspaces->all());
    }

    // GET /api/workspaces/2
    public function show(string $id): void
    {
        $workspace = $this->workspaces->find((int) $id);
        if ($workspace === null) {
            json_error('Рабочее место не найдено', 404);
        }
        json_response($workspace);
    }

    // GET /api/workspaces/2/bookings — вложенный ресурс: бронирования рабочего места
    public function bookings(string $id): void
    {
        if ($this->workspaces->find((int) $id) === null) {
            json_error('Рабочее место не найдено', 404);
        }
        json_response($this->bookings->byWorkspace((int) $id));
    }

    // POST /api/workspaces
    public function store(): void
    {
        $data = json_input();
        $errors = $this->validate($data);
        if ($errors) {
            json_error('Ошибка проверки данных', 422, $errors);
        }

        $workspace = $this->workspaces->add($this->clean($data));
        header('Location: /api/workspaces/' . $workspace['id']);
        json_response($workspace, 201);
    }

    // PUT или PATCH /api/workspaces/2
    public function update(string $id): void
    {
        $workspace = $this->workspaces->find((int) $id);
        if ($workspace === null) {
            json_error('Рабочее место не найдено', 404);
        }

        $data = array_merge($workspace, json_input());
        $errors = $this->validate($data);
        if ($errors) {
            json_error('Ошибка проверки данных', 422, $errors);
        }

        json_response($this->workspaces->update($workspace['id'], $this->clean($data)));
    }

    // DELETE /api/workspaces/2 — место, у которого есть бронирования, удалять нельзя (409)
    public function destroy(string $id): void
    {
        if ($this->workspaces->find((int) $id) === null) {
            json_error('Рабочее место не найдено', 404);
        }
        if ($this->bookings->byWorkspace((int) $id)) {
            json_error('Нельзя удалить рабочее место: на него есть бронирования', 409);
        }
        $this->workspaces->delete((int) $id);
        no_content();
    }

    private function clean(array $data): array
    {
        return [
            'title'       => trim($data['title']),
            'location'    => trim($data['location']),
            'description' => trim((string) ($data['description'] ?? '')),
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];
        if (!is_string($data['title'] ?? null) || mb_strlen(trim($data['title'])) < 3) {
            $errors['title'] = 'Название — не короче 3 символов';
        }
        if (!is_string($data['location'] ?? null) || mb_strlen(trim($data['location'])) < 2) {
            $errors['location'] = 'Расположение — не короче 2 символов';
        }
        if (isset($data['description']) && !is_string($data['description'])) {
            $errors['description'] = 'Описание должно быть строкой';
        }
        return $errors;
    }
}
