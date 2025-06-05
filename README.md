# Citizen Observatory Platform

## Installation & Setup Instructions

### Clone and install dependencies:

```bash
git clone <your-repo>
cd citizen-observatory
composer install
npm install
```

### Environment setup:

```bash
cp .env.example .env
php artisan key:generate
```

### Database setup:

```bash
# Configure your database in .env file
php artisan migrate
php artisan db:seed
```

### Build assets:

```bash
npm run build
# or for development
npm run dev
```

### Start the server:

```bash
php artisan serve
```

---

## Default Users

| Role      | Email                | Password |
|-----------|----------------------|----------|
| Admin     | admin@example.com    | password |
| Verifier  | verifier@example.com | password |
| Approver  | approver@example.com | password |

---

## Key Features Implemented

### ✅ User Authentication & Authorization
- Role-based access control (User, Verifier, Approver, Admin)
- Registration and login system

### ✅ Case Management
- Case submission form with validation
- Multi-stage approval workflow (Pending → Verified → Approved)
- Case listing with filtering and pagination

### ✅ Data Visualization
- Interactive charts using Chart.js
- Filtering by county, case type, gender, and date range
- Only displays approved cases publicly

### ✅ Responsive Design
- Mobile-friendly interface using Tailwind CSS
- Clean, professional UI

### ✅ Security Features
- CSRF protection
- Input validation
- Role-based permissions

---

This MVP provides a solid foundation for the **Citizen Observatory Platform**, with all core features implemented and ready for further development and customization.
