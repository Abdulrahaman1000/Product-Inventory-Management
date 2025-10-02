<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Product Inventory Management</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px 0;
        }
        .container {
            max-width: 1200px;
        }
        .card {
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border: none;
            border-radius: 10px;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 20px;
        }
        .table {
            margin-bottom: 0;
        }
        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
        }
        .btn-action {
            padding: 5px 10px;
            font-size: 12px;
            margin: 0 2px;
        }
        .total-row {
            background-color: #e9ecef;
            font-weight: bold;
        }
        .alert {
            border-radius: 8px;
        }
        .form-label {
            font-weight: 500;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5568d3 0%, #65408b 100%);
        }
        .edit-mode {
            background-color: #fff3cd !important;
        }
        .table-responsive {
            border-radius: 0 0 10px 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Product Inventory Management</h1>
        
        <!-- Alert Messages -->
        <div id="alertContainer"></div>
        
        <!-- Product Form Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-0">Add New Product</h4>
            </div>
            <div class="card-body">
                <form id="productForm">
                    <input type="hidden" id="productId" name="product_id">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="productName" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="productName" name="product_name" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="quantityInStock" class="form-label">Quantity in Stock</label>
                            <input type="number" class="form-control" id="quantityInStock" name="quantity_in_stock" min="0" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="pricePerItem" class="form-label">Price per Item</label>
                            <input type="number" class="form-control" id="pricePerItem" name="price_per_item" step="0.01" min="0" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <span id="submitBtnText">Add Product</span>
                            <span class="spinner-border spinner-border-sm d-none" id="submitSpinner"></span>
                        </button>
                        <button type="button" class="btn btn-secondary d-none" id="cancelBtn">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Products Table Card -->
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Product Inventory</h4>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Quantity in Stock</th>
                            <th>Price per Item</th>
                            <th>Datetime Submitted</th>
                            <th>Total Value</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="productsTableBody">
                        <tr>
                            <td colspan="6" class="text-center">
                                <div class="spinner-border" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="4" class="text-end">Grand Total:</td>
                            <td id="grandTotal">$0.00</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // CSRF Token setup for Ajax
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        let editMode = false;
        let editingProductId = null;

        // Load products on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadProducts();
        });

        // Form submission
        document.getElementById('productForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = {
                product_name: formData.get('product_name'),
                quantity_in_stock: formData.get('quantity_in_stock'),
                price_per_item: formData.get('price_per_item')
            };

            // Clear previous errors
            clearErrors();
            
            // Show loading state
            toggleSubmitButton(true);

            const url = editMode ? `/products/${editingProductId}` : '/products';
            const method = editMode ? 'PUT' : 'POST';

            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                toggleSubmitButton(false);
                
                if (data.success) {
                    showAlert(data.message, 'success');
                    document.getElementById('productForm').reset();
                    loadProducts();
                    resetFormMode();
                } else {
                    if (data.errors) {
                        displayErrors(data.errors);
                    }
                    showAlert('Please correct the errors in the form.', 'danger');
                }
            })
            .catch(error => {
                toggleSubmitButton(false);
                console.error('Error:', error);
                showAlert('An error occurred. Please try again.', 'danger');
            });
        });

        // Load products function
        function loadProducts() {
            fetch('/products')
                .then(response => response.json())
                .then(data => {
                    renderProducts(data.products, data.grand_total);
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('productsTableBody').innerHTML = 
                        '<tr><td colspan="6" class="text-center text-danger">Error loading products</td></tr>';
                });
        }

        // Render products in table
        function renderProducts(products, grandTotal) {
            const tbody = document.getElementById('productsTableBody');
            
            if (products.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center">No products found. Add your first product above!</td></tr>';
                document.getElementById('grandTotal').textContent = '$0.00';
                return;
            }

            tbody.innerHTML = products.map(product => `
                <tr id="product-${product.id}">
                    <td>${escapeHtml(product.product_name)}</td>
                    <td>${product.quantity_in_stock}</td>
                    <td>$${product.price_per_item}</td>
                    <td>${product.created_at}</td>
                    <td>$${product.total_value}</td>
                    <td>
                        <button class="btn btn-sm btn-warning btn-action" onclick="editProduct(${product.id}, '${escapeHtml(product.product_name)}', ${product.quantity_in_stock}, ${product.price_per_item})">
                            Edit
                        </button>
                        <button class="btn btn-sm btn-danger btn-action" onclick="deleteProduct(${product.id})">
                            Delete
                        </button>
                    </td>
                </tr>
            `).join('');

            document.getElementById('grandTotal').textContent = '$' + grandTotal;
        }

        // Edit product function
        function editProduct(id, name, quantity, price) {
            editMode = true;
            editingProductId = id;
            
            document.getElementById('productId').value = id;
            document.getElementById('productName').value = name;
            document.getElementById('quantityInStock').value = quantity;
            document.getElementById('pricePerItem').value = price;
            
            document.getElementById('submitBtnText').textContent = 'Update Product';
            document.getElementById('cancelBtn').classList.remove('d-none');
            
            // Highlight the row being edited
            document.querySelectorAll('#productsTableBody tr').forEach(row => {
                row.classList.remove('edit-mode');
            });
            document.getElementById(`product-${id}`).classList.add('edit-mode');
            
            // Scroll to form
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Delete product function
        function deleteProduct(id) {
            if (!confirm('Are you sure you want to delete this product?')) {
                return;
            }

            fetch(`/products/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    loadProducts();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Error deleting product.', 'danger');
            });
        }

        // Cancel edit mode
        document.getElementById('cancelBtn').addEventListener('click', function() {
            resetFormMode();
        });

        function resetFormMode() {
            editMode = false;
            editingProductId = null;
            document.getElementById('productForm').reset();
            document.getElementById('productId').value = '';
            document.getElementById('submitBtnText').textContent = 'Add Product';
            document.getElementById('cancelBtn').classList.add('d-none');
            
            // Remove highlight from edited row
            document.querySelectorAll('#productsTableBody tr').forEach(row => {
                row.classList.remove('edit-mode');
            });
            
            clearErrors();
        }

        // Show alert message
        function showAlert(message, type) {
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            document.getElementById('alertContainer').innerHTML = alertHtml;
            
            // Auto dismiss after 5 seconds
            setTimeout(() => {
                const alert = document.querySelector('.alert');
                if (alert) {
                    alert.classList.remove('show');
                    setTimeout(() => alert.remove(), 150);
                }
            }, 5000);
        }

        // Toggle submit button loading state
        function toggleSubmitButton(loading) {
            const btn = document.getElementById('submitBtn');
            const btnText = document.getElementById('submitBtnText');
            const spinner = document.getElementById('submitSpinner');
            
            if (loading) {
                btn.disabled = true;
                btnText.classList.add('d-none');
                spinner.classList.remove('d-none');
            } else {
                btn.disabled = false;
                btnText.classList.remove('d-none');
                spinner.classList.add('d-none');
            }
        }

        // Display validation errors
        function displayErrors(errors) {
            for (const [field, messages] of Object.entries(errors)) {
                const input = document.querySelector(`[name="${field}"]`);
                if (input) {
                    input.classList.add('is-invalid');
                    const feedback = input.nextElementSibling;
                    if (feedback && feedback.classList.contains('invalid-feedback')) {
                        feedback.textContent = messages[0];
                    }
                }
            }
        }

        // Clear validation errors
        function clearErrors() {
            document.querySelectorAll('.is-invalid').forEach(el => {
                el.classList.remove('is-invalid');
            });
            document.querySelectorAll('.invalid-feedback').forEach(el => {
                el.textContent = '';
            });
        }

        // Escape HTML to prevent XSS
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>