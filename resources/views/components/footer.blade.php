<footer style="
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    padding: 2rem 1rem;
    color: white;
    text-align: center;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
    margin-top: 3rem;
    position: relative;
    z-index: 10;
">
    <div style="max-width: 1200px; margin: 0 auto;">
        <div style="margin-bottom: 1.5rem;">
            <img src="{{ asset('4-removebg-preview.png') }}" alt="Credit Remedi" style="height: 40px; margin-bottom: 1rem;">
            <p style="opacity: 0.8; font-size: 0.9rem; max-width: 600px; margin: 0 auto;">
                Credit Remedi provides AI-powered tools and educational resources to assist consumers in managing their credit. We are not a law firm and do not provide legal advice.
            </p>
        </div>
        
        <div style="
            display: flex;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        ">
            <a href="{{ route('terms') }}" style="color: white; text-decoration: none; font-size: 0.9rem; opacity: 0.9; transition: opacity 0.3s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.9'">Terms of Service</a>
            <a href="{{ route('privacy') }}" style="color: white; text-decoration: none; font-size: 0.9rem; opacity: 0.9; transition: opacity 0.3s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.9'">Privacy Policy</a>
            <a href="{{ route('refund') }}" style="color: white; text-decoration: none; font-size: 0.9rem; opacity: 0.9; transition: opacity 0.3s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.9'">Refund Policy</a>
            <a href="mailto:info@remedicredit.com" style="color: white; text-decoration: none; font-size: 0.9rem; opacity: 0.9; transition: opacity 0.3s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.9'">Contact Us</a>
        </div>
        
        <p style="opacity: 0.6; font-size: 0.8rem; margin-top: 1rem;">
            &copy; {{ date('Y') }} Credit Remedi. All rights reserved.
        </p>
    </div>
</footer>
