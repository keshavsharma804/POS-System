<footer class="footer mt-auto py-4 bg-dark-blue text-white">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-4 mb-4 mb-lg-0">
                <h5 class="fw-bold mb-3 d-flex align-items-center">
                    <i class="fas fa-cash-register me-2"></i>
                    POS System
                </h5>
                <p class="small text-light-blue">Premium point-of-sale solution for modern retail businesses. Streamline your operations with our powerful tools.</p>
                <div class="social-icons mt-3">
                    <a href="#" class="text-light-blue me-3"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-light-blue me-3"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-light-blue me-3"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" class="text-light-blue"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            
            <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                <h6 class="text-uppercase fw-bold mb-3">Navigation</h6>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="#" class="text-light-blue footer-link d-flex align-items-center" data-section="dashboard" onclick="loadSection('dashboard')">
                            <i class="fas fa-tachometer-alt me-2"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                                        <li class="mb-2">
                        <a href="#" class="text-light-blue footer-link d-flex align-items-center" data-section="product-catalog" onclick="loadSection('product-catalog')">
                            <i class="fas fa-box-open me-2"></i>
                            <span>Products</span>
                        </a>
                    </li>

                    <li class="mb-2">
                        <a href="#" class="text-light-blue footer-link d-flex align-items-center" data-section="sales-forecasting">
                            <i class="fas fa-chart-line me-2"></i>
                            <span>Forecasting</span>
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#" class="text-light-blue footer-link d-flex align-items-center" data-section="sales-report" onclick="loadSection('sales-report')">
                            <i class="fas fa-file-invoice-dollar me-2"></i>
                            <span>Reports</span>
                        </a>
                    </li>
                </ul>
            </div>
            
            <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                <h6 class="text-uppercase fw-bold mb-3">Management</h6>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="#" class="text-light-blue footer-link d-flex align-items-center" data-section="most-sold-products">
                            <i class="fas fa-star me-2"></i>
                            <span>Top Products</span>
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#" class="text-light-blue footer-link d-flex align-items-center" data-section="inventory" onclick="loadSection('inventory')">
                            <i class="fas fa-warehouse me-2"></i>
                            <span>Inventory</span>
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#" class="text-light-blue footer-link d-flex align-items-center" data-section="stock_adjustments">
                            <i class="fas fa-sliders-h me-2"></i>
                            <span>Stock Adjust</span>
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#" class="text-light-blue footer-link d-flex align-items-center" data-section="discounts">
                            <i class="fas fa-tag me-2"></i>
                            <span>Discounts</span>
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="#" class="text-light-blue footer-link d-flex align-items-center" data-section="customers">
                            <i class="fas fa-users me-2"></i>
                            <span>Customers</span>
                        </a>
                    </li>
                </ul>
            </div>
            
            <div class="col-lg-4 col-md-4">
                <h6 class="text-uppercase fw-bold mb-3">Contact Us</h6>
                <ul class="list-unstyled text-light-blue">
                    <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i> 123 Business Ave, Suite 456</li>
                    <li class="mb-2"><i class="fas fa-phone me-2"></i> +1 (555) 123-4567</li>
                    <li class="mb-2"><i class="fas fa-envelope me-2"></i> support@possystem.com</li>
                    <li><i class="fas fa-clock me-2"></i> Mon-Fri: 9AM - 6PM</li>
                </ul>
                <div class="mt-3">
                    <a href="#" class="btn btn-sm btn-outline-light d-flex align-items-center justify-content-center" data-section="cart-section">
                        <i class="fas fa-shopping-cart me-2"></i>
                        <span>View Cart</span>
                        <span id="footerCartItemCountBadge" class="badge bg-danger ms-2">0</span>
                    </a>
                </div>
            </div>
        </div>
        
        <hr class="my-4 bg-light-blue opacity-25">
        
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start">
                <p class="small mb-0">© 2025 POS System. All rights reserved.</p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <ul class="list-inline small mb-0">
                    <li class="list-inline-item"><a href="#" class="text-light-blue footer-link">Privacy Policy</a></li>
                    <li class="list-inline-item mx-2">·</li>
                    <li class="list-inline-item"><a href="#" class="text-light-blue footer-link">Terms of Service</a></li>
                    <li class="list-inline-item mx-2">·</li>
                    <li class="list-inline-item"><a href="#" class="text-light-blue footer-link">Sitemap</a></li>
                </ul>
            </div>
        </div>
    </div>
</footer>

<style>
    :root {
        --dark-blue: #0a4a7a;
        --light-blue: #90caf9;
        --text-light-blue: rgba(200, 230, 255, 0.8);
    }
    
  footer {
    width: 100%;
    background: linear-gradient(135deg, #0a4a7a, #0d6efd);
    color: white;
    padding: 1rem 0;
    margin-top: auto; /* Pushes footer to bottom */
}

/* Ensure footer content uses full width */
footer .container-fluid {
    padding-left: 0;
    padding-right: 15px;
    width: 100%;
    max-width: 100%;
    margin: 0;
}
footer .row {
    margin: 0 -5px; 
}

footer .col {
    padding: 0 5px; 
}
    
    .bg-dark-blue {
        background-color: var(--dark-blue);
    }
    
    .text-light-blue {
        color: var(--text-light-blue);
    }
    
    .footer-link {
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-block;
    }
    
    .footer-link:hover {
        color: white !important;
        transform: translateX(3px);
    }
    
    .social-icons a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background-color: rgba(255, 255, 255, 0.1);
        transition: all 0.3s ease;
    }
    
    .social-icons a:hover {
        background-color: rgba(255, 255, 255, 0.2);
        transform: translateY(-2px);
    }
    
    hr {
        height: 1px;
    }
</style>