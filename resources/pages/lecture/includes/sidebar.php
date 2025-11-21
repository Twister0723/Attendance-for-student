<div class="sidebar">
    <!-- School Logo Section -->
    <div class="sidebar--logo">
        <div class="logo-container">
            <div class="logo-img">
                <img src="resources/images/school-logo.png" alt="School Logo" onerror="this.style.display='none'">
            </div>
            <div class="logo-text">
                <h3>ATTENDANCE SYSTEM</h3>
                <p>School Management</p>
            </div>
        </div>
    </div>

    <!-- Navigation Items -->
    <ul class="sidebar--items">
        <li>
            <a href="home">
                <span class="icon icon-1"><i class="ri-file-text-line"></i></span>
                <span class="sidebar--item">Take Attendance</span>
            </a>
        </li>
        <li>
            <a href="view-attendance">
                <span class="icon icon-1"><i class="ri-map-pin-line"></i></span>
                <span class="sidebar--item" style="white-space: nowrap;">View Attendance</span>
            </a>
        </li>
        <li>
            <a href="view-students">
                <span class="icon icon-1"><i class="ri-user-line"></i></span>
                <span class="sidebar--item">Students</span>
            </a>
        </li>
        <li>
            <a href="download-record">
                <span class="icon icon-1"><i class="ri-download-line"></i></span>
                <span class="sidebar--item">Download Attendance</span>
            </a>
        </li>
    </ul>

    <!-- Bottom Navigation Items -->
    <ul class="sidebar--bottom-items">
        <li>
            <a href="logout">
                <span class="icon icon-2"><i class="ri-logout-box-r-line"></i></span>
                <span class="sidebar--item">Logout</span>
            </a>
        </li>
    </ul>
</div>

<style>
    /* Enhanced Sidebar Styles */
    .sidebar {
        width: 280px;
        background: linear-gradient(180deg, #7c3aed 0%, #5b21b6 100%);
        padding: 0;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: fixed;
        height: 100vh;
        overflow-y: auto;
        border-right: none;
        box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
        z-index: 1000;
    }

    .sidebar--logo {
        padding: 2rem 1.5rem 1.5rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
    }

    .logo-container {
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: all 0.3s ease;
    }

    .logo-container:hover {
        transform: translateX(5px);
    }

    .logo-img {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0.1) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        flex-shrink: 0;
    }

    .logo-img img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 5px;
    }

    /* Fallback if logo doesn't load */
    .logo-img:has(img[style*="display: none"]) {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .logo-img:has(img[style*="display: none"])::after {
        content: "🏫";
        font-size: 1.5rem;
        color: white;
    }

    .logo-text h3 {
        color: white;
        font-size: 1.1rem;
        font-weight: 800;
        margin: 0;
        line-height: 1.2;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .logo-text p {
        color: rgba(255, 255, 255, 0.8);
        font-size: 0.8rem;
        margin: 0;
        font-weight: 500;
    }

    .sidebar--items,
    .sidebar--bottom-items {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .sidebar--items {
        flex: 1;
        padding: 1.5rem 0;
    }

    .sidebar--bottom-items {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        padding: 1rem 0;
        background: rgba(0, 0, 0, 0.1);
    }

    .sidebar--items a,
    .sidebar--bottom-items a {
        display: flex;
        align-items: center;
        padding: 1rem 1.5rem;
        margin: 0.25rem 1rem;
        color: rgba(255, 255, 255, 0.9);
        text-decoration: none;
        border-radius: 12px;
        transition: all 0.3s ease;
        font-weight: 500;
        position: relative;
        overflow: hidden;
    }

    .sidebar--items a::before,
    .sidebar--bottom-items a::before {
        content: '';
        position: absolute;
        left: -100%;
        top: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
        transition: left 0.5s;
    }

    .sidebar--items a:hover::before,
    .sidebar--bottom-items a:hover::before {
        left: 100%;
    }

    .sidebar--items a:hover,
    .sidebar--bottom-items a:hover {
        background: rgba(255, 255, 255, 0.15);
        color: white;
        transform: translateX(8px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    #active--link {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0.1) 100%);
        color: white;
        border-left: 4px solid white;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        backdrop-filter: blur(10px);
    }

    #active--link:hover {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.25) 0%, rgba(255, 255, 255, 0.15) 100%);
        transform: translateX(8px);
    }

    .icon {
        margin-right: 1rem;
        font-size: 1.3rem;
        display: flex;
        align-items: center;
        width: 24px;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .sidebar--items a:hover .icon,
    .sidebar--bottom-items a:hover .icon {
        transform: scale(1.1);
    }

    #active--link .icon {
        color: white;
        transform: scale(1.1);
    }

    .sidebar--item {
        font-size: 0.95rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    /* Scrollbar Styling */
    .sidebar::-webkit-scrollbar {
        width: 4px;
    }

    .sidebar::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
    }

    .sidebar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.3);
        border-radius: 10px;
    }

    .sidebar::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.5);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .sidebar {
            width: 250px;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }

        .sidebar.active {
            transform: translateX(0);
        }

        .sidebar--logo {
            padding: 1.5rem 1rem;
        }

        .logo-container {
            flex-direction: column;
            text-align: center;
            gap: 0.75rem;
        }

        .logo-text h3 {
            font-size: 1rem;
        }

        .logo-text p {
            font-size: 0.75rem;
        }
    }

    @media (max-width: 480px) {
        .sidebar {
            width: 100%;
        }

        .sidebar--items a,
        .sidebar--bottom-items a {
            padding: 1.25rem 1.5rem;
            margin: 0.25rem 0.5rem;
        }
    }

    /* Animation for logo */
    @keyframes logoPulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    .logo-img {
        animation: logoPulse 3s ease-in-out infinite;
    }

    /* Enhanced icon colors */
    .icon-1 {
        color: rgba(255, 255, 255, 0.8);
    }

    .icon-2 {
        color: rgba(255, 255, 255, 0.7);
    }

    #active--link .icon-1,
    #active--link .icon-2 {
        color: white;
    }

    /* Bottom items specific styling */
    .sidebar--bottom-items a {
        padding: 0.875rem 1.5rem;
    }

    /* Improved icon for download */
    .ri-download-line {
        font-size: 1.25rem;
    }

    /* Mobile menu toggle styles */
    .mobile-menu-toggle {
        position: fixed;
        top: 1rem;
        left: 1rem;
        z-index: 1001;
        background: #7c3aed;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 0.5rem;
        font-size: 1.5rem;
        display: none;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transition: all 0.3s ease;
    }

    .mobile-menu-toggle:hover {
        background: #6d28d9;
        transform: scale(1.1);
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var currentUrl = window.location.href;
        var links = document.querySelectorAll('.sidebar a');
        
        links.forEach(function(link) {
            // Remove active class from all links first
            link.id = '';
            
            // Check if current URL matches link href
            if (link.href === currentUrl) {
                link.id = 'active--link';
            }
            
            // Also check for partial matches for better active state detection
            var linkPath = new URL(link.href).pathname;
            var currentPath = window.location.pathname;
            
            // Get the base path without file extension for comparison
            var linkBase = linkPath.split('/').pop().replace('.php', '').replace('.html', '');
            var currentBase = currentPath.split('/').pop().replace('.php', '').replace('.html', '');
            
            if (linkBase && currentBase && linkBase === currentBase) {
                link.id = 'active--link';
            }
        });

        // Mobile sidebar toggle
        const sidebar = document.querySelector('.sidebar');
        let menuToggle = document.querySelector('.mobile-menu-toggle');
        
        // Create mobile menu toggle if it doesn't exist
        if (!menuToggle) {
            menuToggle = document.createElement('button');
            menuToggle.innerHTML = '<i class="ri-menu-line"></i>';
            menuToggle.className = 'mobile-menu-toggle';
            document.body.appendChild(menuToggle);
        }

        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            // Update menu icon
            const icon = menuToggle.querySelector('i');
            if (sidebar.classList.contains('active')) {
                icon.className = 'ri-close-line';
            } else {
                icon.className = 'ri-menu-line';
            }
        });

        // Hide menu toggle on desktop, show on mobile
        function handleResize() {
            if (window.innerWidth <= 768) {
                menuToggle.style.display = 'block';
            } else {
                menuToggle.style.display = 'none';
                sidebar.classList.remove('active');
                // Reset menu icon
                const icon = menuToggle.querySelector('i');
                icon.className = 'ri-menu-line';
            }
        }

        window.addEventListener('resize', handleResize);
        handleResize();

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            if (window.innerWidth <= 768 && 
                !sidebar.contains(event.target) && 
                !menuToggle.contains(event.target)) {
                sidebar.classList.remove('active');
                // Reset menu icon
                const icon = menuToggle.querySelector('i');
                icon.className = 'ri-menu-line';
            }
        });

        // Handle logo loading errors gracefully
        const logoImg = document.querySelector('.logo-img img');
        if (logoImg) {
            logoImg.addEventListener('error', function() {
                this.style.display = 'none';
                // Add fallback content
                const logoContainer = this.closest('.logo-img');
                if (!logoContainer.querySelector('.logo-fallback')) {
                    const fallback = document.createElement('div');
                    fallback.className = 'logo-fallback';
                    fallback.innerHTML = '🏫';
                    fallback.style.cssText = `
                        font-size: 1.5rem;
                        color: white;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        width: 100%;
                        height: 100%;
                    `;
                    logoContainer.appendChild(fallback);
                }
            });
        }
    });
</script>