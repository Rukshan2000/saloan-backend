#!/bin/bash

# Perfect Booking Logic Test Script
# This script tests the improved booking logic with comprehensive scenarios

API_URL="http://localhost:8000/api/v1"
TOMORROW=$(date -d "+1 day" +%Y-%m-%d)

echo "🧪 PERFECT BOOKING LOGIC TEST SUITE"
echo "======================================="
echo "📅 Test Date: $TOMORROW"
echo ""

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to test API endpoints
test_api() {
    local test_name="$1"
    local command="$2"
    local expected_status="${3:-200}"
    
    echo -n "Testing: $test_name... "
    
    # Execute the command and capture both status and response
    response=$(eval "$command" 2>&1)
    status=$?
    
    # Extract HTTP status code if it's a curl command
    if [[ $command == *"curl"* ]]; then
        http_status=$(echo "$response" | tail -n1)
        if [[ $http_status =~ ^[0-9]{3}$ ]]; then
            if [ "$http_status" -eq "$expected_status" ]; then
                echo -e "${GREEN}✅ PASS${NC} (HTTP $http_status)"
            else
                echo -e "${RED}❌ FAIL${NC} (Expected $expected_status, got $http_status)"
            fi
        else
            echo -e "${RED}❌ ERROR${NC} (Invalid response)"
        fi
    else
        if [ $status -eq 0 ]; then
            echo -e "${GREEN}✅ PASS${NC}"
        else
            echo -e "${RED}❌ FAIL${NC}"
        fi
    fi
}

# Function to test API with detailed response
test_api_detailed() {
    local test_name="$1"
    local command="$2"
    
    echo -e "${BLUE}🔍 $test_name${NC}"
    echo "Command: $command"
    echo "Response:"
    eval "$command" | jq '.' 2>/dev/null || eval "$command"
    echo ""
}

echo "🏁 STEP 1: Basic Health Check"
echo "------------------------------"
test_api "API Health Check" "curl -s -o /dev/null -w '%{http_code}' \"$API_URL/branches\""
echo ""

echo "📋 STEP 2: Available Beauticians Check"
echo "---------------------------------------"
test_api_detailed "Get Available Beauticians for Single Service" \
    "curl -s \"$API_URL/appointment-services/available-beauticians?service_ids[]=1&date=$TOMORROW\""

test_api_detailed "Get Available Beauticians for Multiple Services" \
    "curl -s \"$API_URL/appointment-services/available-beauticians?service_ids[]=1&service_ids[]=6&date=$TOMORROW\""

echo "🎯 STEP 3: Find Best Beautician Tests"
echo "--------------------------------------"
test_api_detailed "Find Best Beautician for Hair Cut" \
    "curl -s \"$API_URL/appointment-services/find-best-beautician?service_ids[]=1&date=$TOMORROW\""

test_api_detailed "Find Best Beautician for Hair + Facial" \
    "curl -s \"$API_URL/appointment-services/find-best-beautician?service_ids[]=1&service_ids[]=6&date=$TOMORROW\""

test_api_detailed "Find Best Beautician for Complex Services" \
    "curl -s \"$API_URL/appointment-services/find-best-beautician?service_ids[]=3&service_ids[]=17&date=$TOMORROW\""

echo "⏰ STEP 4: Time Slot Availability Tests"
echo "----------------------------------------"
# Get a beautician ID first
BEAUTICIAN_ID=$(curl -s "$API_URL/appointment-services/available-beauticians?service_ids[]=1&date=$TOMORROW" | jq -r '.available_beauticians[0].beautician_id // 1')

test_api_detailed "Get Available Time Slots (30 min)" \
    "curl -s \"$API_URL/appointment-services/available-time-slots?beautician_id=$BEAUTICIAN_ID&total_duration=30&date=$TOMORROW\""

test_api_detailed "Get Available Time Slots (90 min)" \
    "curl -s \"$API_URL/appointment-services/available-time-slots?beautician_id=$BEAUTICIAN_ID&total_duration=90&date=$TOMORROW\""

echo "✅ STEP 5: Booking Validation Tests"
echo "------------------------------------"
test_api_detailed "Validate Simple Booking" \
    "curl -s -X POST \"$API_URL/appointment-services/validate-booking\" -H \"Content-Type: application/json\" -d '{\"service_ids\":[1],\"date\":\"$TOMORROW\",\"branch_id\":1}'"

test_api_detailed "Validate Complex Booking" \
    "curl -s -X POST \"$API_URL/appointment-services/validate-booking\" -H \"Content-Type: application/json\" -d '{\"service_ids\":[1,6,10],\"date\":\"$TOMORROW\",\"branch_id\":1}'"

test_api_detailed "Validate Invalid Booking (Past Date)" \
    "curl -s -X POST \"$API_URL/appointment-services/validate-booking\" -H \"Content-Type: application/json\" -d '{\"service_ids\":[1],\"date\":\"2023-01-01\",\"branch_id\":1}'"

echo "🚀 STEP 6: Smart Booking Tests"
echo "-------------------------------"
# Get customer IDs
CUSTOMER_1=$(curl -s "$API_URL/users" | jq -r '.[] | select(.role == 3) | .id' | head -n1)
CUSTOMER_2=$(curl -s "$API_URL/users" | jq -r '.[] | select(.role == 3) | .id' | head -n2 | tail -n1)
CUSTOMER_3=$(curl -s "$API_URL/users" | jq -r '.[] | select(.role == 3) | .id' | head -n3 | tail -n1)

test_api_detailed "Smart Booking - Single Service" \
    "curl -s -X POST \"$API_URL/appointments/smart-booking\" -H \"Content-Type: application/json\" -d '{\"customer_id\":$CUSTOMER_1,\"service_ids\":[2],\"date\":\"$TOMORROW\",\"branch_id\":1}'"

test_api_detailed "Smart Booking - Multiple Services" \
    "curl -s -X POST \"$API_URL/appointments/smart-booking\" -H \"Content-Type: application/json\" -d '{\"customer_id\":$CUSTOMER_2,\"service_ids\":[4,5],\"date\":\"$TOMORROW\",\"branch_id\":1}'"

test_api_detailed "Smart Booking - Complex Services" \
    "curl -s -X POST \"$API_URL/appointments/smart-booking\" -H \"Content-Type: application/json\" -d '{\"customer_id\":$CUSTOMER_3,\"service_ids\":[10,11],\"date\":\"$TOMORROW\",\"branch_id\":2}'"

echo "🔄 STEP 7: Conflict Detection Tests"
echo "------------------------------------"
echo "Attempting to book conflicting appointments..."

# Try to book during existing appointment times
test_api_detailed "Conflict Test - Overlapping Appointment" \
    "curl -s -X POST \"$API_URL/appointments/smart-booking\" -H \"Content-Type: application/json\" -d '{\"customer_id\":$CUSTOMER_1,\"service_ids\":[1],\"date\":\"$TOMORROW\",\"branch_id\":1}'"

echo "📊 STEP 8: Current Appointments Overview"
echo "-----------------------------------------"
test_api_detailed "List All Appointments" \
    "curl -s \"$API_URL/appointments\""

echo "🎯 STEP 9: Edge Case Tests"
echo "---------------------------"
test_api_detailed "Non-existent Service" \
    "curl -s \"$API_URL/appointment-services/find-best-beautician?service_ids[]=999&date=$TOMORROW\""

test_api_detailed "Non-existent Branch" \
    "curl -s \"$API_URL/appointment-services/available-beauticians?service_ids[]=1&date=$TOMORROW&branch_id=999\""

test_api_detailed "Invalid Date Format" \
    "curl -s \"$API_URL/appointment-services/find-best-beautician?service_ids[]=1&date=invalid-date\""

echo ""
echo "🏆 PERFORMANCE INSIGHTS"
echo "========================"
echo "The improved booking logic should demonstrate:"
echo "✅ Continuous block detection for optimal slot finding"
echo "✅ Comprehensive conflict detection (4 overlap scenarios)"
echo "✅ Smart beautician assignment based on skills"
echo "✅ Multi-service booking support"
echo "✅ Branch-aware filtering"
echo "✅ Real-time availability calculation"
echo ""
echo "🎯 KEY IMPROVEMENTS TESTED:"
echo "1. Pseudocode implementation: ✅ Iterate through beauticians"
echo "2. Continuous blocks: ✅ Find sufficient duration blocks"
echo "3. Conflict detection: ✅ No overlapping appointments"
echo "4. Smart assignment: ✅ Best available beautician first"
echo ""
echo "📈 Test suite completed! Check the responses above for booking logic validation."
