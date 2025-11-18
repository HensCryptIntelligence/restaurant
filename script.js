// Transaction Page JavaScript

// DOM Elements
const tabButtons = document.querySelectorAll('.tab-button');
const searchInput = document.querySelector('.search-box input');
const transactionCards = document.querySelectorAll('.transaction-card');
const navItems = document.querySelectorAll('.nav-item');
const viewAllButtons = document.querySelectorAll('.view-all-btn, .view-all-primary-btn');
const editButtons = document.querySelectorAll('.edit-btn');
const deleteButtons = document.querySelectorAll('.delete-btn');
const logoutSection = document.querySelector('.logout-section');
const backButton = document.querySelector('.back-button');

// Tab Filter Functionality
tabButtons.forEach(button => {
    button.addEventListener('click', () => {
        // Remove active class from all tabs
        tabButtons.forEach(btn => btn.classList.remove('active'));
        
        // Add active class to clicked tab
        button.classList.add('active');
        
        const filterType = button.textContent.trim().toLowerCase();
        filterTransactions(filterType);
    });
});

// Filter Transactions
function filterTransactions(filterType) {
    transactionCards.forEach(card => {
        const statusBadge = card.querySelector('.status-badge');
        const status = statusBadge ? statusBadge.textContent.trim().toLowerCase() : '';
        
        if (filterType === 'all') {
            card.style.display = 'block';
            animateCard(card);
        } else if (filterType === status) {
            card.style.display = 'block';
            animateCard(card);
        } else {
            card.style.display = 'none';
        }
    });
}

// Animate Card
function animateCard(card) {
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    
    setTimeout(() => {
        card.style.transition = 'all 0.3s ease';
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';
    }, 100);
}

// Search Functionality
let searchTimeout;
searchInput.addEventListener('input', (e) => {
    clearTimeout(searchTimeout);
    
    searchTimeout = setTimeout(() => {
        const searchTerm = e.target.value.toLowerCase().trim();
        searchTransactions(searchTerm);
    }, 300);
});

// Search Transactions
function searchTransactions(searchTerm) {
    if (searchTerm === '') {
        transactionCards.forEach(card => {
            card.style.display = 'block';
            animateCard(card);
        });
        return;
    }
    
    transactionCards.forEach(card => {
        const customerName = card.querySelector('.customer-name').textContent.toLowerCase();
        const orderNumber = card.querySelector('.order-number').textContent.toLowerCase();
        const items = Array.from(card.querySelectorAll('.item-row span')).map(span => span.textContent.toLowerCase());
        
        const matchesSearch = 
            customerName.includes(searchTerm) ||
            orderNumber.includes(searchTerm) ||
            items.some(item => item.includes(searchTerm));
        
        if (matchesSearch) {
            card.style.display = 'block';
            animateCard(card);
        } else {
            card.style.display = 'none';
        }
    });
}

// Navigation
navItems.forEach(item => {
    item.addEventListener('click', (e) => {
        e.preventDefault();
        
        // Remove active class from all nav items
        navItems.forEach(nav => nav.classList.remove('active'));
        
        // Add active class to clicked nav item
        item.classList.add('active');
        
        // Get navigation text
        const navText = item.querySelector('span').textContent;
        console.log(`Navigating to: ${navText}`);
        
        // Here you can add navigation logic
        // window.location.href = `/${navText.toLowerCase()}.html`;
    });
});

// View All Button
viewAllButtons.forEach(button => {
    button.addEventListener('click', (e) => {
        const card = e.target.closest('.transaction-card');
        const orderNumber = card.querySelector('.order-number').textContent;
        const customerName = card.querySelector('.customer-name').textContent;
        
        console.log(`View all items for ${orderNumber} - ${customerName}`);
        
        // Show modal or navigate to detail page
        showTransactionDetail(card);
    });
});

// Show Transaction Detail
function showTransactionDetail(card) {
    // Clone card data
    const cardData = {
        number: card.querySelector('.card-number').textContent,
        orderNumber: card.querySelector('.order-number').textContent,
        customerName: card.querySelector('.customer-name').textContent,
        date: card.querySelector('.date').textContent,
        time: card.querySelector('.time').textContent,
        status: card.querySelector('.status-badge').textContent,
        items: Array.from(card.querySelectorAll('.item-row')).map(row => {
            const spans = row.querySelectorAll('span');
            return {
                serial: spans[0].textContent,
                name: spans[1].textContent,
                qty: spans[2].textContent
            };
        }),
        totalPrice: card.querySelector('.total-price').textContent
    };
    
    console.log('Transaction Detail:', cardData);
    
    // You can create a modal or navigate to detail page
    alert(`Detail for ${cardData.orderNumber}\nCustomer: ${cardData.customerName}\nTotal: ${cardData.totalPrice}`);
}

// Edit Button
editButtons.forEach(button => {
    button.addEventListener('click', (e) => {
        const card = e.target.closest('.transaction-card');
        const orderNumber = card.querySelector('.order-number').textContent;
        
        console.log(`Edit transaction: ${orderNumber}`);
        
        // Add your edit logic here
        alert(`Edit transaction ${orderNumber}`);
    });
});

// Delete Button
deleteButtons.forEach(button => {
    button.addEventListener('click', (e) => {
        const card = e.target.closest('.transaction-card');
        const orderNumber = card.querySelector('.order-number').textContent;
        
        const confirmDelete = confirm(`Are you sure you want to delete ${orderNumber}?`);
        
        if (confirmDelete) {
            // Add delete animation
            card.style.transition = 'all 0.3s ease';
            card.style.opacity = '0';
            card.style.transform = 'scale(0.9)';
            
            setTimeout(() => {
                card.remove();
                console.log(`Deleted transaction: ${orderNumber}`);
            }, 300);
        }
    });
});

// Logout
if (logoutSection) {
    logoutSection.addEventListener('click', () => {
        const confirmLogout = confirm('Are you sure you want to logout?');
        
        if (confirmLogout) {
            console.log('Logging out...');
            // Add your logout logic here
            // window.location.href = '/login.html';
            alert('Logging out...');
        }
    });
}

// Back Button
if (backButton) {
    backButton.addEventListener('click', () => {
        console.log('Going back...');
        // window.history.back();
        alert('Going back to previous page...');
    });
}

// Card Hover Effect
transactionCards.forEach(card => {
    card.addEventListener('mouseenter', () => {
        card.style.transform = 'translateY(-5px)';
        card.style.boxShadow = '0 8px 20px rgba(23, 195, 178, 0.2)';
        card.style.transition = 'all 0.3s ease';
    });
    
    card.addEventListener('mouseleave', () => {
        card.style.transform = 'translateY(0)';
        card.style.boxShadow = 'none';
    });
});

// Initialize page
document.addEventListener('DOMContentLoaded', () => {
    console.log('Transaction page loaded');
    
    // Add initial animations
    transactionCards.forEach((card, index) => {
        setTimeout(() => {
            animateCard(card);
        }, index * 100);
    });
});

// Utility Functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
}

// Export functions for use in other modules
window.TransactionApp = {
    filterTransactions,
    searchTransactions,
    showTransactionDetail,
    formatCurrency,
    formatDate
};