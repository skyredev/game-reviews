document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.querySelector('.games-carousel');
    const prevBtn = document.querySelector('.carousel-prev');
    const nextBtn = document.querySelector('.carousel-next');
    const controls = document.querySelector('.carousel-controls');
    
    if (carousel && prevBtn && nextBtn && controls) {
        const cardWidth = 306 + 16;
        
        prevBtn.addEventListener('click', () => {
            carousel.scrollBy({ left: -cardWidth * 2, behavior: 'smooth' });
        });
        
        nextBtn.addEventListener('click', () => {
            carousel.scrollBy({ left: cardWidth * 2, behavior: 'smooth' });
        });
        
        function updateButtons() {
            const isAtStart = carousel.scrollLeft <= 0;
            const isAtEnd = carousel.scrollLeft >= carousel.scrollWidth - carousel.clientWidth - 10;
            const canScroll = carousel.scrollWidth > carousel.clientWidth;
            
            prevBtn.disabled = isAtStart;
            nextBtn.disabled = isAtEnd;
            
            if (!canScroll || (isAtStart && isAtEnd)) {
                controls.style.display = 'none';
            } else {
                controls.style.display = 'flex';
            }
        }
        
        carousel.addEventListener('scroll', updateButtons);
        window.addEventListener('resize', updateButtons);
        updateButtons();
    }

    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (menuToggle && navMenu) {
        menuToggle.addEventListener('click', () => {
            menuToggle.classList.toggle('active');
            navMenu.classList.toggle('active');
        });
        
        document.addEventListener('click', (e) => {
            if (!menuToggle.contains(e.target) && !navMenu.contains(e.target)) {
                menuToggle.classList.remove('active');
                navMenu.classList.remove('active');
            }
        });
        
        navMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                menuToggle.classList.remove('active');
                navMenu.classList.remove('active');
            });
        });
    }
});

