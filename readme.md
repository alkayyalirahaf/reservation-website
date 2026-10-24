# Tidyhome - Home Cleaning Service Reservation System

A web-based Reservation Management System built as a bootcamp project. It allows users to book home cleaning appointments and provides a full admin dashboard for managing reservations, users, and time slots.

## 🎨 Figma Design
You can view the UI/UX design for this project here:
[Figma Design Link](https://www.figma.com/design/BJuiTnV2YJLODPupnGKvAx/Untitled?node-id=0-1&t=krsT76kesF8KmGoi-1)

## 🛠️ Technologies Used
- **Frontend:** HTML5, CSS3 (Flexbox/Grid), Vanilla JavaScript
- **Backend:** PHP (OOP, PDO)
- **Database:** MySQL
- **Libraries:** Chart.js (via CDN) for the admin dashboard
- **Fonts:** Inter (Google Fonts)

## 🚀 Installation Steps

1. **Clone the repository:**
   `git clone https://github.com/alkayyalirahaf/reservation-website.git`

2. **Move to your local server directory:**
   If using XAMPP, place the project folder inside `C:\xampp\htdocs\Remote-task\`.

3. **Database Setup:**
   - Open phpMyAdmin.
   - Create a new database named `cleaning_reservations`.
   - Import the `database.sql` file included in this repository.

4. **Configure Database Connection:**
   Open `config.php` and update the database credentials if needed (default is `root` with no password).

5. **Run the project:**
   Open your browser and go to: `http://localhost/Remote-task/`

## 🔑 Demo Accounts

**Admin Account:**
- **Email:** `admin@example.com`
- **Password:** `Admin@123`

**User Account:**
- **Email:** `sara@example.com`
- **Password:** `User@123`

## ✨ Features & Business Rules

**User Side:**
- Users can register, log in, and log out.
- View available time slots with real-time remaining capacity.
- Make a reservation with an optional note (starts as Pending).
- View "My Reservations" with status badges (Pending, Confirmed, Rejected, Cancelled).
- Cancel their own upcoming reservations before the slot begins.

**Admin Side:**
- Dashboard with statistics (Total, Pending, Confirmed, Registered Users).
- Interactive doughnut chart using Chart.js for reservation statuses.
- Manage reservations (Search, Filter, Confirm, Reject, Cancel).
- Manage users (Activate/Deactivate accounts).
- Manage time slots (Add, Edit details, Delete).

**Enforced Business Rules:**
- No past slots can be booked.
- Slot capacity cannot be exceeded (Enforced via PDO Transactions).
- Users cannot double-book the same time slot.
- Cancelled/Rejected reservations free up capacity.
- Slots with active reservations cannot be deleted.
- Deactivated users cannot log in.

## 🗄️ Database Structure
- `users`: Stores user data, roles (admin/user), and activation status.
- `time_slots`: Stores available cleaning slots with dates, times, and capacities.
- `reservations`: The bridge table linking users to time slots, with status tracking.