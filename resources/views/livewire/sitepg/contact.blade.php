<div>
    <!-- Page Header -->
    <section class="hero-section" style="min-height: 50vh;">
        <div class="container position-relative">
            <div class="row align-items-center" style="min-height: 50vh;">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="hero-title">Contact <span class="gradient-text">Us</span></h1>
                    <p class="hero-subtitle">
                        Get in touch with us. We'd love to hear from you!
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Content -->
    <section class="section-padding">
        <div class="container">
            <div class="row g-5">
                <!-- Contact Info -->
                <div class="col-lg-5">
                    <h2 class="section-title mb-4">Get in <span class="gradient-text">Touch</span></h2>
                    <p class="text-muted mb-5">
                        Have questions about CAMS or need support? Our team is here to help you get the most out of our carwash management system.
                    </p>

                    <div class="d-flex mb-4">
                        <div class="feature-icon flex-shrink-0" style="width: 60px; height: 60px; border-radius: 15px;">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="ms-4">
                            <h5 class="fw-bold mb-1">Our Location</h5>
                            <p class="text-muted mb-0">Dodoma, Tanzania</p>
                        </div>
                    </div>

                    <div class="d-flex mb-4">
                        <div class="feature-icon flex-shrink-0" style="width: 60px; height: 60px; border-radius: 15px;">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="ms-4">
                            <h5 class="fw-bold mb-1">Phone Number</h5>
                            <p class="text-muted mb-0">+255 659 811 966</p>
                        </div>
                    </div>

                    <div class="d-flex mb-4">
                        <div class="feature-icon flex-shrink-0" style="width: 60px; height: 60px; border-radius: 15px;">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="ms-4">
                            <h5 class="fw-bold mb-1">Email Address</h5>
                            <p class="text-muted mb-0">info@techscales.co.tz</p>
                        </div>
                    </div>

                    <div class="d-flex mb-4">
                        <div class="feature-icon flex-shrink-0" style="width: 60px; height: 60px; border-radius: 15px;">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="ms-4">
                            <h5 class="fw-bold mb-1">Business Hours</h5>
                            <p class="text-muted mb-0">Mon - Sat: 8:00 AM - 6:00 PM</p>
                        </div>
                    </div>

                    <!-- Social Links -->
                    <div class="mt-5">
                        <h5 class="fw-bold mb-3">Follow Us</h5>
                        <div class="d-flex gap-2">
                            <a href="#" class="social-link" style="background: var(--cams-accent);">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="social-link" style="background: var(--cams-accent);">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="social-link" style="background: var(--cams-accent);">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="social-link" style="background: var(--cams-accent);">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="col-lg-7">
                    <div class="feature-card p-4 p-lg-5">
                        <h3 class="fw-bold mb-4">Send us a Message</h3>

                        @if (session()->has('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form wire:submit.prevent="submit">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Full Name</label>
                                    <input type="text"
                                           wire:model="name"
                                           class="form-control form-control-site @error('name') is-invalid @enderror"
                                           placeholder="Enter your name">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Email Address</label>
                                    <input type="email"
                                           wire:model="email"
                                           class="form-control form-control-site @error('email') is-invalid @enderror"
                                           placeholder="Enter your email">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Phone Number</label>
                                    <input type="tel"
                                           wire:model="phone"
                                           class="form-control form-control-site @error('phone') is-invalid @enderror"
                                           placeholder="Enter your phone">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Subject</label>
                                    <input type="text"
                                           wire:model="subject"
                                           class="form-control form-control-site @error('subject') is-invalid @enderror"
                                           placeholder="Enter subject">
                                    @error('subject')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Message</label>
                                    <textarea wire:model="message"
                                              class="form-control form-control-site @error('message') is-invalid @enderror"
                                              rows="5"
                                              placeholder="Write your message here..."></textarea>
                                    @error('message')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn btn-cams btn-lg w-100">
                                        <span wire:loading.remove wire:target="submit">
                                            <i class="fas fa-paper-plane me-2"></i>Send Message
                                        </span>
                                        <span wire:loading wire:target="submit">
                                            <i class="fas fa-spinner fa-spin me-2"></i>Sending...
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="section-padding bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Find <span class="gradient-text">Us</span></h2>
                <p class="section-subtitle">Visit our office in Dodoma, Tanzania</p>
            </div>

            <div class="feature-card p-0 overflow-hidden">
                <div class="ratio ratio-21x9">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d127065.33248900001!2d35.69962525!3d-6.162958799999999!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x184de5a5a3f3c6d1%3A0x7a8f5c8a5c8f5c8a!2sDodoma%2C%20Tanzania!5e0!3m2!1sen!2s!4v1234567890"
                        style="border:0;"
                        allowfullscreen=""
                        loading="lazy"
                        class="w-100 h-100">
                    </iframe>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="section-padding">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Frequently Asked <span class="gradient-text">Questions</span></h2>
                <p class="section-subtitle">Quick answers to common questions</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item border-0 mb-3 feature-card p-0">
                            <h2 class="accordion-header">
                                <button class="accordion-button fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    What is CAMS?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    CAMS (Carwash Management System) is a comprehensive software solution designed to help carwash businesses manage their daily operations, including booking, customer management, invoicing, and analytics.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border-0 mb-3 feature-card p-0">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    How can I get started with CAMS?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    Simply register for an account on our website. You'll get access to a free trial to explore all features. Our team will guide you through the setup process.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border-0 mb-3 feature-card p-0">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    Do you provide customer support?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    Yes! We provide 24/7 customer support via phone, email, and live chat. Our dedicated team is always ready to help you with any questions or issues.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item border-0 feature-card p-0">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    Is CAMS suitable for small businesses?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    Absolutely! CAMS is designed to scale with your business. Whether you're running a single location or multiple branches, our system adapts to your needs.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
