# DataNexus

DataNexus is a full-stack Data Intelligence and ETL (Extract, Transform,
Load) platform designed to automate the processing and validation of
complex sales targets and objective cycles.

![System
Dashboard](https://placehold.co/1000x500?text=DataNexus+Dashboard+Preview)

## Overview

DataNexus orchestrates the full lifecycle of unstructured Excel inputs,
transforming them into standardized datasets ready for database
ingestion. The platform uses a Python backend (Pandas) for data
cleansing, normalization, type enforcement, cycle calculation, and
application of dynamic business rules. The frontend provides a reactive,
modern interface supporting real-time visualization and validation.

The architecture prioritizes security hardening, automated cleanup,
operational safety, and a streamlined user experience, eliminating
common errors from manual spreadsheet handling.

## Key Features

-   Automated ETL Pipeline: Ingestion of raw `.xlsx` files and
    conversion into structured `.txt` files suitable for database
    operations.
-   Python Data Engine: Built with Pandas and NumPy for high-performance
    unpivot operations, cycle calculations, and complex data
    transformations.
-   Reactive Dashboard: Lightweight SPA-like interface for visualizing
    KPIs, distributions and grouped data without page reloads.
-   Security Hardening: Input validation, hashed file renaming, shell
    argument escaping, and backend error suppression.
-   Garbage Collection: Automatic removal of sensitive files immediately
    after processing or after a 24-hour retention window.
-   Audit Logging: Full backend logging for tracking system operations,
    user interactions, and processing exceptions.

## Tech Stack

### Backend & Orchestration

-   PHP 8 (API Controller and Server)
-   Python 3.13 (Data Processing Engine)
-   Shell Scripting (Environment and Execution Management)

### Data Processing

-   Pandas
-   NumPy
-   OpenPyXL

### Frontend & UI

-   HTML5 / JavaScript (ES6+)
-   Tailwind CSS
-   Chart.js
-   SweetAlert2

## Project Structure

    /
    ├── src/
    │   ├── api.php        # Backend controller for upload handling and execution
    │   ├── core.py        # Python ETL engine
    │   ├── config.php     # Environment configuration
    │   ├── index.php      # Frontend dashboard
    │   ├── system.log     # Runtime audit logs
    │   ├── uploads/       # Temporary raw file storage
    │   └── output/        # Processed file exports
    ├── .gitignore
    ├── requirements.txt   # Python dependencies
    ├── start_server.bat   # Windows shortcut for launching the server
    └── README.md

## Installation and Setup

### Prerequisites

-   PHP 8.0 or higher
-   Python 3.x installed and available in the system PATH

### Step 1: Clone the Repository

``` bash
git clone https://github.com/your-username/datanexus.git
cd datanexus
```

### Step 2: Install Python Dependencies

``` bash
pip install -r requirements.txt
```

### Step 3: Configuration (Optional)

If Python is not available as a global command, update `src/api.php` or
`src/config.php` to define the absolute path to the Python executable.

### Step 4: Run the Application

For Windows users:

``` bash
start_server.bat
```

Alternatively:

``` bash
cd src
php -S localhost:8080
```

Access the dashboard at: http://localhost:8080

## Security Details

-   File Whitelisting: Only `.xlsx` files are accepted.
-   Sanitized Filenames: Uploaded files are renamed using random MD5
    hashes, preventing traversal or execution exploits.
-   Command Injection Protection: All shell arguments are escaped using
    `escapeshellarg()`.
-   Auto-Pruning: Input files are removed immediately after processing;
    exported files follow a retention policy before being purged.

## License

This project is open-source and distributed under the MIT License.
