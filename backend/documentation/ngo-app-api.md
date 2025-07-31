# NGO Application API Documentation

## Overview
API for handling NGO onboarding applications, including submission by NGOs and review/approval/rejection by Admins.

## Base URL
```
/api/ngo-applications
```

## Endpoints

### 1. Submit NGO Application
**POST** `/api/ngo-applications`

Submits an NGO application for onboarding.

#### Request Body
```
{
  "organization": "BRAC",
  "contact_person": "Tanzim Ahmmed",
  "designation": "Coordinator",
  "email": "tanzim.ahmmed@g.bracu.ac.bd",
  "phone": "017xxxxxxxx",
  "description": "Provides emergency food and medicine in disaster zones.",
  "based_in": "Dhaka"
}
```

#### Success Response (201 Created)
```
{
  "message": "Application submitted successfully",
  "data": {
    "organization": "BRAC",
    "contact_person": "Tanzim Ahmmed",
    "designation": "Coordinator",
    "email": "tanzim.ahmmed@g.bracu.ac.bd",
    "phone": "017xxxxxxxx",
    "description": "Provides emergency food and medicine in disaster zones.",
    "based_in": "Dhaka",
    "updated_at": "2025-07-31T21:04:05.000000Z",
    "created_at": "2025-07-31T21:04:05.000000Z",
    "id": 1
  }
}
```

---

### 2. List All Applications (Admin)
**GET** `/api/ngo-applications`

Returns all submitted NGO applications, sorted by creation date.

#### Success Response (200 OK)
- Array of `NgoApplication` objects.

---

### 3. Approve NGO Application (Admin)
**POST** `/api/ngo-applications/{id}/approve`

Approves an NGO application, creates the corresponding NGO record, and generates an invite token for the primary NGO admin.

#### Success Response (201 Created)
```
{
  "message": "NGO Application approved and NGO created",
  "ngo_id": 1,
  "invite_link": "http://127.0.0.1:8000/auth/redirect?token=g22eg0p6ynCo1aQG7KrSB4OGOWTUzz3x"
}
```

---

### 4. Reject NGO Application (Admin)
**POST** `/api/ngo-applications/{id}/reject`

Rejects an NGO application and updates its status.

#### Success Response
```
{
  "message": "Application rejected"
}
```

---

## Notes
- Upon approval, a `Ngo` record is created using submitted fields.
- An entry in `ngo_invite_links` is generated with `privilege_role = ngo_admin`, `is_primary = true`.
- Token in the invite link is sent to the frontend for Google OAuth onboarding.

## Related Models
- `NgoApplication`
- `Ngo`
- `NgoInviteLink`

## Middleware
- Admin routes must be protected using `auth:sanctum` and admin role verification.
