# User API Documentation

## Overview
Manages user registration, login, and retrieval of user data.

## Base URL
```
/api/users
```

## Authentication
- Most routes will require Sanctum authentication.
- Login route is public.

---

## Endpoints

### 1. Register User
**POST** `/api/users/register`

Registers a general user.

#### Request Body
```json
{
  "name": "Tanzim",
  "email": "tanzim@example.com",
  "phone": "017xxxxxxxx",
  "password": "password",
  "password_confirmation": "password"
}
```

#### Response (201 Created)
```json
{
  "message": "User registered successfully",
  "user": {
    "id": 1,
    "name": "Tanzim",
    "email": "tanzim@example.com",
    "role": "general"
  }
}
```

---

### 2. Login
**POST** `/api/login`

Authenticates a user.

#### Request Body
```json
{
  "email": "tanzim@example.com",
  "password": "password"
}
```

#### Response (200 OK)
```json
{
  "message": "Login successful",
  "token": "Bearer eyJ0eXAiOiJK..."
}
```

---

### 3. Get Authenticated User Profile
**GET** `/api/user`

Protected by Sanctum.

#### Response
```json
{
  "id": 1,
  "name": "Tanzim",
  "email": "tanzim@example.com",
  "role": "general"
}
```

---

### 4. Logout
**POST** `/api/logout`

Invalidates the token.

#### Response
```json
{
  "message": "Logged out"
}
```

---

## Model: User

| Field        | Type     | Notes                            |
|--------------|----------|----------------------------------|
| id           | UUID/INT | Primary Key                      |
| name         | String   | Required                         |
| email        | String   | Unique, Required                 |
| phone        | String   | Optional                         |
| password     | String   | Hashed                           |
| role         | Enum     | general, ngo, admin              |
| ngo_id       | FK       | Nullable (only if NGO staff)     |
| designation  | String   | NGO-specific role                |
| volunteer    | Boolean  | Marks if user registered as vol. |
| created_at   | Timestamp| Auto                             |
| updated_at   | Timestamp| Auto                             |

---
