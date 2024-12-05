<?php

use App\Utils\Functions;
use App\Utils\DataHelper;
?>

<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Manage Orders</h1>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Search Order/Customer</label>
                    <input type="text" name="search" class="form-control" placeholder="Search..."
                        value="<?php echo isset($_GET['search']) ? Functions::h($_GET['search']) : ''; ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date Range</label>
                    <select name="date_range" class="form-select">
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sort By</label>
                    <select name="sort" class="form-select">
                        <option value="newest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                        <option value="amount_high">Amount: High to Low</option>
                        <option value="amount_low">Amount: Low to High</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo Functions::h($order['customer_name']); ?></td>
                                <td><?php echo DataHelper::formatDate($order['created_at']); ?></td>
                                <td><?php echo DataHelper::formatCurrency($order['total_amount']); ?></td>
                                <td>
                                    <span class="badge <?php echo DataHelper::getOrderStatusClass($order['status']); ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php echo $order['payment_status'] === 'paid' ? 'bg-success' : 'bg-warning'; ?>">
                                        <?php echo ucfirst($order['payment_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-info" onclick="viewOrder(<?php echo $order['id']; ?>)">
                                            View
                                        </button>
                                        <button class="btn btn-sm btn-primary" onclick="updateStatus(<?php echo $order['id']; ?>)">
                                            Update
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $currentPage === $i ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Order Details Modal -->
<div class="modal fade" id="orderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="orderDetails">
                    <!-- Order details will be loaded here via AJAX -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printOrder()">Print Order</button>
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Order Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="updateStatusForm">
                    <input type="hidden" name="order_id" id="statusOrderId">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="shipped">Shipped</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status Note</label>
                        <textarea name="status_note" class="form-control" rows="3"
                            placeholder="Add a note about this status update..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Send Notification</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="notify_customer" checked>
                            <label class="form-check-label">
                                Notify customer about this update
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="updateStatusForm" class="btn btn-primary">Update Status</button>
            </div>
        </div>
    </div>
</div>

<script>
    // View Order Details
    function viewOrder(orderId) {
        fetch(`/admin/orders/details/${orderId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('orderDetails').innerHTML = generateOrderDetailsHTML(data.order);
                    new bootstrap.Modal(document.getElementById('orderModal')).show();
                } else {
                    alert('Error loading order details');
                }
            })
            .catch(error => console.error('Error:', error));
    }

    // Update Order Status
    function updateStatus(orderId) {
        document.getElementById('statusOrderId').value = orderId;
        new bootstrap.Modal(document.getElementById('statusModal')).show();
    }

    // Handle Status Update Form Submit
    document.getElementById('updateStatusForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('/admin/orders/update-status', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); // Refresh to show updated status
                } else {
                    alert(data.message || 'Error updating order status');
                }
            })
            .catch(error => console.error('Error:', error));
    });

    // Generate Order Details HTML
    function generateOrderDetailsHTML(order) {
        return `
        <div class="order-info mb-4">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="mb-2">Customer Information</h6>
                    <p class="mb-1"><strong>Name:</strong> ${order.customer_name}</p>
                    <p class="mb-1"><strong>Email:</strong> ${order.customer_email}</p>
                    <p class="mb-1"><strong>Phone:</strong> ${order.customer_phone}</p>
                </div>
                <div class="col-md-6">
                    <h6 class="mb-2">Order Information</h6>
                    <p class="mb-1"><strong>Order ID:</strong> #${order.id}</p>
                    <p class="mb-1"><strong>Date:</strong> ${order.created_at}</p>
                    <p class="mb-1"><strong>Status:</strong> ${order.status}</p>
                </div>
            </div>
        </div>
        
        <div class="shipping-info mb-4">
            <h6 class="mb-2">Shipping Address</h6>
            <p class="mb-1">${order.shipping_address}</p>
        </div>
        
        <div class="order-items mb-4">
            <h6 class="mb-2">Order Items</h6>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${order.items.map(item => `
                            <tr>
                                <td>${item.product_name}</td>
                                <td>${formatCurrency(item.price)}</td>
                                <td>${item.quantity}</td>
                                <td>${formatCurrency(item.price * item.quantity)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                            <td>${formatCurrency(order.subtotal)}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Shipping:</strong></td>
                            <td>${formatCurrency(order.shipping_fee)}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                            <td><strong>${formatCurrency(order.total_amount)}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        
        <div class="payment-info mb-4">
            <h6 class="mb-2">Payment Information</h6>
            <p class="mb-1"><strong>Payment Method:</strong> ${order.payment_method}</p>
            <p class="mb-1"><strong>Payment Status:</strong> ${order.payment_status}</p>
            ${order.payment_reference ? `<p class="mb-1"><strong>Reference:</strong> ${order.payment_reference}</p>` : ''}
        </div>
        
        <div class="order-timeline">
            <h6 class="mb-2">Order Timeline</h6>
            <div class="timeline">
                ${order.timeline.map(event => `
                    <div class="timeline-item">
                        <div class="timeline-date">${event.date}</div>
                        <div class="timeline-content">
                            <p class="mb-1"><strong>${event.status}</strong></p>
                            <p class="mb-0 text-muted">${event.note}</p>
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
    }

    // Helper function to format currency
    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    }

    // Print Order
    function printOrder() {
        const printContent = document.getElementById('orderDetails').innerHTML;
        const originalContent = document.body.innerHTML;

        document.body.innerHTML = `
        <div class="print-header">
            <img src="/images/logo.png" alt="AgriKonnect" style="max-width: 200px;">
            <h1>Order Details</h1>
        </div>
        ${printContent}
    `;

        window.print();
        document.body.innerHTML = originalContent;

        // Reattach event listeners after restoring content
        attachEventListeners();
    }

    // Attach all event listeners
    function attachEventListeners() {
        document.getElementById('updateStatusForm').addEventListener('submit', function(e) {
            // Re-attach the submit handler
            // ... (previous submit handler code)
        });
    }
</script>

<style>
    .timeline {
        position: relative;
        padding: 20px 0;
    }

    .timeline-item {
        padding: 20px 0;
        border-left: 2px solid #e9ecef;
        position: relative;
        padding-left: 20px;
        margin-left: 10px;
    }

    .timeline-item:before {
        content: '';
        position: absolute;
        left: -6px;
        top: 24px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #007bff;
    }

    .timeline-date {
        color: #6c757d;
        font-size: 0.875rem;
    }

    .print-header {
        text-align: center;
        margin-bottom: 30px;
        padding: 20px;
    }

    @media print {

        .btn,
        .modal-footer {
            display: none !important;
        }

        .modal {
            position: static;
            display: block;
        }

        .modal-dialog {
            margin: 0;
            width: 100%;
        }

        .modal-content {
            border: none;
            box-shadow: none;
        }
    }
</style>