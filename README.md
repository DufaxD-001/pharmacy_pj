Markdown
# 🏥 Pharmacy Management System (pharmacy_pj)

> A secure, dynamic web application designed to streamline pharmacy operations, manage medicine inventories, track sales, and handle multi-role user dashboards efficiently.


---

## ✨ Features

* **Role-Based Dashboards:** Distinct and secure interfaces for different user roles (e.g., Admin, stafft, Client).
* **Inventory Management:** Full CRUD functionality to track, add, update, and monitor stock levels and medicine expiry dates.
* **Sales & Billing:** Seamless transaction handling and invoice/receipt generation.
* **Secure Authentication:** Robust user login and registration system protecting sensitive medical and financial data.
* **Responsive UI:** Clean, intuitive interface built for smooth navigation on both desktop and mobile devices.

## 🛠️ Tech Stack

* **Frontend:** HTML5, CSS3, JavaScript (ES6)
* **Backend:** PHP
* **Database:** MySQL

---

## 🚀 Getting Started

Follow these steps to set up and run this project locally on your machine using a local server environment like XAMPP or WampServer.

### Prerequisites

* Local Web Server (e.g., [XAMPP](https://www.apachefriends.org/) or WampServer)
* A modern web browser (Chrome, Edge, Firefox, etc.)
* Git installed on your system

### Installation & Local Setup

1. **Clone the repository:**
   Navigate to your local server's root directory (e.g., `C:/xampp/htdocs/` for XAMPP) in your terminal and run:
```bash
   git clone [https://github.com/DufaxD-001/pharmacy_pj.git](https://github.com/DufaxD/pharmacy_pj.git
2. Navigate into project directory
3. Set up the Database:

   Open your browser and go to http://localhost/phpmyadmin/.

   Create a new database (e.g., named pharmacy_db).

   Import the provided .sql file (look for a database backup file inside your project folder) into your new database.

4. Configure Database Connection:

  Open the project in your code editor.

  Locate your database connection file (e.g., config.php, db.php, or connection.php).

  Ensure the database name matches the one you created in phpMyAdmin
