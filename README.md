# Shopify CSV Import System

A Laravel 12 application that allows users to upload Shopify product CSV files, process them asynchronously using Laravel Queues, import products into Shopify via GraphQL API, and track import status through a dashboard.

---

## Features

### File Upload
- Upload Shopify-compatible CSV files
- Client-side validation for file type and size
- Upload tracking and status management

### CSV Processing
- Background processing using Laravel Queues
- CSV validation and parsing
- Error handling and logging

### Shopify Integration
- Shopify Admin GraphQL API integration
- Create new products
- Update existing products using SKU matching
- Automatically assign products to a configured collection

### Dashboard
- Upload statistics
- Product import status tracking
- Upload history
- Success and failure monitoring

### Logging
- Application-level error logging
- Import processing logs
- Shopify API error tracking
- Queue failure logging

---

# Technology Stack

| Component | Technology |
|------------|------------|
| Backend | Laravel 12 |
| Database | MySQL |
| Queue | Database Queue |
| Frontend | Blade + Bootstrap 5 |
| Shopify Integration | GraphQL Admin API |
| Logging | Laravel Log + Database Logs |

---

# Application Workflow

```text
Upload CSV
    │
    ▼
Create Upload Record
    │
    ▼
Dispatch Queue Job
    │
    ▼
Parse CSV File
    │
    ▼
Validate Product Data
    │
    ▼
Create / Update Shopify Product
    │
    ▼
Store Import Results
    │
    ▼
Update Dashboard Statistics
```

---

# Database Structure

## uploads

Stores uploaded CSV files and processing status.

| Column |
|----------|
| id |
| original_filename |
| file_path |
| status |
| total_records |
| processed_records |
| successful_records |
| failed_records |
| started_at |
| completed_at |

---

## products

Stores imported product data.

| Column |
|----------|
| id |
| upload_id |
| title |
| sku |
| price |
| shopify_product_id |
| status |
| error_message |

---

## import_records

Stores import history and API interaction results.

| Column |
|----------|
| id |
| upload_id |
| product_id |
| action |
| status |
| message |
| request_payload |
| response_payload |

---

## error_logs

Stores application and import-related errors.

| Column |
|----------|
| id |
| upload_id |
| product_id |
| source |
| message |
| context |

---

# Setup Instructions

## Clone Repository

```bash
git clone https://github.com/malkiatpannu/shopify-import-system.git

cd shopify-import-system
```

## Install Dependencies

```bash
composer install
```

## Environment Configuration

Copy environment file:

```bash
cp .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

---

## Database Configuration

Update `.env`

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=shopify_import
DB_USERNAME=root
DB_PASSWORD=
```

---

## Shopify Configuration

Update `.env`

```env
SHOPIFY_STORE_URL=your-store.myshopify.com

SHOPIFY_ACCESS_TOKEN=your-access-token

SHOPIFY_COLLECTION_ID=123456789
```

---

## Run Migrations

```bash
php artisan migrate
```

---

## Create Queue Tables

```bash
php artisan queue:table

php artisan migrate
```

---

## Configure Queue

Update `.env`

```env
QUEUE_CONNECTION=database
```

---

## Start Queue Worker

```bash
php artisan queue:work
```

---

## Start Application

```bash
php artisan serve
```

Application URL:

```text
http://localhost:8000
```

---

# Shopify GraphQL Implementation

The application uses Shopify GraphQL Admin API for product management.

Implemented operations:

### Product Search

Used to determine whether a product already exists.

```graphql
products(first: 1, query: $query)
```

### Product Creation

```graphql
productCreate
```

### Product Update

```graphql
productUpdate
```

### Collection Assignment

```graphql
collectionAddProducts
```

---

# Import Logic

## Create Product

If SKU does not exist in Shopify:

```text
Create Product
```

## Update Product

If SKU already exists:

```text
Update Product
```

This prevents duplicate products and keeps Shopify data synchronized.

---

# Logging Strategy

The application logs events at multiple levels:

### Application Logs

Stored in:

```text
storage/logs/laravel.log
```

### Database Logs

Stored in:

```text
error_logs
```

# Testing Instructions

## Upload a CSV File

Navigate to:

```text
/upload
```

Upload a Shopify-compatible CSV file.

---

## Verify Queue Processing

Ensure queue worker is running:

```bash
php artisan queue:work
```

Observe product processing.

---

## Verify Dashboard

Navigate to:

```text
/dashboard
```

Verify:

- Upload count
- Product count
- Success count
- Failure count
- Upload history

---

## Verify Product Import

Confirm products appear in Shopify Admin.

---

## Verify Logs

Navigate to:

```text
/logs
```

# Design Decisions

### Queue-Based Processing

CSV imports are processed asynchronously to avoid request timeouts and improve scalability.

### SKU-Based Product Matching

Products are identified by SKU.

Benefits:

- Prevents duplicate products
- Supports updates to existing products
- Simplifies synchronization

### GraphQL Over REST

GraphQL was chosen because:

- Better aligns with Shopify's current API strategy
- Reduces API requests
- Allows flexible querying

### Import Tracking

Separate upload, product, and import tracking tables were created to provide visibility into the import process and maintain audit history.

---

# Assumptions

- Uploaded CSV follows Shopify product export/import format.
- SKU uniquely identifies a product.
- Shopify credentials have appropriate permissions.
- Queue worker is running during imports.
- Valid Shopify Collection ID is configured.

---

# Future Improvements

- Real-time dashboard updates using broadcasting
- Retry failed product imports
- Bulk GraphQL mutations
- Advanced CSV mapping interface
- User authentication and authorization
- Export import reports
- Scheduled imports

---

# Author

Laravel Technical Assessment Submission

Developed using Laravel 12, Bootstrap 5, MySQL, Queue Workers, and Shopify GraphQL API.