// ✅ Import all of Bootstrap (includes Modal, Dropdown, Tooltip, etc.)
import * as bootstrap from 'bootstrap';

// ✅ Import DataTables
import 'datatables.net-bs5';
import 'datatables.net-bs5/css/dataTables.bootstrap5.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';
import { marked } from 'marked';
import hljs from 'highlight.js';
import 'highlight.js/styles/github.css';
import $ from 'jquery';

window.$ = window.jQuery = $;

window.marked = marked;
window.hljs = hljs;

// ✅ SweetAlert
import Swal from 'sweetalert2';
window.Swal = Swal;

// ✅ Make Bootstrap globally available (so Blade can use bootstrap.Modal)
window.bootstrap = bootstrap;

// ========================================
// 🌙 DARK MODE FUNCTIONALITY
// ========================================
document.addEventListener('DOMContentLoaded', () => {
    // Initialize theme from localStorage or default to light
    const currentTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', currentTheme);

    // Get the toggle checkbox
    const themeToggleCheckbox = document.getElementById('theme-toggle-checkbox');

    if (themeToggleCheckbox) {
        // Set initial checkbox state based on current theme
        themeToggleCheckbox.checked = currentTheme === 'dark';

        // Toggle theme on checkbox change
        themeToggleCheckbox.addEventListener('change', () => {
            const newTheme = themeToggleCheckbox.checked ? 'dark' : 'light';

            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);

            // Optional: Show a subtle notification
            const toast = document.createElement('div');
            toast.className = 'position-fixed bottom-0 start-50 translate-middle-x mb-3 p-3 bg-dark text-white rounded-3 shadow-lg';
            toast.style.zIndex = '9999';
            toast.textContent = `${newTheme === 'dark' ? '🌙 Dark' : '☀️ Light'} mode activated`;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.3s';
                setTimeout(() => toast.remove(), 300);
            }, 1500);
        });
    }

    // ========================================
    // ✨ SMOOTH SCROLL BEHAVIOR
    // ========================================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href !== '#!') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });

    // ========================================
    // 🎯 ENHANCED ANIMATIONS ON SCROLL
    // ========================================
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-slide-up');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observe all cards for animation
    document.querySelectorAll('.card').forEach(card => {
        observer.observe(card);
    });
});
