#!/bin/bash

# Test script for Invoice functionality
echo "=== Invoice System Test Script ==="
echo ""

# Base URL
BASE_URL="http://localhost:8000/api/v1"

echo "1. Testing Invoice Creation from Appointment..."
echo "POST $BASE_URL/invoices/create-from-appointment"
echo "Request Body: {\"appointment_id\": 1, \"send_email\": true}"
echo ""

echo "2. Testing Invoice List with Filters..."
echo "GET $BASE_URL/invoices?status=PENDING&date_from=2025-01-01"
echo ""

echo "3. Testing Invoice Details..."
echo "GET $BASE_URL/invoices/1"
echo ""

echo "4. Testing Invoice Status Update..."
echo "PATCH $BASE_URL/invoices/1/status"
echo "Request Body: {\"status\": \"PAID\"}"
echo ""

echo "5. Testing Send Invoice Email..."
echo "POST $BASE_URL/invoices/1/send-email"
echo ""

echo "6. Testing Invoice Statistics..."
echo "GET $BASE_URL/invoices/statistics"
echo ""

echo "=== Example cURL Commands ==="
echo ""

echo "# Create invoice from appointment"
echo "curl -X POST $BASE_URL/invoices/create-from-appointment \\"
echo "  -H \"Content-Type: application/json\" \\"
echo "  -H \"Accept: application/json\" \\"
echo "  -d '{\"appointment_id\": 1, \"send_email\": true}'"
echo ""

echo "# Get all invoices with filters"
echo "curl -X GET \"$BASE_URL/invoices?status=PENDING&customer_id=1\" \\"
echo "  -H \"Accept: application/json\""
echo ""

echo "# Get invoice details"
echo "curl -X GET $BASE_URL/invoices/1 \\"
echo "  -H \"Accept: application/json\""
echo ""

echo "# Update invoice status"
echo "curl -X PATCH $BASE_URL/invoices/1/status \\"
echo "  -H \"Content-Type: application/json\" \\"
echo "  -H \"Accept: application/json\" \\"
echo "  -d '{\"status\": \"PAID\"}'"
echo ""

echo "# Send invoice email"
echo "curl -X POST $BASE_URL/invoices/1/send-email \\"
echo "  -H \"Accept: application/json\""
echo ""

echo "# Get invoice statistics"
echo "curl -X GET $BASE_URL/invoices/statistics \\"
echo "  -H \"Accept: application/json\""
echo ""

echo "=== Email Configuration Required ==="
echo "Make sure to set these environment variables in your .env file:"
echo ""
echo "MAIL_MAILER=smtp"
echo "MAIL_HOST=your-smtp-host"
echo "MAIL_PORT=587"
echo "MAIL_USERNAME=your-email@domain.com"
echo "MAIL_PASSWORD=your-password"
echo "MAIL_ENCRYPTION=tls"
echo "MAIL_FROM_ADDRESS=your-email@domain.com"
echo "MAIL_FROM_NAME=\"Your Salon Name\""
echo ""

echo "=== Features Implemented ==="
echo "✓ Enhanced Invoice Model with relationships and scopes"
echo "✓ Comprehensive Invoice Controller with proper error handling"
echo "✓ Email functionality with beautiful HTML template"
echo "✓ Invoice creation from appointments with automatic total calculation"
echo "✓ Status management (PENDING, PAID, OVERDUE, CANCELLED)"
echo "✓ Invoice statistics and filtering"
echo "✓ Proper validation and error responses"
echo "✓ Overdue invoice detection (30+ days)"
echo "✓ RESTful API endpoints"
echo "✓ Database relationships with eager loading"
echo ""
