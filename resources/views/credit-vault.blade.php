@extends('layouts.app')

@section('title', 'Credit Vault')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

    .credit-vault-container {
        font-family: 'Inter', sans-serif;
        max-width: 1400px;
        margin: 0 auto;
        padding: 2rem 1rem 4rem;
    }

    .vault-header {
        text-align: center;
        margin-bottom: 3rem;
    }

    .vault-title {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .vault-title .gradient-text {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .vault-subtitle {
        color: #6b7280;
        font-size: 1.1rem;
    }

    /* Category Filters */
    .filter-toggle-btn {
        display: none;
        width: 100%;
        padding: 1rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .filter-toggle-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
    }

    .filter-toggle-btn i {
        margin-left: 0.5rem;
        transition: transform 0.3s ease;
    }

    .filter-toggle-btn.active i {
        transform: rotate(180deg);
    }

    .category-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        justify-content: center;
        margin-bottom: 2.5rem;
        padding: 0 1rem;
        transition: max-height 0.3s ease, opacity 0.3s ease;
    }

    .filter-pill {
        padding: 0.6rem 1.5rem;
        border-radius: 50px;
        border: 2px solid #e5e7eb;
        background: white;
        color: #6b7280;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }

    .filter-pill:hover {
        border-color: #667eea;
        color: #667eea;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
    }

    .filter-pill.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-color: transparent;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .filter-pill .count {
        margin-left: 0.5rem;
        padding: 0.15rem 0.5rem;
        background: rgba(255, 255, 255, 0.3);
        border-radius: 12px;
        font-size: 0.85rem;
    }

    /* Video Grid */
    .videos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 2rem;
        margin-bottom: 3rem;
    }

    /* Video Card */
    .video-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
        border: 2px solid transparent;
    }

    .video-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        border-color: #667eea;
    }

    .video-card.watched {
        opacity: 0.85;
    }

    .video-thumbnail-container {
        position: relative;
        width: 100%;
        padding-top: 56.25%; /* 16:9 Aspect Ratio */
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        overflow: hidden;
    }

    .video-thumbnail-container video {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .video-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .video-card:hover .video-overlay {
        opacity: 1;
    }

    .play-button {
        width: 60px;
        height: 60px;
        background: rgba(255, 255, 255, 0.95);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .play-button:hover {
        transform: scale(1.1);
        background: white;
    }

    .play-button i {
        font-size: 1.5rem;
        color: #667eea;
        margin-left: 4px;
    }

    /* Duration Badge */
    .duration-badge {
        position: absolute;
        bottom: 10px;
        right: 10px;
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 0.3rem 0.6rem;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    /* Watched Indicator */
    .watched-indicator {
        position: absolute;
        top: 10px;
        left: 10px;
        background: rgba(34, 197, 94, 0.95);
        color: white;
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.3rem;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .video-card.watched .watched-indicator {
        opacity: 1;
    }

    /* Video Info */
    .video-info {
        padding: 1.25rem;
    }

    .video-title {
        font-size: 1rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.5rem;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .video-category {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        background: #f3f4f6;
        color: #6b7280;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }

    /* Modal Styles */
    .video-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: #000;
        z-index: 9999;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .video-modal.active {
        display: flex;
        opacity: 1;
    }

    .modal-content {
        position: relative;
        width: 100vw;
        height: 100vh;
        background: #000;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-video {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .modal-close {
        position: fixed;
        top: 20px;
        right: 20px;
        background: rgba(255, 255, 255, 0.95);
        border: none;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 1.8rem;
        color: #1f2937;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        z-index: 10000;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }

    .modal-close:hover {
        transform: scale(1.1);
        background: white;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.4);
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

    /* Responsive */
    @media (max-width: 768px) {
        .vault-title {
            font-size: 2rem;
        }

        .videos-grid {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .filter-toggle-btn {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .category-filters {
            gap: 0.5rem;
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            margin-bottom: 1rem;
        }

        .category-filters.show {
            max-height: 1000px;
            opacity: 1;
            margin-bottom: 2rem;
        }

        .filter-pill {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
        }
    }

    /* Dark Mode Support */
    [data-theme="dark"] .credit-vault-container {
        background-color: transparent;
    }

    [data-theme="dark"] .vault-subtitle {
        color: #9ca3af;
    }

    [data-theme="dark"] .filter-pill {
        background: #374151;
        border-color: #4b5563;
        color: #d1d5db;
    }

    [data-theme="dark"] .filter-pill:hover {
        border-color: #667eea;
        color: #667eea;
        background: #1f2937;
    }

    [data-theme="dark"] .filter-pill.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-color: transparent;
    }

    [data-theme="dark"] .video-card {
        background: #1f2937;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        border-color: #374151;
    }

    [data-theme="dark"] .video-card:hover {
        background: #1f2937;
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.5);
        border-color: #667eea;
    }

    [data-theme="dark"] .video-info {
        background: #1f2937;
    }

    [data-theme="dark"] .video-title {
        color: #f3f4f6;
    }

    [data-theme="dark"] .video-category {
        background: #374151;
        color: #d1d5db;
    }

    [data-theme="dark"] .empty-state {
        color: #9ca3af;
    }

    /* Loading Spinner */
    .video-loader {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f3f4f6;
        z-index: 5;
        transition: opacity 0.5s ease;
    }

    [data-theme="dark"] .video-loader {
        background: #1f2937;
    }

    .video-loader .spinner {
        width: 40px;
        height: 40px;
        border: 3px solid rgba(102, 126, 234, 0.3);
        border-top: 3px solid #667eea;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Video Fade In */
    .video-thumbnail-container video {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0;
        transition: opacity 0.5s ease;
    }

    .video-thumbnail-container video.loaded {
        opacity: 1;
    }
</style>

<div class="credit-vault-container">
    <!-- Header -->
    <div class="vault-header">
        <h1 class="vault-title">🔐 <span class="gradient-text">Credit Vault</span></h1>
        <p class="vault-subtitle">Your complete library of credit repair guides and tutorials</p>
    </div>

    @php
        $videos = [
            [
                'file' => 'Step-by-Step Guide to Filing a Complaint Against Credit Bureaus.mp4',
                'title' => 'Filing a Complaint Against Credit Bureaus',
                'category' => 'Credit Bureaus',
                'duration' => '8:45'
            ],
            [
                'file' => 'Step-by-Step Guide to Filing an FTC Report for Fraudulent Accounts.mp4',
                'title' => 'Filing an FTC Report for Fraudulent Accounts',
                'category' => 'Fraud Protection',
                'duration' => '6:30'
            ],
            [
                'file' => 'Step-by-Step Guide to Filing an FTC Identity Theft Report.mp4',
                'title' => 'Filing an FTC Identity Theft Report',
                'category' => 'Identity Theft',
                'duration' => '7:15'
            ],
            [
                'file' => 'Late Payments Challenging the Credit Bureaus.mp4',
                'title' => 'Challenging Late Payments with Credit Bureaus',
                'category' => 'Late Payments',
                'duration' => '5:20'
            ],
            [
                'file' => 'Filing Disputes Against Secondary Bureaus with the CFPB.mp4',
                'title' => 'Disputes Against Secondary Bureaus (CFPB)',
                'category' => 'CFPB',
                'duration' => '9:10'
            ],
            [
                'file' => 'Filing a Dispute with Consumer Finance for Collection Accounts.mp4',
                'title' => 'Disputing Collection Accounts',
                'category' => 'Collections',
                'duration' => '6:55'
            ],
            [
                'file' => 'Filing a Complaint Against ChexSystems_ Step Four Explained.mp4',
                'title' => 'Complaint Against ChexSystems',
                'category' => 'ChexSystems',
                'duration' => '4:40'
            ],
            [
                'file' => 'Disputing Non-Reporting Charge-Off Accounts Through the CFPB.mp4',
                'title' => 'Disputing Non-Reporting Charge-Offs',
                'category' => 'CFPB',
                'duration' => '7:30'
            ],
            [
                'file' => 'Late Payments Challenging Creditor.mp4',
                'title' => 'Challenging Late Payments with Creditors',
                'category' => 'Late Payments',
                'duration' => '5:45'
            ],
        ];

        $categories = array_unique(array_column($videos, 'category'));
        sort($categories);
    @endphp

    <!-- Category Filters -->
    <button class="filter-toggle-btn" id="filterToggle">
        <span>Filter by Category</span>
        <i class="bi bi-chevron-down"></i>
    </button>
    
    <div class="category-filters" id="categoryFilters">
        <button class="filter-pill active" data-category="all">
            All Videos
            <span class="count">{{ count($videos) }}</span>
        </button>
        @foreach ($categories as $category)
            @php
                $count = count(array_filter($videos, fn($v) => $v['category'] === $category));
            @endphp
            <button class="filter-pill" data-category="{{ $category }}">
                {{ $category }}
                <span class="count">{{ $count }}</span>
            </button>
        @endforeach
    </div>

    <!-- Videos Grid -->
    <div class="videos-grid" id="videosGrid">
        @foreach ($videos as $index => $video)
            <div class="video-card" data-category="{{ $video['category'] }}" data-video-id="{{ $index }}">
                <div class="video-thumbnail-container">
                    <div class="video-loader">
                        <div class="spinner"></div>
                    </div>
                    <video preload="none" loading="lazy" id="video-{{ $index }}" class="lazy-video" poster="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='640' height='360'%3E%3Crect fill='%23667eea' width='640' height='360'/%3E%3C/svg%3E">
                        <source data-src="{{ asset('videos/'.$video['file']) }}" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                    <div class="video-overlay">
                        <div class="play-button">
                            <i class="bi bi-play-fill"></i>
                        </div>
                    </div>
                    <div class="duration-badge">{{ $video['duration'] }}</div>
                    <div class="watched-indicator">
                        <i class="bi bi-check-circle-fill"></i>
                        Watched
                    </div>
                </div>
                <div class="video-info">
                    <div class="video-category">{{ $video['category'] }}</div>
                    <h3 class="video-title">{{ $video['title'] }}</h3>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Empty State (hidden by default) -->
    <div class="empty-state" id="emptyState" style="display: none;">
        <i class="bi bi-film"></i>
        <h3>No videos found</h3>
        <p>Try selecting a different category</p>
    </div>
</div>

<!-- Video Modal -->
<div class="video-modal" id="videoModal">
    <div class="modal-content">
        <button class="modal-close" id="modalClose">
            <i class="bi bi-x"></i>
        </button>
        <video class="modal-video" id="modalVideo" controls>
            <source src="" type="video/mp4">
        </video>
    </div>
</div>

<script>
    // Mobile Filter Toggle
    const filterToggle = document.getElementById('filterToggle');
    const categoryFilters = document.getElementById('categoryFilters');

    if (filterToggle) {
        filterToggle.addEventListener('click', () => {
            categoryFilters.classList.toggle('show');
            filterToggle.classList.toggle('active');
        });
    }

    // Lazy Loading & Loader Management - OPTIMIZED FOR PERFORMANCE
    document.addEventListener("DOMContentLoaded", function() {
        const lazyVideos = document.querySelectorAll("video.lazy-video");

        if ("IntersectionObserver" in window) {
            const videoObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        const video = entry.target;
                        const source = video.querySelector("source");
                        
                        // Only load if not already loaded
                        if (source.dataset.src && !source.src) {
                            // Add a small delay to prevent all videos loading at once
                            setTimeout(() => {
                                source.src = source.dataset.src;
                                video.load(); // Fetch metadata/first frame
                            }, 100);
                        }

                        // When frame is ready, hide loader
                        video.onloadeddata = function() {
                            video.classList.add('loaded');
                            const loader = video.parentElement.querySelector('.video-loader');
                            if (loader) {
                                loader.style.opacity = '0';
                                setTimeout(() => loader.style.display = 'none', 500);
                            }
                        };
                        
                        observer.unobserve(video);
                    }
                });
            }, {
                // Load videos only when they're 200px away from viewport
                rootMargin: '200px',
                // Only trigger when at least 10% of video is visible
                threshold: 0.1
            });

            lazyVideos.forEach((video) => {
                videoObserver.observe(video);
            });
        } else {
             // Fallback for older browsers - load only first 3 videos
             lazyVideos.forEach((video, index) => {
                 if (index < 3) {
                     const source = video.querySelector("source");
                     source.src = source.dataset.src;
                     video.load();
                     video.classList.add('loaded');
                 }
             });
        }
    });

    // Category Filtering
    const filterPills = document.querySelectorAll('.filter-pill');
    const videoCards = document.querySelectorAll('.video-card');
    const videosGrid = document.getElementById('videosGrid');
    const emptyState = document.getElementById('emptyState');

    filterPills.forEach(pill => {
        pill.addEventListener('click', () => {
            // Update active state
            filterPills.forEach(p => p.classList.remove('active'));
            pill.classList.add('active');

            const category = pill.dataset.category;
            let visibleCount = 0;

            // Filter videos
            videoCards.forEach(card => {
                if (category === 'all' || card.dataset.category === category) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Show/hide empty state
            if (visibleCount === 0) {
                videosGrid.style.display = 'none';
                emptyState.style.display = 'block';
            } else {
                videosGrid.style.display = 'grid';
                emptyState.style.display = 'none';
            }

            // Auto-close filter on mobile after selection
            if (window.innerWidth <= 768 && categoryFilters.classList.contains('show')) {
                categoryFilters.classList.remove('show');
                filterToggle.classList.remove('active');
            }
        });
    });

    // Video Modal
    const videoModal = document.getElementById('videoModal');
    const modalVideo = document.getElementById('modalVideo');
    const modalClose = document.getElementById('modalClose');

    videoCards.forEach(card => {
        card.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            const videoId = card.dataset.videoId;
            const sourceVideo = document.getElementById(`video-${videoId}`);
            const sourceElement = sourceVideo.querySelector('source');
            
            // Lazy load: Set src from data-src if not already loaded
            if (!sourceElement.src && sourceElement.dataset.src) {
                sourceElement.src = sourceElement.dataset.src;
                sourceVideo.load();
            }
            
            const videoSrc = sourceElement.src;

            // Set modal video source
            const modalSource = modalVideo.querySelector('source');
            modalSource.src = videoSrc;
            modalVideo.load();
            
            // Show modal
            videoModal.classList.add('active');
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
            
            // Play video with error handling
            const playPromise = modalVideo.play();
            if (playPromise !== undefined) {
                playPromise.catch(error => {
                    console.log('Autoplay prevented, user interaction required:', error);
                });
            }

            // Mark as watched
            card.classList.add('watched');
            saveWatchedStatus(videoId);
        });
    });

    // Close modal
    modalClose.addEventListener('click', (e) => {
        e.stopPropagation();
        closeModal();
    });
    videoModal.addEventListener('click', (e) => {
        if (e.target === videoModal) {
            closeModal();
        }
    });

    function closeModal() {
        videoModal.classList.remove('active');
        modalVideo.pause();
        modalVideo.currentTime = 0;
        document.body.style.overflow = ''; // Restore scrolling
    }

    // Keyboard support
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && videoModal.classList.contains('active')) {
            closeModal();
        }
    });

    // Watched Status Management (User-Specific)
    const userId = '{{ auth()->id() }}'; // Get current user ID
    const watchedKey = `watchedVideos_user_${userId}`;

    function saveWatchedStatus(videoId) {
        let watched = JSON.parse(localStorage.getItem(watchedKey) || '[]');
        if (!watched.includes(videoId)) {
            watched.push(videoId);
            localStorage.setItem(watchedKey, JSON.stringify(watched));
            console.log(`Video ${videoId} marked as watched for user ${userId}`);
        }
    }

    function loadWatchedStatus() {
        const watched = JSON.parse(localStorage.getItem(watchedKey) || '[]');
        console.log(`Loading ${watched.length} watched videos for user ${userId}`);
        watched.forEach(videoId => {
            const card = document.querySelector(`[data-video-id="${videoId}"]`);
            if (card) {
                card.classList.add('watched');
            }
        });
    }

    // Load watched status on page load
    loadWatchedStatus();
</script>
@endsection
