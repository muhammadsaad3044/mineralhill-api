# Mineral Hill Shilajit — Backend API Documentation

Base URL: `http://localhost:8000/api/v1`  
All responses are formatted as JSON (`Accept: application/json`).

---

## 1. Authentication & Session

### 1.1 Admin Login
- **Method:** `POST`
- **URL:** `/admin/login`
- **Auth:** None (Public). Rate limited to 5 attempts per minute.
- **Request Body (JSON):**
  ```json
  {
    "email": "admin@mineralhill.com",
    "password": "admin123"
  }
  ```
- **Success Response (`200 OK`):**
  ```json
  {
    "success": true,
    "message": "Login successful.",
    "token": "1|AbCdEf123456...",
    "user": {
      "id": 1,
      "name": "Mineral Hill Admin",
      "email": "admin@mineralhill.com",
      "isAdmin": true,
      "createdAt": "2026-11-25T11:00:00.000000Z"
    }
  }
  ```
- **Error Response (`422 Unprocessable Entity`):**
  ```json
  {
    "message": "The provided credentials do not match our records.",
    "errors": {
      "email": ["The provided credentials do not match our records."]
    }
  }
  ```

### 1.2 Get Current Admin Profile
- **Method:** `GET`
- **URL:** `/admin/me`
- **Auth:** `Bearer <token>` (`auth:sanctum` + `admin`)
- **Success Response (`200 OK`):**
  ```json
  {
    "success": true,
    "user": {
      "id": 1,
      "name": "Mineral Hill Admin",
      "email": "admin@mineralhill.com",
      "isAdmin": true
    }
  }
  ```

### 1.3 Admin Logout
- **Method:** `POST`
- **URL:** `/admin/logout`
- **Auth:** `Bearer <token>` (`auth:sanctum` + `admin`)
- **Success Response (`200 OK`):**
  ```json
  {
    "success": true,
    "message": "Successfully logged out."
  }
  ```

---

## 2. Public Storefront Endpoints

### 2.1 Get Public Settings
Returns the store branding logo, WhatsApp configuration, enabled social links (in saved order), and enabled contact links.
- **Method:** `GET`
- **URL:** `/settings`
- **Auth:** None (Public, Cached)
- **Success Response (`200 OK`):**
  ```json
  {
    "success": true,
    "data": {
      "logo": "http://localhost:8000/storage/brand/logo_xyz.png",
      "whatsapp": {
        "enabled": true,
        "phoneNumber": "+92 300 1234567",
        "defaultMessage": "Hello Mineral Hill Shilajit! I would like to inquire about pure Himalayan Shilajit."
      },
      "socialLinks": [
        {
          "id": 1,
          "platform": "Facebook",
          "label": "Facebook",
          "url": "https://facebook.com/mineralhillshilajit",
          "enabled": true,
          "sortOrder": 1
        },
        {
          "id": 2,
          "platform": "Instagram",
          "label": "Instagram",
          "url": "https://instagram.com/mineralhillshilajit",
          "enabled": true,
          "sortOrder": 2
        }
      ],
      "accountLinks": [
        {
          "id": 1,
          "type": "Email",
          "label": "Customer Support Email",
          "value": "support@mineralhillshilajit.com",
          "enabled": true,
          "sortOrder": 1
        }
      ]
    }
  }
  ```

### 2.2 Get Public Testimonials
Returns only testimonials with status `Published`, with `featured` testimonials prioritized.
- **Method:** `GET`
- **URL:** `/testimonials` (Optional query parameter: `?featured=1`)
- **Auth:** None (Public, Cached)
- **Success Response (`200 OK`):**
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 1,
        "name": "Dr. Tariq Jamil",
        "role": "Wellness Consultant, Lahore",
        "locationOrRole": "Wellness Consultant, Lahore",
        "avatar": "http://localhost:8000/storage/avatars/avatar_123.jpg",
        "rating": 5,
        "product": "Gold Grade Shilajit Resin (50g)",
        "review": "The purity of Mineral Hill Shilajit is unmatched...",
        "date": "2026-11-21",
        "status": "Published",
        "featured": true
      }
    ]
  }
  ```

---

## 3. Admin Site Settings Management

### 3.1 Get Admin Settings
- **Method:** `GET`
- **URL:** `/admin/settings`
- **Auth:** `Bearer <token>`
- **Success Response (`200 OK`):**
  ```json
  {
    "success": true,
    "data": {
      "logo": "http://localhost:8000/storage/brand/logo_xyz.png",
      "whatsapp": {
        "enabled": true,
        "phoneNumber": "+92 300 1234567",
        "defaultMessage": "Hello Mineral Hill Shilajit!..."
      },
      "socialLinks": [...],
      "accountLinks": [...]
    }
  }
  ```

### 3.2 Update WhatsApp Configuration
- **Method:** `PUT`
- **URL:** `/admin/settings/whatsapp`
- **Auth:** `Bearer <token>`
- **Request Body (JSON):**
  ```json
  {
    "phoneNumber": "+923001234567",
    "defaultMessage": "Hello Mineral Hill Shilajit! I would like to inquire about pure Himalayan Shilajit.",
    "enabled": true
  }
  ```
- **Success Response (`200 OK`):**
  ```json
  {
    "success": true,
    "message": "WhatsApp configuration updated successfully.",
    "data": {
      "enabled": true,
      "phoneNumber": "+923001234567",
      "defaultMessage": "Hello Mineral Hill Shilajit!..."
    }
  }
  ```

### 3.3 Delete / Disable WhatsApp Number
- **Method:** `DELETE`
- **URL:** `/admin/settings/whatsapp`
- **Auth:** `Bearer <token>`
- **Success Response (`200 OK`):**
  ```json
  {
    "success": true,
    "message": "WhatsApp number deleted and disabled successfully.",
    "data": {
      "enabled": false,
      "phoneNumber": "",
      "defaultMessage": "Hello Mineral Hill Shilajit!..."
    }
  }
  ```

### 3.4 Upload Brand Logo
- **Method:** `POST`
- **URL:** `/admin/settings/logo`
- **Auth:** `Bearer <token>`
- **Content-Type:** `multipart/form-data`
- **Request Body:**
  - `logo`: File (PNG, JPG, JPEG, SVG, WEBP; max 2 MB). SVG is automatically parsed and sanitized against XSS.
- **Success Response (`200 OK`):**
  ```json
  {
    "success": true,
    "message": "Brand logo uploaded successfully.",
    "data": {
      "logo": "http://localhost:8000/storage/brand/logo_abc.png",
      "whatsapp": {...},
      "socialLinks": [...],
      "accountLinks": [...]
    }
  }
  ```

### 3.5 Remove Brand Logo
- **Method:** `DELETE`
- **URL:** `/admin/settings/logo`
- **Auth:** `Bearer <token>`
- **Success Response (`200 OK`):**
  ```json
  {
    "success": true,
    "message": "Brand logo removed successfully.",
    "data": {
      "logo": null,
      "whatsapp": {...},
      "socialLinks": [...],
      "accountLinks": [...]
    }
  }
  ```

### 3.6 Reset All Settings
- **Method:** `POST`
- **URL:** `/admin/settings/reset`
- **Auth:** `Bearer <token>`
- **Success Response (`200 OK`):**
  ```json
  {
    "success": true,
    "message": "All settings reset to default successfully.",
    "data": {
      "logo": null,
      "whatsapp": {
        "enabled": true,
        "phoneNumber": "",
        "defaultMessage": "Hello Mineral Hill Shilajit!..."
      },
      "socialLinks": [...],
      "accountLinks": [...]
    }
  }
  ```

---

## 4. Admin Social Links Management

### 4.1 List Social Links
- **Method:** `GET`
- **URL:** `/admin/social-links`
- **Auth:** `Bearer <token>`

### 4.2 Create Social Link
- **Method:** `POST`
- **URL:** `/admin/social-links`
- **Auth:** `Bearer <token>`
- **Request Body (JSON):**
  ```json
  {
    "platform": "Telegram",
    "label": "VIP Community",
    "url": "https://t.me/mineralhill",
    "enabled": true
  }
  ```
- **Success Response (`201 Created`):**
  ```json
  {
    "success": true,
    "message": "Social link created successfully.",
    "data": {
      "id": 5,
      "platform": "Telegram",
      "label": "VIP Community",
      "url": "https://t.me/mineralhill",
      "enabled": true,
      "sortOrder": 5
    }
  }
  ```

### 4.3 Update Social Link
- **Method:** `PUT`
- **URL:** `/admin/social-links/{id}`
- **Auth:** `Bearer <token>`
- **Request Body (JSON):**
  ```json
  {
    "url": "https://t.me/mineralhill_updated",
    "enabled": false
  }
  ```

### 4.4 Delete Social Link
- **Method:** `DELETE`
- **URL:** `/admin/social-links/{id}`
- **Auth:** `Bearer <token>`

### 4.5 Reorder Social Links
- **Method:** `POST`
- **URL:** `/admin/social-links/reorder`
- **Auth:** `Bearer <token>`
- **Request Body (JSON):**
  ```json
  {
    "ids": [4, 2, 1, 3]
  }
  ```

---

## 5. Admin Account & Contact Links Management

### 5.1 List Account Links
- **Method:** `GET`
- **URL:** `/admin/account-links`
- **Auth:** `Bearer <token>`

### 5.2 Create Account Link
- **Method:** `POST`
- **URL:** `/admin/account-links`
- **Auth:** `Bearer <token>`
- **Request Body (JSON):**
  ```json
  {
    "type": "Phone",
    "label": "Direct Phone Helpline",
    "value": "+92 300 1234567",
    "enabled": true
  }
  ```
- **Success Response (`201 Created`):**
  ```json
  {
    "success": true,
    "message": "Account link created successfully.",
    "data": {
      "id": 5,
      "type": "Phone",
      "label": "Direct Phone Helpline",
      "value": "+92 300 1234567",
      "enabled": true,
      "sortOrder": 5
    }
  }
  ```

### 5.3 Update Account Link
- **Method:** `PUT`
- **URL:** `/admin/account-links/{id}`
- **Auth:** `Bearer <token>`

### 5.4 Delete Account Link
- **Method:** `DELETE`
- **URL:** `/admin/account-links/{id}`
- **Auth:** `Bearer <token>`

### 5.5 Reorder Account Links
- **Method:** `POST`
- **URL:** `/admin/account-links/reorder`
- **Auth:** `Bearer <token>`
- **Request Body (JSON):**
  ```json
  {
    "ids": [3, 1, 2, 4]
  }
  ```

---

## 6. Admin Testimonials Management

### 6.1 List Testimonials (With Search, Filters, Sorting & Stats)
- **Method:** `GET`
- **URL:** `/admin/testimonials?search=&status=All&rating=All&sortBy=newest&page=1&per_page=20`
- **Auth:** `Bearer <token>`
- **Success Response (`200 OK`):**
  ```json
  {
    "success": true,
    "testimonials": [
      {
        "id": 1,
        "name": "Dr. Tariq Jamil",
        "role": "Wellness Consultant, Lahore",
        "locationOrRole": "Wellness Consultant, Lahore",
        "avatar": "http://localhost:8000/storage/avatars/avatar_xyz.jpg",
        "rating": 5,
        "product": "Gold Grade Shilajit Resin (50g)",
        "review": "The purity of Mineral Hill Shilajit is unmatched...",
        "date": "2026-11-21",
        "status": "Published",
        "featured": true
      }
    ],
    "stats": {
      "total": 10,
      "published": 7,
      "pending": 2,
      "hidden": 1,
      "averageRating": "4.6",
      "distribution": [
        { "stars": 5, "count": 6, "percentage": 60 },
        { "stars": 4, "count": 2, "percentage": 20 },
        { "stars": 3, "count": 1, "percentage": 10 },
        { "stars": 2, "count": 1, "percentage": 10 },
        { "stars": 1, "count": 0, "percentage": 0 }
      ]
    },
    "pagination": {
      "currentPage": 1,
      "lastPage": 1,
      "perPage": 20,
      "total": 10
    }
  }
  ```

### 6.2 Get Single Testimonial
- **Method:** `GET`
- **URL:** `/admin/testimonials/{id}`
- **Auth:** `Bearer <token>`

### 6.3 Create Testimonial
- **Method:** `POST`
- **URL:** `/admin/testimonials`
- **Auth:** `Bearer <token>`
- **Content-Type:** `multipart/form-data` or `application/json`
- **Parameters:**
  - `name`: string (required)
  - `role` / `location_or_role`: string (required)
  - `avatar`: file (optional image) or string URL
  - `rating`: integer 1-5 (required)
  - `product`: string (required)
  - `review`: string min 20, max 300 (required)
  - `date` / `reviewed_at`: date (optional)
  - `status`: "published" | "pending" | "hidden" (optional, default: "published")
  - `featured`: boolean (optional, default: false)
- **Success Response (`201 Created`)**

### 6.4 Update Testimonial
- **Method:** `PUT` or `POST`
- **URL:** `/admin/testimonials/{id}`
- **Auth:** `Bearer <token>`

### 6.5 Delete Testimonial
- **Method:** `DELETE`
- **URL:** `/admin/testimonials/{id}`
- **Auth:** `Bearer <token>`

### 6.6 Bulk Update Status
- **Method:** `POST`
- **URL:** `/admin/testimonials/bulk-status`
- **Auth:** `Bearer <token>`
- **Request Body (JSON):**
  ```json
  {
    "ids": [6, 7],
    "status": "published"
  }
  ```
- **Success Response (`200 OK`):**
  ```json
  {
    "success": true,
    "message": "Successfully updated status for 2 testimonials.",
    "updatedCount": 2
  }
  ```

### 6.7 Bulk Delete Testimonials
- **Method:** `POST`
- **URL:** `/admin/testimonials/bulk-delete`
- **Auth:** `Bearer <token>`
- **Request Body (JSON):**
  ```json
  {
    "ids": [8]
  }
  ```
- **Success Response (`200 OK`):**
  ```json
  {
    "success": true,
    "message": "Successfully deleted 1 testimonials.",
    "deletedCount": 1
  }
  ```
