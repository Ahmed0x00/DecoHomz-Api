# DecoHomz API Implementation Plan

## Project Overview
E-commerce furniture store API with full CRUD operations, admin panel, and role-based access.

## Database Configuration
- **Database:** MySQL
- **Name:** DecoHomz
- **Username:** root
- **Password:** (none)

---

## PHASE 0: DATABASE SETUP
**Priority:** First - Create all tables before API implementation

### Tables to Create (in order due to foreign keys):

#### 1. users (modify existing)
```sql
- id (bigint, PK, auto)
- name (varchar 255)
- email (varchar 255, unique)
- email_verified_at (timestamp, nullable)
- password (varchar 255)
- phone (varchar 20, nullable)
- role (enum: 'admin', 'user', default: 'user')
- avatar (varchar 255, nullable)
- remember_token (varchar 100, nullable)
- created_at, updated_at (timestamps)
```

#### 2. categories
```sql
- id (bigint, PK, auto)
- name (varchar 100, unique)
- slug (varchar 100, unique)
- description (text, nullable)
- image (varchar 255, nullable)
- is_active (boolean, default: true)
- sort_order (int, default: 0)
- created_at, updated_at (timestamps)
```

#### 3. products
```sql
- id (bigint, PK, auto)
- category_id (bigint, FK -> categories)
- name (varchar 255)
- slug (varchar 255, unique)
- description (text, nullable)
- price (decimal 10,2)
- old_price (decimal 10,2, nullable)
- material (varchar 100, nullable)
- colors (JSON, nullable) -- array of hex colors
- stars (tinyint, default: 5)
- badge (varchar 50, nullable) -- Best Seller, Sale, New
- badge_color (varchar 20, nullable)
- stock (int, default: 0)
- is_active (boolean, default: true)
- is_featured (boolean, default: false)
- created_at, updated_at (timestamps)
```

#### 4. product_images
```sql
- id (bigint, PK, auto)
- product_id (bigint, FK -> products)
- image (varchar 255)
- alt_text (varchar 255, nullable)
- sort_order (int, default: 0)
- is_primary (boolean, default: false)
- created_at, updated_at (timestamps)
```

#### 5. addresses (user saved addresses)
```sql
- id (bigint, PK, auto)
- user_id (bigint, FK -> users)
- label (varchar 50) -- Home, Office, etc.
- first_name (varchar 100)
- last_name (varchar 100)
- phone (varchar 20)
- address (text)
- city (varchar 100)
- governorate (varchar 100)
- postal_code (varchar 20, nullable)
- is_default (boolean, default: false)
- created_at, updated_at (timestamps)
```

#### 6. carts
```sql
- id (bigint, PK, auto)
- user_id (bigint, FK -> users, nullable)
- session_id (varchar 255, nullable) -- for guest carts
- created_at, updated_at (timestamps)
```

#### 7. cart_items
```sql
- id (bigint, PK, auto)
- cart_id (bigint, FK -> carts)
- product_id (bigint, FK -> products)
- quantity (int, default: 1)
- variant (varchar 100, nullable) -- color/size
- created_at, updated_at (timestamps)
```

#### 8. coupons
```sql
- id (bigint, PK, auto)
- code (varchar 50, unique)
- discount_type (enum: 'percentage', 'fixed')
- discount_value (decimal 10,2)
- min_order_amount (decimal 10,2, nullable)
- max_uses (int, nullable)
- used_count (int, default: 0)
- expires_at (timestamp, nullable)
- is_active (boolean, default: true)
- created_at, updated_at (timestamps)
```

#### 9. orders
```sql
- id (bigint, PK, auto)
- user_id (bigint, FK -> users)
- order_number (varchar 20, unique) -- DH + random
- status (enum: 'pending', 'processing', 'shipped', 'delivered', 'cancelled', default: 'pending')
- subtotal (decimal 10,2)
- discount (decimal 10,2, default: 0)
- delivery_fee (decimal 10,2, default: 0)
- total (decimal 10,2)
- payment_method (enum: 'card', 'fawry', 'cod')
- payment_status (enum: 'pending', 'paid', 'failed', default: 'pending')
- coupon_id (bigint, FK -> coupons, nullable)
- notes (text, nullable)
- created_at, updated_at (timestamps)
```

#### 10. order_items
```sql
- id (bigint, PK, auto)
- order_id (bigint, FK -> orders)
- product_id (bigint, FK -> products)
- name (varchar 255) -- snapshot
- price (decimal 10,2) -- snapshot
- quantity (int)
- variant (varchar 100, nullable)
- created_at, updated_at (timestamps)
```

#### 11. shipping_addresses (per order)
```sql
- id (bigint, PK, auto)
- order_id (bigint, FK -> orders)
- first_name (varchar 100)
- last_name (varchar 100)
- email (varchar 255)
- phone (varchar 20)
- address (text)
- city (varchar 100)
- governorate (varchar 100)
- postal_code (varchar 20, nullable)
- created_at, updated_at (timestamps)
```

#### 12. wishlists
```sql
- id (bigint, PK, auto)
- user_id (bigint, FK -> users)
- product_id (bigint, FK -> products)
- created_at (timestamp)
- UNIQUE(user_id, product_id)
```

#### 13. reviews
```sql
- id (bigint, PK, auto)
- user_id (bigint, FK -> users)
- product_id (bigint, FK -> products)
- rating (tinyint, 1-5)
- comment (text, nullable)
- is_approved (boolean, default: false)
- created_at, updated_at (timestamps)
```

#### 14. contacts
```sql
- id (bigint, PK, auto)
- name (varchar 100)
- email (varchar 255)
- phone (varchar 20, nullable)
- subject (varchar 255)
- message (text)
- status (enum: 'new', 'read', 'replied', default: 'new')
- created_at, updated_at (timestamps)
```

#### 15. settings (for admin config)
```sql
- id (bigint, PK, auto)
- key (varchar 100, unique)
- value (text, nullable)
- created_at, updated_at (timestamps)
```

---

## PHASE 1: AUTHENTICATION & ROLES
**Modify existing auth to support roles**

### API Endpoints
| Method | Endpoint | Access | Description |
|--------|----------|--------|-------------|
| POST | /api/auth/register | Public | Register new user |
| POST | /api/auth/login | Public | Login |
| POST | /api/auth/logout | Auth | Logout |
| GET | /api/auth/user | Auth | Get current user |
| PUT | /api/auth/profile | Auth | Update profile |
| PUT | /api/auth/password | Auth | Change password |

---

## PHASE 2: CATEGORIES (Admin CRUD + Public Read)

### API Endpoints
| Method | Endpoint | Access | Description |
|--------|----------|--------|-------------|
| GET | /api/categories | Public | List all active categories |
| GET | /api/categories/{id} | Public | Get single category |
| POST | /api/admin/categories | Admin | Create category |
| PUT | /api/admin/categories/{id} | Admin | Update category |
| DELETE | /api/admin/categories/{id} | Admin | Delete category |

---

## PHASE 3: PRODUCTS (Admin CRUD + Public Read)

### API Endpoints
| Method | Endpoint | Access | Description |
|--------|----------|--------|-------------|
| GET | /api/products | Public | List products (filters: category, search, sort, material, featured) |
| GET | /api/products/{id} | Public | Get single product with images |
| GET | /api/products/{id}/related | Public | Get related products |
| POST | /api/admin/products | Admin | Create product |
| PUT | /api/admin/products/{id} | Admin | Update product |
| DELETE | /api/admin/products/{id} | Admin | Delete product |
| POST | /api/admin/products/{id}/images | Admin | Upload product images |
| DELETE | /api/admin/products/{id}/images/{imageId} | Admin | Delete product image |

---

## PHASE 4: CART

### API Endpoints
| Method | Endpoint | Access | Description |
|--------|----------|--------|-------------|
| GET | /api/cart | Public* | Get cart (by session or user) |
| POST | /api/cart/items | Public* | Add item to cart |
| PUT | /api/cart/items/{id} | Public* | Update item quantity |
| DELETE | /api/cart/items/{id} | Public* | Remove item |
| DELETE | /api/cart | Public* | Clear cart |
| POST | /api/cart/coupon | Public* | Apply coupon |
| DELETE | /api/cart/coupon | Public* | Remove coupon |

*Uses session_id for guests, user_id for authenticated users

---

## PHASE 5: ORDERS

### API Endpoints - User
| Method | Endpoint | Access | Description |
|--------|----------|--------|-------------|
| POST | /api/orders | Auth | Create order (checkout) |
| GET | /api/orders | Auth | List user's orders |
| GET | /api/orders/{id} | Auth | Get order details |
| POST | /api/orders/{id}/cancel | Auth | Cancel order (if pending) |

### API Endpoints - Admin
| Method | Endpoint | Access | Description |
|--------|----------|--------|-------------|
| GET | /api/admin/orders | Admin | List all orders (filters: status, date, user) |
| GET | /api/admin/orders/{id} | Admin | Get order details |
| PUT | /api/admin/orders/{id} | Admin | Update order status |
| DELETE | /api/admin/orders/{id} | Admin | Delete order |

---

## PHASE 6: USER ADDRESSES

### API Endpoints
| Method | Endpoint | Access | Description |
|--------|----------|--------|-------------|
| GET | /api/addresses | Auth | List user addresses |
| POST | /api/addresses | Auth | Create address |
| PUT | /api/addresses/{id} | Auth | Update address |
| DELETE | /api/addresses/{id} | Auth | Delete address |
| POST | /api/addresses/{id}/default | Auth | Set as default |

---

## PHASE 7: WISHLIST

### API Endpoints
| Method | Endpoint | Access | Description |
|--------|----------|--------|-------------|
| GET | /api/wishlist | Auth | Get wishlist |
| POST | /api/wishlist/{productId} | Auth | Add to wishlist |
| DELETE | /api/wishlist/{productId} | Auth | Remove from wishlist |

---

## PHASE 8: REVIEWS

### API Endpoints - User
| Method | Endpoint | Access | Description |
|--------|----------|--------|-------------|
| GET | /api/products/{id}/reviews | Public | Get product reviews (approved only) |
| POST | /api/products/{id}/reviews | Auth | Submit review |
| PUT | /api/reviews/{id} | Auth | Update own review |
| DELETE | /api/reviews/{id} | Auth | Delete own review |

### API Endpoints - Admin
| Method | Endpoint | Access | Description |
|--------|----------|--------|-------------|
| GET | /api/admin/reviews | Admin | List all reviews |
| PUT | /api/admin/reviews/{id} | Admin | Approve/reject review |
| DELETE | /api/admin/reviews/{id} | Admin | Delete review |

---

## PHASE 9: COUPONS (Admin Only)

### API Endpoints
| Method | Endpoint | Access | Description |
|--------|----------|--------|-------------|
| GET | /api/admin/coupons | Admin | List all coupons |
| GET | /api/admin/coupons/{id} | Admin | Get coupon details |
| POST | /api/admin/coupons | Admin | Create coupon |
| PUT | /api/admin/coupons/{id} | Admin | Update coupon |
| DELETE | /api/admin/coupons/{id} | Admin | Delete coupon |
| POST | /api/coupons/validate | Public | Validate coupon code |

---

## PHASE 10: USERS (Admin Only)

### API Endpoints
| Method | Endpoint | Access | Description |
|--------|----------|--------|-------------|
| GET | /api/admin/users | Admin | List all users |
| GET | /api/admin/users/{id} | Admin | Get user details |
| POST | /api/admin/users | Admin | Create user |
| PUT | /api/admin/users/{id} | Admin | Update user |
| DELETE | /api/admin/users/{id} | Admin | Delete user |

---

## PHASE 11: CONTACTS

### API Endpoints
| Method | Endpoint | Access | Description |
|--------|----------|--------|-------------|
| POST | /api/contact | Public | Submit contact form |
| GET | /api/admin/contacts | Admin | List all contacts |
| GET | /api/admin/contacts/{id} | Admin | Get contact details |
| PUT | /api/admin/contacts/{id} | Admin | Update status |
| DELETE | /api/admin/contacts/{id} | Admin | Delete contact |

---

## PHASE 12: DASHBOARD & SETTINGS (Admin)

### API Endpoints
| Method | Endpoint | Access | Description |
|--------|----------|--------|-------------|
| GET | /api/admin/dashboard | Admin | Get dashboard stats |
| GET | /api/admin/settings | Admin | Get all settings |
| PUT | /api/admin/settings | Admin | Update settings |

### Dashboard Stats Include:
- Total orders (today, week, month, all)
- Total revenue (today, week, month, all)
- Total users
- Total products
- Recent orders
- Top selling products
- Low stock products

---

## Implementation Order Summary

1. **PHASE 0:** Create all database migrations and run them
2. **PHASE 1:** Update Auth with roles
3. **PHASE 2:** Categories CRUD
4. **PHASE 3:** Products CRUD with images
5. **PHASE 4:** Cart functionality
6. **PHASE 5:** Orders & Checkout
7. **PHASE 6:** User Addresses
8. **PHASE 7:** Wishlist
9. **PHASE 8:** Reviews
10. **PHASE 9:** Coupons
11. **PHASE 10:** User Management (Admin)
12. **PHASE 11:** Contact Form
13. **PHASE 12:** Dashboard & Settings

---

## Middleware Structure

- `auth:sanctum` - Requires authentication
- `admin` - Requires admin role (custom middleware)

---

## File Storage

Product images stored in: `storage/app/public/products/`
Category images stored in: `storage/app/public/categories/`
User avatars stored in: `storage/app/public/avatars/`

Run: `php artisan storage:link` to create public symlink

---

## Seeder Data

Create seeders for:
1. Admin user (admin@decohomz.com / password)
2. Categories (Living Room, Bedroom, Dining, Office, Outdoor, Decor)
3. Sample products (8 from original prototype)
4. Sample coupon (DECO10 - 10% off)

---

Ready to start with PHASE 0: Database Migrations?
