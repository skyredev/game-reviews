/**
 * AJAX requests for reviews, admin actions, etc.
 * 
 * @file ajax.js
 */
document.addEventListener('DOMContentLoaded', function() {
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
            
            const csrfInput = document.querySelector('input[name="csrf_token"]');
            if (!csrfInput) {
                alert('Chyba: CSRF token nenalezen.');
                return;
            }
            
            const formData = new FormData();
            formData.append('review_id', reviewId);
            formData.append('game_id', gameId);
            formData.append('csrf_token', csrfInput.value);
            
            this.disabled = true;
            
            fetch('api/review/delete', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (reviewBlock) {
                        reviewBlock.classList.add('fade-out');
                        setTimeout(() => {
                            reviewBlock.remove();
                            location.reload();
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
    
    // Approve game via AJAX
    const approveBtn = document.querySelector('.approve-game-btn');
    if (approveBtn) {
        approveBtn.addEventListener('click', function() {
            const gameId = this.dataset.gameId;
            const btn = this;
            
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
    
    // Reject game via AJAX (including modal handling)
    const rejectBtn = document.querySelector('.reject-game-btn');
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
                    const gameId = currentGameId;
                    closeModal();
                    submitRejection(gameId, reason);
                }
            });
        }
        
        if (rejectModal) {
            rejectModal.addEventListener('click', function(e) {
                if (e.target === rejectModal) {
                    closeModal();
                }
            });
        }
        
        /**
         * Submit game rejection via AJAX
         * 
         * @param {number} gameId Game ID to reject
         * @param {string} reason Rejection reason
         * @returns {void}
         */
        function submitRejection(gameId, reason) {
            if (!gameId) {
                console.error('Game ID is required for rejection');
                alert('Chyba: Chybí ID hry.');
                return;
            }
            
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
    
    // Admin user management buttons
    document.querySelectorAll('.admin-toggle-btn, .block-toggle-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = this.dataset.userId;
            const action = this.dataset.action;
            const btn = this;
            
            const csrfInput = document.querySelector('input[name="csrf_token"]');
            if (!csrfInput) {
                alert('Chyba: CSRF token nenalezen.');
                return;
            }
            
            const formData = new FormData();
            formData.append('user_id', userId);
            formData.append('csrf_token', csrfInput.value);
            
            btn.disabled = true;
            
            const endpoint = action === 'toggle-admin' ? 'api/admin/user/toggle-admin' : 'api/admin/user/toggle-block';
            
            fetch(endpoint, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message || 'Nepodařilo se provést akci.');
                    btn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Došlo k chybě.');
                btn.disabled = false;
            });
        });
    });
});

