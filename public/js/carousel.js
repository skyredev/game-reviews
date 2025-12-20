/**
 * Carousel functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    const carousels = document.querySelectorAll('.games-carousel');
    
    carousels.forEach((carousel) => {
        const carouselType = carousel.dataset.carousel;
        const container = carousel.closest('.top-games-container, .recent-games-container');
        
        if (!container) return;
        
        const prevBtn = container.querySelector(`.carousel-prev[data-carousel="${carouselType}"]`);
        const nextBtn = container.querySelector(`.carousel-next[data-carousel="${carouselType}"]`);
        const controls = container.querySelector('.carousel-controls');
        
        if (prevBtn && nextBtn && controls) {
            const cardWidth = 306 + 16;
            
            prevBtn.addEventListener('click', () => {
                carousel.scrollBy({ left: -cardWidth * 2, behavior: 'smooth' });
            });
            
            nextBtn.addEventListener('click', () => {
                carousel.scrollBy({ left: cardWidth * 2, behavior: 'smooth' });
            });
            
            function updateButtons() {
                requestAnimationFrame(() => {
                    const scrollWidth = carousel.scrollWidth;
                    const clientWidth = carousel.clientWidth;
                    const scrollLeft = carousel.scrollLeft;
                    
                    const threshold = 5;
                    const isAtStart = scrollLeft <= threshold;
                    const isAtEnd = scrollLeft >= scrollWidth - clientWidth - threshold;
                    const canScroll = scrollWidth > clientWidth + threshold;
                    
                    prevBtn.disabled = isAtStart;
                    nextBtn.disabled = isAtEnd;
                    
                    if (!canScroll) {
                        controls.classList.add('hidden');
                    } else {
                        controls.classList.remove('hidden');
                    }
                });
            }
            
            carousel.addEventListener('scroll', updateButtons);
            window.addEventListener('resize', updateButtons);
            
            setTimeout(updateButtons, 100);
            updateButtons();
        }
    });
});

