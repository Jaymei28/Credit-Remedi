@extends('layouts.app')

@section('title', 'Resource Center')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

    .resource-center-container {
        font-family: 'Inter', sans-serif;
        max-width: 1400px;
        margin: 0 auto;
        padding: 2rem 1rem 4rem;
    }

    /* Header Section */
    .resource-header {
        text-align: center;
        margin-bottom: 3rem;
    }

    .resource-title {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .resource-title .gradient-text {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .resource-subtitle {
        color: #6b7280;
        font-size: 1.1rem;
        max-width: 700px;
        margin: 0 auto;
    }

    /* Category Filters */
    .category-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        justify-content: center;
        margin-bottom: 3rem;
    }

    .category-badge {
        padding: 0.6rem 1.5rem;
        border-radius: 50px;
        border: 2px solid #e5e7eb;
        background: white;
        color: #6b7280;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.9rem;
    }

    .category-badge:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .category-badge.active {
        border-color: transparent;
        color: white;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .category-badge.all.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .category-badge.government.active {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    }

    .category-badge.financial.active {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .category-badge.education.active {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }

    .category-badge.consumer.active {
        background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
    }

    /* Featured Section */
    .featured-section {
        margin-bottom: 3rem;
    }

    .section-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .section-title i {
        color: #f59e0b;
        font-size: 1.8rem;
    }

    /* Resource Cards Grid */
    .resources-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 2rem;
        margin-bottom: 3rem;
    }

    /* Resource Card */
    .resource-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        border: 2px solid transparent;
    }

    .resource-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .resource-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        border-color: #667eea;
    }

    .resource-card:hover::before {
        transform: scaleX(1);
    }

    .resource-card.government::before {
        background: linear-gradient(90deg, #3b82f6 0%, #1d4ed8 100%);
    }

    .resource-card.financial::before {
        background: linear-gradient(90deg, #10b981 0%, #059669 100%);
    }

    .resource-card.education::before {
        background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%);
    }

    .resource-card.consumer::before {
        background: linear-gradient(90deg, #ec4899 0%, #db2777 100%);
    }

    /* Card Icon */
    .card-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
    }

    .resource-card.government .card-icon {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: #1d4ed8;
    }

    .resource-card.financial .card-icon {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #059669;
    }

    .resource-card.education .card-icon {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #d97706;
    }

    .resource-card.consumer .card-icon {
        background: linear-gradient(135deg, #fce7f3 0%, #fbcfe8 100%);
        color: #db2777;
    }

    .resource-card:hover .card-icon {
        transform: scale(1.1) rotate(5deg);
    }

    /* Card Content */
    .card-category {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 1rem;
        letter-spacing: 0.5px;
    }

    .resource-card.government .card-category {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .resource-card.financial .card-category {
        background: #d1fae5;
        color: #059669;
    }

    .resource-card.education .card-category {
        background: #fef3c7;
        color: #d97706;
    }

    .resource-card.consumer .card-category {
        background: #fce7f3;
        color: #db2777;
    }

    .card-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 0.75rem;
        line-height: 1.4;
    }

    .card-description {
        color: #6b7280;
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 1.5rem;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Hover Info */
    .card-hover-info {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
    }

    .resource-card:hover .card-hover-info {
        max-height: 200px;
    }

    .card-use-case {
        background: #f9fafb;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
    }

    .use-case-title {
        font-weight: 600;
        color: #374151;
        font-size: 0.85rem;
        margin-bottom: 0.5rem;
    }

    .use-case-text {
        color: #6b7280;
        font-size: 0.85rem;
        line-height: 1.5;
    }

    /* Card Link */
    .card-link {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: #667eea;
        font-weight: 600;
        text-decoration: none;
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }

    .card-link:hover {
        gap: 0.75rem;
        color: #764ba2;
    }

    .card-link i {
        transition: transform 0.3s ease;
    }

    .card-link:hover i {
        transform: translateX(4px);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #6b7280;
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    /* Dark Mode */
    [data-theme="dark"] .resource-subtitle {
        color: #9ca3af;
    }

    [data-theme="dark"] .category-badge {
        background: #374151;
        border-color: #4b5563;
        color: #d1d5db;
    }

    [data-theme="dark"] .category-badge:hover {
        background: #1f2937;
    }

    [data-theme="dark"] .section-title {
        color: #f3f4f6;
    }

    [data-theme="dark"] .resource-card {
        background: #1f2937;
        border-color: #374151;
    }

    [data-theme="dark"] .card-title {
        color: #f3f4f6;
    }

    [data-theme="dark"] .card-description {
        color: #9ca3af;
    }

    [data-theme="dark"] .card-use-case {
        background: #374151;
    }

    [data-theme="dark"] .use-case-title {
        color: #e5e7eb;
    }

    [data-theme="dark"] .use-case-text {
        color: #9ca3af;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .resource-title {
            font-size: 2rem;
        }

        .resources-grid {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .category-filters {
            gap: 0.5rem;
        }

        .category-badge {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
        }
    }
</style>

<div class="resource-center-container">
    <!-- Header -->
    <div class="resource-header">
        <h1 class="resource-title">📚 <span class="gradient-text">Resource Center</span></h1>
        <p class="resource-subtitle">Trusted government and industry agencies to file complaints and escalate your credit disputes</p>
    </div>

    <!-- Category Filters -->
    <div class="category-filters">
        <button class="category-badge all active" data-category="all">All Resources</button>
        <button class="category-badge government" data-category="government">Government</button>
        <button class="category-badge financial" data-category="financial">Financial</button>
        <button class="category-badge education" data-category="education">Education</button>
        <button class="category-badge consumer" data-category="consumer">Consumer Protection</button>
    </div>

    @php
        $resources = [
            [
                'title' => 'Consumer Financial Protection Bureau',
                'acronym' => 'CFPB',
                'category' => 'government',
                'icon' => 'bi-shield-check',
                'description' => 'Federal agency protecting consumers in the financial sector',
                'useCase' => 'Disputes involving credit bureaus, debt collectors, inaccurate reporting, and FCRA violations',
                'url' => 'https://www.consumerfinance.gov/complaint',
                'featured' => true
            ],
            [
                'title' => 'Federal Trade Commission',
                'acronym' => 'FTC',
                'category' => 'government',
                'icon' => 'bi-flag',
                'description' => 'Protecting consumers and promoting competition',
                'useCase' => 'Identity theft, deceptive collection practices, unauthorized accounts or inquiries, and fraud',
                'url' => 'https://reportfraud.ftc.gov/',
                'featured' => true
            ],
            [
                'title' => 'Better Business Bureau',
                'acronym' => 'BBB',
                'category' => 'consumer',
                'icon' => 'bi-award',
                'description' => 'Advancing marketplace trust and ethical business practices',
                'useCase' => 'Complaints against credit bureaus, collectors, or lenders for unfair or unethical business practices',
                'url' => 'https://www.bbb.org/',
                'featured' => true
            ],
            [
                'title' => 'State Attorney General\'s Office',
                'acronym' => 'AG',
                'category' => 'government',
                'icon' => 'bi-bank',
                'description' => 'State-level consumer protection and legal enforcement',
                'useCase' => 'Reporting violations of consumer protection laws within your state',
                'url' => '#',
                'featured' => false
            ],
            [
                'title' => 'Office of the Comptroller of the Currency',
                'acronym' => 'OCC',
                'category' => 'financial',
                'icon' => 'bi-building',
                'description' => 'Regulating and supervising national banks',
                'useCase' => 'Complaints against national banks and federally chartered institutions',
                'url' => 'https://www.helpwithmybank.gov/',
                'featured' => false
            ],
            [
                'title' => 'National Credit Union Administration',
                'acronym' => 'NCUA',
                'category' => 'financial',
                'icon' => 'bi-piggy-bank',
                'description' => 'Insuring and regulating credit unions',
                'useCase' => 'Issues related to credit unions, including credit reporting and loan servicing errors',
                'url' => 'https://www.ncua.gov/',
                'featured' => false
            ],
            [
                'title' => 'Federal Communications Commission',
                'acronym' => 'FCC',
                'category' => 'government',
                'icon' => 'bi-telephone',
                'description' => 'Regulating communications by radio, television, wire, and cable',
                'useCase' => 'Disputes involving phone or utility-based accounts reporting inaccurate or unauthorized inquiries',
                'url' => 'https://consumercomplaints.fcc.gov/',
                'featured' => false
            ],
            [
                'title' => 'U.S. Department of Education – FSA Ombudsman',
                'acronym' => 'FSA',
                'category' => 'education',
                'icon' => 'bi-mortarboard',
                'description' => 'Federal student aid dispute resolution',
                'useCase' => 'Disputes related to federal student loan servicing, collections, and incorrect credit reporting',
                'url' => 'https://studentaid.gov/feedback-ombudsman/disputes',
                'featured' => false
            ],
            [
                'title' => 'Student Privacy Policy Office',
                'acronym' => 'SPPO',
                'category' => 'education',
                'icon' => 'bi-file-lock',
                'description' => 'Protecting student education records',
                'useCase' => 'Violations of FERPA involving misuse or unauthorized access of educational or student loan records. Complaints must be filed within 180 days',
                'url' => 'https://studentprivacy.ed.gov/file-a-complaint',
                'featured' => false
            ],
        ];

        $featuredResources = array_filter($resources, fn($r) => $r['featured']);
        $regularResources = array_filter($resources, fn($r) => !$r['featured']);
    @endphp

    <!-- Featured Resources -->
    @if(count($featuredResources) > 0)
    <div class="featured-section">
        <h2 class="section-title">
            <i class="bi bi-star-fill"></i>
            Featured Resources
        </h2>
        <div class="resources-grid" id="featuredGrid">
            @foreach($featuredResources as $resource)
            <div class="resource-card {{ $resource['category'] }}" data-category="{{ $resource['category'] }}" data-title="{{ strtolower($resource['title']) }}" data-description="{{ strtolower($resource['description']) }}">
                <div class="card-icon">
                    <i class="{{ $resource['icon'] }}"></i>
                </div>
                <div class="card-category">{{ $resource['acronym'] }}</div>
                <h3 class="card-title">{{ $resource['title'] }}</h3>
                <p class="card-description">{{ $resource['description'] }}</p>
                
                <div class="card-hover-info">
                    <div class="card-use-case">
                        <div class="use-case-title">Best Used For:</div>
                        <div class="use-case-text">{{ $resource['useCase'] }}</div>
                    </div>
                </div>

                @if($resource['url'] !== '#')
                <a href="{{ $resource['url'] }}" target="_blank" class="card-link">
                    Visit Website <i class="bi bi-arrow-right"></i>
                </a>
                @else
                <span class="card-link" style="cursor: default;">
                    Search for your state <i class="bi bi-search"></i>
                </span>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- All Resources -->
    <div class="resources-section">
        <h2 class="section-title">
            <i class="bi bi-grid-3x3-gap-fill"></i>
            All Resources
        </h2>
        <div class="resources-grid" id="resourcesGrid">
            @foreach($regularResources as $resource)
            <div class="resource-card {{ $resource['category'] }}" data-category="{{ $resource['category'] }}" data-title="{{ strtolower($resource['title']) }}" data-description="{{ strtolower($resource['description']) }}">
                <div class="card-icon">
                    <i class="{{ $resource['icon'] }}"></i>
                </div>
                <div class="card-category">{{ $resource['acronym'] }}</div>
                <h3 class="card-title">{{ $resource['title'] }}</h3>
                <p class="card-description">{{ $resource['description'] }}</p>
                
                <div class="card-hover-info">
                    <div class="card-use-case">
                        <div class="use-case-title">Best Used For:</div>
                        <div class="use-case-text">{{ $resource['useCase'] }}</div>
                    </div>
                </div>

                @if($resource['url'] !== '#')
                <a href="{{ $resource['url'] }}" target="_blank" class="card-link">
                    Visit Website <i class="bi bi-arrow-right"></i>
                </a>
                @else
                <span class="card-link" style="cursor: default;">
                    Search for your state <i class="bi bi-search"></i>
                </span>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    <!-- Empty State -->
    <div class="empty-state" id="emptyState" style="display: none;">
        <i class="bi bi-inbox"></i>
        <h3>No resources found</h3>
        <p>Try adjusting your search or filter</p>
    </div>
</div>

<script>
    // Category Filtering
    const allCards = document.querySelectorAll('.resource-card');
    const featuredGrid = document.getElementById('featuredGrid');
    const resourcesGrid = document.getElementById('resourcesGrid');
    const emptyState = document.getElementById('emptyState');
    const categoryBadges = document.querySelectorAll('.category-badge');
    
    categoryBadges.forEach(badge => {
        badge.addEventListener('click', () => {
            // Update active state
            categoryBadges.forEach(b => b.classList.remove('active'));
            badge.classList.add('active');
            
            filterResources();
        });
    });

    function filterResources() {
        const activeCategory = document.querySelector('.category-badge.active').dataset.category;
        let visibleCount = 0;

        allCards.forEach(card => {
            const category = card.dataset.category;
            
            const matchesCategory = activeCategory === 'all' || category === activeCategory;
            
            if (matchesCategory) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        // Show/hide empty state
        const featuredVisible = featuredGrid && Array.from(featuredGrid.querySelectorAll('.resource-card')).some(card => card.style.display !== 'none');
        const resourcesVisible = Array.from(resourcesGrid.querySelectorAll('.resource-card')).some(card => card.style.display !== 'none');

        if (visibleCount === 0) {
            emptyState.style.display = 'block';
            if (featuredGrid) featuredGrid.parentElement.style.display = 'none';
            resourcesGrid.parentElement.style.display = 'none';
        } else {
            emptyState.style.display = 'none';
            if (featuredGrid) {
                featuredGrid.parentElement.style.display = featuredVisible ? 'block' : 'none';
            }
            resourcesGrid.parentElement.style.display = resourcesVisible ? 'block' : 'none';
        }
    }
</script>
@endsection
