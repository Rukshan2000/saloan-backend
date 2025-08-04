# Invoice System Documentation

## Overview
This enhanced Invoice system provides comprehensive functionality for creating, managing, and emailing invoices for salon appointments. The system includes proper database relationships, email notifications, status management, and detailed reporting.

## Features Implemented

### 1. Enhanced Invoice Model (`app/Models/Invoice.php`)
- **Proper Relationships**: Includes eager loading for appointment, customer, services, branch, and beautician data
- **Scopes**: Added query scopes for filtering by status (pending, paid, overdue, cancelled)
- **Automatic Invoice Number Generation**: Generates unique invoice numbers in format `INV-000001`
- **Status Management**: Handles PENDING, PAID, OVERDUE, and CANCELLED statuses
- **Formatted Output**: Provides formatted total amounts and overdue detection
- **Casting**: Proper data type casting for decimal amounts and timestamps

### 2. Comprehensive Invoice Controller (`app/Http/Controllers/InvoiceController.php`)
- **Full CRUD Operations** with proper error handling
- **Create from Appointment**: Automatically calculates totals from appointment services
- **Status Updates**: Dedicated endpoint for updating invoice status
- **Email Functionality**: Send and resend invoice emails
- **Statistics**: Comprehensive invoice and revenue statistics
- **Filtering**: Filter invoices by status, customer, date range
- **Automatic Overdue Detection**: Updates invoices older than 30 days to OVERDUE status
- **Validation**: Comprehensive input validation with detailed error responses

### 3. Email System (`app/Mail/InvoiceMail.php` & `resources/views/emails/invoice.blade.php`)
- **Beautiful HTML Template**: Professional, responsive email design
- **Complete Invoice Details**: Customer info, appointment details, services breakdown
- **Branding**: Includes salon branding and contact information
- **Status Indicators**: Visual status badges for invoice status
- **Service Details**: Detailed breakdown of services with categories and pricing

### 4. API Endpoints

#### Invoice Management
```
GET    /api/v1/invoices                           # List all invoices with filters
GET    /api/v1/invoices/{id}                      # Get specific invoice details
GET    /api/v1/invoices/statistics                # Get invoice statistics
POST   /api/v1/invoices/create-from-appointment   # Create invoice from appointment
PATCH  /api/v1/invoices/{id}/status               # Update invoice status
POST   /api/v1/invoices/{id}/send-email           # Send/resend invoice email
DELETE /api/v1/invoices/{id}                      # Delete invoice (restrictions apply)
```

## API Usage Examples

### 1. Create Invoice from Appointment
```bash
curl -X POST http://localhost:8000/api/v1/invoices/create-from-appointment \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "appointment_id": 1,
    "send_email": true
  }'
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "appointment_id": 1,
    "customer_id": 2,
    "invoice_number": "INV-000001",
    "status": "PENDING",
    "total": "150.00",
    "customer": {
      "id": 2,
      "name": "Jane Doe",
      "email": "jane@example.com"
    },
    "appointment": {
      "id": 1,
      "date": "2025-08-05",
      "start_time": "10:00:00",
      "end_time": "11:30:00",
      "services": [...],
      "branch": {...},
      "beautician": {...}
    }
  },
  "message": "Invoice created successfully and email sent"
}
```

### 2. Get Invoices with Filters
```bash
curl -X GET "http://localhost:8000/api/v1/invoices?status=PENDING&customer_id=2&date_from=2025-08-01" \
  -H "Accept: application/json"
```

### 3. Update Invoice Status
```bash
curl -X PATCH http://localhost:8000/api/v1/invoices/1/status \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "status": "PAID"
  }'
```

### 4. Send Invoice Email
```bash
curl -X POST http://localhost:8000/api/v1/invoices/1/send-email \
  -H "Accept: application/json"
```

### 5. Get Invoice Statistics
```bash
curl -X GET http://localhost:8000/api/v1/invoices/statistics \
  -H "Accept: application/json"
```

**Response:**
```json
{
  "success": true,
  "data": {
    "total_invoices": 45,
    "pending_invoices": 12,
    "paid_invoices": 30,
    "overdue_invoices": 2,
    "cancelled_invoices": 1,
    "total_revenue": 4500.00,
    "pending_revenue": 1200.00,
    "monthly_revenue": 2500.00
  }
}
```

## Database Relationships

### Invoice Model Relationships
- **Belongs To Customer** (`users` table)
- **Belongs To Appointment** with eager loading of:
  - Services with category information
  - Branch details
  - Beautician information

### Optimized Queries
The system uses eager loading to minimize database queries:
```php
Invoice::with([
    'customer:id,name,email',
    'appointment' => function($query) {
        $query->with([
            'services.service.category',
            'branch:id,name,address,phone,email',
            'beautician:id,name'
        ]);
    }
])
```

## Email Configuration

Add these to your `.env` file:
```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@domain.com
MAIL_FROM_NAME="Your Salon Name"
```

## Status Management

### Status Types
- **PENDING**: Newly created, payment pending
- **PAID**: Payment received
- **OVERDUE**: Pending for more than 30 days
- **CANCELLED**: Invoice cancelled

### Automatic Status Updates
- Invoices automatically change from PENDING to OVERDUE after 30 days
- Only PENDING and CANCELLED invoices can be deleted

## Error Handling

The system provides comprehensive error handling:
- **Validation Errors**: 422 status with detailed field errors
- **Not Found**: 404 status for missing resources
- **Conflict**: 409 status for duplicate invoices
- **Server Errors**: 500 status with logged error details
- **Business Logic Errors**: 400 status for invalid operations

## Security Considerations

- Input validation on all endpoints
- Prevention of duplicate invoice creation
- Restrictions on invoice deletion
- Proper error logging without exposing sensitive data
- Email validation before sending

## Performance Optimizations

- Eager loading of relationships to prevent N+1 queries
- Pagination for invoice lists (15 per page)
- Scoped queries for efficient filtering
- Database indexing on foreign keys and status fields

## Testing

Use the provided test script:
```bash
./test_invoice_system.sh
```

This will show you all available endpoints and example usage.

## Future Enhancements

Potential improvements could include:
- PDF invoice generation
- Payment gateway integration
- Automated reminder emails
- Invoice templates customization
- Multi-currency support
- Invoice attachments
- Bulk operations
- Advanced reporting and analytics
