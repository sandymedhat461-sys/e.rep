# E-Rep API Documentation

REST API for the E-Rep (medical rep / doctor / company) platform. All JSON bodies use `Content-Type: application/json` unless noted.

---

## Base URL

| Environment | Base URL |
|-------------|----------|
| Local (default) | `http://localhost:8000` |
|swagger (public)| `https://embezzle-skedaddle-creatable.ngrok-free.dev`| 
| API prefix | `/api` |

**Example:** Login URL = `{base}/api/auth/doctor/login` → `http://localhost:8000/api/auth/doctor/login`

---

## How to authenticate

1. **Login** with the appropriate `POST /api/auth/{role}/login` endpoint.
2. Read the **`token`** from the JSON response (`data.token` or nested under `data` per role—see each auth response).
3. Send the token on every protected request:

```http
Authorization: Bearer {token}
```

4. Use the **correct guard** per area (the app uses Laravel Sanctum; each role has its own token ability):

| Area | Header |
|------|--------|
| Admin | `Authorization: Bearer {admin_token}` |
| Company | `Authorization: Bearer {company_token}` |
| Doctor | `Authorization: Bearer {doctor_token}` |
| Medical rep | `Authorization: Bearer {rep_token}` |

**Logout** (optional): `POST` to `/api/auth/{role}/logout` with the same `Authorization` header.

---

## Test accounts (demo / seed data)

Use these only in non-production environments.

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@erep.com` | `password123` |
| Company | `company@pharmaegypt.com` | `password123` |
| Doctor | `doctor1@erep.com` | `password123` |
| Medical rep | `rep1@erep.com` | `password123` |

---

## Common responses & errors

Responses typically follow:

```json
{
  "success": true,
  "data": { },
  "message": "Optional human-readable message"
}
```

**Common error shapes**

| HTTP | When | Example body |
|------|------|----------------|
| **401** | Missing/invalid token, wrong password | `{ "success": false, "message": "Unauthenticated." }` or `{ "success": false, "message": "Invalid credentials" }` |
| **403** | Valid auth but not allowed (e.g. pending doctor, blocked user) | `{ "success": false, "message": "Account awaiting admin approval" }` |
| **404** | Resource not found | `{ "success": false, "message": "Not found" }` |
| **422** | Validation failed | `{ "success": false, "message": "Validation failed", "errors": { "email": ["The email field is required."] } }` |
| **500** | Server error | `{ "message": "Server Error" }` |

Unless an endpoint specifies otherwise, assume **401** for missing `Authorization: Bearer`, **404** for bad IDs, and **422** for invalid body/query.

---

# AUTH

Public auth routes do **not** require a token. `logout` and `me` require the matching role token.

---

### Admin register

- **Method:** `POST`
- **URL:** `/api/auth/admin/register`
- **Auth:** No

**Body:**

```json
{
  "full_name": "Admin User",
  "email": "newadmin@erep.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "+201000000099"
}
```

**Success (201):**

```json
{
  "success": true,
  "data": {
    "admin": {
      "id": 1,
      "full_name": "Admin User",
      "email": "newadmin@erep.com",
      "phone": "+201000000099"
    },
    "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
  }
}
```

**Errors:** `422` validation; `409` / message if email exists (if implemented).

---

### Admin login

- **Method:** `POST`
- **URL:** `/api/auth/admin/login`
- **Auth:** No

**Body:**

```json
{
  "email": "admin@erep.com",
  "password": "password123"
}
```

**Success (200):**

```json
{
  "success": true,
  "data": {
    "admin": {
      "id": 1,
      "full_name": "Super Admin",
      "email": "admin@erep.com"
    },
    "token": "2|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
  }
}
```

**Errors:** `401` invalid credentials; `422` validation.

---

### Admin logout

- **Method:** `POST`
- **URL:** `/api/auth/admin/logout`
- **Auth:** Yes — **Admin** (`Authorization: Bearer {token}`)

**Body:** None

**Success (200):**

```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

**Errors:** `401` unauthenticated.

---

### Admin me

- **Method:** `GET`
- **URL:** `/api/auth/admin/me`
- **Auth:** Yes — **Admin**

**Success (200):**

```json
{
  "success": true,
  "data": {
    "admin": {
      "id": 1,
      "full_name": "Super Admin",
      "email": "admin@erep.com",
      "phone": "+201000000001"
    }
  }
}
```

**Errors:** `401`.

---

### Company register

- **Method:** `POST`
- **URL:** `/api/auth/company/register`
- **Auth:** No

**Body:**

```json
{
  "company_name": "New Pharma LLC",
  "email": "register@newpharma.com",
  "password": "password123",
  "password_confirmation": "password123",
  "hotline": "+20212345678",
  "commercial_register": "CREG-EGY-2026-999"
}
```

**Success (201):**

```json
{
  "success": true,
  "data": {
    "company": {
      "id": 3,
      "company_name": "New Pharma LLC",
      "email": "register@newpharma.com",
      "status": "pending"
    },
    "token": "3|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
  }
}
```

**Errors:** `422` validation; duplicate email/commercial register.

---

### Company login

- **Method:** `POST`
- **URL:** `/api/auth/company/login`
- **Auth:** No

**Body:**

```json
{
  "email": "company@pharmaegypt.com",
  "password": "password123"
}
```

**Success (200):**

```json
{
  "success": true,
  "data": {
    "company": {
      "id": 1,
      "company_name": "Pharma Egypt Co.",
      "email": "company@pharmaegypt.com",
      "status": "approved"
    },
    "token": "4|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
  }
}
```

**Errors:** `401`; `403` if company pending/blocked.

---

### Company logout

- **Method:** `POST`
- **URL:** `/api/auth/company/logout`
- **Auth:** Yes — **Company**

**Body:** None

**Success (200):** `{ "success": true, "message": "Logged out successfully" }`

**Errors:** `401`.

---

### Company me

- **Method:** `GET`
- **URL:** `/api/auth/company/me`
- **Auth:** Yes — **Company**

**Success (200):**

```json
{
  "success": true,
  "data": {
    "company": {
      "id": 1,
      "company_name": "Pharma Egypt Co.",
      "email": "company@pharmaegypt.com",
      "status": "approved"
    }
  }
}
```

**Errors:** `401`.

---

### Doctor register

- **Method:** `POST`
- **URL:** `/api/auth/doctor/register`
- **Auth:** No

**Body:**

```json
{
  "full_name": "Dr. Example User",
  "email": "newdoctor@hospital.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "+201155500001",
  "national_id": "29001001550001",
  "specialization": "Cardiology",
  "hospital_name": "Cairo General",
  "syndicate_id": "SYN999"
}
```

**Success (201):**

```json
{
  "success": true,
  "data": {
    "doctor": {
      "id": 10,
      "full_name": "Dr. Example User",
      "email": "newdoctor@hospital.com",
      "status": "pending"
    },
    "token": "5|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
  }
}
```

**Errors:** `422` validation.

---

### Doctor login

- **Method:** `POST`
- **URL:** `/api/auth/doctor/login`
- **Auth:** No

**Body:**

```json
{
  "email": "doctor1@erep.com",
  "password": "password123"
}
```

**Success (200):**

```json
{
  "success": true,
  "data": {
    "doctor": {
      "id": 1,
      "full_name": "Dr. Ahmed Sayed",
      "email": "doctor1@erep.com",
      "specialization": "Cardiology",
      "status": "active"
    },
    "token": "6|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
  }
}
```

**Errors:** `401`; `403` pending approval or blocked.

---

### Doctor logout

- **Method:** `POST`
- **URL:** `/api/auth/doctor/logout`
- **Auth:** Yes — **Doctor**

**Body:** None

**Success (200):** `{ "success": true, "message": "Logged out successfully" }`

**Errors:** `401`.

---

### Doctor me

- **Method:** `GET`
- **URL:** `/api/auth/doctor/me`
- **Auth:** Yes — **Doctor**

**Success (200):**

```json
{
  "success": true,
  "data": {
    "doctor": {
      "id": 1,
      "full_name": "Dr. Ahmed Sayed",
      "email": "doctor1@erep.com",
      "specialization": "Cardiology"
    }
  }
}
```

**Errors:** `401`.

---

### Doctor check syndicate

- **Method:** `POST`
- **URL:** `/api/auth/doctor/check-syndicate`
- **Auth:** No

**Body:**

```json
{
  "syndicate_id": "SYN001"
}
```

**Success (200):**

```json
{
  "success": true,
  "data": {
    "available": true,
    "message": "Syndicate ID is available"
  }
}
```

**Errors:** `422` validation.

---

### Medical rep register

- **Method:** `POST`
- **URL:** `/api/auth/rep/register`
- **Auth:** No

**Body:**

```json
{
  "full_name": "New Rep Name",
  "email": "newrep@company.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "+201200000099",
  "national_id": "29002002002999",
  "company_id": 1,
  "category_id": 1
}
```

**Success (201):**

```json
{
  "success": true,
  "data": {
    "rep": {
      "id": 5,
      "full_name": "New Rep Name",
      "email": "newrep@company.com",
      "company_id": 1,
      "category_id": 1,
      "status": "pending"
    },
    "token": "7|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
  }
}
```

**Errors:** `422`; `403` if company/category invalid.

---

### Medical rep login

- **Method:** `POST`
- **URL:** `/api/auth/rep/login`
- **Auth:** No

**Body:**

```json
{
  "email": "rep1@erep.com",
  "password": "password123"
}
```

**Success (200):**

```json
{
  "success": true,
  "data": {
    "rep": {
      "id": 1,
      "full_name": "Karim Mostafa",
      "email": "rep1@erep.com",
      "company_id": 1,
      "status": "active"
    },
    "token": "8|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
  }
}
```

**Errors:** `401`; `403` pending/blocked.

---

### Medical rep logout

- **Method:** `POST`
- **URL:** `/api/auth/rep/logout`
- **Auth:** Yes — **Rep**

**Body:** None

**Success (200):** `{ "success": true, "message": "Logged out successfully" }`

**Errors:** `401`.

---

### Medical rep me

- **Method:** `GET`
- **URL:** `/api/auth/rep/me`
- **Auth:** Yes — **Rep**

**Success (200):**

```json
{
  "success": true,
  "data": {
    "rep": {
      "id": 1,
      "full_name": "Karim Mostafa",
      "email": "rep1@erep.com",
      "company_id": 1,
      "category_id": 1
    }
  }
}
```

**Errors:** `401`.

---

# ADMIN

All routes require: `Authorization: Bearer {admin_token}`

---

### Admin dashboard stats

- **Method:** `GET`
- **URL:** `/api/admin/dashboard/stats`
- **Auth:** Yes — Admin

**Success (200):**

```json
{
  "success": true,
  "data": {
    "pending_doctors": 4,
    "pending_reps": 2,
    "pending_companies": 1
  }
}
```

**Errors:** `401`.

---

### Admin list pending users

- **Method:** `GET`
- **URL:** `/api/admin/users/pending`
- **Auth:** Yes — Admin

**Success (200):**

```json
{
  "success": true,
  "data": {
    "doctors": [],
    "reps": [],
    "companies": []
  }
}
```

**Errors:** `401`.

---

### Admin approve user

- **Method:** `POST`
- **URL:** `/api/admin/users/{type}/{id}/approve`
- **Auth:** Yes — Admin

**Path params:** `type` = `doctor` | `company` | `rep` (or as implemented); `id` = user id.

**Body:** None or `{}`

**Success (200):**

```json
{
  "success": true,
  "message": "User approved",
  "data": { "id": 5, "status": "active" }
}
```

**Errors:** `401`, `404`, `422`.

---

### Admin block user

- **Method:** `POST`
- **URL:** `/api/admin/users/{type}/{id}/block`
- **Auth:** Yes — Admin

**Body:** None or `{ "reason": "Policy violation" }` (if supported)

**Success (200):**

```json
{
  "success": true,
  "message": "User blocked"
}
```

**Errors:** `401`, `404`.

---

### Admin list drug categories

- **Method:** `GET`
- **URL:** `/api/admin/categories`
- **Auth:** Yes — Admin

**Success (200):**

```json
{
  "success": true,
  "data": {
    "categories": [
      {
        "id": 1,
        "name": "Cardiology",
        "line_manager_name": "Dr. Ahmed Hassan"
      }
    ]
  }
}
```

**Errors:** `401`.

---

### Admin create drug category

- **Method:** `POST`
- **URL:** `/api/admin/categories`
- **Auth:** Yes — Admin

**Body:**

```json
{
  "name": "Endocrinology",
  "line_manager_name": "Dr. Example Manager"
}
```

**Success (201):**

```json
{
  "success": true,
  "data": {
    "category": {
      "id": 6,
      "name": "Endocrinology",
      "line_manager_name": "Dr. Example Manager"
    }
  }
}
```

**Errors:** `401`, `422`.

---

### Admin update drug category

- **Method:** `PUT`
- **URL:** `/api/admin/categories/{id}`
- **Auth:** Yes — Admin

**Body:**

```json
{
  "name": "Endocrinology (updated)",
  "line_manager_name": "Dr. New Manager"
}
```

**Success (200):** `{ "success": true, "data": { "category": { "id": 6, "name": "..." } } }`

**Errors:** `401`, `404`, `422`.

---

### Admin delete drug category

- **Method:** `DELETE`
- **URL:** `/api/admin/categories/{id}`
- **Auth:** Yes — Admin

**Success (200):** `{ "success": true, "message": "Category deleted" }`

**Errors:** `401`, `404`, `409` if in use.

---

# COMPANY

All routes require: `Authorization: Bearer {company_token}`

---

### Company dashboard

- **Method:** `GET`
- **URL:** `/api/company/dashboard`
- **Auth:** Yes — Company

**Success (200):**

```json
{
  "success": true,
  "data": {
    "stats": {
      "drugs_count": 12,
      "events_count": 3,
      "reps_count": 2
    }
  }
}
```

**Errors:** `401`, `403`.

---

### Company list active ingredients

- **Method:** `GET`
- **URL:** `/api/company/ingredients`
- **Auth:** Yes — Company

**Success (200):**

```json
{
  "success": true,
  "data": {
    "ingredients": [
      {
        "id": 1,
        "name": "Paracetamol",
        "description": null,
        "created_by_company_id": null
      }
    ]
  }
}
```

**Errors:** `401`.

---

### Company create active ingredient

- **Method:** `POST`
- **URL:** `/api/company/ingredients`
- **Auth:** Yes — Company

**Body:**

```json
{
  "name": "Custom Ingredient X",
  "description": "Short description",
  "side_effect": "May cause drowsiness"
}
```

**Success (201):** `{ "success": true, "data": { "ingredient": { "id": 20, "name": "Custom Ingredient X" } } }`

**Errors:** `401`, `422`.

---

### Company update active ingredient

- **Method:** `PUT`
- **URL:** `/api/company/ingredients/{id}`
- **Auth:** Yes — Company

**Body:**

```json
{
  "name": "Custom Ingredient X (v2)",
  "description": "Updated"
}
```

**Success (200):** `{ "success": true, "data": { "ingredient": {} } }`

**Errors:** `401`, `404`, `422`.

---

### Company delete active ingredient

- **Method:** `DELETE`
- **URL:** `/api/company/ingredients/{id}`
- **Auth:** Yes — Company

**Success (200):** `{ "success": true, "message": "Deleted" }`

**Errors:** `401`, `404`.

---

### Company list drugs

- **Method:** `GET`
- **URL:** `/api/company/drugs`
- **Auth:** Yes — Company

**Success (200):**

```json
{
  "success": true,
  "data": {
    "drugs": [
      {
        "id": 1,
        "name": "Example Drug",
        "market_name": "BrandX",
        "status": "active"
      }
    ]
  }
}
```

**Errors:** `401`.

---

### Company create drug

- **Method:** `POST`
- **URL:** `/api/company/drugs`
- **Auth:** Yes — Company

**Body (example):**

```json
{
  "name": "New Drug",
  "market_name": "BrandY",
  "category_id": 1,
  "ingredient_ids": [1, 2],
  "description": "Indications and usage"
}
```

**Success (201):** `{ "success": true, "data": { "drug": { "id": 5 } } }`

**Errors:** `401`, `422`.

---

### Company show drug

- **Method:** `GET`
- **URL:** `/api/company/drugs/{id}`
- **Auth:** Yes — Company

**Success (200):** `{ "success": true, "data": { "drug": {} } }`

**Errors:** `401`, `404`.

---

### Company update drug

- **Method:** `PUT`
- **URL:** `/api/company/drugs/{id}`
- **Auth:** Yes — Company

**Body:** Partial fields same as create.

**Success (200):** `{ "success": true, "data": { "drug": {} } }`

**Errors:** `401`, `404`, `422`.

---

### Company delete drug

- **Method:** `DELETE`
- **URL:** `/api/company/drugs/{id}`
- **Auth:** Yes — Company

**Success (200):** `{ "success": true, "message": "Drug deleted" }`

**Errors:** `401`, `404`.

---

### Company list events

- **Method:** `GET`
- **URL:** `/api/company/events`
- **Auth:** Yes — Company

**Success (200):** `{ "success": true, "data": { "events": [] } }`

**Errors:** `401`.

---

### Company create event

- **Method:** `POST`
- **URL:** `/api/company/events`
- **Auth:** Yes — Company

**Body:**

```json
{
  "title": "Cardiology webinar",
  "description": "Annual update",
  "starts_at": "2026-05-01T10:00:00Z",
  "ends_at": "2026-05-01T12:00:00Z",
  "location": "Online",
  "max_capacity": 100
}
```

**Success (201):** `{ "success": true, "data": { "event": { "id": 1 } } }`

**Errors:** `401`, `422`.

---

### Company show event

- **Method:** `GET`
- **URL:** `/api/company/events/{id}`
- **Auth:** Yes — Company

**Success (200):** `{ "success": true, "data": { "event": {} } }`

**Errors:** `401`, `404`.

---

### Company update event

- **Method:** `PUT`
- **URL:** `/api/company/events/{id}`
- **Auth:** Yes — Company

**Body:** Partial event fields.

**Success (200):** `{ "success": true, "data": { "event": {} } }`

**Errors:** `401`, `404`, `422`.

---

### Company delete event

- **Method:** `DELETE`
- **URL:** `/api/company/events/{id}`
- **Auth:** Yes — Company

**Success (200):** `{ "success": true, "message": "Event deleted" }`

**Errors:** `401`, `404`.

---

### Company list event requests

- **Method:** `GET`
- **URL:** `/api/company/events/{eventId}/requests`
- **Auth:** Yes — Company

**Success (200):** `{ "success": true, "data": { "requests": [] } }`

**Errors:** `401`, `404`.

---

### Company approve event request

- **Method:** `POST`
- **URL:** `/api/company/events/{eventId}/requests/{id}/approve`
- **Auth:** Yes — Company

**Body:** `{}` or optional note.

**Success (200):** `{ "success": true, "message": "Request approved" }`

**Errors:** `401`, `404`, `422`.

---

### Company reject event request

- **Method:** `POST`
- **URL:** `/api/company/events/{eventId}/requests/{id}/reject`
- **Auth:** Yes — Company

**Body:**

```json
{
  "reason": "Capacity full"
}
```

**Success (200):** `{ "success": true, "message": "Request rejected" }`

**Errors:** `401`, `404`, `422`.

---

### Company invite to event

- **Method:** `POST`
- **URL:** `/api/company/events/{eventId}/invite`
- **Auth:** Yes — Company

**Body:**

```json
{
  "doctor_ids": [1, 2],
  "rep_ids": [1]
}
```

**Success (200):** `{ "success": true, "data": { "invitations_sent": 3 } }`

**Errors:** `401`, `404`, `422`.

---

### Company list event invitations

- **Method:** `GET`
- **URL:** `/api/company/events/{eventId}/invitations`
- **Auth:** Yes — Company

**Success (200):** `{ "success": true, "data": { "invitations": [] } }`

**Errors:** `401`, `404`.

---

### Company list rewards

- **Method:** `GET`
- **URL:** `/api/company/rewards`
- **Auth:** Yes — Company

**Success (200):** `{ "success": true, "data": { "rewards": [] } }`

**Errors:** `401`.

---

### Company create reward

- **Method:** `POST`
- **URL:** `/api/company/rewards`
- **Auth:** Yes — Company

**Body:**

```json
{
  "name": "Gift voucher",
  "description": "500 EGP voucher",
  "points_cost": 500,
  "quantity_available": 20
}
```

**Success (201):** `{ "success": true, "data": { "reward": { "id": 1 } } }`

**Errors:** `401`, `422`.

---

### Company show reward

- **Method:** `GET`
- **URL:** `/api/company/rewards/{id}`
- **Auth:** Yes — Company

**Success (200):** `{ "success": true, "data": { "reward": {} } }`

**Errors:** `401`, `404`.

---

### Company update reward

- **Method:** `PUT`
- **URL:** `/api/company/rewards/{id}`
- **Auth:** Yes — Company

**Success (200):** `{ "success": true, "data": { "reward": {} } }`

**Errors:** `401`, `404`, `422`.

---

### Company delete reward

- **Method:** `DELETE`
- **URL:** `/api/company/rewards/{id}`
- **Auth:** Yes — Company

**Success (200):** `{ "success": true, "message": "Reward deleted" }`

**Errors:** `401`, `404`.

---

### Company list redemptions

- **Method:** `GET`
- **URL:** `/api/company/redemptions`
- **Auth:** Yes — Company

**Success (200):** `{ "success": true, "data": { "redemptions": [] } }`

**Errors:** `401`.

---

### Company fulfill redemption

- **Method:** `POST`
- **URL:** `/api/company/redemptions/{id}/fulfill`
- **Auth:** Yes — Company

**Body:** `{}`

**Success (200):** `{ "success": true, "message": "Redemption fulfilled" }`

**Errors:** `401`, `404`, `422`.

---

### Company cancel redemption

- **Method:** `POST`
- **URL:** `/api/company/redemptions/{id}/cancel`
- **Auth:** Yes — Company

**Success (200):** `{ "success": true, "message": "Redemption cancelled" }`

**Errors:** `401`, `404`.

---

### Company list reps

- **Method:** `GET`
- **URL:** `/api/company/reps`
- **Auth:** Yes — Company

**Success (200):** `{ "success": true, "data": { "reps": [] } }`

**Errors:** `401`.

---

### Company show rep

- **Method:** `GET`
- **URL:** `/api/company/reps/{id}`
- **Auth:** Yes — Company

**Success (200):** `{ "success": true, "data": { "rep": {} } }`

**Errors:** `401`, `404`.

---

### Company list rep targets

- **Method:** `GET`
- **URL:** `/api/company/reps/{id}/targets`
- **Auth:** Yes — Company

**Success (200):** `{ "success": true, "data": { "targets": [] } }`

**Errors:** `401`, `404`.

---

### Company upsert rep target

- **Method:** `POST`
- **URL:** `/api/company/reps/{id}/targets`
- **Auth:** Yes — Company

**Body:**

```json
{
  "target_type": "meetings",
  "target_value": 10,
  "period_start": "2026-04-01",
  "period_end": "2026-04-30"
}
```

**Success (200):** `{ "success": true, "data": { "target": {} } }`

**Errors:** `401`, `404`, `422`.

---

### Company list messages

- **Method:** `GET`
- **URL:** `/api/company/messages`
- **Auth:** Yes — Company

**Success (200):** `{ "success": true, "data": { "messages": [] } }`

**Errors:** `401`.

---

### Company send message

- **Method:** `POST`
- **URL:** `/api/company/messages`
- **Auth:** Yes — Company

**Body:**

```json
{
  "receiver_type": "doctor",
  "receiver_id": 1,
  "body": "Hello from company"
}
```

**Success (201):** `{ "success": true, "data": { "message": { "id": 1 } } }`

**Errors:** `401`, `422`.

---

### Company mark message read

- **Method:** `POST`
- **URL:** `/api/company/messages/{id}/read`
- **Auth:** Yes — Company

**Body:** `{}`

**Success (200):** `{ "success": true, "message": "Marked as read" }`

**Errors:** `401`, `404`.

---

### Company list notifications

- **Method:** `GET`
- **URL:** `/api/company/notifications`
- **Auth:** Yes — Company

**Success (200):** `{ "success": true, "data": { "notifications": [] } }`

**Errors:** `401`.

---

### Company mark notification read

- **Method:** `POST`
- **URL:** `/api/company/notifications/{id}/read`
- **Auth:** Yes — Company

**Success (200):** `{ "success": true }`

**Errors:** `401`, `404`.

---

### Company mark all notifications read

- **Method:** `POST`
- **URL:** `/api/company/notifications/read-all`
- **Auth:** Yes — Company

**Success (200):** `{ "success": true, "message": "All read" }`

**Errors:** `401`.

---

### Company list posts

- **Method:** `GET`
- **URL:** `/api/company/posts`
- **Auth:** Yes — Company

**Success (200):** `{ "success": true, "data": { "posts": [] } }`

**Errors:** `401`.

---

### Company create post

- **Method:** `POST`
- **URL:** `/api/company/posts`
- **Auth:** Yes — Company

**Body:**

```json
{
  "body": "Company announcement text",
  "tag_ids": [1, 2]
}
```

**Success (201):** `{ "success": true, "data": { "post": { "id": 1 } } }`

**Errors:** `401`, `422`.

---

### Company show post

- **Method:** `GET`
- **URL:** `/api/company/posts/{id}`
- **Auth:** Yes — Company

**Success (200):** `{ "success": true, "data": { "post": {} } }`

**Errors:** `401`, `404`.

---

### Company update post

- **Method:** `PUT`
- **URL:** `/api/company/posts/{id}`
- **Auth:** Yes — Company

**Body:** `{ "body": "Updated text" }`

**Success (200):** `{ "success": true, "data": { "post": {} } }`

**Errors:** `401`, `404`, `422`.

---

### Company delete post

- **Method:** `DELETE`
- **URL:** `/api/company/posts/{id}`
- **Auth:** Yes — Company

**Success (200):** `{ "success": true, "message": "Post deleted" }`

**Errors:** `401`, `404`.

---

### Company add comment on post

- **Method:** `POST`
- **URL:** `/api/company/posts/{postId}/comments`
- **Auth:** Yes — Company

**Body:**

```json
{
  "body": "Nice post!"
}
```

**Success (201):** `{ "success": true, "data": { "comment": { "id": 1 } } }`

**Errors:** `401`, `404`, `422`.

---

### Company like post

- **Method:** `POST`
- **URL:** `/api/company/posts/{postId}/like`
- **Auth:** Yes — Company

**Success (200):** `{ "success": true, "data": { "liked": true } }`

**Errors:** `401`, `404`.

---

### Company unlike post

- **Method:** `DELETE`
- **URL:** `/api/company/posts/{postId}/unlike`
- **Auth:** Yes — Company

**Success (200):** `{ "success": true, "data": { "liked": false } }`

**Errors:** `401`, `404`.

---

### Company delete comment

- **Method:** `DELETE`
- **URL:** `/api/company/comments/{id}`
- **Auth:** Yes — Company

**Success (200):** `{ "success": true, "message": "Comment deleted" }`

**Errors:** `401`, `404`.

---

# DOCTOR

All routes require: `Authorization: Bearer {doctor_token}`

---

### Doctor list drugs

- **Method:** `GET`
- **URL:** `/api/doctor/drugs`
- **Auth:** Yes — Doctor

**Success (200):** `{ "success": true, "data": { "drugs": [] } }`

**Errors:** `401`.

---

### Doctor show drug

- **Method:** `GET`
- **URL:** `/api/doctor/drugs/{id}`
- **Auth:** Yes — Doctor

**Success (200):** `{ "success": true, "data": { "drug": {} } }`

**Errors:** `401`, `404`.

---

### Doctor list drug reviews

- **Method:** `GET`
- **URL:** `/api/doctor/drugs/{drugId}/reviews`
- **Auth:** Yes — Doctor

**Success (200):** `{ "success": true, "data": { "reviews": [] } }`

**Errors:** `401`, `404`.

---

### Doctor create drug review

- **Method:** `POST`
- **URL:** `/api/doctor/drugs/{drugId}/reviews`
- **Auth:** Yes — Doctor

**Body:**

```json
{
  "rating": 5,
  "comment": "Excellent efficacy"
}
```

**Success (201):** `{ "success": true, "data": { "review": { "id": 1 } } }`

**Errors:** `401`, `404`, `422`.

---

### Doctor list favorites

- **Method:** `GET`
- **URL:** `/api/doctor/favorites`
- **Auth:** Yes — Doctor

**Success (200):** `{ "success": true, "data": { "favorites": [] } }`

**Errors:** `401`.

---

### Doctor add favorite

- **Method:** `POST`
- **URL:** `/api/doctor/favorites`
- **Auth:** Yes — Doctor

**Body:**

```json
{
  "drug_id": 5
}
```

**Success (201):** `{ "success": true, "data": { "favorite": {} } }`

**Errors:** `401`, `422`.

---

### Doctor remove favorite

- **Method:** `DELETE`
- **URL:** `/api/doctor/favorites/{drugId}`
- **Auth:** Yes — Doctor

**Success (200):** `{ "success": true, "message": "Removed from favorites" }`

**Errors:** `401`, `404`.

---

### Doctor list samples

- **Method:** `GET`
- **URL:** `/api/doctor/samples`
- **Auth:** Yes — Doctor

**Success (200):** `{ "success": true, "data": { "samples": [] } }`

**Errors:** `401`.

---

### Doctor request sample

- **Method:** `POST`
- **URL:** `/api/doctor/samples`
- **Auth:** Yes — Doctor

**Body:**

```json
{
  "drug_id": 3,
  "rep_id": 1,
  "notes": "For clinic stock"
}
```

**Success (201):** `{ "success": true, "data": { "sample": { "id": 1 } } }`

**Errors:** `401`, `422`.

---

### Doctor list meetings

- **Method:** `GET`
- **URL:** `/api/doctor/meetings`
- **Auth:** Yes — Doctor

**Success (200):** `{ "success": true, "data": { "meetings": [] } }`

**Errors:** `401`.

---

### Doctor show meeting

- **Method:** `GET`
- **URL:** `/api/doctor/meetings/{id}`
- **Auth:** Yes — Doctor

**Success (200):** `{ "success": true, "data": { "meeting": {} } }`

**Errors:** `401`, `404`.

---

### Doctor meeting video room

- **Method:** `GET`
- **URL:** `/api/doctor/meetings/{id}/video-room`
- **Auth:** Yes — Doctor

**Success (200):**

```json
{
  "success": true,
  "data": {
    "room_url": "https://meet.jit.si/erep-1-abc123",
    "room_name": "erep-1-abc123"
  }
}
```

**Errors:** `401`, `403`, `404`.

---

### Doctor list events

- **Method:** `GET`
- **URL:** `/api/doctor/events`
- **Auth:** Yes — Doctor

**Success (200):** `{ "success": true, "data": { "events": [] } }`

**Errors:** `401`.

---

### Doctor show event

- **Method:** `GET`
- **URL:** `/api/doctor/events/{id}`
- **Auth:** Yes — Doctor

**Success (200):** `{ "success": true, "data": { "event": {} } }`

**Errors:** `401`, `404`.

---

### Doctor list event requests

- **Method:** `GET`
- **URL:** `/api/doctor/event-requests`
- **Auth:** Yes — Doctor

**Success (200):** `{ "success": true, "data": { "requests": [] } }`

**Errors:** `401`.

---

### Doctor create event request

- **Method:** `POST`
- **URL:** `/api/doctor/event-requests`
- **Auth:** Yes — Doctor

**Body:**

```json
{
  "event_id": 1,
  "message": "I would like to attend"
}
```

**Success (201):** `{ "success": true, "data": { "request": { "id": 1 } } }`

**Errors:** `401`, `422`.

---

### Doctor list invitations

- **Method:** `GET`
- **URL:** `/api/doctor/invitations`
- **Auth:** Yes — Doctor

**Success (200):** `{ "success": true, "data": { "invitations": [] } }`

**Errors:** `401`.

---

### Doctor accept invitation

- **Method:** `POST`
- **URL:** `/api/doctor/invitations/{id}/accept`
- **Auth:** Yes — Doctor

**Body:** `{}`

**Success (200):** `{ "success": true, "message": "Invitation accepted" }`

**Errors:** `401`, `404`, `422`.

---

### Doctor decline invitation

- **Method:** `POST`
- **URL:** `/api/doctor/invitations/{id}/decline`
- **Auth:** Yes — Doctor

**Body:** `{ "reason": "Schedule conflict" }` (optional)

**Success (200):** `{ "success": true, "message": "Invitation declined" }`

**Errors:** `401`, `404`.

---

### Doctor points history

- **Method:** `GET`
- **URL:** `/api/doctor/points`
- **Auth:** Yes — Doctor

**Success (200):**

```json
{
  "success": true,
  "data": {
    "points": [
      {
        "id": 1,
        "points": 10,
        "description": "Meeting completed with rep",
        "source_type": "meeting"
      }
    ]
  }
}
```

**Errors:** `401`.

---

### Doctor points total

- **Method:** `GET`
- **URL:** `/api/doctor/points/total`
- **Auth:** Yes — Doctor

**Success (200):** `{ "success": true, "data": { "total": 150 } }`

**Errors:** `401`.

---

### Doctor list rewards

- **Method:** `GET`
- **URL:** `/api/doctor/rewards`
- **Auth:** Yes — Doctor

**Success (200):** `{ "success": true, "data": { "rewards": [] } }`

**Errors:** `401`.

---

### Doctor list redemptions

- **Method:** `GET`
- **URL:** `/api/doctor/redemptions`
- **Auth:** Yes — Doctor

**Success (200):** `{ "success": true, "data": { "redemptions": [] } }`

**Errors:** `401`.

---

### Doctor redeem reward

- **Method:** `POST`
- **URL:** `/api/doctor/rewards/{rewardId}/redeem`
- **Auth:** Yes — Doctor

**Body:** `{}` or `{ "shipping_address": "..." }` if required

**Success (201):** `{ "success": true, "data": { "redemption": { "id": 1, "status": "pending" } } }`

**Errors:** `401`, `404`, `422` insufficient points.

---

### Doctor list posts

- **Method:** `GET`
- **URL:** `/api/doctor/posts`
- **Auth:** Yes — Doctor

**Success (200):** `{ "success": true, "data": { "posts": [] } }`

**Errors:** `401`.

---

### Doctor create post

- **Method:** `POST`
- **URL:** `/api/doctor/posts`
- **Auth:** Yes — Doctor

**Body:**

```json
{
  "body": "Sharing clinical experience",
  "tag_ids": [1]
}
```

**Success (201):** `{ "success": true, "data": { "post": { "id": 1 } } }`

**Errors:** `401`, `422`.

---

### Doctor show post

- **Method:** `GET`
- **URL:** `/api/doctor/posts/{id}`
- **Auth:** Yes — Doctor

**Success (200):** `{ "success": true, "data": { "post": {} } }`

**Errors:** `401`, `404`.

---

### Doctor update post

- **Method:** `PUT`
- **URL:** `/api/doctor/posts/{id}`
- **Auth:** Yes — Doctor

**Success (200):** `{ "success": true, "data": { "post": {} } }`

**Errors:** `401`, `404`, `422`.

---

### Doctor delete post

- **Method:** `DELETE`
- **URL:** `/api/doctor/posts/{id}`
- **Auth:** Yes — Doctor

**Success (200):** `{ "success": true, "message": "Post deleted" }`

**Errors:** `401`, `404`.

---

### Doctor add comment

- **Method:** `POST`
- **URL:** `/api/doctor/posts/{postId}/comments`
- **Auth:** Yes — Doctor

**Body:** `{ "body": "Comment text" }`

**Success (201):** `{ "success": true, "data": { "comment": { "id": 1 } } }`

**Errors:** `401`, `404`, `422`.

---

### Doctor delete comment

- **Method:** `DELETE`
- **URL:** `/api/doctor/comments/{id}`
- **Auth:** Yes — Doctor

**Success (200):** `{ "success": true, "message": "Comment deleted" }`

**Errors:** `401`, `404`.

---

### Doctor like post

- **Method:** `POST`
- **URL:** `/api/doctor/posts/{postId}/like`
- **Auth:** Yes — Doctor

**Success (200):** `{ "success": true, "data": { "liked": true } }`

**Errors:** `401`, `404`.

---

### Doctor unlike post

- **Method:** `DELETE`
- **URL:** `/api/doctor/posts/{postId}/unlike`
- **Auth:** Yes — Doctor

**Success (200):** `{ "success": true, "data": { "liked": false } }`

**Errors:** `401`, `404`.

---

### Doctor list messages

- **Method:** `GET`
- **URL:** `/api/doctor/messages`
- **Auth:** Yes — Doctor

**Success (200):** `{ "success": true, "data": { "messages": [] } }`

**Errors:** `401`.

---

### Doctor send message

- **Method:** `POST`
- **URL:** `/api/doctor/messages`
- **Auth:** Yes — Doctor

**Body:**

```json
{
  "receiver_type": "medical_rep",
  "receiver_id": 1,
  "body": "Hello rep"
}
```

**Success (201):** `{ "success": true, "data": { "message": { "id": 1 } } }`

**Errors:** `401`, `422`.

---

### Doctor mark message read

- **Method:** `POST`
- **URL:** `/api/doctor/messages/{id}/read`
- **Auth:** Yes — Doctor

**Success (200):** `{ "success": true }`

**Errors:** `401`, `404`.

---

### Doctor list notifications

- **Method:** `GET`
- **URL:** `/api/doctor/notifications`
- **Auth:** Yes — Doctor

**Success (200):** `{ "success": true, "data": { "notifications": [] } }`

**Errors:** `401`.

---

### Doctor mark notification read

- **Method:** `POST`
- **URL:** `/api/doctor/notifications/{id}/read`
- **Auth:** Yes — Doctor

**Success (200):** `{ "success": true }`

**Errors:** `401`, `404`.

---

### Doctor mark all notifications read

- **Method:** `POST`
- **URL:** `/api/doctor/notifications/read-all`
- **Auth:** Yes — Doctor

**Success (200):** `{ "success": true, "message": "All read" }`

**Errors:** `401`.

---

### Doctor generate report

- **Method:** `GET`
- **URL:** `/api/doctor/report/generate`
- **Auth:** Yes — Doctor

**Query (example):** `?from=2026-01-01&to=2026-04-01`

**Success (200):**

```json
{
  "success": true,
  "data": {
    "report": {
      "summary": {},
      "generated_at": "2026-04-18T12:00:00Z"
    }
  }
}
```

Or file download — follow `Content-Type` / `Content-Disposition` if the app returns a file.

**Errors:** `401`, `422`.

---

# MEDICAL REP

All routes require: `Authorization: Bearer {rep_token}`

---

### Rep list assigned doctors

- **Method:** `GET`
- **URL:** `/api/rep/doctors`
- **Auth:** Yes — Rep

**Success (200):** `{ "success": true, "data": { "doctors": [] } }`

**Errors:** `401`.

---

### Rep show doctor

- **Method:** `GET`
- **URL:** `/api/rep/doctors/{id}`
- **Auth:** Yes — Rep

**Success (200):** `{ "success": true, "data": { "doctor": {} } }`

**Errors:** `401`, `404`.

---

### Rep list meetings

- **Method:** `GET`
- **URL:** `/api/rep/meetings`
- **Auth:** Yes — Rep

**Success (200):** `{ "success": true, "data": { "meetings": [] } }`

**Errors:** `401`.

---

### Rep schedule meeting

- **Method:** `POST`
- **URL:** `/api/rep/meetings`
- **Auth:** Yes — Rep

**Body:**

```json
{
  "doctor_id": 1,
  "scheduled_at": "2026-05-10T14:00:00Z",
  "notes": "Product discussion"
}
```

**Success (201):** `{ "success": true, "data": { "meeting": { "id": 1, "status": "scheduled" } } }`

**Errors:** `401`, `422`, `403` doctor not assigned.

---

### Rep show meeting

- **Method:** `GET`
- **URL:** `/api/rep/meetings/{id}`
- **Auth:** Yes — Rep

**Success (200):** `{ "success": true, "data": { "meeting": {} } }`

**Errors:** `401`, `404`.

---

### Rep complete meeting

- **Method:** `POST`
- **URL:** `/api/rep/meetings/{id}/complete`
- **Auth:** Yes — Rep

**Body:** None

**Success (200):**

```json
{
  "success": true,
  "message": "Meeting completed",
  "data": {
    "meeting": {
      "id": 1,
      "status": "completed"
    }
  }
}
```

**Errors:** `401`, `404`, `422` wrong status.

---

### Rep cancel meeting

- **Method:** `POST`
- **URL:** `/api/rep/meetings/{id}/cancel`
- **Auth:** Yes — Rep

**Success (200):** `{ "success": true, "data": { "meeting": { "status": "cancelled" } } }`

**Errors:** `401`, `404`, `422`.

---

### Rep meeting video room

- **Method:** `GET`
- **URL:** `/api/rep/meetings/{id}/video-room`
- **Auth:** Yes — Rep

**Success (200):** `{ "success": true, "data": { "room_url": "https://meet.jit.si/...", "room_name": "..." } }`

**Errors:** `401`, `403`, `404`.

---

### Rep list samples

- **Method:** `GET`
- **URL:** `/api/rep/samples`
- **Auth:** Yes — Rep

**Success (200):** `{ "success": true, "data": { "samples": [] } }`

**Errors:** `401`.

---

### Rep show sample

- **Method:** `GET`
- **URL:** `/api/rep/samples/{id}`
- **Auth:** Yes — Rep

**Success (200):** `{ "success": true, "data": { "sample": {} } }`

**Errors:** `401`, `404`.

---

### Rep approve sample

- **Method:** `POST`
- **URL:** `/api/rep/samples/{id}/approve`
- **Auth:** Yes — Rep

**Body:** `{}`

**Success (200):** `{ "success": true, "data": { "sample": { "status": "approved" } } }`

**Errors:** `401`, `404`, `422`.

---

### Rep reject sample

- **Method:** `POST`
- **URL:** `/api/rep/samples/{id}/reject`
- **Auth:** Yes — Rep

**Body:** `{ "reason": "Out of stock" }`

**Success (200):** `{ "success": true, "message": "Sample request rejected" }`

**Errors:** `401`, `404`, `422`.

---

### Rep deliver sample

- **Method:** `POST`
- **URL:** `/api/rep/samples/{id}/deliver`
- **Auth:** Yes — Rep

**Success (200):** `{ "success": true, "data": { "sample": { "status": "delivered" } } }`

**Errors:** `401`, `404`, `422`.

---

### Rep list drugs

- **Method:** `GET`
- **URL:** `/api/rep/drugs`
- **Auth:** Yes — Rep

**Success (200):** `{ "success": true, "data": { "drugs": [] } }`

**Errors:** `401`.

---

### Rep show drug

- **Method:** `GET`
- **URL:** `/api/rep/drugs/{id}`
- **Auth:** Yes — Rep

**Success (200):** `{ "success": true, "data": { "drug": {} } }`

**Errors:** `401`, `404`.

---

### Rep list targets

- **Method:** `GET`
- **URL:** `/api/rep/targets`
- **Auth:** Yes — Rep

**Success (200):** `{ "success": true, "data": { "targets": [] } }`

**Errors:** `401`.

---

### Rep list invitations

- **Method:** `GET`
- **URL:** `/api/rep/invitations`
- **Auth:** Yes — Rep

**Success (200):** `{ "success": true, "data": { "invitations": [] } }`

**Errors:** `401`.

---

### Rep invite doctors to event

- **Method:** `POST`
- **URL:** `/api/rep/events/{eventId}/invite`
- **Auth:** Yes — Rep

**Body:**

```json
{
  "doctor_ids": [1, 2]
}
```

**Success (200):** `{ "success": true, "data": { "invited": 2 } }`

**Errors:** `401`, `404`, `422`.

---

### Rep list posts

- **Method:** `GET`
- **URL:** `/api/rep/posts`
- **Auth:** Yes — Rep

**Success (200):** `{ "success": true, "data": { "posts": [] } }`

**Errors:** `401`.

---

### Rep create post

- **Method:** `POST`
- **URL:** `/api/rep/posts`
- **Auth:** Yes — Rep

**Body:** `{ "body": "Rep update", "tag_ids": [] }`

**Success (201):** `{ "success": true, "data": { "post": { "id": 1 } } }`

**Errors:** `401`, `422`.

---

### Rep show post

- **Method:** `GET`
- **URL:** `/api/rep/posts/{id}`
- **Auth:** Yes — Rep

**Success (200):** `{ "success": true, "data": { "post": {} } }`

**Errors:** `401`, `404`.

---

### Rep update post

- **Method:** `PUT`
- **URL:** `/api/rep/posts/{id}`
- **Auth:** Yes — Rep

**Success (200):** `{ "success": true, "data": { "post": {} } }`

**Errors:** `401`, `404`, `422`.

---

### Rep delete post

- **Method:** `DELETE`
- **URL:** `/api/rep/posts/{id}`
- **Auth:** Yes — Rep

**Success (200):** `{ "success": true, "message": "Post deleted" }`

**Errors:** `401`, `404`.

---

### Rep add comment

- **Method:** `POST`
- **URL:** `/api/rep/posts/{postId}/comments`
- **Auth:** Yes — Rep

**Body:** `{ "body": "Comment" }`

**Success (201):** `{ "success": true, "data": { "comment": { "id": 1 } } }`

**Errors:** `401`, `404`, `422`.

---

### Rep delete comment

- **Method:** `DELETE`
- **URL:** `/api/rep/comments/{id}`
- **Auth:** Yes — Rep

**Success (200):** `{ "success": true, "message": "Comment deleted" }`

**Errors:** `401`, `404`.

---

### Rep like post

- **Method:** `POST`
- **URL:** `/api/rep/posts/{postId}/like`
- **Auth:** Yes — Rep

**Success (200):** `{ "success": true, "data": { "liked": true } }`

**Errors:** `401`, `404`.

---

### Rep unlike post

- **Method:** `DELETE`
- **URL:** `/api/rep/posts/{postId}/unlike`
- **Auth:** Yes — Rep

**Success (200):** `{ "success": true, "data": { "liked": false } }`

**Errors:** `401`, `404`.

---

### Rep list messages

- **Method:** `GET`
- **URL:** `/api/rep/messages`
- **Auth:** Yes — Rep

**Success (200):** `{ "success": true, "data": { "messages": [] } }`

**Errors:** `401`.

---

### Rep send message

- **Method:** `POST`
- **URL:** `/api/rep/messages`
- **Auth:** Yes — Rep

**Body:**

```json
{
  "receiver_type": "doctor",
  "receiver_id": 1,
  "body": "Follow-up after visit"
}
```

**Success (201):** `{ "success": true, "data": { "message": { "id": 1 } } }`

**Errors:** `401`, `422`.

---

### Rep mark message read

- **Method:** `POST`
- **URL:** `/api/rep/messages/{id}/read`
- **Auth:** Yes — Rep

**Success (200):** `{ "success": true }`

**Errors:** `401`, `404`.

---

### Rep list notifications

- **Method:** `GET`
- **URL:** `/api/rep/notifications`
- **Auth:** Yes — Rep

**Success (200):** `{ "success": true, "data": { "notifications": [] } }`

**Errors:** `401`.

---

### Rep mark notification read

- **Method:** `POST`
- **URL:** `/api/rep/notifications/{id}/read`
- **Auth:** Yes — Rep

**Success (200):** `{ "success": true }`

**Errors:** `401`, `404`.

---

### Rep mark all notifications read

- **Method:** `POST`
- **URL:** `/api/rep/notifications/read-all`
- **Auth:** Yes — Rep

**Success (200):** `{ "success": true, "message": "All read" }`

**Errors:** `401`.

---

## Broadcast authentication (WebSockets)

`POST /broadcasting/auth` (Laravel default) — used by Echo for private channels. Requires a valid Sanctum session or token per your `Broadcast::routes` setup in `routes/api.php`. Not a REST business endpoint; see Laravel broadcasting docs.

---

*Generated for the E-Rep Laravel API. Field names and nested keys are illustrative; always confirm against live responses and OpenAPI / `l5-swagger` if published.*
