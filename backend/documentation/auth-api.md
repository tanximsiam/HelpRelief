# Auth API Documentation

## Overview
Handles user registration, login, authenticated profile access, and logout.

## Base URL
```
/api
```

## Authentication
- Sanctum token-based authentication
- Google OAuth2 login via Laravel Socialite

---

## Endpoints

### 1. Register User
**POST** `/api/register`

Registers a new general user.

#### Request Body
```json
{
  "name": "Tanzim",
  "email": "tanzim@example.com",
  "phone": "017xxxxxxxx",
  "password": "secret123",
  "password_confirmation": "secret123"
}
```

#### Success Response (201 Created)
```json
{
  "message": "User registered successfully",
  "user": {
    "name": "Tanzim",
    "email": "tanzim@example.com",
    "role": "general",
    "updated_at": "2025-07-30T20:19:17.000000Z",
    "created_at": "2025-07-30T20:19:17.000000Z",
    "id": 1
  }
}
```

#### Validation Error (422)
```json
{
  "message": "Kindly fill all required fields.",
  "errors": {
    "email": ["An account with this email already exists."]
  }
}
```

---

### 2. Login
**POST** `/api/login`

Authenticates a user and returns a token.

#### Request Body
```json
{
  "email": "tanzim@example.com",
  "password": "secret123"
}
```

#### Success Response (200 OK)
```json
{
  "message": "Login successful",
  "token": "Bearer 1|eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1..."
}
```

#### Invalid Credentials (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["Invalid credentials."]
  }
}
```

---

### 3. Login via Google (Redirect)
**GET** `/auth/redirect`

Redirects user to Google OAuth login page.

#### Response
302 Redirect to Google login

---

### 4. Google OAuth Callback
**GET** `/auth/callback`

Handles the response from Google and issues an API token.

#### Redirects to:
`<FRONTEND_URL>/socialite-token-receiver?token=<token>`

#### Token Format
`Bearer 3|eyJ0eXAiOiJKV1QiLCJh...`

User is auto-created if not found. Role is determined by domain:
- If email domain matches an approved NGO, role is `ngo`
- Otherwise, role is `general`

---

### 5. Get Authenticated User (Profile)
**GET** `/api/profile`

Requires Authorization header.

#### Headers
```
Authorization: Bearer 3|eyJ0eXAiOiJKV1QiLCJh...
```

#### Success Response
```json
{
  "id": 1,
  "name": "Tanzim",
  "email": "tanzim@example.com",
  "role": "general",
  "ngo_id": null,
  "designation": null,
  "privilege_role": null,
  "volunteer": 0,
  "created_at": "2025-07-30T18:58:59.000000Z",
  "updated_at": "2025-07-30T18:58:59.000000Z"
}
```

#### Unauthorized (401)
```json
{
  "message": "Unauthenticated."
}
```

---

### 6. Logout
**POST** `/api/logout`

Revokes current access token.

#### Headers
```
Authorization: Bearer 3|eyJ0eXAiOiJKV1QiLCJh...
```

#### Success Response
```json
{
  "message": "Logged out"
}
```

---

## Notes
- Always include `Accept: application/json` in headers for API usage for POSTMAN
- Tokens must be sent in `Authorization` header as `Bearer <token>`
- All authenticated routes are protected via Sanctum middleware
- Google login returns API token via frontend redirect
