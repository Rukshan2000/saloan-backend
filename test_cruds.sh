#!/bin/bash

API_URL="http://127.0.0.1:8000/api/v1"

function test_api() {
    description=$1
    command=$2
    echo -n "$description: "
    response=$(eval "$command")
    status=$?
    if [ $status -eq 0 ]; then
        echo "PASS"
    else
        echo "FAIL"
    fi
}

# Branch CRUD
test_api "Create Branch" "curl -s -o /dev/null -w '%{http_code}' -X POST \"$API_URL/branches\" -H \"Content-Type: application/json\" -d '{\"name\":\"Test Branch\",\"address\":\"123 Main St\",\"contact\":\"123456789\"}'"
test_api "Get Branches" "curl -s -o /dev/null -w '%{http_code}' \"$API_URL/branches\""
test_api "Update Branch" "curl -s -o /dev/null -w '%{http_code}' -X PUT \"$API_URL/branches/1\" -H \"Content-Type: application/json\" -d '{\"name\":\"Updated Branch\"}'"
test_api "Delete Branch" "curl -s -o /dev/null -w '%{http_code}' -X DELETE \"$API_URL/branches/1\""

# User CRUD
test_api "Create User" "curl -s -o /dev/null -w '%{http_code}' -X POST \"$API_URL/users\" -H \"Content-Type: application/json\" -d '{\"name\":\"Test User\",\"email\":\"test@example.com\",\"password\":\"password\",\"role\":\"CUSTOMER\"}'"
test_api "Get Users" "curl -s -o /dev/null -w '%{http_code}' \"$API_URL/users\""
test_api "Update User" "curl -s -o /dev/null -w '%{http_code}' -X PUT \"$API_URL/users/1\" -H \"Content-Type: application/json\" -d '{\"name\":\"Updated User\"}'"
test_api "Delete User" "curl -s -o /dev/null -w '%{http_code}' -X DELETE \"$API_URL/users/1\""

# Category CRUD
test_api "Create Category" "curl -s -o /dev/null -w '%{http_code}' -X POST \"$API_URL/categories\" -H \"Content-Type: application/json\" -d '{\"name\":\"Test Category\"}'"
test_api "Get Categories" "curl -s -o /dev/null -w '%{http_code}' \"$API_URL/categories\""
test_api "Update Category" "curl -s -o /dev/null -w '%{http_code}' -X PUT \"$API_URL/categories/1\" -H \"Content-Type: application/json\" -d '{\"name\":\"Updated Category\"}'"
test_api "Delete Category" "curl -s -o /dev/null -w '%{http_code}' -X DELETE \"$API_URL/categories/1\""

# Service CRUD
test_api "Create Service" "curl -s -o /dev/null -w '%{http_code}' -X POST \"$API_URL/services\" -H \"Content-Type: application/json\" -d '{\"name\":\"Test Service\",\"description\":\"Desc\",\"duration\":30,\"price\":100,\"category_id\":1,\"active\":true}'"
test_api "Get Services" "curl -s -o /dev/null -w '%{http_code}' \"$API_URL/services\""
test_api "Update Service" "curl -s -o /dev/null -w '%{http_code}' -X PUT \"$API_URL/services/1\" -H \"Content-Type: application/json\" -d '{\"name\":\"Updated Service\"}'"
test_api "Delete Service" "curl -s -o /dev/null -w '%{http_code}' -X DELETE \"$API_URL/services/1\""

# ServiceBeautician CRUD
test_api "Create ServiceBeautician" "curl -s -o /dev/null -w '%{http_code}' -X POST \"$API_URL/service-beauticians\" -H \"Content-Type: application/json\" -d '{\"service_id\":1,\"beautician_id\":1}'"
test_api "Get ServiceBeauticians" "curl -s -o /dev/null -w '%{http_code}' \"$API_URL/service-beauticians\""
test_api "Update ServiceBeautician" "curl -s -o /dev/null -w '%{http_code}' -X PUT \"$API_URL/service-beauticians/1\" -H \"Content-Type: application/json\" -d '{\"service_id\":1,\"beautician_id\":1}'"
test_api "Delete ServiceBeautician" "curl -s -o /dev/null -w '%{http_code}' -X DELETE \"$API_URL/service-beauticians/1\""

# Appointment CRUD
test_api "Create Appointment" "curl -s -o /dev/null -w '%{http_code}' -X POST \"$API_URL/appointments\" -H \"Content-Type: application/json\" -d '{\"customer_id\":1,\"beautician_id\":1,\"branch_id\":1,\"date\":\"2025-07-25T10:00:00\",\"status\":\"SCHEDULED\",\"receipt_number\":\"R123\"}'"
test_api "Get Appointments" "curl -s -o /dev/null -w '%{http_code}' \"$API_URL/appointments\""
test_api "Update Appointment" "curl -s -o /dev/null -w '%{http_code}' -X PUT \"$API_URL/appointments/1\" -H \"Content-Type: application/json\" -d '{\"status\":\"CONFIRMED\"}'"
test_api "Delete Appointment" "curl -s -o /dev/null -w '%{http_code}' -X DELETE \"$API_URL/appointments/1\""

# AppointmentService CRUD
test_api "Create AppointmentService" "curl -s -o /dev/null -w '%{http_code}' -X POST \"$API_URL/appointment-services\" -H \"Content-Type: application/json\" -d '{\"appointment_id\":1,\"service_id\":1,\"price\":100,\"duration\":30}'"
test_api "Get AppointmentServices" "curl -s -o /dev/null -w '%{http_code}' \"$API_URL/appointment-services\""
test_api "Update AppointmentService" "curl -s -o /dev/null -w '%{http_code}' -X PUT \"$API_URL/appointment-services/1\" -H \"Content-Type: application/json\" -d '{\"price\":120}'"
test_api "Delete AppointmentService" "curl -s -o /dev/null -w '%{http_code}' -X DELETE \"$API_URL/appointment-services/1\""

# BeauticianAvailability CRUD
test_api "Create BeauticianAvailability" "curl -s -o /dev/null -w '%{http_code}' -X POST \"$API_URL/beautician-availability\" -H \"Content-Type: application/json\" -d '{\"beautician_id\":1,\"day_of_week\":1,\"start_time\":\"09:00:00\",\"end_time\":\"17:00:00\"}'"
test_api "Get BeauticianAvailability" "curl -s -o /dev/null -w '%{http_code}' \"$API_URL/beautician-availability\""
test_api "Update BeauticianAvailability" "curl -s -o /dev/null -w '%{http_code}' -X PUT \"$API_URL/beautician-availability/1\" -H \"Content-Type: application/json\" -d '{\"end_time\":\"18:00:00\"}'"
test_api "Delete BeauticianAvailability" "curl -s -o /dev/null -w '%{http_code}' -X DELETE \"$API_URL/beautician-availability/1\""

# TimeSlot CRUD
test_api "Create TimeSlot" "curl -s -o /dev/null -w '%{http_code}' -X POST \"$API_URL/time-slots\" -H \"Content-Type: application/json\" -d '{\"start_time\":\"09:00:00\",\"end_time\":\"09:30:00\"}'"
test_api "Get TimeSlots" "curl -s -o /dev/null -w '%{http_code}' \"$API_URL/time-slots\""
test_api "Update TimeSlot" "curl -s -o /dev/null -w '%{http_code}' -X PUT \"$API_URL/time-slots/1\" -H \"Content-Type: application/json\" -d '{\"end_time\":\"10:00:00\"}'"
test_api "Delete TimeSlot" "curl -s -o /dev/null -w '%{http_code}' -X DELETE \"$API_URL/time-slots/1\""

# Invoice CRUD
test_api "Create Invoice" "curl -s -o /dev/null -w '%{http_code}' -X POST \"$API_URL/invoices\" -H \"Content-Type: application/json\" -d '{\"appointment_id\":1,\"customer_id\":1,\"invoice_number\":\"INV123\",\"status\":\"PENDING\",\"total\":100}'"
test_api "Get Invoices" "curl -s -o /dev/null -w '%{http_code}' \"$API_URL/invoices\""
test_api "Update Invoice" "curl -s -o /dev/null -w '%{http_code}' -X PUT \"$API_URL/invoices/1\" -H \"Content-Type: application/json\" -d '{\"status\":\"PAID\"}'"
test_api "Delete Invoice" "curl -s -o /dev/null -w '%{http_code}' -X DELETE \"$API_URL/invoices/1\""

# Promotion CRUD
test_api "Create Promotion" "curl -s -o /dev/null -w '%{http_code}' -X POST \"$API_URL/promotions\" -H \"Content-Type: application/json\" -d '{\"type\":\"PERCENTAGE\",\"code\":\"PROMO10\",\"value\":10,\"max_discount\":50,\"min_amount\":100,\"start_date\":\"2025-07-25\",\"end_date\":\"2025-08-25\"}'"
test_api "Get Promotions" "curl -s -o /dev/null -w '%{http_code}' \"$API_URL/promotions\""
test_api "Update Promotion" "curl -s -o /dev/null -w '%{http_code}' -X PUT \"$API_URL/promotions/1\" -H \"Content-Type: application/json\" -d '{\"value\":15}'"
test_api "Delete Promotion" "curl -s -o /dev/null -w '%{http_code}' -X DELETE \"$API_URL/promotions/1\""

# Enhanced Booking Logic Tests
echo "=== Enhanced Booking Logic Tests ==="

# Test finding best beautician for services
test_api "Find Best Beautician for Services" "curl -s -o /dev/null -w '%{http_code}' \"$API_URL/appointment-services/find-best-beautician?service_ids[]=1&service_ids[]=2&date=2025-08-05\""

# Test getting available beauticians for services
test_api "Get Available Beauticians for Services" "curl -s -o /dev/null -w '%{http_code}' \"$API_URL/appointment-services/available-beauticians?service_ids[]=1&date=2025-08-05\""

# Test with branch filter
test_api "Find Best Beautician with Branch Filter" "curl -s -o /dev/null -w '%{http_code}' \"$API_URL/appointment-services/find-best-beautician?service_ids[]=1&date=2025-08-05&branch_id=1\""

# Test improved available time slots
test_api "Get Available Time Slots (Improved)" "curl -s -o /dev/null -w '%{http_code}' \"$API_URL/appointment-services/available-time-slots?beautician_id=1&total_duration=30&date=2025-08-05\""

# Smart Booking Test
test_api "Smart Booking Appointment" "curl -s -o /dev/null -w '%{http_code}' -X POST \"$API_URL/appointments/smart-booking\" -H \"Content-Type: application/json\" -d '{\"customer_id\":1,\"service_ids\":[1,2],\"date\":\"2025-08-05\",\"branch_id\":1}'"

# Validation Test
test_api "Validate Booking Request" "curl -s -o /dev/null -w '%{http_code}' -X POST \"$API_URL/appointment-services/validate-booking\" -H \"Content-Type: application/json\" -d '{\"service_ids\":[1,2],\"date\":\"2025-08-05\",\"branch_id\":1}'"
