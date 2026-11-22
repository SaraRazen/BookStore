# 📚 Online Bookstore

A full-stack online bookstore built with **PHP**, **MySQL**, and a single-page **HTML/JavaScript** frontend. Customers can browse, search, and buy books, while admins manage inventory, confirm publisher restock orders, and view sales reports.

Much of the business logic (stock deduction, overselling prevention, automatic reordering) lives in the database itself as **MySQL triggers**, so inventory stays consistent no matter which client talks to it.

## Features

### Customers
- Register, log in, and edit profile (including password change)
- Search books by title, ISBN, category, publisher, or author
- Shopping cart and checkout with card number and expiry validation
- View personal order history

### Admins
- Add new books and update price, stock, and reorder threshold
- Confirm pending publisher orders (adds the ordered quantity to stock)
- View all customer orders
- Reports:
  - Total sales for the previous month
  - Total sales on a specific date
  - Top 5 customers (last 3 months)
  - Top 10 selling books (last 3 months)
  - Number of times a given book has been reordered

### Database automation (triggers)
| Trigger | What it does |
|---|---|
| `prevent_negative_stock` | Rejects any update that would make stock negative |
| `auto_reorder_books` | Creates a `PENDING` publisher order (qty 10) when stock drops below the threshold |
| `confirm_order_add_stock` | Adds stock to the book when a publisher order becomes `CONFIRMED` |
| `prevent_overselling` | Blocks an order item if the requested quantity exceeds current stock |
| `deduct_stock_after_purchase` | Deducts stock automatically after an order item is inserted |

## Tech Stack

- **Frontend:** HTML, CSS, vanilla JavaScript (`fetch` API)
- **Backend:** PHP (`mysqli`), JSON REST-style endpoints
- **Database:** MySQL / MariaDB

## Project Structure

```
.
├── frontend.html            # Single-page UI (customer + admin views)
├── schema.sql               # Database schema and triggers
└── backend/
    ├── db.php               # Database connection
    ├── register.php         # Customer registration
    ├── login.php            # Authentication
    ├── update_profile.php   # Edit customer profile
    ├── search_books.php     # Book search / filters
    ├── checkout.php         # Place an order (transactional)
    ├── my_orders.php        # Customer order history
    ├── admin_orders.php     # All orders (admin)
    ├── add_book.php         # Add a book (admin)
    ├── update_book.php      # Update price/stock/threshold (admin)
    ├── confirm_order.php    # Confirm publisher order (admin)
    ├── report_sales_last_month.php
    ├── report_sales_by_date.php
    ├── report_top_customers.php
    ├── report_top_books.php
    ├── report_reorders.php
    └── report_sales.php
```

> The frontend calls endpoints at `backend/*.php`, so put all the PHP files in a `backend/` folder next to `frontend.html`.

## Getting Started

### Prerequisites
- PHP 7.4+ with the `mysqli` extension
- MySQL 5.7+ or MariaDB
- A local web server stack such as [XAMPP](https://www.apachefriends.org/), WAMP, or MAMP

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/<your-username>/<your-repo>.git
   ```

2. **Create the database**
   ```bash
   mysql -u root -p < schema.sql
   ```
   This creates the `bookstore` database, all tables, and the triggers.

3. **Configure the connection** in `backend/db.php`:
   ```php
   $conn = new mysqli("localhost", "root", "", "bookstore");
   ```
   Update the host, username, and password to match your setup.

4. **Deploy the files**
   Copy the project into your web server's root (e.g. `htdocs/bookstore/` for XAMPP) so the layout looks like:
   ```
   htdocs/bookstore/frontend.html
   htdocs/bookstore/backend/*.php
   ```

5. **Seed initial data**
   The schema doesn't include sample data. You'll need at least:
   - A few rows in `publishers` (required before adding books)
   - Optionally rows in `authors` / `book_authors` so author search works
   - An admin account (registration only creates `CUSTOMER` users). Generate a hash and insert it:
     ```bash
     php -r "echo password_hash('your-password', PASSWORD_DEFAULT);"
     ```
     ```sql
     INSERT INTO users (username, password, first_name, last_name, email, phone, address, role)
     VALUES ('admin', '<paste-hash-here>', 'Admin', 'User', 'admin@example.com', '0000000000', 'N/A', 'ADMIN');
     ```

6. **Run it**
   Start Apache and MySQL, then open `http://localhost/bookstore/frontend.html`.

## Database Overview

| Table | Purpose |
|---|---|
| `users` | Customers and admins (`role` ENUM) |
| `publishers` | Book publishers |
| `books` | Catalog with price, stock, and reorder threshold |
| `authors`, `book_authors` | Authors and the many-to-many link to books |
| `customer_orders`, `order_items` | Customer purchases |
| `publisher_orders` | Restock orders (`PENDING` / `CONFIRMED`) |

Book categories: `Science`, `Art`, `Religion`, `History`, `Geography`.

## API Endpoints

| Endpoint | Method | Description |
|---|---|---|
| `register.php` | POST | Create a customer account |
| `login.php` | POST | Log in, returns `user_id`, `username`, `role` |
| `update_profile.php` | POST | Update profile fields |
| `search_books.php` | GET | Params: `q`, `cat`, `pub`, `auth` |
| `checkout.php` | POST | Body: `user_id`, `cart`, `card`, `exp` |
| `my_orders.php` | GET | Param: `user_id` |
| `admin_orders.php` | GET | All orders |
| `add_book.php` | POST | Add a book |
| `update_book.php` | POST | Update price / stock / threshold by ISBN |
| `confirm_order.php` | POST | Body: `order_id` |
| `report_*.php` | GET | Sales and inventory reports |

## Known Limitations

This project is intended for learning and coursework, and is **not production-ready**. Before deploying publicly, address the following:

- **SQL injection:** several endpoints (`search_books`, `my_orders`, `checkout`, and the report endpoints) build queries with string interpolation. Switch them to prepared statements.
- **No server-side authorization:** admin endpoints don't verify the caller's role, and there are no sessions or tokens. Roles are only enforced in the UI.
- **Client-trusted prices:** `checkout.php` uses prices sent by the browser. Look prices up from the `books` table instead.
- **Card data:** card numbers are only format-checked and are never charged or stored. Use a real payment provider for anything beyond a demo.
- **Debug output:** `display_errors` is enabled in some files, and `db.php` uses root with no password. Disable both outside local development.


