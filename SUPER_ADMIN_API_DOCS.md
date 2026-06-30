# Super Admin API Documentation

## Overview
This API provides full access to all Super Admin panel functionality including:
- Authentication (Login/Logout)
- Dashboard statistics
- Admin management
- Business partner management (Buyers & Craftsmen)
- Work order management
- Purchase order management
- Product management
- Design approval
- Catalogue access

All endpoints are protected by Laravel Sanctum authentication.

## Base URL
```
http://127.0.0.1:8000/api/super-admin
```

**✅ Correct URL for login:** `http://127.0.0.1:8000/api/super-admin/login`

## Authentication

### Login
**POST** `/login`

**Request:**
```json
{
  "email_or_user_code": "SA0001",
  "password": "your-password"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "user_code": "SA0001",
      "full_name": "Super Admin",
      "email_id": "admin@example.com",
      "role": "super_admin"
    },
    "token": "1|abcdefghijklmnopqrstuvwxyz123456"
  }
}
```

### Logout
**POST** `/logout`
*Requires authentication header*

**Headers:**
```
Authorization: Bearer YOUR_TOKEN_HERE
```

**Response:**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

## Dashboard

### Get Dashboard Statistics
**GET** `/dashboard/stats`
*Requires authentication*

**Response:**
```json
{
  "success": true,
  "data": {
    "totalBusinessPartners": 45,
    "totalBuyers": 20,
    "totalCraftsmen": 25,
    "pendingKycCount": 0,
    "totalAdmins": 5,
    "totalKeyUsers": 12,
    "totalUsers": 30,
    "totalWorkOrders": 150,
    "totalProducts": 200,
    "totalDesigns": 180,
    "totalCatalogues": 175,
    "totalPurchaseOrders": 80,
    "financeTotal": 1250000
  }
}
```

## Admin Management

### Get All Admins
**GET** `/admins`
*Requires authentication*

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_code": "AD0001",
      "full_name": "Admin User",
      "email_id": "admin@example.com",
      "mobile_no": "9876543210",
      "role": "admin",
      "permissions": ["business_partner", "work_order"]
    }
  ]
}
```

### Create Admin
**POST** `/admins`
*Requires authentication*

**Request:**
```json
{
  "full_name": "New Admin",
  "email_id": "newadmin@example.com",
  "mobile_no": "9876543210",
  "password": "password123",
  "password_confirmation": "password123",
  "city": "Chennai",
  "state": "Tamil Nadu",
  "permissions": ["business_partner", "work_order", "product"]
}
```

### Update Admin
**PUT** `/admins/{admin_id}`
*Requires authentication*

### Delete Admin
**DELETE** `/admins/{admin_id}`
*Requires authentication*

## Business Partners

### Get Buyers
**GET** `/buyers`
*Requires authentication*

**Query Parameters:**
- `search` - Search term
- `bp_code` - Filter by BP code
- `business_name` - Filter by business name
- `city` - Filter by city
- `state` - Filter by state
- `sort_by` - Sort column (default: created_at)
- `sort_order` - Sort order (asc/desc, default: desc)
- `per_page` - Items per page (default: 15)

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "bp_code": "BP0001",
        "business_name": "ABC Jewelry",
        "name": "John Doe",
        "mobile": "9876543210",
        "email": "john@abc.com",
        "city": "Chennai",
        "state": "Tamil Nadu"
      }
    ],
    "total": 20
  }
}
```

### Create Buyer
**POST** `/buyers`
*Requires authentication*

**Request:**
```json
{
  "business_name": "ABC Jewelry",
  "name": "John Doe",
  "mobile": "9876543210",
  "email": "john@abc.com",
  "city": "Chennai",
  "state": "Tamil Nadu",
  "password": "password123",
  "password_confirmation": "password123",
  "aadhar_name": ["John Doe"],
  "aadhar_number": ["123456789012"],
  "pan_number": ["ABCDE1234F"]
}
```

### Get Single Buyer
**GET** `/buyers/{id}`
*Requires authentication*

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "bp_code": "BA001",
    "business_name": "Malabar",
    "name": "kavin",
    "mobile": "8790123456",
    "email": "malabar@gmail.com",
    "city": null,
    "state": null,
    "permissions": "[\"product\",\"design\",\"catalogue\"]",
    "aadhar_details": [
      {
        "id": 1,
        "buyer_id": 1,
        "aadhar_name": "ara",
        "aadhar_number": "87877878787878"
      }
    ],
    "pan_details": [
      {
        "id": 1,
        "buyer_id": 1,
        "pan_number": "JKFHGFJGJHGF"
      }
    ],
    "bank_details": []
  }
}
```

### Update Buyer
**PUT** `/buyers/{id}`
*Requires authentication*

**Request:**
```json
{
  "business_name": "Malabar Updated",
  "name": "kavin kumar",
  "mobile": "8790123456",
  "email": "malabar@gmail.com",
  "city": "Chennai",
  "state": "Tamil Nadu",
  "password": "newpassword",
  "password_confirmation": "newpassword",
  "aadhar_name": ["ara", "new aadhar"],
  "aadhar_number": ["87877878787878", "999999999999"],
  "pan_number": ["JKFHGFJGJHGF", "NEWPAN1234"],
  "gst_no": "33ABCDE1234F1Z5",
  "cin_no": "U12345TN1990PTC123456",
  "permissions": ["product", "design", "catalogue"]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Buyer updated successfully",
  "data": {
    "id": 1,
    "bp_code": "BA001",
    "business_name": "Malabar Updated",
    "name": "kavin kumar",
    "mobile": "8790123456",
    "email": "malabar@gmail.com",
    "city": "Chennai",
    "state": "Tamil Nadu",
    "permissions": "[\"product\",\"design\",\"catalogue\"]",
    "aadhar_details": [
      {
        "id": 6,
        "buyer_id": 1,
        "aadhar_name": "ara",
        "aadhar_number": "87877878787878"
      },
      {
        "id": 7,
        "buyer_id": 1,
        "aadhar_name": "new aadhar",
        "aadhar_number": "999999999999"
      }
    ],
    "pan_details": [
      {
        "id": 6,
        "buyer_id": 1,
        "pan_number": "JKFHGFJGJHGF"
      },
      {
        "id": 7,
        "buyer_id": 1,
        "pan_number": "NEWPAN1234"
      }
    ],
    "bank_details": []
  }
}
```

### Delete Buyer
**DELETE** `/buyers/{id}`
*Requires authentication*

**Response:**
```json
{
  "success": true,
  "message": "Buyer deleted successfully"
}
```

### Get Craftsmen
**GET** `/craftsmen`
*Requires authentication*

**Query Parameters:**
- `search` - Search term
- `craftman_code` - Filter by craftsman code
- `business_name` - Filter by business name
- `city` - Filter by city
- `state` - Filter by state
- `sort_by` - Sort column (default: created_at)
- `sort_order` - Sort order (asc/desc, default: desc)
- `per_page` - Items per page (default: 15)

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "craftman_code": "CA001",
        "business_name": "Test Craftman",
        "name": "Test Person",
        "mobile": "9999999999",
        "email": "test@example.com",
        "city": "Test City",
        "state": "Test State",
        "aadhar_details": [
          {
            "id": 1,
            "craftman_id": 1,
            "aadhar_name": "Test Aadhar 1",
            "aadhar_number": "111111111111"
          }
        ],
        "pan_details": [
          {
            "id": 1,
            "craftman_id": 1,
            "pan_number": "TESTPAN1234"
          }
        ],
        "bank_details": []
      }
    ],
    "total": 1
  }
}
```

### Get Single Craftsman
**GET** `/craftsmen/{id}`
*Requires authentication*

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "craftman_code": "CA001",
    "business_name": "Test Craftman",
    "name": "Test Person",
    "mobile": "9999999999",
    "email": "test@example.com",
    "city": "Test City",
    "state": "Test State",
    "permissions": null,
    "aadhar_details": [
      {
        "id": 1,
        "craftman_id": 1,
        "aadhar_name": "Test Aadhar 1",
        "aadhar_number": "111111111111"
      }
    ],
    "pan_details": [
      {
        "id": 1,
        "craftman_id": 1,
        "pan_number": "TESTPAN1234"
      }
    ],
    "bank_details": []
  }
}
```

### Create Craftsman
**POST** `/craftsmen`
*Requires authentication*

**Request:**
```json
{
  "business_name": "Test Craftman",
  "name": "Test Person",
  "mobile": "9999999999",
  "email": "test@example.com",
  "city": "Test City",
  "state": "Test State",
  "password": "password123",
  "password_confirmation": "password123",
  "aadhar_name": ["Test Aadhar 1", "Test Aadhar 2"],
  "aadhar_number": ["111111111111", "222222222222"],
  "pan_number": ["TESTPAN1234", "TESTPAN5678"],
  "gst_no": "12ABCDE1234P1Z5",
  "cin_no": "U12345TG1990PTC123456"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Craftsman created successfully",
  "data": {
    "craftsman": {
      "id": 1,
      "craftman_code": "CA001",
      "business_name": "Test Craftman",
      "name": "Test Person",
      "mobile": "9999999999",
      "email": "test@example.com",
      "city": "Test City",
      "state": "Test State",
      "aadhar_details": [
        {
          "id": 1,
          "craftman_id": 1,
          "aadhar_name": "Test Aadhar 1",
          "aadhar_number": "111111111111"
        },
        {
          "id": 2,
          "craftman_id": 1,
          "aadhar_name": "Test Aadhar 2",
          "aadhar_number": "222222222222"
        }
      ],
      "pan_details": [
        {
          "id": 1,
          "craftman_id": 1,
          "pan_number": "TESTPAN1234"
        },
        {
          "id": 2,
          "craftman_id": 1,
          "pan_number": "TESTPAN5678"
        }
      ],
      "bank_details": []
    },
    "craftman_code": "CA001"
  }
}
```

### Update Craftsman
**PUT** `/craftsmen/{id}`
*Requires authentication*

**Request:**
```json
{
  "business_name": "Updated Test Craftman",
  "name": "Updated Test Person",
  "mobile": "9999999999",
  "email": "test@example.com",
  "city": "Updated City",
  "state": "Updated State",
  "password": "newpassword",
  "password_confirmation": "newpassword",
  "aadhar_name": ["Updated Aadhar 1", "Updated Aadhar 2", "Updated Aadhar 3"],
  "aadhar_number": ["111111111111", "222222222222", "333333333333"],
  "pan_number": ["UPDATPAN1234", "UPDATPAN5678"],
  "gst_no": "99ABCDE9876P9Z9",
  "cin_no": "U98765TG1990PTC987654"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Craftsman updated successfully",
  "data": {
    "id": 1,
    "craftman_code": "CA001",
    "business_name": "Updated Test Craftman",
    "name": "Updated Test Person",
    "mobile": "9999999999",
    "email": "test@example.com",
    "city": "Updated City",
    "state": "Updated State",
    "aadhar_details": [
      {
        "id": 3,
        "craftman_id": 1,
        "aadhar_name": "Updated Aadhar 1",
        "aadhar_number": "111111111111"
      },
      {
        "id": 4,
        "craftman_id": 1,
        "aadhar_name": "Updated Aadhar 2",
        "aadhar_number": "222222222222"
      },
      {
        "id": 5,
        "craftman_id": 1,
        "aadhar_name": "Updated Aadhar 3",
        "aadhar_number": "333333333333"
      }
    ],
    "pan_details": [
      {
        "id": 3,
        "craftman_id": 1,
        "pan_number": "UPDATPAN1234"
      },
      {
        "id": 4,
        "craftman_id": 1,
        "pan_number": "UPDATPAN5678"
      }
    ],
    "bank_details": []
  }
}
```

### Delete Craftsman
**DELETE** `/craftsmen/{id}`
*Requires authentication*

**Response:**
```json
{
  "success": true,
  "message": "Craftsman deleted successfully"
}
```

## Work Orders

### Get Work Orders
**GET** `/work-orders`
*Requires authentication*

**Query Parameters:**
- `status` - Filter by status (new, in_progress, completed, etc.)
- `category_id` - Filter by category
- `search` - Search term
- `sort_by` - Sort column (default: created_at)
- `sort_order` - Sort order (default: desc)
- `per_page` - Items per page (default: 10)

### Create Work Order
**POST** `/work-orders`
*Requires authentication*

**Request:**
```json
{
  "customer_name": "Customer Name",
  "quantity": 10,
  "product_name": "Gold Ring",
  "due_date": "2026-02-15",
  "product_category_id": 1,
  "subcategory_id": 2,
  "type": "Piece",
  "open_close": "Open",
  "weight_from": 5.0,
  "weight_to": 10.0,
  "hallmark": "BIS",
  "size": "Medium"
}
```

## Purchase Orders

### Get Purchase Orders
**GET** `/purchase-orders`
*Requires authentication*

**Query Parameters:**
- `status` - Filter by status
- `craftsman_status` - Filter by craftsman status
- `search` - Search term
- `sort_by` - Sort column
- `sort_order` - Sort order
- `per_page` - Items per page

### Create Purchase Order
**POST** `/purchase-orders`
*Requires authentication*

**Request:**
```json
{
  "due_date": "2026-02-20",
  "notes": "Special order",
  "items": [
    {
      "product_id": 1,
      "quantity": [2, 3],
      "grams": [5.5, 6.0]
    }
  ]
}
```

## Products

### Get Product Categories
**GET** `/product-categories`
*Requires authentication*

### Create Product Category
**POST** `/product-categories`
*Requires authentication*

**Request (Create Category):**
```json
{
  "name": "Bracelets",
  "has_hook": true,
  "has_enamel": true,
  "has_rodium": true,
  "has_open_close": false,
  "has_stone": true
}
```

**Request (Create Subcategory):**
```json
{
  "parent_category_id": 4,
  "name": "Cuff Bracelets"
}
```

**Responses:**

*Category Creation:*
```json
{
  "success": true,
  "message": "Category created successfully",
  "data": {
    "category": {
      "id": 4,
      "name": "Bracelets",
      "has_hook": true,
      "has_enamel": true,
      "has_rodium": true,
      "has_open_close": false,
      "has_stone": true,
      "created_at": "2026-01-30T06:39:23.000000Z",
      "updated_at": "2026-01-30T06:39:23.000000Z"
    }
  }
}
```

*Subcategory Creation:*
```json
{
  "success": true,
  "message": "Subcategory created successfully",
  "data": {
    "subcategory": {
      "id": 7,
      "product_category_id": 4,
      "name": "Cuff Bracelets",
      "created_at": "2026-01-30T06:39:40.000000Z",
      "updated_at": "2026-01-30T06:39:40.000000Z"
    }
  }
}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Rings",
      "has_hook": false,
      "has_enamel": true,
      "has_rodium": true,
      "has_open_close": true,
      "has_stone": true
    },
    {
      "id": 2,
      "name": "Necklaces",
      "has_hook": true,
      "has_enamel": true,
      "has_rodium": true,
      "has_open_close": false,
      "has_stone": true
    }
  ]
}
```

### Get Product Subcategories
**GET** `/product-categories/{categoryId}/subcategories`
*Requires authentication*

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Engagement Rings"
    },
    {
      "id": 2,
      "name": "Wedding Bands"
    }
  ]
}
```

### Get Category Options
**GET** `/category-options?category_id={id}`
*Requires authentication*

**Response:**
```json
{
  "success": true,
  "data": {
    "has_hook": false,
    "has_enamel": true,
    "has_rodium": true,
    "has_open_close": true,
    "has_stone": true
  }
}
```

### Get Products
**GET** `/products`
*Requires authentication*

**Query Parameters:**
- `category_id` - Filter by category
- `subcategory_id` - Filter by subcategory
- `design_status` - Filter by design status
- `search` - Search term
- `sort_by` - Sort column
- `sort_order` - Sort order
- `per_page` - Items per page

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "product_code": "PRD0001",
        "product_name": "Test Gold Ring",
        "product_category_id": 1,
        "product_subcategory_id": 1,
        "type": "Piece",
        "open_close": "Open",
        "size": "Medium",
        "weight_from": "5.500",
        "weight_to": "7.200",
        "hallmark": "BIS",
        "rodium": "White",
        "stone": "Diamond",
        "enamel": "Blue",
        "bp_code": "BA001",
        "category": {
          "id": 1,
          "name": "Rings"
        },
        "subcategory": {
          "id": 1,
          "name": "Engagement Rings"
        },
        "images": []
      }
    ],
    "total": 1
  }
}
```

### Create Product
**POST** `/products`
*Requires authentication*

**Request:**
```json
{
  "product_name": "Test Gold Ring",
  "bp_code": "BA001",
  "product_category_id": 1,
  "subcategory_id": 1,
  "type": "Piece",
  "open_close": "Open",
  "size": "Medium",
  "weight_from": 5.5,
  "weight_to": 7.2,
  "hallmark": "BIS",
  "rodium": "White",
  "stone": "Diamond",
  "enamel": "Blue"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Product created successfully",
  "data": {
    "product_code": "PRD0001",
    "product_name": "Test Gold Ring",
    "bp_code": "BA001",
    "product_category_id": 1,
    "product_subcategory_id": 1,
    "type": "Piece",
    "open_close": "Open",
    "size": "Medium",
    "weight_from": "5.500",
    "weight_to": "7.200",
    "hallmark": "BIS",
    "rodium": "White",
    "stone": "Diamond",
    "enamel": "Blue"
  }
}
```

**Note:** File uploads for product images are supported using multipart/form-data.

## Designs

### Get Design Products
**GET** `/designs`
*Requires authentication*

Returns all products awaiting design approval.

### Accept Design
**POST** `/designs/{product_id}/accept`
*Requires authentication*

Accepts a product design and generates a design code.

### Reject Design
**POST** `/designs/{product_id}/reject`
*Requires authentication*

Rejects a product design.

## Catalogue

### Get Catalogue Products
**GET** `/catalogue`
*Requires authentication*

Returns all accepted products with design codes.

## Error Responses

### Validation Error (422)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

### Authentication Error (401)
```json
{
  "success": false,
  "message": "Invalid credentials."
}
```

### Not Found (404)
```json
{
  "success": false,
  "message": "Resource not found"
}
```

## Permissions

Super Admin has access to all endpoints by default. The API mirrors the exact same business logic implemented in the Super Admin web panel.

## Notes

1. All protected endpoints require the `Authorization: Bearer {token}` header
2. Date format: `YYYY-MM-DD`
3. All monetary values are in rupees
4. File uploads for images use multipart/form-data
5. Pagination follows Laravel's default format
6. Search is case-insensitive and searches across multiple fields
7. All CRUD operations are fully implemented