MENU MANAGEMENT SYSTEM - BCA 4TH SEMESTER

TECHNOLOGY:
HTML, CSS, JavaScript, PHP, MySQL, MySQLi

INSTALLATION:
1. Install XAMPP.
2. Start Apache and MySQL.
3. Copy this "menu_management" folder into:
   C:\xampp\htdocs\
4. Open phpMyAdmin:
   http://localhost/phpmyadmin
5. Import:
   database/menu_management.sql
6. Open:
   http://localhost/menu_management/

ADMIN:
1. Register a normal account from /register.php
2. In phpMyAdmin run:
   UPDATE users SET role='admin' WHERE email='your@email.com';
3. Login again.
4. Open Admin.

IMPORTANT:
- This project intentionally uses MySQLi, not PDO.
- Passwords are stored using password_hash().
- Cart uses PHP sessions.
- Orders are stored in MySQL.
- Image uploads go to assets/images/.

UPDATES (latest):
- Admin > Orders: fixed status update (button name clashed with the status dropdown).
  Added success/error message, status filter, items column, status colors.
- Added config/helpers.php (flash messages + safe image upload).
- Image upload now accepts only real JPG/PNG/GIF/WEBP up to 2 MB, saved with a random name.
- Deleting a menu item that has past orders marks it Unavailable (keeps order history/reports).
- Categories with menu items cannot be deleted.
- Checkout saves order + items in one transaction and ignores unavailable items.
- Cart ignores deleted/unavailable items; quantity limited to 99.

VALIDATION (latest):
- All forms validated on the server: required fields, length limits, allowed characters,
  email format, password 6-72 chars, price 0.01-99999999.99 (2 decimals), valid category,
  valid status values, duplicate category names, duplicate item names per category.
- Delete buttons and admin forms use POST with a CSRF token (config/helpers.php).
- Database errors are caught and shown as messages instead of a blank page.
