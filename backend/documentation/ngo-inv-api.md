# NGO Invite Link API Documentation

## Overview
API to manage NGO staff onboarding through invite links. Admins or NGO admins can generate limited-use invite tokens for roles like `manager`, `general_staff`, etc.

## Base URL
```
/api/ngo-invite-links
```

## Endpoints

### 1. List All Invite Links for NGO
**GET** `/api/ngo-invite-links/{ngoId}`

Returns all invite links associated with the given NGO ID.

#### Success Response (200 OK)
- Array of `NgoInviteLink` objects.

---

### 2. Create New Invite Link
**POST** `/api/ngo-invite-links`

Generates a new invite token for an NGO staff role.

#### Request Body
```
{
  "ngo_id": 1,
  "privilege_role": "manager",
  "usage_limit": 5
}
```

- `privilege_role` must be one of: `ngo_admin`, `manager`, `general_staff`
- `usage_limit` is optional; if set, link becomes invalid after max uses.

#### Success Response (201 Created)
```
{
  "message": "NGO Application approved and NGO created",
  "ngo_id": 1,
  "invite_link": "http://127.0.0.1:8000/auth/redirect?token=g22eg0p6ynCo1aQG7KrSB4OGOWTUzz3x"
}
```

---

### 3. Mark Invite Link as Used (Internal Method)
**Method:** `markInviteUsed(NgoInviteLink $invite)`

Internal method to increment the usage count and deactivate link if limit is reached.

---

## Notes
- Invite links are used during Google OAuth onboarding to assign correct role and NGO.
- Token uniqueness is handled via random 32-character strings.
- `usage_limit` and `used_count` ensure controlled onboarding.

## Related Models
- `NgoInviteLink`
- `NgoStaff`
- `User`

## Middleware
- All write operations should be protected using `auth:sanctum` and NGO/admin role checks.

## Role-based Usage
- Tokens are used during Google OAuth redirect to register users with the proper `privilege_role` and `ngo_id`.
