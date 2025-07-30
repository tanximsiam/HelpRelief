# NGO API Documentation

## Overview
Handles NGO entity creation, updates, and listing.

## Base URL
```
/api/ngos
```

## Authentication
All routes require authentication (admin or NGO staff).

---

## Endpoints

### 1. Get All NGOs
**GET** `/api/ngos`

Returns list of all approved NGOs.

#### Response
```json
[
  {
    "id": 1,
    "name": "Save The People",
    "contact": "Raiat Reza",
    "email": "contact@savethepeople.org",
    "phone": "018xxxxxxxx",
    "cause_focus": "Health",
    "location": "Barisal"
  }
]
```

---

### 2. Get Single NGO
**GET** `/api/ngos/{id}`

Returns detailed NGO info.

---

### 3. Create NGO (Admin Use)
**POST** `/api/ngos`

Registers a new NGO directly.

#### Request Body
```json
{
  "name": "Save The People",
  "contact": "Raiat Reza",
  "email": "contact@savethepeople.org",
  "phone": "018xxxxxxxx",
  "location": "Barisal",
  "cause_focus": "Health"
}
```

---

## Model: NGO

| Field        | Type     | Notes                         |
|--------------|----------|-------------------------------|
| id           | UUID/INT | Primary Key                   |
| user_id      | FK       | Points to main NGO user       |
| contact      | String   | Representative name           |
| email        | String   | Contact email                 |
| phone        | String   | Optional                      |
| location     | String   | Address or area of operation  |
| cause_focus  | String   | e.g., Health, Education       |
| website      | String   | Optional                      |
| created_at   | Timestamp| Auto                          |
| updated_at   | Timestamp| Auto                          |

---

