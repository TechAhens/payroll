# API Documentation

## Overview

The PayrollPro system provides several AJAX endpoints for dynamic data loading and real-time updates. All API endpoints require authentication and return JSON responses.

## Authentication

All API requests must include a valid session. For AJAX requests, include the `X-Requested-With: XMLHttpRequest` header.

## Base URL

```
/api/
```

## Endpoints

### Dashboard APIs

#### Get Attendance Summary
```
GET /api/attendance-summary?date=YYYY-MM-DD
```

**Parameters:**
- `date` (optional): Date in YYYY-MM-DD format. Defaults to today.

**Response:**
```json
{
  "success": true,
  "summary": {
    "present": 25,
    "absent": 3,
    "half_day": 1,
    "late": 2
  }
}
```

#### Get Current Period
```
GET /api/current-period
```

**Response:**
```json
{
  "success": true,
  "period_id": 7,
  "period_name": "July 2024"
}
```

### Employee APIs

#### Generate Employee Code
```
GET /api/generate-employee-code
```

**Response:**
```json
{
  "success": true,
  "code": "EMP006"
}
```

#### Employee Search
```
GET /api/employee-search?q=search_term&limit=10
```

**Parameters:**
- `q`: Search query (minimum 2 characters)
- `limit` (optional): Maximum results to return (default: 10, max: 20)

**Response:**
```json
{
  "success": true,
  "employees": [
    {
      "id": 1,
      "emp_code": "EMP001",
      "first_name": "John",
      "last_name": "Doe",
      "email": "john.doe@company.com"
    }
  ]
}
```

### Payroll APIs

#### Salary Calculator
```
GET /api/salary-calculator?basic=30000&components[HRA]=12000
```

**Parameters:**
- `basic`: Basic salary amount
- `components`: Array of component codes and amounts

**Response:**
```json
{
  "success": true,
  "calculations": {
    "BASIC": {
      "name": "Basic Salary",
      "type": "earning",
      "amount": 30000
    },
    "HRA": {
      "name": "House Rent Allowance",
      "type": "earning",
      "amount": 12000
    }
  },
  "total_earnings": 42000,
  "total_deductions": 6000,
  "net_salary": 36000
}
```

### Notification APIs

#### Get Unread Count
```
GET /api/notifications/unread-count
```

**Response:**
```json
{
  "success": true,
  "count": 5
}
```

#### Mark Notification as Read
```
POST /notifications/mark-read
```

**Parameters:**
- `notification_id`: ID of the notification to mark as read

**Response:**
```json
{
  "success": true
}
```

## Error Responses

All endpoints return consistent error responses:

```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field_name": "Field-specific error message"
  }
}
```

## HTTP Status Codes

- `200`: Success
- `400`: Bad Request (validation errors)
- `401`: Unauthorized
- `403`: Forbidden
- `404`: Not Found
- `500`: Internal Server Error

## Rate Limiting

API endpoints are rate-limited to prevent abuse:
- 100 requests per minute per user
- 1000 requests per hour per user

## Examples

### JavaScript Fetch Example

```javascript
// Get attendance summary
fetch('/api/attendance-summary?date=2024-07-22', {
    headers: {
        'X-Requested-With': 'XMLHttpRequest'
    }
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        console.log('Attendance summary:', data.summary);
    } else {
        console.error('Error:', data.message);
    }
});
```

### jQuery Example

```javascript
// Employee search with autocomplete
$('#employee-search').on('input', function() {
    const query = $(this).val();
    
    if (query.length >= 2) {
        $.get('/api/employee-search', { q: query, limit: 10 })
            .done(function(data) {
                if (data.success) {
                    // Update autocomplete dropdown
                    updateAutocomplete(data.employees);
                }
            });
    }
});
```

## Security Considerations

1. **CSRF Protection**: All POST requests require a valid CSRF token
2. **Input Validation**: All inputs are validated and sanitized
3. **SQL Injection Prevention**: All queries use prepared statements
4. **Access Control**: Endpoints respect user permissions
5. **Session Management**: Sessions expire after 30 minutes of inactivity

## Debugging

Enable debug mode in development by setting:
```php
define('DEBUG_MODE', true);
```

This will include additional debugging information in API responses.