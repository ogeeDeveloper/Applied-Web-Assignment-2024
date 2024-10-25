<?php
use App\Utils\Functions;

// Define navigation sections for footer
$footerNavigation = [
    'My Account' => [
        'My Account' => '/account',
        'Order History' => '/orders',
        'Shopping Cart' => '/cart',
        'Wishlist' => '/wishlist'
    ],
    'Help Center' => [
        'Contact Us' => '/contact',
        'FAQs' => '/faq',
        'Terms & Conditions' => '/terms',
        'Privacy Policy' => '/privacy'
    ],
    'Quick Links' => [
        'About Us' => '/about',
        'Shop' => '/shop',
        'Products' => '/products',
        'Track Order' => '/track-order'
    ]
];

// Social media links
$socialLinks = [
    'facebook' => [
        'url' => 'https://facebook.com/agrikonnect',
        'icon' => '<path d="M7.99764 2.98875H9.64089V0.12675C9.35739 0.08775 8.38239 0 7.24689 0C4.87764 0 3.25464 1.49025 3.25464 4.22925V6.75H0.640137V9.9495H3.25464V18H6.46014V9.95025H8.96889L9.36714 6.75075H6.45939V4.5465C6.46014 3.62175 6.70914 2.98875 7.99764 2.98875Z"'
    ],
    'twitter' => [
        'url' => 'https://twitter.com/agrikonnect',
        'icon' => '<path d="M18 2.41888C17.3306 2.7125 16.6174 2.90713 15.8737 3.00163C16.6388 2.54488 17.2226 1.82713 17.4971 0.962C16.7839 1.38725 15.9964 1.68763 15.1571 1.85525C14.4799 1.13413 13.5146 0.6875 12.4616 0.6875C10.4186 0.6875 8.77387 2.34575 8.77387 4.37863C8.77387 4.67113 8.79862 4.95238 8.85938 5.22013C5.7915 5.0705 3.07687 3.60013 1.25325 1.36025C0.934875 1.91263 0.748125 2.54488 0.748125 3.2255C0.748125 4.5035 1.40625 5.63638 2.38725 6.29225C1.79437 6.281 1.21275 6.10888 0.72 5.83775C0.72 5.849 0.72 5.86363 0.72 5.87825C0.72 7.6715 1.99912 9.161 3.6765 9.50413C3.37612 9.58625 3.04875 9.62563 2.709 9.62563C2.47275 9.62563 2.23425 9.61213 2.01038 9.56263C2.4885 11.024 3.84525 12.0984 5.4585 12.1333C4.203 13.1154 2.60888 13.7071 0.883125 13.7071C0.5805 13.7071 0.29025 13.6936 0 13.6565C1.63462 14.7106 3.57188 15.3125 5.661 15.3125C12.4515 15.3125 16.164 9.6875 16.164 4.81175C16.164 4.64863 16.1584 4.49113 16.1505 4.33475C16.8829 3.815 17.4982 3.16588 18 2.41888Z"'
    ],
    'instagram' => [
        'url' => 'https://instagram.com/agrikonnect',
        'icon' => '<path d="M8.24471 0C3.31136 0 0.687744 3.16139 0.687744 6.60855C0.687744 8.20724 1.58103 10.2008 3.01097 10.8331C3.22811 10.931 3.34624 10.8894 3.39462 10.688C3.43737 10.535 3.62525 9.79807 3.71638 9.45042C3.74451 9.33904 3.72988 9.24229 3.63988 9.13766C3.16511 8.58864 2.78821 7.58847 2.78821 6.65017C2.78821 4.24594 4.69967 1.91146 7.9522 1.91146C10.7648 1.91146 12.7325 3.73854 12.7325 6.35204C12.7325 9.30529 11.1698 11.3484 9.13912 11.3484C8.0152 11.3484 7.17816 10.4663 7.44367 9.37505C7.76431 8.07561 8.39321 6.6783 8.39321 5.74113C8.39321 4.90072 7.91844 4.20544 6.94865 4.20544C5.80447 4.20544 4.87631 5.33837 4.87631 6.85943C4.87631 7.82585 5.21832 8.47838 5.21832 8.47838C5.21832 8.47838 4.08652 13.0506 3.87614 13.9045C3.52062 15.3502 3.92451 17.6914 3.95939 17.8928C3.98077 18.0042 4.10565 18.0391 4.1754 17.9479C4.28678 17.8017 5.65484 15.8497 6.03848 14.4389C6.17799 13.9248 6.75064 11.84 6.75064 11.84C7.12753 12.5207 8.21546 13.0911 9.37426 13.0911C12.8214 13.0911 15.3123 10.0613 15.3123 6.30141C15.2999 2.69675 12.215 0 8.24471 0Z"'
    ]
];

// Payment partners 
$paymentPartners = [
    'maya' => '/images/partners/maya.png',
    'gcash' => '/images/partners/gcash.png',
    'bpi' => '/images/partners/bpi.png',
    'bdo' => '/images/partners/bdo.png'
];
?>

<!-- Footer Section Start -->
<footer class="footer footer--three">
    <div class="container">
        <div class="footer__top">
            <div class="row justify-content-between">
                <!-- Brand Information -->
                <div class="col-xl-4">
                    <div class="footer__brand-info">
                        <div class="footer__brand-info-logo">
                            <img src="/images/logo-white.png" alt="AgriKonnect" />
                        </div>
                        <p class="font-body--md-400">
                            Connecting farmers to consumers - Fresh produce direct from local farms.
                        </p>
                        <div class="footer__brand-info-contact">
                            <a href="tel:+18761236789"><span>(+1876) 123-6789</span></a>
                            or
                            <a href="mailto:info@agrikonnect.ph"><span>info@agrikonnect.com</span></a>
                        </div>
                    </div>
                </div>

                <!-- Navigation Links -->
                <?php foreach ($footerNavigation as $title => $links): ?>
                    <div class="col-xl-auto col-sm-4 col-6">
                        <ul class="footer__navigation">
                            <li class="footer__navigation-title">
                                <h2 class="font-body--lg-500"><?php echo Functions::h($title); ?></h2>
                            </li>
                            <?php foreach ($links as $label => $url): ?>
                                <li class="footer__navigation-link">
                                    <a href="<?php echo Functions::h($url); ?>">
                                        <?php echo Functions::h($label); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>

                <!-- Mobile App Links -->
                <div class="col-xl-3">
                    <ul class="footer__navigation mb-0">
                        <li class="footer__navigation-title">
                            <h2 class="font-body--lg-500">Download Mobile App</h2>
                        </li>
                    </ul>
                    <div class="footer__mobile-app">
                        <a href="#" class="footer__mobile-app--item">
                            <span>
                                <svg width="24" height="29" viewBox="0 0 24 29" fill="none">
                                    <!-- App Store SVG -->
                                    <path d="M23.4239 22.0659C23.0156 23.0169 22.5113 23.9238 21.9189 24.7725C21.1268 25.9013 20.4787 26.6824 19.9793 27.1164C19.2053 27.828 18.3752 28.1932 17.4868 28.2136C16.8492 28.2136 16.0803 28.0322 15.1849 27.6641C14.2866 27.2978 13.4612 27.1158 12.7063 27.1158C11.9148 27.1158 11.066 27.2978 10.1578 27.6641C9.24833 28.0322 8.51567 28.2241 7.95567 28.2428C7.104 28.2795 6.25467 27.9044 5.4065 27.1164C4.86575 26.6439 4.18967 25.8354 3.37884 24.6897C2.50909 23.4659 1.79392 22.0472 1.23392 20.429C0.634252 18.682 0.333252 16.9897 0.333252 15.3517C0.333252 13.4751 0.738669 11.8569 1.55067 10.5007C2.18942 9.41103 3.03817 8.55237 4.101 7.9212C5.1437 7.29723 6.33344 6.96145 7.5485 6.9482C8.22517 6.9482 9.11242 7.15762 10.2155 7.56887C11.3151 7.98128 12.0209 8.1907 12.3307 8.1907C12.5617 8.1907 13.3463 7.9457 14.6757 7.45803C15.9333 7.00537 16.9944 6.81812 17.8636 6.8922C20.2197 7.08237 21.9895 8.01103 23.1661 9.68403C21.0597 10.9604 20.0173 12.7483 20.0383 15.0419C20.0569 16.8287 20.705 18.3156 21.979 19.4957C22.5565 20.044 23.2011 20.4675 23.918 20.768C23.7677 21.2059 23.6029 21.6388 23.4239 22.0659ZM18.0211 0.805701C18.0211 2.2057 17.5095 3.51353 16.4898 4.72337C15.259 6.16245 13.7709 6.9937 12.1568 6.86245C12.1351 6.68634 12.1242 6.50906 12.1242 6.33162C12.1242 4.98762 12.7093 3.54912 13.7488 2.37253C14.2679 1.77695 14.9271 1.2817 15.7274 0.886784C16.5266 0.497701 17.2814 0.282451 17.9919 0.245117C18.0123 0.432367 18.0211 0.619617 18.0211 0.805117V0.805701Z" fill="currentColor"/>
                                </svg>
                            </span>
                            <div class="footer__mobile-app--info">
                                <h5>Download on the</h5>
                                <h2 class="font-body--xl-500">App Store</h2>
                            </div>
                        </a>
                        <a href="#" class="footer__mobile-app--item">
                            <span>
                                <svg width="22" height="25" viewBox="0 0 22 25" fill="none">
                                    <!-- Google Play SVG -->
                                    <path d="M14.0652 11.7299L3.7188 1.35472L16.8828 8.91232L14.0652 11.7299ZM1.0176 0.745117C0.408 1.06432 0 1.64512 0 2.40112V23.0891C0 23.8451 0.408 24.4259 1.0176 24.7451L13.05 12.7427L1.0176 0.745117ZM20.9532 11.3219L18.192 9.72352L15.1116 12.7475L18.192 15.7715L21.0096 14.1731C21.8532 13.5023 21.8532 11.9927 20.9532 11.3219ZM3.7188 24.1403L16.8828 16.5827L14.0652 13.7651L3.7188 24.1403Z" fill="currentColor"/>
                                </svg>
                            </span>
                            <div class="footer__mobile-app--info">
                                <h5>Get it on</h5>
                                <h2 class="font-body--xl-500">Google Play</h2>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

         <!-- Footer Bottom -->
         <div class="footer__bottom">
            <!-- Social Icons -->
            <ul class="social-icon">
                <?php foreach ($socialLinks as $platform => $data): ?>
                    <li class="social-icon-link">
                        <a href="<?php echo Functions::h($data['url']); ?>">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <?php echo $data['icon']; ?>
                            </svg>
                        </a>
                        </li>
                <?php endforeach; ?>
            </ul>

            <!-- Copyright -->
            <p class="footer__copyright-text">
                AgriKonnect © <?php echo date('Y'); ?>. All Rights Reserved
            </p>

            <!-- Payment Partners -->
            <div class="footer__partner d-flex">
                <?php foreach ($paymentPartners as $partner => $image): ?>
                    <a href="#" class="footer__partner-item">
                        <img src="<?php echo Functions::h($image); ?>" alt="<?php echo Functions::h($partner); ?> payment" />
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</footer>

<!-- Newsletter Modal -->
<?php if (!isset($_COOKIE['newsletter_dismissed'])): ?>
<div class="modal fade newsletter-popup" id="newsletter" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <div class="row newsletter-popup__content">
                    <div class="col-lg-5">
                        <div class="newsletter-popup__img-wrapper">
                            <img src="/images/newsletter-bg.jpg" alt="Subscribe to our newsletter" />
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="newsletter-popup__text-content">
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            <h5 class="font-title--xl">Subscribe to Our Newsletter</h5>
                            <p class="font-body--lg">
                                Subscribe to our newsletter and save <span>20%</span> on your first order with our discount code!
                            </p>

                            <form action="/newsletter/subscribe" method="POST" id="newsletterForm">
                                <div class="contact-mail">
                                    <input type="email" name="email" placeholder="Enter your email" required />
                                    <button type="submit" class="button button--md">Subscribe</button>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="doNotShowNewsletter" />
                                    <label class="form-check-label font-body--md-400" for="doNotShowNewsletter">
                                        Do not show this window again
                                    </label>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Core Scripts -->
<script src="/lib/js/jquery.min.js"></script>
<script src="/lib/js/bootstrap.bundle.min.js"></script>
<script src="/lib/js/swiper-bundle.min.js"></script>
<script src="/lib/js/venobox.min.js"></script>

<!-- Custom Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Newsletter popup handling
    const newsletterModal = document.getElementById('newsletter');
    if (newsletterModal) {
        setTimeout(() => {
            const modal = new bootstrap.Modal(newsletterModal);
            modal.show();
        }, 3000);

        // Handle "Do not show again" checkbox
        const doNotShowCheckbox = document.getElementById('doNotShowNewsletter');
        if (doNotShowCheckbox) {
            doNotShowCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    document.cookie = 'newsletter_dismissed=1; path=/; max-age=604800'; // 1 week
                }
            });
        }

        // Handle newsletter form submission
        const newsletterForm = document.getElementById('newsletterForm');
        if (newsletterForm) {
            newsletterForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const email = this.querySelector('input[type="email"]').value;
                
                try {
                    const response = await fetch('/newsletter/subscribe', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ email })
                    });

                    if (response.ok) {
                        const modal = bootstrap.Modal.getInstance(newsletterModal);
                        modal.hide();
                        // Show success message
                        alert('Thank you for subscribing!');
                    } else {
                        throw new Error('Failed to subscribe');
                    }
                } catch (error) {
                    console.error('Newsletter subscription error:', error);
                    alert('Sorry, there was an error. Please try again later.');
                }
            });
        }
    }

    // Initialize VenoBox for modals if needed
    if (typeof VenoBox !== 'undefined') {
        new VenoBox({
            selector: '.venobox'
        });
    }
});
</script>
</body>
</html>