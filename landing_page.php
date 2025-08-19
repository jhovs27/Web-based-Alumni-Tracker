<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SLSU Alumni Tracer - Southern Leyte State University - Hinunangan Campus</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --secondary: #f59e0b;
            --secondary-dark: #d97706;
            --accent: #10b981;
            --dark: #0f172a;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--dark);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Header Styles */
        header {
            background: rgba(37, 99, 235, 0.95);
            backdrop-filter: blur(20px);
            color: white;
            padding: 1rem 0;
            position: fixed;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        header.scrolled {
            padding: 0.5rem 0;
            background: rgba(37, 99, 235, 0.98);
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo img {
            height: 50px;
            width: auto;
            object-fit: contain;
            filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.2));
            transition: transform 0.3s ease;
        }

        .logo:hover img {
            transform: scale(1.05);
        }

        .logo-text h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.2rem;
            letter-spacing: -0.025em;
        }

        .logo-text p {
            font-size: 0.8rem;
            opacity: 0.9;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        nav ul {
            display: flex;
            list-style: none;
            gap: 2rem;
            align-items: center;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            position: relative;
            padding: 0.5rem 0;
        }

        nav ul li a:hover {
            color: var(--secondary);
        }

        nav ul li a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--secondary);
            transition: width 0.3s ease;
        }

        nav ul li a:hover::after,
        nav ul li a.active::after {
            width: 100%;
        }

        .btn-header {
            background: rgba(255, 255, 255, 0.1);
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .btn-header:hover {
            background: var(--secondary);
            border-color: var(--secondary);
            color: var(--dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.3);
        }

        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
        }

        /* Hero Section */
        .hero {
            height: 100vh;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.8) 0%, rgba(30, 64, 175, 0.6) 100%), url('images/hc.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            display: flex;
            align-items: center;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 30% 70%, rgba(37, 99, 235, 0.3) 0%, transparent 50%);
        }

        .hero-content {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 2rem;
            position: relative;
            z-index: 2;
        }

        .hero h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 4rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            line-height: 1.1;
            letter-spacing: -0.025em;
            animation: fadeInUp 1s ease;
        }

        .hero h1 .highlight {
            background: linear-gradient(135deg, var(--secondary) 0%, #fbbf24 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            position: relative;
        }

        .hero p {
            font-size: 1.25rem;
            margin-bottom: 2.5rem;
            opacity: 0.95;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            animation: fadeInUp 1s ease 0.3s both;
        }

        .cta-buttons {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            animation: fadeInUp 1s ease 0.6s both;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 2.5rem;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 1rem;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.6s ease;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-dark) 100%);
            color: var(--dark);
            box-shadow: 0 8px 30px rgba(245, 158, 11, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 40px rgba(245, 158, 11, 0.4);
        }

        .btn-secondary {
            background: transparent;
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-3px);
        }

        /* Section Styles */
        .section {
            padding: 6rem 2rem;
        }

        .section-title {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-title h2 {
            font-family: 'Poppins', sans-serif;
            font-size: 3rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1rem;
            letter-spacing: -0.025em;
        }

        .section-title p {
            font-size: 1.2rem;
            color: var(--gray-600);
            max-width: 600px;
            margin: 0 auto;
        }

        /* About Section */
        .about-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            max-width: 1200px;
            margin: 0 auto;
            align-items: center;
        }

        .about-text h3 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--dark);
        }

        .about-text p {
            font-size: 1.1rem;
            color: var(--gray-600);
            margin-bottom: 1.5rem;
            line-height: 1.7;
        }

        .about-image {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .about-image img {
            width: 100%;
            height: auto;
            display: block;
            transition: transform 0.5s ease;
        }

        .about-image:hover img {
            transform: scale(1.05);
        }

        /* Mission Vision Section */
        .mission-vision {
            background: linear-gradient(135deg, var(--gray-50) 0%, var(--gray-100) 100%);
        }

        .mvv-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .mvv-card {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .mvv-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        }

        .mvv-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }

        .mvv-icon {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
            font-size: 2rem;
            color: white;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        }

        .mvv-card.mission .mvv-icon {
            background: linear-gradient(135deg, var(--accent) 0%, #059669 100%);
        }

        .mvv-card.values .mvv-icon {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        }

        .mvv-title {
            font-family: 'Poppins', sans-serif;
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--dark);
        }

        .mvv-content {
            color: var(--gray-600);
            line-height: 1.7;
        }

        .values-list {
            list-style: none;
            margin-top: 1rem;
        }

        .values-list li {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            padding: 0.75rem;
            background: var(--gray-50);
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .values-list li:hover {
            background: var(--gray-100);
            transform: translateX(5px);
        }

        .values-list li::before {
            content: '✦';
            color: #8b5cf6;
            font-weight: bold;
            margin-right: 1rem;
            font-size: 1.2rem;
        }

        /* Features Section */
        .features {
            background: white;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: 1px solid var(--gray-200);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            border-color: var(--primary);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: white;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        }

        .feature-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--dark);
        }

        .feature-card p {
            color: var(--gray-600);
            line-height: 1.6;
        }

        /* Stats Section */
        .stats {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            text-align: center;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            max-width: 1000px;
            margin: 0 auto;
        }

        .stat-item h3 {
            font-size: 4rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, white 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-item p {
            font-size: 1.2rem;
            opacity: 0.9;
            font-weight: 500;
        }

        /* Testimonials */
        .testimonials {
            background: var(--gray-50);
        }

        .testimonial {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
            position: relative;
        }

        .testimonial-content {
            font-size: 1.2rem;
            font-style: italic;
            margin-bottom: 2rem;
            color: var(--gray-700);
            line-height: 1.7;
            position: relative;
        }

        .testimonial-content::before,
        .testimonial-content::after {
            content: '"';
            font-size: 4rem;
            color: var(--secondary);
            opacity: 0.3;
            position: absolute;
            font-family: serif;
        }

        .testimonial-content::before {
            top: -20px;
            left: -20px;
        }

        .testimonial-content::after {
            bottom: -40px;
            right: -20px;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }

        .testimonial-author img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--secondary);
        }

        .author-info h4 {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }

        .author-info p {
            color: var(--gray-600);
            font-size: 0.95rem;
        }

        /* CTA Section */
        .cta {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.9) 0%, rgba(30, 64, 175, 0.8) 100%), url('images/main.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: white;
            text-align: center;
        }

        .cta h2 {
            font-family: 'Poppins', sans-serif;
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }

        .cta p {
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto 2.5rem;
            opacity: 0.95;
        }

        /* Footer */
        footer {
            background: var(--dark);
            color: white;
            padding: 4rem 0 0;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .footer-column h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 10px;
        }

        .footer-column h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--secondary);
            border-radius: 2px;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 0.75rem;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            display: block;
        }

        .footer-links a:hover {
            color: var(--secondary);
            transform: translateX(5px);
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 45px;
            height: 45px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: white;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            background: var(--secondary);
            color: var(--dark);
            transform: translateY(-5px);
        }

        .footer-bottom {
            background: rgba(0, 0, 0, 0.3);
            padding: 2rem 0;
            text-align: center;
            margin-top: 3rem;
        }

        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: var(--secondary);
            color: var(--dark);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 20px rgba(245, 158, 11, 0.3);
            opacity: 0;
            visibility: hidden;
        }

        .back-to-top:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(245, 158, 11, 0.4);
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .about-content {
                grid-template-columns: 1fr;
                gap: 3rem;
            }
            
            .hero h1 {
                font-size: 3rem;
            }
        }

        @media (max-width: 768px) {
            nav ul {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                background: var(--primary);
                flex-direction: column;
                padding: 2rem 0;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            }

            nav ul.show {
                display: flex;
            }

            .mobile-menu-btn {
                display: block;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .hero p {
                font-size: 1.1rem;
            }

            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }

            .section-title h2 {
                font-size: 2.5rem;
            }

            .mvv-card,
            .feature-card {
                padding: 2rem;
            }

            .section {
                padding: 4rem 1rem;
            }
        }

        @media (max-width: 480px) {
            .header-container {
                padding: 0 1rem;
            }

            .hero h1 {
                font-size: 2rem;
            }

            .section-title h2 {
                font-size: 2rem;
            }

            .cta h2 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header id="header">
        <div class="header-container">
            <div class="logo">
                <img src="images/slsu_logo.png" alt="SLSU Logo">
                <div class="logo-text">
                    <h1>SLSU-HC Alumni Tracer</h1>
                    <p>Southern Leyte State University-Hinunangan Campus</p>
                </div>
            </div>
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </button>
            <nav>
                <ul id="navMenu">
                    <li><a href="#home" class="active">Home</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#mission-vision">Vision & Mission</a></li>
                    <li><a href="#features">Features</a></li>
                    <li><a href="#testimonials">Testimonials</a></li>
                    <li><a href="login.php" class="btn-header">SIGN IN</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content">
            <h1>Reconnect With Your <span class="highlight">SLSU-HC Family</span></h1>
            <p>Join thousands of Hinunangan Campus alumni in building a stronger community and creating lasting connections that transcend graduation.</p>
            <div class="cta-buttons">
                <a href="register.php" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i>Register Now
                </a>
                <a href="#about" class="btn btn-secondary">
                    <i class="fas fa-info-circle"></i>Learn More
                </a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="section" id="about">
        <div class="section-title">
            <h2>Web-based Alumni Tracer and Data Monitoring System</h2>
            <p>Connecting graduates, fostering relationships, and building a stronger alumni community</p>
        </div>
        <div class="about-content">
            <div class="about-text">
                <h3>Building Bridges Beyond Graduation</h3>
                <p>The Southern Leyte State University - Hinunangan Campus Alumni Tracer is a dedicated platform designed to maintain and strengthen the bonds between the university and its graduates.</p>
                <p>Our mission is to create a vibrant alumni community that fosters professional networking, mentorship opportunities, and lifelong connections among graduates from all batches.</p>
                <p>By joining the SLSU Alumni Tracer, you become part of a growing network that celebrates our shared heritage while supporting current students and future generations of SLSU graduates.</p>
            </div>
            <div class="about-image">
                <img src="images/slsu.jpg" alt="SLSU Hinunangan Campus">
            </div>
        </div>
    </section>

    <!-- Mission, Vision, and Core Values Section -->
    <section class="section mission-vision" id="mission-vision">
        <div class="section-title">
            <h2>Our Foundation</h2>
            <p>The principles that guide our mission and shape our future</p>
        </div>
        <div class="mvv-grid">
            <!-- Vision Card -->
            <div class="mvv-card vision">
                <div class="mvv-icon">
                    <i class="fas fa-eye"></i>
                </div>
                <h3 class="mvv-title">Vision</h3>
                <div class="mvv-content">
                    <p>By 2040, Southern Leyte State University is a leading higher education institution that advances knowledge and will be known for innovation and compassion for humanity, creating an inclusive society and a sustainable world.</p>
                </div>
            </div>
            <!-- Mission Card -->
            <div class="mvv-card mission">
                <div class="mvv-icon">
                    <i class="fas fa-bullseye"></i>
                </div>
                <h3 class="mvv-title">Mission</h3>
                <div class="mvv-content">
                    <p>We commit to be a smart and green University that advances education, technological and professional instruction, research and innovation, community engagement services and progressive leadership in arts, sciences and technology that are relevant to the needs of the glocal communities.</p>
                </div>
            </div>
            <!-- Core Values Card -->
            <div class="mvv-card values">
                <div class="mvv-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <h3 class="mvv-title">Core Values</h3>
                <div class="mvv-content">
                    <ul class="values-list">
                        <li><span>Excellence</span></li>
                        <li><span>Leadership and Good Governance</span></li>
                        <li><span>Innovation</span></li>
                        <li><span>Social Responsibility</span></li>
                        <li><span>Integrity</span></li>
                        <li><span>Professionalism</span></li>
                        <li><span>Spirituality</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="section features" id="features">
        <div class="section-title">
            <h2>Why Join Our Community</h2>
            <p>Discover the benefits of being part of the SLSU-HC alumni network</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-network-wired"></i>
                </div>
                <h3>Powerful Networking</h3>
                <p>Connect with fellow alumni across different industries and professions to expand your professional network and discover new opportunities.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-bullhorn"></i>
                </div>
                <h3>Stay Informed</h3>
                <p>Receive updates about campus developments, alumni events, and opportunities to give back to your alma mater.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-hands-helping"></i>
                </div>
                <h3>Mentorship Programs</h3>
                <p>Share your expertise with current students or find mentors among established alumni to guide your career growth.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-briefcase"></i>
                </div>
                <h3>Career Opportunities</h3>
                <p>Access exclusive job postings and career resources shared by fellow alumni and partner organizations.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h3>Alumni Events</h3>
                <p>Get invited to reunions, homecomings, and special events that celebrate our shared SLSU heritage.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h3>Give Back</h3>
                <p>Support current students through scholarships, internships, and sharing your professional journey.</p>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="section stats">
        <div class="section-title">
            <h2 style="color: white;">Our Growing Community</h2>
            <p style="color: rgba(255, 255, 255, 0.9);">Numbers that reflect our impact and reach</p>
        </div>
        <div class="stats-grid">
            <div class="stat-item">
                <h3 id="alumniCount">0</h3>
                <p>Registered Alumni</p>
            </div>
            <div class="stat-item">
                <h3 id="batchCount">0</h3>
                <p>Graduating Batches</p>
            </div>
            <div class="stat-item">
                <h3 id="eventCount">0</h3>
                <p>Annual Events</p>
            </div>
            <div class="stat-item">
                <h3 id="countryCount">0</h3>
                <p>Campuses Represented</p>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="section testimonials" id="testimonials">
        <div class="section-title">
            <h2>What Our Alumni Say</h2>
            <p>Hear from graduates who have benefited from our alumni network</p>
        </div>
        <div class="testimonial">
            <div class="testimonial-content">
                The SLSU Alumni Tracer helped me reconnect with classmates I hadn't seen in years. Through this platform, I found a business partner and we've now launched a successful startup together.
            </div>
            <div class="testimonial-author">
                <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Alumni">
                <div class="author-info">
                    <h4>Juan Dela Cruz</h4>
                    <p>BS Computer Science, Batch 2015</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="section cta">
        <h2>Ready to Reconnect With Your SLSU Family?</h2>
        <p>Join our growing community of alumni today and be part of something bigger. Together, we can strengthen the SLSU legacy and support future generations of graduates.</p>
        <a href="register.php" class="btn btn-primary">
            <i class="fas fa-user-plus"></i>Join Now
        </a>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-column">
                <h3>About SLSU</h3>
                <p>Southern Leyte State University - Hinunangan Campus is committed to providing quality education and producing competent graduates who contribute to national development.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div class="footer-column">
                <h3>Quick Links</h3>
                <ul class="footer-links">
                    <li><a href="#home">Home</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#mission-vision">Vision & Mission</a></li>
                    <li><a href="#features">Features</a></li>
                    <li><a href="#testimonials">Testimonials</a></li>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Contact Us</h3>
                <p><i class="fas fa-map-marker-alt"></i> Hinunangan, Southern Leyte, Philippines</p>
                <p><i class="fas fa-phone"></i> (053) 572-8717</p>
                <p><i class="fas fa-envelope"></i> alumni@slsu-hinunangan.edu.ph</p>
                <p><i class="fas fa-clock"></i> Mon-Fri: 8:00 AM - 5:00 PM</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 Southern Leyte State University - Hinunangan Campus. All Rights Reserved.</p>
        </div>
    </footer>

    <a href="#home" class="back-to-top" id="backToTop">
        <i class="fas fa-arrow-up"></i>
    </a>

    <script>
        // Mobile Menu Toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const navMenu = document.getElementById('navMenu');

        mobileMenuBtn.addEventListener('click', () => {
            navMenu.classList.toggle('show');
        });

        // Header Scroll Effect
        const header = document.getElementById('header');

        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Back to Top Button
        const backToTop = document.getElementById('backToTop');

        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                backToTop.style.opacity = '1';
                backToTop.style.visibility = 'visible';
            } else {
                backToTop.style.opacity = '0';
                backToTop.style.visibility = 'hidden';
            }
        });

        backToTop.addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Counter Animation
        function animateCounter(elementId, target, duration = 2000) {
            const element = document.getElementById(elementId);
            const start = 0;
            const increment = target / (duration / 16);
            let current = start;

            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    clearInterval(timer);
                    current = target;
                }
                element.textContent = Math.floor(current);
            }, 16);
        }

        // Animate stats when they come into view
        const statsSection = document.querySelector('.stats');
        const observerOptions = {
            threshold: 0.5
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounter('alumniCount', 5864);
                    animateCounter('batchCount', 15);
                    animateCounter('eventCount', 2);
                    animateCounter('countryCount', 4);
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        observer.observe(statsSection);

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();

                const targetId = this.getAttribute('href');
                if (targetId === '#') return;

                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });

                    // Close mobile menu if open
                    if (navMenu.classList.contains('show')) {
                        navMenu.classList.remove('show');
                    }
                }
            });
        });

        // Scroll spy functionality
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('nav ul li a');

        function highlightNavItem() {
            const scrollY = window.pageYOffset;

            sections.forEach(section => {
                const sectionHeight = section.offsetHeight;
                const sectionTop = section.offsetTop - 100;
                const sectionId = section.getAttribute('id');

                if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
                    navLinks.forEach(link => {
                        link.classList.remove('active');
                        if (link.getAttribute('href') === `#${sectionId}`) {
                            link.classList.add('active');
                        }
                    });
                }
            });
        }

        window.addEventListener('scroll', highlightNavItem);
        document.addEventListener('DOMContentLoaded', highlightNavItem);
    </script>
</body>
</html>
