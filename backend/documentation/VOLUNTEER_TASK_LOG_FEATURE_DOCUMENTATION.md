# Volunteer Task Log Feature Documentation

## Overview
The Volunteer Task Log feature lets NGO staff (and authorized users) view, start (check in), and finish (check out) volunteer task activity for a specific disaster campaign. It provides:

1. Campaign–scoped task listing (dropdown) filtered to only tasks that have not yet been checked in.
2. A log table showing all existing `VolunteerTaskLog` entries for the selected campaign.
3. Actions to check in to a task (create or update a log) and to check out (end) an in‑progress task.
4. Automatic hiding of tasks from the dropdown once they have any `check_in` recorded.
5. Fallback logic to approximate a tasks list from existing logs if the direct tasks endpoint fails or returns empty.

Frontend implementation lives in `frontend/src/components/VolunteerTaskLogOverlay.vue` and is opened from the campaign list modal (`CampaignListModal.vue`).

## Data Model Summary

### Task (excerpt)
| Field | Type | Notes |
|-------|------|------|
| id | int | Primary key |
| campaign_id | int | FK -> DisasterCampaignAssignment |
| task_type | string | e.g. `delivery`, `aid_request` |
| status | string | Business state (assigned / completed, etc.) |
| urgency | string | `critical|high|medium|low` |
| aid_type | string | `medical|resource|financial|…` |
| assigned_to | int nullable | User ID of assignee |
| start_time / end_time | datetime nullable | Planned times |

### VolunteerTaskLog
| Field | Type | Notes |
|-------|------|------|
| id | int | Primary key |
| task_id | int | FK -> tasks.id |
| campaign_id | int | Redundant denormalization for fast filtering |
| volunteer_id | int | User performing the task (volunteer / staff) |
| status | string | `assigned`, `started`, `ended` (actual set may include others) |
| check_in | datetime nullable | Set on check‑in |
| check_out | datetime nullable | Set on check‑out |
| start_verified_by / end_verified_by | int nullable | (Future) verifier IDs |
| report | string nullable | Simple textual report/status |

## API Endpoints

All authenticated endpoints are behind `auth:sanctum` middleware unless noted.

### 1. List Logs
GET `/api/task-logs`

Query Parameters:
* `campaign_id` (optional) – limits logs to a specific campaign.
* (Other generic filters may be added later.)

Response (200):
```
[
  {
    "id": 12,
    "task_id": 7,
    "campaign_id": 1,
    "status": "assigned|started|ended",
    "check_in": "2025-08-27T13:04:05.000000Z",
    "check_out": null,
    "report": "normal",
    "task": { "task_type": "delivery" },
    "volunteer": { "name": "volunteer1" }
  },
  ...
]
```

### 2. Campaign Tasks
GET `/api/campaigns/{campaignId}/tasks`

Response (200):
```
{
  "campaign_found": true,
  "count": 3,
  "tasks": [
    { "id":7, "task_type":"delivery", "status":"completed", "urgency":"critical", "aid_type":"medical", "location":"Dhaka, Bangladesh", "assigned_to":4, "assigned_to_name":"admin1" },
    ...
  ]
}
```

Notes:
* Returns 404 only if both campaign and tasks absent.
* Frontend accepts either array form (legacy) or the object with `tasks` key.

### 3. Check In
POST `/api/task-log/checkin`

Body JSON: `{ "task_id": <int> }`

Behavior:
* Creates a new `VolunteerTaskLog` if none active for the task, with `check_in` timestamp and status (e.g. `started` or `assigned` depending on controller logic).
* May update an existing log if previously assigned without `check_in`.
* Returns updated log in `log` field plus a `message`.

Sample Response:
```
{
  "message": "Checked in to task.",
  "log": { "id":15, "task_id":8, "status":"started", "check_in":"2025-08-27T13:07:11Z", ... }
}
```

### 4. Check Out
POST `/api/task-log/checkout`

Body JSON: `{ "task_id": <int> }`

Behavior:
* Finds the current in‑progress log for that task, sets `check_out` & `status` = `ended` (or similar), and returns it.

Sample Response:
```
{
  "message": "Checked out of task.
  ",
  "log": { "id":15, "task_id":8, "status":"ended", "check_in":"...", "check_out":"..." }
}
```

## Frontend Component (`VolunteerTaskLogOverlay.vue`)

Props:
* `open: boolean` – visibility toggle.
* `campaignId?: number` – campaign context; when provided fetches only campaign tasks & logs.

Emits:
* `close` – user closed the modal.

Lifecycle & Data Fetching:
* On mount AND whenever `open` becomes `true`, it fetches logs, tasks, and campaign name.
* On `campaignId` change while open, re-fetches data.
* Builds fallback task list from logs if API returns no tasks.

Task Dropdown Behavior:
* Shows only tasks that have NOT yet been checked in (i.e., no log with `check_in` for that task).
* After a successful check‑in, the task disappears from the dropdown immediately.

Actions:
* Check In button triggers POST `/task-log/checkin`; success prepends or updates the log list and clears selection.
* Checkout is triggered within `VolunteerTaskLogTable` via emitted `checkOut` event calling POST `/task-log/checkout`.

UI Notes:
* Gradient panel styling matches other dashboard overlays.
* No manual refresh button (data loads only on open / campaign change).
* Title includes campaign disaster name (fallback: `Campaign #<id>`).

## Status & Transitions (Typical)
```
assigned (log created) -> started (after check-in) -> ended (after check-out)
```
Exact transitions depend on controller logic; adjust docs if you formalize a state machine.

## Seeder Context
* `TaskSeeder` inserts deterministic tasks across first few campaigns.
* `VolunteerTaskLogsSeeder` creates a couple of initial logs referencing real task & campaign IDs (derives campaign_id from task to avoid FK mismatches).
* Some tasks intentionally have no logs so they appear as available for manual check-in during demos.

## Error Handling
* Missing campaign or tasks returns 404 from tasks endpoint only if both absent.
* Frontend captures fetch errors and displays inline short message beneath the dropdown.
* Silent fallback (logs -> tasks) prevents an empty dropdown if the tasks API momentarily fails.

## Security / Auth
* All primary endpoints require `auth:sanctum`.
* A temporary local debug route (`/api/debug/campaigns/{id}/tasks`) may exist in development; ensure it is disabled in production.
* Add policy checks (future) to ensure only NGO staff assigned to the campaign can fetch tasks.

## Extension Points / Future Enhancements
| Idea | Benefit |
|------|---------|
| Add pagination & date range filters to `/task-logs` | Scale with large datasets |
| Introduce task state machine (enum) | Enforce valid transitions |
| Real‑time updates via WebSockets or SSE | Live log updates without reopen |
| Verification workflow (start/end approvers) | Auditable confirmations |
| Role‑based filtering (volunteer sees only own logs) | Privacy & clarity |
| Bulk check‑in/out / shift scheduling | Efficiency for large teams |
| Export (CSV / PDF) | Reporting & offline analysis |

## Quick Reference (Happy Path)
1. Open overlay with `open=true` & `campaignId` -> tasks & logs load.
2. Select available task (no prior check_in) -> click Check In.
3. Task disappears from dropdown; new log appears in table (status started/assigned).
4. Later, click Check Out (table action) -> status becomes ended.
5. Log remains visible historically; task stays hidden from dropdown (already used).

## Troubleshooting
| Symptom | Likely Cause | Fix |
|---------|--------------|-----|
| Dropdown empty | No tasks returned & no logs yet | Verify seeders ran; check `/api/campaigns/{id}/tasks` auth header |
| Tasks appear but cannot check in | `selectedTaskId` empty or task already logged | Choose a task without prior check_in |
| 404 on tasks endpoint | Campaign ID invalid OR route cache stale | `php artisan optimize:clear` then retry |
| Login redirect (HTML) instead of JSON | Missing auth token | Authenticate and include bearer token |

## Minimal API Test Sequence (HTTP)
```
GET /api/campaigns/{id}/tasks
GET /api/task-logs?campaign_id={id}
POST /api/task-log/checkin { "task_id": <id> }
POST /api/task-log/checkout { "task_id": <id> }
```

---
Maintained: 2025-08-27
Owner: Volunteer Task Log Feature Team
