// modules/products/functions.js

// جعل البحث يعمل
function setupSearch() {
    const searchInput = document.querySelector('input[placeholder*="بحث"]');
    const productRows = document.querySelectorAll('tbody tr');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchText = this.value.toLowerCase();
            
            productRows.forEach(row => {
                const productName = row.cells[2].textContent.toLowerCase();
                if (productName.includes(searchText)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
}

// جعل التصفية تعمل
function setupFilters() {
    const categorySelect = document.querySelector('select');
    const productRows = document.querySelectorAll('tbody tr');
    
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            const selectedCategory = this.value;
            
            productRows.forEach(row => {
                if (selectedCategory === '' || row.cells[3].textContent === selectedCategory) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
}

// جعل الأزرار تعمل
function setupButtons() {
    // زر التعديل
    document.querySelectorAll('.btn-outline-primary').forEach(btn => {
        btn.onclick = function() {
            alert('سيتم فتح نافذة تعديل المنتج');
        };
    });
    
    // زر الحذف
    document.querySelectorAll('.btn-outline-danger').forEach(btn => {
        btn.onclick = function() {
            if (confirm('هل تريد حذف هذا المنتج؟')) {
                alert('تم حذف المنتج');
            }
        };
    });
}

// تشغيل كل شيء عندما يتم تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    setupSearch();
    setupFilters();
    setupButtons();
});