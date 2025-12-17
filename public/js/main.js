document.addEventListener('DOMContentLoaded', function() {
    // Initialize all carousels
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
                // Use requestAnimationFrame to ensure layout is calculated
                requestAnimationFrame(() => {
                    const scrollWidth = carousel.scrollWidth;
                    const clientWidth = carousel.clientWidth;
                    const scrollLeft = carousel.scrollLeft;
                    
                    // Add small threshold (5px) to account for rounding errors
                    const threshold = 5;
                    const isAtStart = scrollLeft <= threshold;
                    const isAtEnd = scrollLeft >= scrollWidth - clientWidth - threshold;
                    const canScroll = scrollWidth > clientWidth + threshold;
                    
                    // Disable buttons based on scroll position
                    prevBtn.disabled = isAtStart;
                    nextBtn.disabled = isAtEnd;
                    
                    // Hide entire controls block if nothing to scroll
                    if (!canScroll) {
                        controls.classList.add('hidden');
                    } else {
                        controls.classList.remove('hidden');
                    }
                });
            }
            
            carousel.addEventListener('scroll', updateButtons);
            window.addEventListener('resize', updateButtons);
            
            // Initial check with small delay to ensure layout is ready
            setTimeout(updateButtons, 100);
            updateButtons();
        }
    });

    // Sort select handler
    const sortSelect = document.getElementById('sort-select');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const sortValue = this.value;
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('sort', sortValue);
            // Reset to page 1 when sorting changes
            currentUrl.searchParams.set('page', '1');
            window.location.href = currentUrl.toString();
        });
    }
    
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    const menuIcon = document.getElementById('menu-icon');
    
    function updateMenuIcon() {
        if (!menuIcon) return;
        const isActive = menuToggle.classList.contains('active');
        const currentSrc = menuIcon.src;
        if (isActive) {
            menuIcon.src = currentSrc.replace('menu.svg', 'close.svg');
            menuIcon.alt = 'Close';
        } else {
            menuIcon.src = currentSrc.replace('close.svg', 'menu.svg');
            menuIcon.alt = 'Menu';
        }
    }
    
    if (menuToggle && navMenu) {
        menuToggle.addEventListener('click', () => {
            menuToggle.classList.toggle('active');
            navMenu.classList.toggle('active');
            updateMenuIcon();
        });
        
        document.addEventListener('click', (e) => {
            if (!menuToggle.contains(e.target) && !navMenu.contains(e.target)) {
                menuToggle.classList.remove('active');
                navMenu.classList.remove('active');
                updateMenuIcon();
            }
        });
        
        navMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                menuToggle.classList.remove('active');
                navMenu.classList.remove('active');
                updateMenuIcon();
            });
        });
    }
    
    // Review reactions (like/dislike)
    document.querySelectorAll('.reaction-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (this.disabled) return;
            
            const reviewId = this.dataset.reviewId;
            const reaction = this.dataset.reaction;
            const btn = this;
            
            const formData = new FormData();
            formData.append('review_id', reviewId);
            formData.append('reaction', reaction);
            
            fetch('api/review/reaction', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update counts
                    const likeBtn = document.querySelector(`.like-btn[data-review-id="${reviewId}"]`);
                    const dislikeBtn = document.querySelector(`.dislike-btn[data-review-id="${reviewId}"]`);
                    
                    if (likeBtn) {
                        likeBtn.querySelector('.reaction-count').textContent = data.counts.likes;
                        likeBtn.classList.toggle('active', data.reaction === 'like');
                    }
                    
                    if (dislikeBtn) {
                        dislikeBtn.querySelector('.reaction-count').textContent = data.counts.dislikes;
                        dislikeBtn.classList.toggle('active', data.reaction === 'dislike');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
    
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
    
    // Delete review via AJAX
    document.querySelectorAll('.delete-review-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!confirm('Opravdu chcete smazat tuto recenzi?')) {
                return;
            }
            
            const reviewId = this.dataset.reviewId;
            const gameId = this.dataset.gameId;
            const reviewBlock = this.closest('.review-block');
            const isUserReview = reviewBlock && reviewBlock.classList.contains('user-review');
            
            // Get CSRF token from any form on the page
            const csrfInput = document.querySelector('input[name="csrf_token"]');
            if (!csrfInput) {
                alert('Chyba: CSRF token nenalezen.');
                return;
            }
            
            const formData = new FormData();
            formData.append('review_id', reviewId);
            formData.append('game_id', gameId);
            formData.append('csrf_token', csrfInput.value);
            
            // Disable button during request
            this.disabled = true;
            
            fetch('api/review/delete', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove review block from DOM
                    if (reviewBlock) {
                        reviewBlock.style.transition = 'opacity 0.3s';
                        reviewBlock.style.opacity = '0';
                        setTimeout(() => {
                            reviewBlock.remove();
                            
                            // If it was user's review, reload to show create form
                            if (isUserReview) {
                                location.reload();
                            }
                        }, 300);
                    } else {
                        location.reload();
                    }
                } else {
                    alert(data.message || 'Nepodařilo se smazat recenzi.');
                    this.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Došlo k chybě při mazání recenze.');
                this.disabled = false;
            });
        });
    });
    
    // Approve/Reject game via AJAX
    const approveBtn = document.querySelector('.approve-game-btn');
    const rejectBtn = document.querySelector('.reject-game-btn');
    
    if (approveBtn) {
        approveBtn.addEventListener('click', function() {
            const gameId = this.dataset.gameId;
            const btn = this;
            
            // Get CSRF token
            const csrfInput = document.querySelector('input[name="csrf_token"]');
            if (!csrfInput) {
                alert('Chyba: CSRF token nenalezen.');
                return;
            }
            
            const formData = new FormData();
            formData.append('game_id', gameId);
            formData.append('csrf_token', csrfInput.value);
            
            btn.disabled = true;
            btn.textContent = 'Schvalování...';
            
            fetch('api/admin/game/approve', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message || 'Nepodařilo se schválit hru.');
                    btn.disabled = false;
                    btn.textContent = 'Schválit';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Došlo k chybě při schvalování hry.');
                btn.disabled = false;
                btn.textContent = 'Schválit';
            });
        });
    }
    
    if (rejectBtn) {
        const rejectModal = document.getElementById('reject-modal');
        const rejectReasonTextarea = document.getElementById('reject-reason');
        const modalClose = rejectModal?.querySelector('.modal-close');
        const modalCancel = rejectModal?.querySelector('.modal-cancel');
        const modalConfirm = rejectModal?.querySelector('.modal-confirm-reject');
        
        let currentGameId = null;
        
        rejectBtn.addEventListener('click', function() {
            const gameId = this.dataset.gameId;
            if (!gameId) {
                console.error('Game ID not found in button dataset');
                alert('Chyba: Nelze najít ID hry.');
                return;
            }
            currentGameId = gameId;
            if (rejectModal) {
                rejectReasonTextarea.value = '';
                rejectModal.classList.remove('hidden');
            } else {
                // Fallback to prompt if modal not found
                const reason = prompt('Důvod zamítnutí (volitelné):');
                if (reason !== null) {
                    submitRejection(gameId, reason);
                }
            }
        });
        
        function closeModal() {
            if (rejectModal) {
                rejectModal.classList.add('hidden');
                rejectReasonTextarea.value = '';
                currentGameId = null;
            }
        }
        
        if (modalClose) {
            modalClose.addEventListener('click', closeModal);
        }
        
        if (modalCancel) {
            modalCancel.addEventListener('click', closeModal);
        }
        
        if (modalConfirm) {
            modalConfirm.addEventListener('click', function() {
                if (currentGameId) {
                    const reason = rejectReasonTextarea.value.trim();
                    const gameId = currentGameId; // Save before closing modal
                    closeModal();
                    submitRejection(gameId, reason);
                }
            });
        }
        
        // Close modal on outside click
        if (rejectModal) {
            rejectModal.addEventListener('click', function(e) {
                if (e.target === rejectModal) {
                    closeModal();
                }
            });
        }
        
        function submitRejection(gameId, reason) {
            if (!gameId) {
                console.error('Game ID is required for rejection');
                alert('Chyba: Chybí ID hry.');
                return;
            }
            
            // Get CSRF token
            const csrfInput = document.querySelector('input[name="csrf_token"]');
            if (!csrfInput) {
                alert('Chyba: CSRF token nenalezen.');
                return;
            }
            
            const formData = new FormData();
            formData.append('game_id', gameId);
            formData.append('reason', reason || '');
            formData.append('csrf_token', csrfInput.value);
            
            const btn = rejectBtn;
            btn.disabled = true;
            btn.textContent = 'Zamítání...';
            
            fetch('api/admin/game/reject', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message || 'Nepodařilo se zamítnout hru.');
                    btn.disabled = false;
                    btn.textContent = 'Zamítnout';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Došlo k chybě při zamítání hry.');
                btn.disabled = false;
                btn.textContent = 'Zamítnout';
            });
        }
    }
});

