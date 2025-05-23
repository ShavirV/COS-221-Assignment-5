/**
 * Footer functionality for Compare IT
 * Handles scroll-to-top and newsletter form
 */

document.addEventListener('DOMContentLoaded', function() {
    // Scroll to top button
    const scrollToTopBtn = document.getElementById('scrollToTop');
    
    if (scrollToTopBtn) {
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                scrollToTopBtn.classList.add('visible');
            } else {
                scrollToTopBtn.classList.remove('visible');
            }
        });

        scrollToTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    // Newsletter form submission
    const newsletterForm = document.querySelector('.newsletter-form');
    
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const emailInput = this.querySelector('input');
            const email = emailInput.value.trim();
            
            if (email) {
                // Here you would typically send to your backend
                console.log('Subscribed email:', email);
                
                // Show feedback
                const originalText = this.querySelector('button').textContent;
                this.querySelector('button').textContent = 'Subscribed!';
                
                // Reset form
                emailInput.value = '';
                
                // Reset button text after delay
                setTimeout(() => {
                    this.querySelector('button').textContent = originalText;
                }, 2000);
            }
        });
    }
});