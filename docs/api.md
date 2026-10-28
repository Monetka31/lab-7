# Документация REST API — система бронирования рабочих мест
## Общее

- Базовый адрес при запуске: `http://localhost:8000`. Все адреса API начинаются с `/api`.
- Данные передаются и возвращаются в формате JSON (`Content-Type: application/json`). В запросах с телом (POST, PUT, PATCH) нужно присылать JSON-объект.
- Номера записей (`id`) выдаёт сервер, прислать их в теле нельзя: лишние поля игнорируются.
- Если адрес начинается не с `/api` — это обычные HTML-страницы сайта.

### Формат ошибки

```json
{ "error": "Текст ошибки", "errors": { "поле": "что не так" } }
```

Поле `errors` есть только у ответа 422.

### Коды ответов

| Код | Когда |
|---|---|
| 200 OK | запрос выполнен (чтение, изменение) |
| 201 Created | запись создана; в заголовке `Location` — адрес новой записи |
| 204 No Content | запись удалена; тела в ответе нет |
| 400 Bad Request | в теле запроса не JSON-объект |
| 404 Not Found | записи или адреса нет |
| 405 Method Not Allowed | у адреса нет такого метода; в заголовке `Allow` — список допустимых |
| 409 Conflict | запрос понятен, но противоречит данным (место занято; нельзя удалить запись, на которую есть ссылки) |
| 422 Unprocessable Entity | данные не прошли проверку |
| 500 Internal Server Error | ошибка в самом сервере |

## Сводная таблица конечных точек

| № | Метод | URL | Назначение | Успех |
|---|---|---|---|---|
| 1 | GET | `/api/bookings` | Список бронирований | 200 |
| 2 | GET | `/api/bookings/{id}` | Одно бронирование | 200 |
| 3 | POST | `/api/bookings` | Создать бронирование | 201 |
| 4 | PUT, PATCH | `/api/bookings/{id}` | Изменить бронирование | 200 |
| 5 | DELETE | `/api/bookings/{id}` | Удалить бронирование | 204 |
| 6 | GET | `/api/workspaces` | Список рабочих мест | 200 |
| 7 | GET | `/api/workspaces/{id}` | Одно рабочее место | 200 |
| 8 | GET | `/api/workspaces/{id}/bookings` | Бронирования рабочего места | 200 |
| 9 | POST | `/api/workspaces` | Создать рабочее место | 201 |
| 10 | PUT, PATCH | `/api/workspaces/{id}` | Изменить рабочее место | 200 |
| 11 | DELETE | `/api/workspaces/{id}` | Удалить рабочее место | 204 |
| 12 | GET | `/api/employees` | Список сотрудников | 200 |
| 13 | GET | `/api/employees/{id}` | Один сотрудник | 200 |
| 14 | GET | `/api/employees/{id}/bookings` | Бронирования сотрудника | 200 |
| 15 | POST | `/api/employees` | Создать сотрудника | 201 |
| 16 | PUT, PATCH | `/api/employees/{id}` | Изменить сотрудника | 200 |
| 17 | DELETE | `/api/employees/{id}` | Удалить сотрудника | 204 |

Всего — 17 конечных точек: 5 у бронирований, 6 у рабочих мест, 6 у сотрудников. PUT и PATCH направлены в один обработчик, поэтому описаны вместе.

## Ресурсы и их поля

**Бронирование** (`/api/bookings`)

| Поле | Тип | Обязательное | Описание |
|---|---|---|---|
| id | number | — | номер, выдаёт сервер |
| workspace_id | number | да | номер существующего рабочего места |
| employee_id | number | да | номер существующего сотрудника |
| status | string | нет | `new` (по умолчанию), `confirmed`, `cancelled` |
| time_slot | string | нет | `full_day` (по умолчанию), `morning`, `afternoon` |
| booking_date | string | да | дата в формате ГГГГ-ММ-ДД |

Правило занятости: одно рабочее место нельзя забронировать на одну дату и пересекающееся время. Интервалы пересекаются, если они одинаковые или один из них `full_day`. Отменённые (`cancelled`) бронирования не мешают.

**Рабочее место** (`/api/workspaces`)

| Поле | Тип | Обязательное | Описание |
|---|---|---|---|
| id | number | — | номер, выдаёт сервер |
| title | string | да | название, не короче 3 символов |
| location | string | да | расположение (этаж, зона), не короче 2 символов |
| description | string | нет | характеристики (монитор, проектор и т. п.) |

**Сотрудник** (`/api/employees`)

| Поле | Тип | Обязательное | Описание |
|---|---|---|---|
| id | number | — | номер, выдаёт сервер |
| name | string | да | ФИО, не короче 2 символов |
| email | string | да | адрес электронной почты |
| department | string | да | отдел: `it`, `sales`, `hr` или `finance` |

---

## Бронирования

### GET /api/bookings — список бронирований

**Назначение:** Возвращает все бронирования. Можно отфильтровать.

Параметры строки запроса (все необязательные):

| Параметр | Описание |
|---|---|
| `status` | `new`, `confirmed` или `cancelled` |
| `workspace_id` | номер рабочего места |
| `employee_id` | номер сотрудника |
| `date` | дата в формате ГГГГ-ММ-ДД |

**Пример запроса:**

```http
GET /api/bookings?status=confirmed
```

**Пример ответа (200 OK):**

```http
[
  {
    "id": 1,
    "workspace_id": 1,
    "employee_id": 1,
    "status": "confirmed",
    "time_slot": "morning",
    "booking_date": "2026-10-01"
  },
  {
    "id": 4,
    "workspace_id": 2,
    "employee_id": 4,
    "status": "confirmed",
    "time_slot": "full_day",
    "booking_date": "2026-09-28"
  },
  {
    "id": 6,
    "workspace_id": 2,
    "employee_id": 6,
    "status": "confirmed",
    "time_slot": "afternoon",
    "booking_date": "2026-10-25"
  }
]
```

**Ошибки:** Своих ошибок нет (если ничего не найдено — вернётся пустой список `[]`).

### GET /api/bookings/{id} — одно бронирование

**Назначение:** Возвращает бронирование по номеру.

Параметр адреса: `id` — номер бронирования.

**Пример запроса:**

```http
GET /api/bookings/3
```

**Пример ответа (200 OK):**

```http
{
  "id": 3,
  "workspace_id": 1,
  "employee_id": 3,
  "status": "cancelled",
  "time_slot": "full_day",
  "booking_date": "2026-10-15"
}
```

**Ошибки:** 404 — бронирования с таким номером нет.

### POST /api/bookings — создать бронирование

**Назначение:** Создаёт новое бронирование. Номер выдаёт сервер.

Параметры тела (JSON):

| Поле | Тип | Обязательное | Описание |
|---|---|---|---|
| workspace_id | number | да | номер существующего рабочего места |
| employee_id | number | да | номер существующего сотрудника |
| status | string | нет | `new` (по умолчанию), `confirmed`, `cancelled` |
| time_slot | string | нет | `full_day` (по умолчанию), `morning`, `afternoon` |
| booking_date | string | да | дата в формате ГГГГ-ММ-ДД |

**Пример запроса:**

```http
POST /api/bookings
Content-Type: application/json

{
  "workspace_id": 4,
  "employee_id": 2,
  "booking_date": "2026-11-02"
}
```

**Пример ответа (201 Created):**

```http
Location: /api/bookings/8

{
  "id": 8,
  "workspace_id": 4,
  "employee_id": 2,
  "status": "new",
  "time_slot": "full_day",
  "booking_date": "2026-11-02"
}
```

**Ошибки:** 400 — тело не JSON; 422 — данные не прошли проверку; 409 — рабочее место уже занято в это время (отменённые бронирования не мешают).

### PUT, PATCH /api/bookings/{id} — изменить бронирование

**Назначение:** Меняет бронирование. Присланные поля заменяют старые, остальные остаются прежними (PUT и PATCH работают одинаково).

Параметр адреса: `id` — номер бронирования. Тело — те же поля, что при создании; все необязательные.

**Пример запроса:**

```http
PATCH /api/bookings/8
Content-Type: application/json

{
  "status": "confirmed"
}
```

**Пример ответа (200 OK):**

```http
{
  "id": 8,
  "workspace_id": 4,
  "employee_id": 2,
  "status": "confirmed",
  "time_slot": "full_day",
  "booking_date": "2026-11-02"
}
```

**Пример запроса:**

```http
PUT /api/bookings/8
Content-Type: application/json

{
  "workspace_id": 4,
  "employee_id": 2,
  "status": "confirmed",
  "time_slot": "morning",
  "booking_date": "2026-11-03"
}
```

**Пример ответа (200 OK):**

```http
{
  "id": 8,
  "workspace_id": 4,
  "employee_id": 2,
  "status": "confirmed",
  "time_slot": "morning",
  "booking_date": "2026-11-03"
}
```

**Ошибки:** 400 — тело не JSON; 404 — бронирования нет; 422 — данные не прошли проверку; 409 — место занято в это время.

### DELETE /api/bookings/{id} — удалить бронирование

**Назначение:** Удаляет бронирование.

Параметр адреса: `id` — номер бронирования.

**Пример запроса:**

```http
DELETE /api/bookings/8
```

**Пример ответа (204 No Content):**

Тело ответа пустое.

**Ошибки:** 404 — бронирования нет.

---

## Рабочие места

### GET /api/workspaces — список рабочих мест

**Назначение:** Возвращает все рабочие места.

Параметров нет.

**Пример запроса:**

```http
GET /api/workspaces
```

**Пример ответа (200 OK):**

```http
[
  {
    "id": 1,
    "title": "Стол №5 (open space)",
    "location": "2 этаж, у окна",
    "description": "Открытое пространство, монитор 27\""
  },
  {
    "id": 2,
    "title": "Переговорная «Алтай»",
    "location": "3 этаж",
    "description": "Закрытая комната на 6 человек, проектор"
  },
  {
    "id": 3,
    "title": "Кабинет тишины №2",
    "location": "1 этаж",
    "description": "Отдельный кабинет на 1 человека, без окон"
  },
  {
    "id": 4,
    "title": "Стол №8 (open space)",
    "location": "2 этаж, у лифта",
    "description": "Открытое пространство, две розетки"
  }
]
```

**Ошибки:** Своих ошибок нет.

### GET /api/workspaces/{id} — одно рабочее место

**Назначение:** Возвращает рабочее место по номеру.

Параметр адреса: `id` — номер рабочего места.

**Пример запроса:**

```http
GET /api/workspaces/2
```

**Пример ответа (200 OK):**

```http
{
  "id": 2,
  "title": "Переговорная «Алтай»",
  "location": "3 этаж",
  "description": "Закрытая комната на 6 человек, проектор"
}
```

**Ошибки:** 404 — рабочего места нет.

### GET /api/workspaces/{id}/bookings — бронирования рабочего места

**Назначение:** Возвращает все бронирования одного рабочего места (вложенный ресурс).

Параметр адреса: `id` — номер рабочего места.

**Пример запроса:**

```http
GET /api/workspaces/2/bookings
```

**Пример ответа (200 OK):**

```http
[
  {
    "id": 4,
    "workspace_id": 2,
    "employee_id": 4,
    "status": "confirmed",
    "time_slot": "full_day",
    "booking_date": "2026-09-28"
  },
  {
    "id": 5,
    "workspace_id": 2,
    "employee_id": 5,
    "status": "new",
    "time_slot": "morning",
    "booking_date": "2026-10-12"
  },
  {
    "id": 6,
    "workspace_id": 2,
    "employee_id": 6,
    "status": "confirmed",
    "time_slot": "afternoon",
    "booking_date": "2026-10-25"
  }
]
```

**Ошибки:** 404 — рабочего места нет.

### POST /api/workspaces — создать рабочее место

**Назначение:** Создаёт рабочее место.

Параметры тела (JSON):

| Поле | Тип | Обязательное | Описание |
|---|---|---|---|
| title | string | да | название, не короче 3 символов |
| location | string | да | расположение (этаж, зона), не короче 2 символов |
| description | string | нет | характеристики (монитор, проектор и т. п.) |

**Пример запроса:**

```http
POST /api/workspaces
Content-Type: application/json

{
  "title": "Стол №9 (open space)",
  "location": "2 этаж, у кухни",
  "description": "Две розетки"
}
```

**Пример ответа (201 Created):**

```http
Location: /api/workspaces/5

{
  "id": 5,
  "title": "Стол №9 (open space)",
  "location": "2 этаж, у кухни",
  "description": "Две розетки"
}
```

**Ошибки:** 400 — тело не JSON; 422 — данные не прошли проверку.

### PUT, PATCH /api/workspaces/{id} — изменить рабочее место

**Назначение:** Меняет рабочее место. Присланные поля заменяют старые.

Параметр адреса: `id`. Тело — те же поля, что при создании.

**Пример запроса:**

```http
PATCH /api/workspaces/5
Content-Type: application/json

{
  "location": "2 этаж, у переговорной"
}
```

**Пример ответа (200 OK):**

```http
{
  "id": 5,
  "title": "Стол №9 (open space)",
  "location": "2 этаж, у переговорной",
  "description": "Две розетки и монитор"
}
```

**Пример запроса:**

```http
PUT /api/workspaces/5
Content-Type: application/json

{
  "title": "Стол №9 (open space)",
  "location": "2 этаж, у кухни",
  "description": "Две розетки и монитор"
}
```

**Пример ответа (200 OK):**

```http
{
  "id": 5,
  "title": "Стол №9 (open space)",
  "location": "2 этаж, у кухни",
  "description": "Две розетки и монитор"
}
```

**Ошибки:** 400 — тело не JSON; 404 — рабочего места нет; 422 — данные не прошли проверку.

### DELETE /api/workspaces/{id} — удалить рабочее место

**Назначение:** Удаляет рабочее место. Если на него есть бронирования, удалить нельзя.

Параметр адреса: `id` — номер рабочего места.

**Пример запроса:**

```http
DELETE /api/workspaces/5
```

**Пример ответа (204 No Content):**

Тело ответа пустое.

**Ошибки:** 404 — рабочего места нет; 409 — на рабочее место есть бронирования.

---

## Сотрудники

### GET /api/employees — список сотрудников

**Назначение:** Возвращает всех сотрудников. Можно отфильтровать по отделу.

Параметр строки запроса (необязательный): `department` — `it`, `sales`, `hr` или `finance`.

**Пример запроса:**

```http
GET /api/employees?department=it
```

**Пример ответа (200 OK):**

```http
[
  {
    "id": 1,
    "name": "Иванов Иван",
    "email": "ivanov@example.com",
    "department": "it"
  },
  {
    "id": 3,
    "name": "Петров Олег",
    "email": "petrov@example.com",
    "department": "it"
  }
]
```

**Ошибки:** Своих ошибок нет.

### GET /api/employees/{id} — один сотрудник

**Назначение:** Возвращает сотрудника по номеру.

Параметр адреса: `id` — номер сотрудника.

**Пример запроса:**

```http
GET /api/employees/3
```

**Пример ответа (200 OK):**

```http
{
  "id": 3,
  "name": "Петров Олег",
  "email": "petrov@example.com",
  "department": "it"
}
```

**Ошибки:** 404 — сотрудника нет.

### GET /api/employees/{id}/bookings — бронирования сотрудника

**Назначение:** Возвращает все бронирования одного сотрудника (вложенный ресурс).

Параметр адреса: `id` — номер сотрудника.

**Пример запроса:**

```http
GET /api/employees/3/bookings
```

**Пример ответа (200 OK):**

```http
[
  {
    "id": 3,
    "workspace_id": 1,
    "employee_id": 3,
    "status": "cancelled",
    "time_slot": "full_day",
    "booking_date": "2026-10-15"
  }
]
```

**Ошибки:** 404 — сотрудника нет.

### POST /api/employees — создать сотрудника

**Назначение:** Добавляет сотрудника.

Параметры тела (JSON):

| Поле | Тип | Обязательное | Описание |
|---|---|---|---|
| name | string | да | ФИО, не короче 2 символов |
| email | string | да | адрес электронной почты |
| department | string | да | отдел: `it`, `sales`, `hr` или `finance` |

**Пример запроса:**

```http
POST /api/employees
Content-Type: application/json

{
  "name": "Орлова Ирина",
  "email": "orlova@example.com",
  "department": "hr"
}
```

**Пример ответа (201 Created):**

```http
Location: /api/employees/7

{
  "id": 7,
  "name": "Орлова Ирина",
  "email": "orlova@example.com",
  "department": "hr"
}
```

**Ошибки:** 400 — тело не JSON; 422 — данные не прошли проверку.

### PUT, PATCH /api/employees/{id} — изменить сотрудника

**Назначение:** Меняет сотрудника. Присланные поля заменяют старые.

Параметр адреса: `id`. Тело — те же поля, что при создании.

**Пример запроса:**

```http
PATCH /api/employees/7
Content-Type: application/json

{
  "email": "i.orlova@example.com"
}
```

**Пример ответа (200 OK):**

```http
{
  "id": 7,
  "name": "Орлова Ирина",
  "email": "i.orlova@example.com",
  "department": "finance"
}
```

**Пример запроса:**

```http
PUT /api/employees/7
Content-Type: application/json

{
  "name": "Орлова Ирина",
  "email": "orlova@example.com",
  "department": "finance"
}
```

**Пример ответа (200 OK):**

```http
{
  "id": 7,
  "name": "Орлова Ирина",
  "email": "orlova@example.com",
  "department": "finance"
}
```

**Ошибки:** 400 — тело не JSON; 404 — сотрудника нет; 422 — данные не прошли проверку.

### DELETE /api/employees/{id} — удалить сотрудника

**Назначение:** Удаляет сотрудника. Если у него есть бронирования, удалить нельзя.

Параметр адреса: `id` — номер сотрудника.

**Пример запроса:**

```http
DELETE /api/employees/7
```

**Пример ответа (204 No Content):**

Тело ответа пустое.

**Ошибки:** 404 — сотрудника нет; 409 — у сотрудника есть бронирования.

---

## Примеры ответов с ошибками

### 400 — тело запроса не JSON

**Пример запроса:**

```http
POST /api/bookings
Content-Type: application/json

{'workspace_id': 1}
```

**Пример ответа (400 Bad Request):**

```http
{
  "error": "Тело запроса должно быть корректным JSON-объектом"
}
```

### 404 — записи нет

**Пример запроса:**

```http
GET /api/bookings/999
```

**Пример ответа (404 Not Found):**

```http
{
  "error": "Бронирование не найдено"
}
```

### 404 — такого адреса нет (старый адрес из учебного примера)

**Пример запроса:**

```http
GET /api/tasks
```

**Пример ответа (404 Not Found):**

```http
{
  "error": "Адрес /api/tasks не найден"
}
```

### 405 — метод не поддерживается (заголовок `Allow: GET, POST`)

**Пример запроса:**

```http
DELETE /api/bookings
```

**Пример ответа (405 Method Not Allowed):**

```http
{
  "error": "Метод DELETE для этого адреса не поддерживается"
}
```

### 422 — данные не прошли проверку

**Пример запроса:**

```http
POST /api/bookings
Content-Type: application/json

{
  "workspace_id": 99,
  "employee_id": 2,
  "booking_date": "2026-13-45"
}
```

**Пример ответа (422 Unprocessable Entity):**

```http
{
  "error": "Ошибка проверки данных",
  "errors": {
    "workspace_id": "Рабочего места с таким номером нет",
    "booking_date": "Дата в формате ГГГГ-ММ-ДД"
  }
}
```

### 409 — рабочее место уже занято

**Пример запроса:**

```http
POST /api/bookings
Content-Type: application/json

{
  "workspace_id": 2,
  "employee_id": 2,
  "time_slot": "morning",
  "booking_date": "2026-10-12"
}
```

**Пример ответа (409 Conflict):**

```http
{
  "error": "Рабочее место уже занято в это время (бронирование №5)"
}
```

### 409 — нельзя удалить: на место есть бронирования

**Пример запроса:**

```http
DELETE /api/workspaces/1
```

**Пример ответа (409 Conflict):**

```http
{
  "error": "Нельзя удалить рабочее место: на него есть бронирования"
}
```
