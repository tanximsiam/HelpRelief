# Auth API Documentation

## Overview
Authentication system including standard registration/login, Google OAuth onboarding with NGO invite token, and user profile/logout endpoints.

## Base URL
```
/api
```

## Authentication
- Token-based via Laravel Sanctum
- Google OAuth2 via Laravel Socialite

---

## Endpoints

### 1. Register User
**POST** `/api/register`

Registers a general user.

#### Request Body
```
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "01712345678",
  "password": "secret123",
  "password_confirmation": "secret123"
}
```

#### Response (201 Created)
```
{
  "message": "User registered successfully",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "01712345678",
    "role": "general",
    "created_at": "2025-08-01T00:00:00.000000Z",
    "updated_at": "2025-08-01T00:00:00.000000Z"
  }
}
```

---

### 2. Login User
**POST** `/api/login`

Authenticates user and returns token.

#### Request Body
```
{
  "email": "john@example.com",
  "password": "secret123"
}
```

#### Response
```
{
  "message": "Login successful",
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "01712345678",
    "role": "general",
    "created_at": "2025-08-01T00:00:00.000000Z",
    "updated_at": "2025-08-01T00:00:00.000000Z"
  }
}
```

---

### 3. Google OAuth Redirect
**GET** `/api/auth/google/redirect`

Redirects user to Google OAuth. Accepts optional token as `?token=XYZ`.

#### Example
```
/api/auth/google/redirect?token=NGO123TOKEN
```

---

### 4. Google OAuth Callback
**GET** `/api/auth/google/callback`

Handles Google callback and registers/logs in user based on invite token.

- Without token → registers general user
- With valid token → registers as `ngo_staff` and creates `NgoStaff` record

#### Example Redirection:
```
/dashboard?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```



---

### 5. Get Authenticated User Profile
**GET** `/api/profile`

Requires auth token.

#### Response
```
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "01712345678",
  "role": "general",
  "created_at": "2025-08-01T00:00:00.000000Z",
  "updated_at": "2025-08-01T00:00:00.000000Z"
}
```

---

### 6. Logout
**POST** `/api/logout`

Revokes current token.

#### Response
```
{
  "message": "Logged out"
}
```

---

## Notes
- `NgoInviteLink` tokens support usage limits and role assignment.
- `NgoStaff` records track `ngo_id` and `privilege_role`.
- Google OAuth registrations without token are treated as general users.



## Related Models
- `User`
- `NgoInviteLink`
- `NgoStaff`

## Middleware
- Protected routes use `auth:sanctum`

## Role-based Redirection (frontend)
- All OAuth redirects send token via `?token=` to frontend.
