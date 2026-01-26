#!/bin/bash
#
# OpenCATS REST API - Live Integration Test
#

API_BASE="http://localhost:8888/index.php?m=api"
API_KEY="dev-test-key-12345"

echo "============================================================"
echo "OpenCATS REST API - Live Integration Test"
echo "============================================================"
echo ""
echo "API Base: $API_BASE"
echo "API Key: $API_KEY"
echo ""

# Counter for tests
PASSED=0
FAILED=0

# Test function
test_endpoint() {
    local name=$1
    local endpoint=$2
    local auth=$3
    local expected=$4

    if [ "$auth" == "yes" ]; then
        response=$(curl -s -H "X-Api-Key: $API_KEY" "$API_BASE&a=$endpoint")
    else
        response=$(curl -s "$API_BASE&a=$endpoint")
    fi

    if echo "$response" | grep -q "$expected"; then
        echo "[PASS] $name"
        ((PASSED++))
    else
        echo "[FAIL] $name"
        echo "       Response: $response" | head -c 200
        echo ""
        ((FAILED++))
    fi
}

echo "--- Testing Unauthenticated Endpoints ---"
test_endpoint "Ping (health check)" "ping" "no" "status"

echo ""
echo "--- Testing Authenticated GET Endpoints ---"
test_endpoint "Candidates List" "candidates" "yes" "total"
test_endpoint "Job Orders List" "joborders" "yes" "total"
test_endpoint "Companies List" "companies" "yes" "total"
test_endpoint "Contacts List" "contacts" "yes" "total"
test_endpoint "Tearsheets List" "tearsheets" "yes" "total"
test_endpoint "Job Submissions List" "jobsubmissions" "yes" "total"
test_endpoint "Placements List" "placements" "yes" "total"
test_endpoint "Notes List" "notes" "yes" "total"
test_endpoint "Appointments List" "appointments" "yes" "total"
test_endpoint "Tasks List" "tasks" "yes" "total"
test_endpoint "Webhooks List" "subscriptions" "yes" "total"
test_endpoint "Meta (Entities)" "meta" "yes" "entities"

echo ""
echo "--- Testing Authentication ---"
# Test unauthorized access
response=$(curl -s "$API_BASE&a=candidates")
if echo "$response" | grep -q "Unauthorized"; then
    echo "[PASS] Unauthorized access blocked"
    ((PASSED++))
else
    echo "[FAIL] Unauthorized access NOT blocked"
    ((FAILED++))
fi

echo ""
echo "--- Testing POST Create ---"
# Create a candidate
response=$(curl -s -X POST \
    -H "X-Api-Key: $API_KEY" \
    -H "Content-Type: application/json" \
    -d '{"firstName":"Test","lastName":"Candidate","email":"test@example.com"}' \
    "$API_BASE&a=candidates")

if echo "$response" | grep -q "id"; then
    echo "[PASS] Create Candidate (POST)"
    CANDIDATE_ID=$(echo "$response" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
    ((PASSED++))
else
    echo "[FAIL] Create Candidate (POST)"
    echo "       Response: $response" | head -c 200
    echo ""
    ((FAILED++))
fi

# Create a company
response=$(curl -s -X POST \
    -H "X-Api-Key: $API_KEY" \
    -H "Content-Type: application/json" \
    -d '{"name":"Test Company Inc","city":"Austin","state":"TX"}' \
    "$API_BASE&a=companies")

if echo "$response" | grep -q "id"; then
    echo "[PASS] Create Company (POST)"
    COMPANY_ID=$(echo "$response" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
    ((PASSED++))
else
    echo "[FAIL] Create Company (POST)"
    echo "       Response: $response" | head -c 200
    echo ""
    ((FAILED++))
fi

echo ""
echo "--- Testing GET Single Record ---"
if [ ! -z "$CANDIDATE_ID" ]; then
    response=$(curl -s -H "X-Api-Key: $API_KEY" "$API_BASE&a=candidates&id=$CANDIDATE_ID")
    if echo "$response" | grep -q "Test"; then
        echo "[PASS] Get Single Candidate"
        ((PASSED++))
    else
        echo "[FAIL] Get Single Candidate"
        ((FAILED++))
    fi
fi

echo ""
echo "--- Testing PUT Update ---"
if [ ! -z "$CANDIDATE_ID" ]; then
    response=$(curl -s -X PUT \
        -H "X-Api-Key: $API_KEY" \
        -H "Content-Type: application/json" \
        -d '{"city":"Denver","state":"CO"}' \
        "$API_BASE&a=candidates&id=$CANDIDATE_ID")
    if echo "$response" | grep -q "Denver"; then
        echo "[PASS] Update Candidate (PUT)"
        ((PASSED++))
    else
        echo "[FAIL] Update Candidate (PUT)"
        echo "       Response: $response" | head -c 200
        echo ""
        ((FAILED++))
    fi
fi

echo ""
echo "--- Testing Rate Limit Headers ---"
response=$(curl -s -I -H "X-Api-Key: $API_KEY" "$API_BASE&a=candidates" 2>&1)
if echo "$response" | grep -q "X-RateLimit"; then
    echo "[PASS] Rate Limit Headers Present"
    ((PASSED++))
else
    echo "[FAIL] Rate Limit Headers Missing"
    ((FAILED++))
fi

echo ""
echo "============================================================"
echo "TEST SUMMARY"
echo "============================================================"
echo "Passed: $PASSED"
echo "Failed: $FAILED"
echo "Total:  $((PASSED + FAILED))"
echo ""

if [ $FAILED -eq 0 ]; then
    echo "STATUS: ALL TESTS PASSED!"
    exit 0
else
    echo "STATUS: SOME TESTS FAILED"
    exit 1
fi
