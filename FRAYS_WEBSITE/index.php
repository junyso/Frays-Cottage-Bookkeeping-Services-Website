<?php
require_once __DIR__ . '/includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookkeeping Services - Professional Accounting Solutions Botswana</title>
    <meta name="description" content="Professional bookkeeping services specializing in FrontAccounting implementation, tax compliance, and financial management for businesses across Botswana.">
    
    <!-- Favicon -->
    <link rel="icon" href="/assets/images/favicon.png" type="image/png">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Remix Icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Lato:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'frays-red': '#990000',
                        'frays-yellow': '#CCCC66',
                        'frays-parchment': '#F1F1D4'
                    },
                    fontFamily: {
                        'display': ['Playfair Display', 'serif'],
                        'sans': ['Lato', 'sans-serif']
                    }
                }
            }
        }
    </script>
    
    <style>
        .hero-gradient { background: linear-gradient(135deg, #FFFFFF 0%, #F1F1D4 100%); }
        .gold-gradient { background: linear-gradient(135deg, #CCCC66 0%, #DDDD88 100%); }
        .card-hover:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        body { color: #000000; background-color: #FFFFFF; font-family: 'Lato', sans-serif; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Playfair Display', serif; color: #000000; }
        
        /* Carousel styles - Varying Transition Effects */
        .carousel-slide { 
            display: none; 
            opacity: 0;
        }
        .carousel-slide.active { 
            display: block; 
            opacity: 1;
        }
        
        /* Fade In */
        .transition-fade-in {
            animation: fadeIn 0.8s ease-in-out forwards;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Slide In Left */
        .transition-slide-left {
            animation: slideLeft 0.8s ease-out forwards;
        }
        @keyframes slideLeft {
            from { opacity: 0; transform: translateX(-50px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        /* Slide In Right */
        .transition-slide-right {
            animation: slideRight 0.8s ease-out forwards;
        }
        @keyframes slideRight {
            from { opacity: 0; transform: translateX(50px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        /* Pop In */
        .transition-pop-in {
            animation: popIn 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
        }
        @keyframes popIn {
            from { opacity: 0; transform: scale(0.5); }
            to { opacity: 1; transform: scale(1); }
        }
        
        /* Dissolve */
        .transition-dissolve {
            animation: dissolve 1s ease-in-out forwards;
        }
        @keyframes dissolve {
            from { opacity: 0; filter: blur(10px); }
            to { opacity: 1; filter: blur(0); }
        }
        
        /* Wipe Up */
        .transition-wipe-up {
            animation: wipeUp 0.7s ease-out forwards;
        }
        @keyframes wipeUp {
            from { opacity: 0; transform: translateY(100%); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Zoom In */
        .transition-zoom-in {
            animation: zoomIn 0.8s ease-out forwards;
        }
        @keyframes zoomIn {
            from { opacity: 0; transform: scale(0.8); }
            to { opacity: 1; transform: scale(1); }
        }
        
        /* Flip In */
        .transition-flip-in {
            animation: flipIn 0.8s ease-out forwards;
        }
        @keyframes flipIn {
            from { opacity: 0; transform: rotateY(-90deg); }
            to { opacity: 1; transform: rotateY(0); }
        }
        
        /* Bounce In */
        .transition-bounce-in {
            animation: bounceIn 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
        }
        @keyframes bounceIn {
            0% { opacity: 0; transform: scale(0.3); }
            50% { opacity: 1; transform: scale(1.05); }
            70% { transform: scale(0.9); }
            100% { transform: scale(1); }
        }
        
        .carousel-dots .dot { 
            width: 8px; 
            height: 8px; 
            md\:w-12 md\:h-12 border-radius: 50%; 
            background: #CCCC66; 
            opacity: 0.5; 
            cursor: pointer; 
            transition: all 0.3s; 
        }
        @media (min-width: 768px) {
            .carousel-dots .dot { 
                width: 12px; 
                height: 12px; 
            }
        }
        .carousel-dots .dot.active { 
            opacity: 1; 
            transform: scale(1.2); 
            background: #990000;
        }
    </style>
</head>
<body class="font-sans text-gray-800 bg-white">
    
    <!-- Top Contact Bar - Single Line (Fixed) -->
    <div class="fixed top-0 left-0 right-0 z-50 bg-frays-red text-white text-xs md:text-sm py-2 md:py-2.5 shadow-md">
        <div class="max-w-7xl mx-auto px-2">
            <div class="flex justify-center items-center whitespace-nowrap gap-6">
                <!-- Address -->
                <a href="https://www.google.com/maps/search/?api=1&query=Plot+68287%2C+Unit+203%2C+Phakalane+Industrial%2C+Gaborone%2C+Botswana" target="_blank" class="flex items-center gap-1.5 hover:text-frays-yellow transition-colors">
                    <i class="ri-map-pin-line text-[10px] md:text-xs"></i>
                    <span>Plot 68287, Unit 203, Phakalane Industrial, Gaborone, Botswana</span>
                </a>
                
                <!-- Contact Info -->
                <div class="flex items-center gap-1.5">
                    <a href="mailto:helpdesk@frayscottage.co.bw" class="flex items-center gap-1.5 hover:text-frays-yellow transition-colors">
                        <i class="ri-mail-line text-[10px] md:text-xs"></i>
                        <span class="hidden md:inline">helpdesk@frayscottage.co.bw</span>
                    </a>
                    <a href="tel:+2673966011" class="flex items-center gap-1.5 hover:text-frays-yellow transition-colors">
                        <i class="ri-phone-line text-[10px] md:text-xs"></i>
                        <span>(+267) 396 6011</span>
                    </a>
                    <a href="https://wa.me/2673966011" target="_blank" class="flex items-center gap-1.5 hover:text-frays-yellow transition-colors">
                        <i class="ri-whatsapp-line text-[10px] md:text-xs"></i>
                        <span>(+267) 396 6011</span>
                    </a>
                </div>
                
                <!-- Hours -->
                <div class="flex items-center gap-1.5">
                    <i class="ri-time-line text-[10px] md:text-xs"></i>
                    <span>Mon-Fri 8am-5pm | Closed Weekends</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Navigation -->
    <nav class="fixed w-full z-40 bg-white/95 backdrop-blur-sm shadow-md transition-all duration-300 mt-10 md:mt-12" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-24">
                <a href="/" class="flex items-center gap-4">
                    <img src="/assets/images/logo.png" alt="Bookkeeping Services Logo" class="h-24 w-auto">
                    <div class="flex flex-col text-center">
                        <span class="font-display text-2xl font-bold text-black leading-none">Bookkeeping</span>
                        <span class="font-display text-2xl font-bold text-frays-red leading-none">Services</span>
                    </div>
                </a>
                
                <div class="hidden md:flex items-center gap-8">
                    <a href="#about" class="text-black hover:text-frays-red transition-colors font-medium">About</a>
                    <a href="#services" class="text-black hover:text-frays-red transition-colors font-medium">Services</a>
                    <?php if (isAdmin()): ?>
                    <a href="#clients" class="text-black hover:text-frays-red transition-colors font-medium">Clients</a>
                    <?php endif; ?>
                    <a href="/portal" class="bg-frays-yellow text-black px-6 py-2 rounded-lg hover:opacity-90 transition-colors font-medium flex items-center gap-1">
                        <i class="ri-dashboard-line text-frays-red"></i>
                        Client Portal
                    </a>
                </div>
                
                <button class="md:hidden text-2xl text-frays-red" onclick="toggleMobileMenu()">
                    <i class="ri-menu-line"></i>
                </button>
            </div>
        </div>
        
        <div class="hidden md:hidden bg-white border-t" id="mobileMenu">
            <div class="px-4 py-4 space-y-3">
                <a href="#about" class="block py-2 text-gray-600">About</a>
                <a href="#services" class="block py-2 text-gray-600">Services</a>
                <?php if (isAdmin()): ?>
                <a href="#clients" class="block py-2 text-gray-600">Clients</a>
                <?php endif; ?>
                <a href="/portal" class="block bg-frays-yellow text-black px-6 py-3 rounded-lg text-center font-medium">Client Portal</a>
            </div>
        </div>
    </nav>

    <!-- Hero Carousel -->
    <section class="relative bg-white overflow-hidden pt-8 md:pt-10">
        <div class="absolute inset-0">
            <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-r from-frays-parchment/50 to-white"></div>
        </div>
        
        <div class="relative max-w-7xl mx-auto">
            <div id="heroCarousel" class="relative">
                <!-- Slide 1 - Fade In -->
        
        <div class="relative max-w-7xl mx-auto px-3 md:px-4">
            <div id="heroCarousel" class="relative">
                <!-- Slide 1 - Fade In -->
                <div class="carousel-slide active transition-fade-in">
                    <div class="flex flex-col md:flex-row items-center justify-center min-h-[40vh] md:min-h-[60vh] py-0 md:py-0 px-3">
                        <div class="md:w-1/2 text-center md:text-left px-4 md:px-8 mb-6 md:mb-0">
                            <span class="inline-block px-3 py-1 rounded-full bg-frays-yellow text-black text-xs md:text-sm font-medium mb-3 md:mb-4">
                                <i class="ri-focus-2-line text-frays-red mr-1"></i>
                                Strategic Alignment
                            </span>
                            <h1 class="font-display text-2xl md:text-4xl lg:text-5xl font-bold text-black mb-4 leading-tight">
                                Quarterly Strategic Alignment
                            </h1>
                            <p class="text-sm md:text-base lg:text-lg text-gray-600 mb-6 leading-relaxed">
                                We facilitate compulsory management meetings with Directors to ensure your business remains on the path to market leadership.
                            </p>
                            <div class="flex flex-col sm:flex-row gap-3 justify-center md:justify-start">
                                <a href="#contact" class="inline-flex items-center justify-center gap-1 bg-frays-red text-white px-5 py-3 rounded-lg font-semibold hover:opacity-90 transition-all text-sm md:text-base">
                                    <i class="ri-calendar-check-line"></i>
                                    Book a Consultation
                                </a>
                            </div>
                        </div>
                        <div class="md:w-1/2 flex justify-center px-4">
                            <img src="/assets/images/carousel/carousel-1.jpg" alt="Strategic Alignment" class="max-w-full md:max-w-4xl w-full h-auto">
                        </div>
                    </div>
                </div>
                
                <!-- Slide 2 - Slide In Left -->
                <div class="carousel-slide transition-slide-left">
                    <div class="flex flex-col md:flex-row items-center justify-center min-h-[40vh] md:min-h-[60vh] py-0 md:py-0 px-4">
                        <div class="md:w-1/2 text-center md:text-left px-8">
                            <span class="inline-block px-4 py-1 rounded-full bg-frays-yellow text-black text-sm font-medium mb-4">
                                <i class="ri-handshake-line text-frays-red mr-1"></i>
                                Contract Flexibility
                            </span>
                            <h1 class="font-display text-4xl md:text-5xl font-bold text-black mb-6 leading-tight">
                                Professionalism Without the Long-Term Tie
                            </h1>
                            <p class="text-lg text-gray-600 mb-8 leading-relaxed">
                                Experience our "Superior Professional Services" through an open-ended monthly contract with a simple one-month notice period.
                            </p>
                            <a href="#services" class="inline-flex items-center justify-center gap-1 bg-frays-yellow text-black px-5 py-3 rounded-lg font-semibold hover:opacity-90 transition-all text-sm md:text-base">
                                <i class="ri-file-list-3-line"></i>
                                View Pricing Terms
                            </a>
                        </div>
                        <div class="md:w-1/2 flex justify-center px-4">
                            <img src="/assets/images/carousel/carousel-2.jpg" alt="Contract Flexibility" class="max-w-full md:max-w-4xl w-full h-auto">
                        </div>
                    </div>
                </div>
                
                <!-- Slide 3 - Pop In -->
                <div class="carousel-slide transition-pop-in">
                    <div class="flex flex-col md:flex-row items-center justify-center min-h-[40vh] md:min-h-[60vh] py-0 md:py-0 px-3">
                        <div class="md:w-1/2 text-center md:text-left px-4 md:px-8 mb-6 md:mb-0">
                            <span class="inline-block px-3 py-1 rounded-full bg-frays-yellow text-black text-xs md:text-sm font-medium mb-3 md:mb-4">
                                <i class="ri-money-dollar-circle-line text-frays-red mr-1"></i>
                                Financial Control
                            </span>
                            <h1 class="font-display text-2xl md:text-4xl lg:text-5xl font-bold text-black mb-4 leading-tight">
                                Predict Your Future
                            </h1>
                            <p class="text-sm md:text-base lg:text-lg text-gray-600 mb-6 leading-relaxed">
                                We implement Budgetary Control Frameworks and produce cash-flow projections to help you manage your journey to prosperity.
                            </p>
                            <a href="#contact" class="inline-flex items-center justify-center gap-1 bg-frays-red text-white px-5 py-3 rounded-lg font-semibold hover:opacity-90 transition-all text-sm md:text-base">
                                <i class="ri-line-chart-line"></i>
                                Request a Projection
                            </a>
                        </div>
                        <div class="md:w-1/2 flex justify-center px-4">
                            <img src="/assets/images/carousel/carousel-3.jpg" alt="Financial Control" class="max-w-full md:max-w-4xl w-full h-auto">
                        </div>
                    </div>
                </div>
                
                <!-- Slide 4 - Dissolve -->
                <div class="carousel-slide transition-dissolve">
                    <div class="flex flex-col md:flex-row items-center justify-center min-h-[40vh] md:min-h-[60vh] py-0 md:py-0 px-4">
                        <div class="md:w-1/2 text-center md:text-left px-8">
                            <span class="inline-block px-4 py-1 rounded-full bg-frays-yellow text-black text-sm font-medium mb-4">
                                <i class="ri-bar-chart-box-line text-frays-red mr-1"></i>
                                Data Interpretation
                            </span>
                            <h1 class="font-display text-4xl md:text-5xl font-bold text-black mb-6 leading-tight">
                                Beyond the Numbers
                            </h1>
                            <p class="text-lg text-gray-600 mb-8 leading-relaxed">
                                We don't just provide management accounts; we help you analyse and interpret what the data means for your growth.
                            </p>
                            <a href="#services" class="inline-flex items-center gap-1 bg-frays-yellow text-black px-8 py-4 rounded-lg font-semibold hover:opacity-90 transition-all">
                                <i class="ri-growth-line"></i>
                                Get Smarter Reports
                            </a>
                        </div>
                        <div class="md:w-1/2 flex justify-center px-8 mt-8 md:mt-0">
                            <img src="/assets/images/carousel/carousel-4.jpg" alt="Data Interpretation" class="max-w-full md:max-w-4xl w-full h-auto">
                        </div>
                    </div>
                </div>
                
                <!-- Slide 5 - Wipe Up -->
                <div class="carousel-slide transition-wipe-up">
                    <div class="flex flex-col md:flex-row items-center justify-center min-h-[40vh] md:min-h-[60vh] py-0 md:py-0 px-4">
                        <div class="md:w-1/2 text-center md:text-left px-8">
                            <span class="inline-block px-4 py-1 rounded-full bg-frays-yellow text-black text-sm font-medium mb-4">
                                <i class="ri-shield-check-line text-frays-red mr-1"></i>
                                Audit Readiness
                            </span>
                            <h1 class="font-display text-4xl md:text-5xl font-bold text-black mb-6 leading-tight">
                                Seamless Audits, Zero Stress
                            </h1>
                            <p class="text-lg text-gray-600 mb-8 leading-relaxed">
                                We balance the books and prepare your audit files in advance for a smooth process by your appointed auditors.
                            </p>
                            <a href="#contact" class="inline-flex items-center gap-1 bg-frays-red text-white px-8 py-4 rounded-lg font-semibold hover:opacity-90 transition-all">
                                <i class="ri-file-check-line"></i>
                                Prepare for Audit
                            </a>
                        </div>
                        <div class="md:w-1/2 flex justify-center px-8 mt-8 md:mt-0">
                            <img src="/assets/images/carousel/carousel-5.jpg" alt="Audit Readiness" class="max-w-full md:max-w-4xl w-full h-auto">
                        </div>
                    </div>
                </div>
                
                <!-- Slide 6 - Zoom In -->
                <div class="carousel-slide transition-zoom-in">
                    <div class="flex flex-col md:flex-row items-center justify-center min-h-[40vh] md:min-h-[60vh] py-0 md:py-0 px-4">
                        <div class="md:w-1/2 text-center md:text-left px-8">
                            <span class="inline-block px-4 py-1 rounded-full bg-frays-yellow text-black text-sm font-medium mb-4">
                                <i class="ri-team-line text-frays-red mr-1"></i>
                                Top Talent
                            </span>
                            <h1 class="font-display text-4xl md:text-5xl font-bold text-black mb-6 leading-tight">
                                Access Expert Talent
                            </h1>
                            <p class="text-lg text-gray-600 mb-8 leading-relaxed">
                                We hire only top talent and ensure our staff are highly trained to deliver quality and expertise.
                            </p>
                            <a href="#about" class="inline-flex items-center gap-1 bg-frays-yellow text-black px-8 py-4 rounded-lg font-semibold hover:opacity-90 transition-all">
                                <i class="ri-user-heart-line"></i>
                                Meet the Experts
                            </a>
                        </div>
                        <div class="md:w-1/2 flex justify-center px-8 mt-8 md:mt-0">
                            <img src="/assets/images/carousel/carousel-6.jpg" alt="Top Talent" class="max-w-full md:max-w-4xl w-full h-auto">
                        </div>
                    </div>
                </div>
                
                <!-- Slide 7 - Flip In -->
                <div class="carousel-slide transition-flip-in">
                    <div class="flex flex-col md:flex-row items-center justify-center min-h-[40vh] md:min-h-[60vh] py-0 md:py-0 px-4">
                        <div class="md:w-1/2 text-center md:text-left px-8">
                            <span class="inline-block px-4 py-1 rounded-full bg-frays-yellow text-black text-sm font-medium mb-4">
                                <i class="ri-tax-line text-frays-red mr-1"></i>
                                Total Tax Compliance
                            </span>
                            <h1 class="font-display text-4xl md:text-5xl font-bold text-black mb-6 leading-tight">
                                Remain Tax Compliant, Always
                            </h1>
                            <p class="text-lg text-gray-600 mb-8 leading-relaxed">
                                From VAT and Income Tax to Tax Clearance Certificates, we handle the computations and filings for you.
                            </p>
                            <a href="#contact" class="inline-flex items-center gap-1 bg-frays-red text-white px-8 py-4 rounded-lg font-semibold hover:opacity-90 transition-all">
                                <i class="ri-shield-star-line"></i>
                                Secure Your Compliance
                            </a>
                        </div>
                        <div class="md:w-1/2 flex justify-center px-8 mt-8 md:mt-0">
                            <img src="/assets/images/carousel/carousel-7.jpg" alt="Tax Compliance" class="max-w-full md:max-w-4xl w-full h-auto">
                        </div>
                    </div>
                </div>
                
                <!-- Slide 8 - Bounce In -->
                <div class="carousel-slide transition-bounce-in">
                    <div class="flex flex-col md:flex-row items-center justify-center min-h-[40vh] md:min-h-[60vh] py-0 md:py-0 px-4">
                        <div class="md:w-1/2 text-center md:text-left px-8">
                            <span class="inline-block px-4 py-1 rounded-full bg-frays-yellow text-black text-sm font-medium mb-4">
                                <i class="ri-cloud-line text-frays-red mr-1"></i>
                                Cloud Technology
                            </span>
                            <h1 class="font-display text-4xl md:text-5xl font-bold text-black mb-6 leading-tight">
                                Best-in-Class Online Accounting
                            </h1>
                            <p class="text-lg text-gray-600 mb-8 leading-relaxed">
                                As Sage One Certified Advisers, we provide full software setup, configuration, and onsite user training.
                            </p>
                            <a href="#services" class="inline-flex items-center gap-1 bg-frays-yellow text-black px-8 py-4 rounded-lg font-semibold hover:opacity-90 transition-all">
                                <i class="ri-calendar-event-line"></i>
                                Book Your Training
                            </a>
                        </div>
                        <div class="md:w-1/2 flex justify-center px-8 mt-8 md:mt-0">
                            <img src="/assets/images/carousel/carousel-8.jpg" alt="Cloud Technology" class="max-w-full md:max-w-4xl w-full h-auto">
                        </div>
                    </div>
                </div>
                
                <!-- Slide 9 - Slide In Right -->
                <div class="carousel-slide transition-slide-right">
                    <div class="flex flex-col md:flex-row items-center justify-center min-h-[40vh] md:min-h-[60vh] py-0 md:py-0 px-4">
                        <div class="md:w-1/2 text-center md:text-left px-8">
                            <span class="inline-block px-4 py-1 rounded-full bg-frays-yellow text-black text-sm font-medium mb-4">
                                <i class="ri-vip-diamond-line text-frays-red mr-1"></i>
                                The Silver Package
                            </span>
                            <h1 class="font-display text-4xl md:text-5xl font-bold text-black mb-6 leading-tight">
                                Complete Solution for BWP 5,500.00
                            </h1>
                            <p class="text-lg text-gray-600 mb-8 leading-relaxed">
                                Get Payroll, Bookkeeping, Tax, and Company Secretarial services in one integrated monthly package.
                            </p>
                            <a href="#services" class="inline-flex items-center gap-1 bg-frays-red text-white px-8 py-4 rounded-lg font-semibold hover:opacity-90 transition-all">
                                <i class="ri-package-3-line"></i>
                                View Package Details
                            </a>
                        </div>
                        <div class="md:w-1/2 flex justify-center px-8 mt-8 md:mt-0">
                            <img src="/assets/images/carousel/carousel-9.jpg" alt="Silver Package" class="max-w-full md:max-w-4xl w-full h-auto">
                        </div>
                    </div>
                </div>
                
                <!-- Slide 10 - Fade In -->
                <div class="carousel-slide transition-fade-in">
                    <div class="flex flex-col md:flex-row items-center justify-center min-h-[40vh] md:min-h-[60vh] py-0 md:py-0 px-4">
                        <div class="md:w-1/2 text-center md:text-left px-8">
                            <span class="inline-block px-4 py-1 rounded-full bg-frays-yellow text-black text-sm font-medium mb-4">
                                <i class="ri-briefcase-3-line text-frays-red mr-1"></i>
                                The Mission
                            </span>
                            <h1 class="font-display text-4xl md:text-5xl font-bold text-black mb-6 leading-tight">
                                Your Prosperity is Our Mission
                            </h1>
                            <p class="text-lg text-gray-600 mb-8 leading-relaxed">
                                Since 2016, we have existed to inspire and provide high-quality business solutions to passionate entrepreneurs.
                            </p>
                            <a href="#about" class="inline-flex items-center gap-1 bg-frays-yellow text-black px-8 py-4 rounded-lg font-semibold hover:opacity-90 transition-all">
                                <i class="ri-compass-3-line"></i>
                                Explore Our Solutions
                            </a>
                        </div>
                        <div class="md:w-1/2 flex justify-center px-8 mt-8 md:mt-0">
                            <img src="/assets/images/carousel/carousel-10.jpg" alt="Our Mission" class="max-w-full md:max-w-4xl w-full h-auto">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Carousel Controls -->
            <button onclick="changeSlide(-1)" class="absolute left-0 -ml-1.5 md:ml-0 top-1/2 transform -translate-y-1/2 w-10 h-10 md:w-12 md:h-12 rounded-r-lg md:rounded-full bg-white/50 hover:bg-frays-yellow/90 transition-all z-10 flex items-center justify-center pl-1 md:pl-0">
                <i class="ri-arrow-left-line text-frays-red text-lg md:text-xl"></i>
            </button>
            <button onclick="changeSlide(1)" class="absolute right-0 -mr-1.5 md:mr-0 top-1/2 transform -translate-y-1/2 w-10 h-10 md:w-12 md:h-12 rounded-l-lg md:rounded-full bg-white/50 hover:bg-frays-yellow/90 transition-all z-10 flex items-center justify-center pr-1 md:pr-0">
                <i class="ri-arrow-right-line text-frays-red text-lg md:text-xl"></i>
            </button>
            
            <!-- Carousel Dots -->
            <div class="absolute bottom-4 md:bottom-8 left-1/2 transform -translate-x-1/2 carousel-dots flex gap-2 md:gap-3">
                <button onclick="goToSlide(0)" class="dot active"></button>
                <button onclick="goToSlide(1)" class="dot"></button>
                <button onclick="goToSlide(2)" class="dot"></button>
                <button onclick="goToSlide(3)" class="dot"></button>
                <button onclick="goToSlide(4)" class="dot"></button>
                <button onclick="goToSlide(5)" class="dot"></button>
                <button onclick="goToSlide(6)" class="dot"></button>
                <button onclick="goToSlide(7)" class="dot"></button>
                <button onclick="goToSlide(8)" class="dot"></button>
                <button onclick="goToSlide(9)" class="dot"></button>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-12 md:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-8 md:gap-16 items-center">
                <div>
                    <span class="text-frays-yellow font-semibold tracking-wider uppercase text-xs md:text-sm">About Us</span>
                    <h2 class="font-display text-2xl md:text-4xl font-bold text-black mt-2 mb-4 md:mb-6">
                        Trusted Partner in Financial Excellence
                    </h2>
                    <p class="text-gray-600 leading-relaxed mb-4 md:mb-6 text-sm md:text-base">
                        Bookkeeping Services is a premier bookkeeping and accounting firm 
                        dedicated to providing comprehensive financial solutions for businesses of all sizes.
                    </p>
                    <p class="text-gray-600 leading-relaxed mb-4 md:mb-6 text-sm md:text-base">
                        Our team of experienced professionals specializes in FrontAccounting implementation, 
                        tax compliance, payroll management, and financial reporting.
                    </p>
                    
                    <div class="grid grid-cols-2 gap-3 md:gap-4 mt-6 md:mt-8">
                        <div class="flex items-center gap-2 md:gap-3">
                            <div class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-frays-yellow flex items-center justify-center flex-shrink-0">
                                <i class="ri-check-line text-frays-red text-sm md:text-base"></i>
                            </div>
                            <span class="text-black font-medium text-xs md:text-sm">Certified Experts</span>
                        </div>
                        <div class="flex items-center gap-2 md:gap-3">
                            <div class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-frays-yellow flex items-center justify-center flex-shrink-0">
                                <i class="ri-check-line text-frays-red text-sm md:text-base"></i>
                            </div>
                            <span class="text-black font-medium text-xs md:text-sm">BURS Compliant</span>
                        </div>
                        <div class="flex items-center gap-2 md:gap-3">
                            <div class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-frays-yellow flex items-center justify-center flex-shrink-0">
                                <i class="ri-check-line text-frays-red text-sm md:text-base"></i>
                            </div>
                            <span class="text-black font-medium text-xs md:text-sm">Cloud Solutions</span>
                        </div>
                        <div class="flex items-center gap-2 md:gap-3">
                            <div class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-frays-yellow flex items-center justify-center flex-shrink-0">
                                <i class="ri-check-line text-frays-red text-sm md:text-base"></i>
                            </div>
                            <span class="text-black font-medium text-xs md:text-sm">24/7 Support</span>
                        </div>
                    </div>
                </div>
                
                <div class="relative">
                    <div class="frays-red rounded-2xl p-8 text-white shadow-2xl">
                        <h3 class="font-display text-2xl font-semibold mb-6">Our Mission</h3>
                        <p class="text-gray-300 leading-relaxed mb-6">
                            To empower businesses with accurate, timely, and insightful financial 
                            information that drives informed decision-making and sustainable growth.
                        </p>
                        <h3 class="font-display text-2xl font-semibold mb-6 mt-8">Our Vision</h3>
                        <p class="text-gray-300 leading-relaxed">
                            To be the leading bookkeeping and accounting service provider in Botswana, 
                            recognized for excellence and unwavering commitment to client success.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-24 bg-frays-parchment">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="text-frays-yellow font-semibold tracking-wider uppercase text-sm">What We Do</span>
                <h2 class="font-display text-4xl font-bold text-black mt-2 mb-4">Our Services</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Comprehensive bookkeeping and accounting solutions designed to meet the diverse needs of modern businesses.
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php
                $services = [
                    ['icon' => 'ri-account-box-line', 'title' => 'Bookkeeping Services', 'items' => ['Daily transaction recording', 'Bank reconciliation', 'Accounts payable/receivable']],
                    ['icon' => 'ri-tax-line', 'title' => 'Tax Compliance', 'items' => ['PAYE & VAT returns', 'Tax planning advice', 'BURS compliance']],
                    ['icon' => 'ri-dashboard-3-line', 'title' => 'Financial Reporting', 'items' => ['Monthly statements', 'Cash flow analysis', 'Custom reports']],
                    ['icon' => 'ri-computer-line', 'title' => 'FrontAccounting Setup', 'items' => ['System installation', 'Training & support', 'Customization']],
                    ['icon' => 'ri-money-dollar-circle-line', 'title' => 'Payroll Services', 'items' => ['Salary processing', 'Leave management', 'Statutory deductions']],
                    ['icon' => 'ri-customer-service-2-line', 'title' => 'Consulting & Advisory', 'items' => ['Business analysis', 'Growth strategies', 'Process improvement']]
                ];
                
                foreach ($services as $s): ?>
                <div class="bg-white rounded-2xl p-8 shadow-lg card-hover transition-all duration-300 border border-frays-yellow/30">
                    <div class="w-14 h-14 rounded-xl bg-frays-yellow flex items-center justify-center mb-6">
                        <i class="<?= $s['icon'] ?> text-3xl text-frays-red"></i>
                    </div>
                    <h3 class="font-display text-xl font-semibold text-black mb-3"><?= $s['title'] ?></h3>
                    <ul class="text-sm text-gray-600 space-y-2 mt-4">
                        <?php foreach ($s['items'] as $item): ?>
                        <li class="flex items-center gap-1">
                            <i class="ri-check-line text-frays-red"></i>
                            <?= $item ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?php if (isAdmin()): ?>
    <!-- Clients/FA Instances Section - Admin Only -->
    <section id="clients" class="py-12 md:py-24 bg-frays-parchment">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8 md:mb-16">
                <span class="text-frays-red font-semibold tracking-wider uppercase text-xs md:text-sm">Our Clients</span>
                <h2 class="font-display text-2xl md:text-4xl font-bold text-black mt-2 mb-3 md:mb-4">FrontAccounting Instances</h2>
                <p class="text-gray-600 max-w-2xl mx-auto text-sm md:text-base">We manage accounting systems for diverse businesses across Botswana.</p>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-4">
                <?php foreach ($FA_INSTANCES as $slug => $instance): ?>
                <a href="<?= htmlspecialchars($instance['url']) ?>" class="bg-white rounded-lg p-3 md:p-4 hover:bg-frays-yellow transition-all group text-center border border-frays-yellow">
                    <div class="text-frays-red text-xs md:text-sm mb-1"><?= htmlspecialchars($instance['version']) ?></div>
                    <div class="text-black font-medium text-xs md:text-sm truncate group-hover:text-frays-red transition-colors">
                        <?= htmlspecialchars($instance['name']) ?>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-8 md:mt-12">
                <p class="text-gray-600 mb-3 md:mb-4 text-sm md:text-base">And many more...</p>
                <a href="/portal" class="inline-flex items-center justify-center gap-1 bg-frays-red text-white px-6 py-3 rounded-lg font-semibold hover:opacity-90 transition-all text-sm md:text-base">
                    <i class="ri-login-box-line"></i>
                    Access Client Portal
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Portal Section -->
    <section id="portal" class="py-12 md:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-8 md:gap-16 items-center">
                <div>
                    <span class="text-frays-yellow font-semibold tracking-wider uppercase text-xs md:text-sm">Client Portal</span>
                    <h2 class="font-display text-2xl md:text-4xl font-bold text-black mt-2 mb-4 md:mb-6">
                        Secure Access to Your Financial Data
                    </h2>
                    <p class="text-gray-600 leading-relaxed mb-6 text-sm md:text-base">
                        Our unified client portal provides secure, convenient access to all your 
                        financial documents, invoices, and reports.
                    </p>
                    
                    <div class="space-y-3 md:space-y-4">
                        <div class="flex items-start gap-3 md:gap-4">
                            <div class="w-8 h-8 md:w-10 md:h-10 rounded-lg bg-frays-yellow flex items-center justify-center flex-shrink-0">
                                <i class="ri-shield-check-line text-lg md:text-xl text-frays-red"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-black text-sm md:text-base">Secure Authentication</h4>
                                <p class="text-gray-600 text-xs md:text-sm">Bank-level security for your financial data</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 md:gap-4">
                            <div class="w-8 h-8 md:w-10 md:h-10 rounded-lg bg-frays-yellow flex items-center justify-center flex-shrink-0">
                                <i class="ri-file-upload-line text-lg md:text-xl text-frays-red"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-black text-sm md:text-base">Document Upload</h4>
                                <p class="text-gray-600 text-xs md:text-sm">Upload invoices and receipts for processing</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 md:gap-4">
                            <div class="w-8 h-8 md:w-10 md:h-10 rounded-lg bg-frays-yellow flex items-center justify-center flex-shrink-0">
                                <i class="ri-download-cloud-line text-lg md:text-xl text-frays-red"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-black text-sm md:text-base">Easy Export</h4>
                                <p class="text-gray-600 text-xs md:text-sm">Download reports and financial statements</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 md:mt-8">
                        <a href="/portal" class="flex-1 bg-frays-yellow text-black py-3 rounded-lg font-semibold text-center hover:opacity-90 transition-all flex items-center justify-center gap-2 text-sm md:text-base">
                            <i class="ri-login-box-line text-frays-red"></i>
                            Login to Portal
                        </a>
                    </div>
                </div>
                
                <div class="relative">
                    <div class="bg-white rounded-2xl shadow-2xl p-6 md:p-8 border border-frays-yellow">
                        <div class="flex items-center gap-3 md:gap-4 mb-4 md:mb-6 pb-4 md:pb-6 border-b border-frays-yellow/30">
                            <div class="w-10 md:w-12 h-10 md:h-12 rounded-lg bg-frays-yellow flex items-center justify-center">
                                <i class="ri-calculator-line text-lg md:text-xl text-frays-red"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-black text-sm md:text-base">Client Portal</h3>
                                <p class="text-xs md:text-sm text-gray-500">Secure Document Processing</p>
                            </div>
                        </div>
                        
                        <div class="space-y-3 md:space-y-4">
                            <div>
                                <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1">Username</label>
                                <input type="text" class="w-full px-3 md:px-4 py-2.5 md:py-3 rounded-lg border border-gray-300 text-sm" placeholder="Enter your username">
                            </div>
                            <div>
                                <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1">Password</label>
                                <input type="password" class="w-full px-3 md:px-4 py-2.5 md:py-3 rounded-lg border border-gray-300 text-sm" placeholder="••••••••">
                            </div>
                            <a href="/portal" class="block w-full bg-frays-yellow text-black py-2.5 md:py-3 rounded-lg font-semibold text-center hover:opacity-90 transition-all text-sm md:text-base">
                                Sign In
                            </a>
                        </div>
                        
                        <div class="mt-4 md:mt-6 pt-4 border-t text-center">
                            <p class="text-xs md:text-sm text-gray-500">
                                <i class="ri-lock-line mr-1"></i>
                                256-bit SSL Encrypted
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-frays-parchment py-8 md:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-12 mb-8 md:mb-12">
                <div class="col-span-2 md:col-span-1">
                    <div class="mb-3 md:mb-4">
                        <img src="/assets/images/logo.png" alt="Bookkeeping Services Logo" class="h-14 md:h-20 w-auto">
                    </div>
                    <p class="text-gray-600 text-xs md:text-sm">Professional bookkeeping and accounting services for businesses across Botswana.</p>
                </div>
                
                <div>
                    <h4 class="font-semibold text-black mb-3 md:mb-4 text-sm md:text-base">Services</h4>
                    <ul class="space-y-1.5 md:space-y-2 text-gray-600 text-xs md:text-sm">
                        <li>Bookkeeping</li>
                        <li>Tax Compliance</li>
                        <li>Financial Reporting</li>
                        <li>Payroll Services</li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-semibold text-black mb-3 md:mb-4 text-sm md:text-base">Quick Links</h4>
                    <ul class="space-y-1.5 md:space-y-2 text-gray-600 text-xs md:text-sm">
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#services">Our Services</a></li>
                        <li><a href="/portal">Client Portal</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-3 md:mb-4 text-sm md:text-base">Connect With Us</h4>
                    <div class="flex gap-2 md:gap-3">
                        <a href="#" class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-frays-yellow flex items-center justify-center hover:opacity-80">
                            <i class="ri-facebook-fill text-frays-red text-sm md:text-base"></i>
                        </a>
                        <a href="#" class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-frays-yellow flex items-center justify-center hover:opacity-80">
                            <i class="ri-linkedin-fill text-frays-red text-sm md:text-base"></i>
                        </a>
                        <a href="https://wa.me/2673966011" class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-frays-yellow flex items-center justify-center hover:opacity-80">
                            <i class="ri-whatsapp-fill text-frays-red text-sm md:text-base"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-frays-yellow pt-6 md:pt-8 text-center text-gray-600 text-xs md:text-sm">
                © <?= date('Y') ?> Bookkeeping Services. All rights reserved.
            </div>
        </div>
    </footer>

    <script>
        function toggleMobileMenu() {
            document.getElementById('mobileMenu').classList.toggle('hidden');
        }
        
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            navbar.classList.toggle('shadow-md', window.scrollY > 50);
        });
        
        // Carousel functionality
        let currentSlide = 0;
        const slides = document.querySelectorAll('.carousel-slide');
        const dots = document.querySelectorAll('.carousel-dots .dot');
        const totalSlides = slides.length;
        
        function showSlide(index) {
            slides.forEach(slide => slide.classList.remove('active'));
            dots.forEach(dot => dot.classList.remove('active'));
            
            slides[index].classList.add('active');
            dots[index].classList.add('active');
        }
        
        function changeSlide(direction) {
            currentSlide += direction;
            if (currentSlide >= totalSlides) currentSlide = 0;
            if (currentSlide < 0) currentSlide = totalSlides - 1;
            showSlide(currentSlide);
        }
        
        function goToSlide(index) {
            currentSlide = index;
            showSlide(currentSlide);
        }
        
        // Auto-advance carousel every 3 seconds
        setInterval(() => {
            changeSlide(1);
        }, 3000);
    </script>
    
    <!-- Floating WhatsApp Button -->
    <a href="https://wa.me/2673966011" target="_blank" class="fixed bottom-6 right-6 z-50 group">
        <div class="relative">
            <!-- Pulse Animation -->
            <div class="absolute inset-0 rounded-full bg-green-500 animate-ping opacity-75"></div>
            <div class="absolute inset-0 rounded-full bg-green-500 opacity-50 animate-pulse"></div>
            <!-- Button -->
            <div class="relative bg-green-500 text-white px-5 py-3 rounded-full shadow-2xl flex items-center gap-3 hover:bg-green-600 transition-all transform hover:scale-105">
                <i class="ri-whatsapp-line text-2xl"></i>
                <span class="font-medium whitespace-nowrap hidden sm:block">Talk to Us, we are here to help you!</span>
            </div>
        </div>
    </a>
</body>
</html>
