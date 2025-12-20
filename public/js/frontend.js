/**
 * Frontend UI interactions (menus, forms, modals)
 */
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    const menuIcon = document.getElementById('menu-icon');
    
    if (menuToggle && navMenu && menuIcon) {
        // Store original paths
        const menuPath = menuIcon.src;
        const basePath = menuPath.substring(0, menuPath.lastIndexOf('/'));
        const menuSvg = basePath + '/menu.svg';
        const closeSvg = basePath + '/close.svg';
        
        function updateMenuIcon() {
            if (!menuIcon) return;
            const isActive = menuToggle.classList.contains('active');
            const targetSrc = isActive ? closeSvg : menuSvg;
            
            // Only update if different to prevent unnecessary reloads
            if (menuIcon.src !== targetSrc) {
                menuIcon.src = targetSrc;
                menuIcon.alt = isActive ? 'Close' : 'Menu';
            }
        }
        
        menuToggle.addEventListener('click', () => {
            menuToggle.classList.toggle('active');
            navMenu.classList.toggle('active');
            updateMenuIcon();
        });
        
        document.addEventListener('click', (e) => {
            if (!menuToggle.contains(e.target) && !navMenu.contains(e.target)) {
                const wasActive = menuToggle.classList.contains('active');
                menuToggle.classList.remove('active');
                navMenu.classList.remove('active');
                if (wasActive) {
                    updateMenuIcon();
                }
            }
        });
        
        navMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                const wasActive = menuToggle.classList.contains('active');
                menuToggle.classList.remove('active');
                navMenu.classList.remove('active');
                if (wasActive) {
                    updateMenuIcon();
                }
            });
        });
    }
    
    // Sort select handler
    const sortSelect = document.getElementById('sort-select');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const sortValue = this.value;
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('sort', sortValue);
            currentUrl.searchParams.set('page', '1');
            window.location.href = currentUrl.toString();
        });
    }
    
    // Edit review toggle
    const editReviewBtn = document.querySelector('.edit-review-btn');
    const cancelEditBtn = document.querySelector('.cancel-edit-btn');
    const reviewDisplay = document.getElementById('review-display');
    const reviewEditForm = document.getElementById('review-edit-form');
    
    if (editReviewBtn && reviewDisplay && reviewEditForm) {
        editReviewBtn.addEventListener('click', function() {
            reviewDisplay.classList.add('hidden');
            reviewEditForm.classList.remove('hidden');
        });
        
        if (cancelEditBtn) {
            cancelEditBtn.addEventListener('click', function() {
                reviewDisplay.classList.remove('hidden');
                reviewEditForm.classList.add('hidden');
            });
        }
    }
});

