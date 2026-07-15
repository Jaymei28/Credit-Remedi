@extends('layouts.app')

@section('title', auth()->user()->role === 'admin' ? 'All Disputes' : 'My Disputes')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/dataTables.bootstrap5.min.css') }}">
<style>
    @media (min-width: 1200px) {
        .container {
            max-width: 1140px;
        }
    }

    /* Page Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .page-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 1.25rem;
        font-weight: 600;
        margin: 0;
    }

    /* Custom Controls Row */
    .custom-controls-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        gap: 1rem;
    }

    .controls-left {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .controls-right {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    /* Filter Group Styling */
    .filter-group {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .filter-group label {
        margin: 0;
        font-size: 0.875rem;
        color: var(--text-secondary);
        white-space: nowrap;
    }

    /* Make all dropdowns match */
    .filter-group select,
    .dataTables_wrapper .dataTables_length select {
        padding: 0.375rem 2rem 0.375rem 0.75rem !important;
        border: 1px solid var(--border-color) !important;
        border-radius: var(--border-radius-md) !important;
        background: var(--bg-primary) !important;
        color: var(--text-primary) !important;
        font-size: 0.875rem !important;
        appearance: none !important;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23666' d='M6 9L1 4h10z'/%3E%3C/svg%3E") !important;
        background-repeat: no-repeat !important;
        background-position: right 0.5rem center !important;
        background-size: 12px !important;
    }

    .filter-group select:focus,
    .dataTables_wrapper .dataTables_length select:focus {
        outline: none !important;
        border-color: var(--color-primary) !important;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important;
    }

    /* Hide default DataTables controls positioning */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        float: none !important;
    }

    /* Make DataTables controls align inline */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin: 0;
    }

    .dataTables_wrapper .dataTables_length label,
    .dataTables_wrapper .dataTables_filter label {
        margin: 0;
        white-space: nowrap;
    }

    /* Search input styling */
    .dataTables_wrapper .dataTables_filter input {
        margin-left: 0.5rem;
        padding: 0.375rem 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-md);
        background: var(--bg-primary);
        color: var(--text-primary);
    }

    .dataTables_wrapper .dataTables_filter input:focus {
        outline: none;
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    /* Table View Enhancements */
    .table-view {
        background: var(--bg-primary);
        border-radius: var(--border-radius-lg);
        overflow: hidden;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border-color);
    }

    .table-view .card-body {
        padding: 1.5rem;
    }

    #disputesTable {
        margin-bottom: 0;
        color: var(--text-primary);
        background-color: var(--bg-primary); /* Force background */
    }

    /* Override Bootstrap Table Vars */
    #disputesTable {
        --bs-table-bg: var(--bg-primary);
        --bs-table-color: var(--text-primary);
        --bs-table-hover-bg: var(--bg-secondary);
        --bs-table-hover-color: var(--text-primary);
    }

    /* Force Cells transparency relative to table */
    #disputesTable th,
    #disputesTable td {
        background-color: var(--bg-primary) !important;
        color: var(--text-primary) !important;
        border-bottom-color: var(--border-color);
    }

    /* Header Specifics */
    #disputesTable thead th {
        background-color: var(--bg-primary) !important; /* Deep dark matches body */
        color: var(--text-primary) !important; /* Bright white text */
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        border-bottom: 2px solid var(--border-color); /* Stronger border to separate */
    }

    /* Hover State */
    #disputesTable tbody tr:hover td {
        background-color: var(--bg-secondary) !important;
        cursor: pointer;
    }

    /* Muted text fix for dark mode */
    [data-theme="dark"] .text-muted {
        color: var(--text-muted) !important;
    }

    #disputesTable thead {
        background: var(--bg-secondary);
    }

    #disputesTable tbody tr {
        transition: all var(--transition-base);
    }

    #disputesTable tbody tr:hover {
        background: var(--bg-secondary);
        cursor: pointer;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .custom-controls-row {
            flex-direction: column;
            align-items: stretch;
        }

        .controls-left,
        .controls-right {
            width: 100%;
        }
    }

    /* 
       🔥 FORCE OVERRIDES FOR DARK MODE VISIBILITY 
       This bypasses any variable inheritance issues 
    */
    [data-theme="dark"] #disputesTable thead th {
        color: #ffffff !important; /* Pure White */
        background-color: #1a202c !important; /* Dark Bg */
        border-color: #4a5568 !important;
    }

    [data-theme="dark"] #disputesTable tbody td {
        color: #e2e8f0 !important; /* Off-White for body */
        border-color: #4a5568 !important;
    }

    [data-theme="dark"] #disputesTable tbody tr:hover td {
        background-color: #2d3748 !important; /* Lighter dark for hover */
        color: #ffffff !important;
    }

    [data-theme="dark"] .status-badge {
        font-weight: 700 !important; /* Bolder text for badges */
    }

    /* Fix Pagination Buttons in Dark Mode (Bootstrap 5 Integration) */
    [data-theme="dark"] .dataTables_wrapper .dataTables_paginate .pagination .page-item .page-link,
    [data-theme="dark"] .pagination .page-item .page-link,
    [data-theme="dark"] .page-link {
        background-color: #1a202c !important;
        color: #ffffff !important;
        border-color: #4a5568 !important;
    }

    [data-theme="dark"] .dataTables_wrapper .dataTables_paginate .pagination .page-item .page-link:hover,
    [data-theme="dark"] .pagination .page-item .page-link:hover,
    [data-theme="dark"] .page-link:hover {
        background-color: #2d3748 !important;
        color: #ffffff !important;
        border-color: #667eea !important;
    }

    [data-theme="dark"] .dataTables_wrapper .dataTables_paginate .pagination .page-item.active .page-link,
    [data-theme="dark"] .pagination .page-item.active .page-link,
    [data-theme="dark"] .page-item.active .page-link {
        background-color: #667eea !important; /* Primary color */
        border-color: #667eea !important;
        color: #ffffff !important;
    }

    [data-theme="dark"] .dataTables_wrapper .dataTables_paginate .pagination .page-item.disabled .page-link,
    [data-theme="dark"] .pagination .page-item.disabled .page-link,
    [data-theme="dark"] .page-item.disabled .page-link {
        background-color: #1a202c !important;
        color: #718096 !important; /* Muted text */
        border-color: #4a5568 !important;
        opacity: 0.6;
    }

    /* DataTables info text in dark mode */
    [data-theme="dark"] .dataTables_wrapper .dataTables_info {
        color: #e2e8f0 !important;
    }

    /* Pagination wrapper background */
    [data-theme="dark"] .dataTables_wrapper .dataTables_paginate {
        color: #e2e8f0 !important;
    }

    /* Hide pagination and info when table is empty */
    .dataTables_empty ~ .dataTables_info,
    .dataTables_empty ~ .dataTables_paginate {
        display: none !important;
    }
    
    /* Alternative: Hide when showing "No disputes found" */
    #disputesTable tbody tr:only-child .empty-state {
        padding: 3rem 0;
    }
    
    /* Hide info/pagination when only 1 row exists and it's empty */
    .dataTables_wrapper:has(.empty-state) .dataTables_info,
    .dataTables_wrapper:has(.empty-state) .dataTables_paginate {
        display: none !important;
    }


    /* 
    📱 MOBILE CARD VIEW - REDESIGNED 
    Transform the table into a clean app-like list
    */
    @media (max-width: 768px) {
        /* Hide entire header */
        #disputesTable thead {
            display: none !important;
        }

        /* Card Container Styles */
        #disputesTable tbody tr {
            display: flex;
            flex-direction: column;
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-lg);
            margin-bottom: 1rem;
            padding: 1.25rem; /* Increased padding */
            box-shadow: var(--shadow-sm);
            position: relative;
            width: 100% !important; /* Force full width */
        }
        
        /* Force table to be full width block */
        #disputesTable, #disputesTable tbody {
            display: block;
            width: 100%;
        }

        /* Hide the default table cell borders/spacing */
        #disputesTable tbody td {
            display: block;
            border: none;
            padding: 0;
            text-align: left;
            width: 100%;
        }

        /* --- Custom Layout for specific Columns --- */

        /* 1. Date (Top Left) and Status Wrapper */
        /* We need to ensure they don't overlap by clearing space */

        #disputesTable tbody td[data-label="Date"] {
            order: 1;
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        /* 2. Status (Top Right - Absolutely Positioned) */
        #disputesTable tbody td[data-label="Status"] {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: auto;
            z-index: 2;
        }
        
        /* Adjust badge size for mobile */
        #disputesTable tbody td[data-label="Status"] .status-badge {
            padding: 0.25rem 0.6rem;
            font-size: 0.75rem;
        }

        /* 3. Subject (Main Title) */
        #disputesTable tbody td[data-label="Subject"] {
            order: 2;
            margin-bottom: 0.75rem;
            padding-right: 0; 
            margin-top: 0.5rem; /* Space from Date */
        }
        
        #disputesTable tbody td[data-label="Subject"] strong {
            font-size: 1.1rem; /* Larger title */
            line-height: 1.4;
            color: var(--text-primary);
            display: -webkit-box;
            -webkit-line-clamp: 3; /* Limit to 3 lines */
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* 4. Creditor (Subtitle) */
        #disputesTable tbody td[data-label="Creditor"] {
            order: 3;
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin-bottom: 1.25rem; /* More space before button */
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap; /* Allow wrapping if long */
        }
        
        #disputesTable tbody td[data-label="Creditor"]::before {
            content: '🏛'; /* Icon for creditor */
            font-size: 0.9rem;
            opacity: 0.7;
        }

        /* 5. Action Button (Bottom Full Width) */
        #disputesTable tbody td[data-label="Action"] {
            order: 4;
            margin-top: 0.5rem;
        }
        
        #disputesTable tbody td[data-label="Action"] .btn {
            width: 100%;
            justify-content: center;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        /* Hide User column on mobile (admin only mostly) */
        #disputesTable tbody td[data-label="User"] {
            display: none;
        }

        /* Hide pseudo-labels we added before */
        #disputesTable tbody td::before {
            display: none; /* We don't want "DATE: ..." anymore */
        }
        
        /* Dark Mode Specifics for Card */
        [data-theme="dark"] #disputesTable tbody tr {
            background-color: #1a202c !important;
            border-color: #4a5568 !important;
        }
        
        [data-theme="dark"] #disputesTable tbody td[data-label="Subject"] strong {
            color: #fff !important;
        }
    }
</style>
@endpush

@section('content')

<div class="container mt-5 mb-5">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0 d-flex align-items-center gap-2" style="font-weight: 600; font-size: 1.25rem;">
            <i class="bi bi-file-earmark-text"></i>
            <span>{{ auth()->user()->role === 'admin' ? 'All Dispute Letters' : 'My Dispute Letters' }}</span>
        </h5>
        <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#guidelineModal">
            <i class="bi bi-question-circle me-1"></i> Help
        </button>
    </div>

    {{-- Loading Spinner --}}
    <div id="loaderSpinner" class="text-center my-4 d-none">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    {{-- Table View --}}
    <div class="table-view">
        <div class="card-body">
    {{-- Custom Controls Row --}}
    <div class="custom-controls-row">
        <div class="controls-left">
            <div class="search-group">
                <i class="bi bi-search search-icon"></i>
                <input type="text" id="customSearch" class="form-control" placeholder="Search disputes...">
            </div>
        </div>
        <div class="controls-right">
            <div class="filter-group">
                <label for="statusFilter">Status:</label>
                <select id="statusFilter" class="modern-select">
                    <option value="">All Statuses</option>
                    <option value="Pending">⏳ Pending</option>
                    <option value="Posted">✅ Posted</option>
                </select>
            </div>
            <div class="filter-group">
                <select id="customLength" class="modern-select">
                    <option value="10">10 entries</option>
                    <option value="25">25 entries</option>
                    <option value="50">50 entries</option>
                    <option value="100">100 entries</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Table View --}}
    <div class="table-view">
        <div class="table-responsive">
            @include('partials.dispute-table', ['disputes' => $disputes])
        </div>
    </div>
</div>

<!-- Modal: Guidelines -->
@include('partials.guidelines-modal')

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const spinner = document.getElementById('loaderSpinner');
        let dataTable;

        // Initialize DataTable
        function initDataTable() {
            if ($.fn.DataTable.isDataTable('#disputesTable')) {
                $('#disputesTable').DataTable().destroy();
            }

            dataTable = $('#disputesTable').DataTable({
                pageLength: 10,
                responsive: true,
                autoWidth: false,
                order: [[0, 'desc']], // Sort by date descending
                columnDefs: [
                    { orderable: false, targets: [1, -2, -1] }, // Disable sorting for Subject (1), Status (-2), and Action (-1)
                    { targets: '_all', defaultContent: '' } // Prevent unknown parameter error
                ],
                dom: 'rtip', // Hide default Header (f=filter, l=length), keep Table(t), Info(i), Pagination(p)
                language: {
                    emptyTable: "No disputes found"
                }
            });

            // 1. Custom Search
            $('#customSearch').on('keyup', function() {
                dataTable.search(this.value).draw();
            });

            // 2. Custom Entries Per Page
            $('#customLength').on('change', function() {
                dataTable.page.len(this.value).draw();
            });

            // 3. Status Filter
            $('#statusFilter').on('change', function() {
                const status = this.value;
                const statusColumnIndex = {{ auth()->user()->role === 'admin' ? '4' : '3' }};
                dataTable.column(statusColumnIndex).search(status).draw();
            });
        }

        // Initialize DataTable
        initDataTable();
    });
</script>
<style>
    /* Custom CSS for the fixed layout */
    .custom-controls-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .controls-left {
        flex: 1;
        min-width: 250px;
    }

    .controls-right {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    /* Search Input Styling */
    .search-group {
        position: relative;
        max-width: 300px;
    }

    .search-icon {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        z-index: 5;
    }

    #customSearch {
        padding-left: 2.5rem;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-md);
        background: var(--bg-primary);
        color: var(--text-primary);
    }

    #customSearch:focus {
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        border-color: var(--color-primary);
    }

    /* Selects Styling */
    .modern-select {
        padding: 0.375rem 2rem 0.375rem 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-md);
        background: var(--bg-primary);
        color: var(--text-primary);
        font-size: 0.875rem;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23666' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.5rem center;
        background-size: 12px;
        cursor: pointer;
    }

    .modern-select:focus {
        outline: none;
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .filter-group {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .filter-group label {
        margin: 0;
        font-weight: 500;
        color: var(--text-secondary);
        white-space: nowrap;
    }

    .modern-btn {
        padding: 0.375rem 1rem;
        border-radius: var(--border-radius-md);
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        height: 38px; /* Match select height approximately */
    }
    
    @media (max-width: 768px) {
        .custom-controls-row {
            flex-direction: column;
            align-items: stretch;
        }
        .controls-right {
            justify-content: space-between;
        }
        .search-group {
            max-width: 100%;
        }
    }
</style>
@endpush
