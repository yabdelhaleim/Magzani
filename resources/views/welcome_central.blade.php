<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مخزني SaaS | نظام إدارة المخازن ونقاط البيع السحابي</title>
    
    <!-- Google Fonts: Cairo & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bg-color: #0f172a;
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            --accent-gradient: linear-gradient(135deg, #3b82f6 0%, #06b6d4 100%);
            --glass-bg: rgba(30, 41, 59, 0.7);
            --glass-border: rgba(255, 255, 255, 0.08);
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --card-hover: rgba(51, 65, 85, 0.5);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Cairo', 'Inter', sans-serif;
            scroll-behavior: smooth;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-primary);
            overflow-x: hidden;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(99, 102, 241, 0.15) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(168, 85, 247, 0.15) 0%, transparent 40%);
            background-attachment: fixed;
        }

        /* Navbar Styling */
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 8%;
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--glass-border);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 800;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo i {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-links a {
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: #a855f7;
        }

        .nav-btn {
            background: var(--primary-gradient);
            color: white;
            padding: 0.6rem 1.8rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(168, 85, 247, 0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .nav-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(168, 85, 247, 0.5);
        }

        /* Hero Section */
        .hero {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 8rem 8% 4rem 8%;
            text-align: center;
        }

        .badge {
            background: rgba(99, 102, 241, 0.15);
            border: 1px solid rgba(99, 102, 241, 0.3);
            color: #818cf8;
            padding: 0.5rem 1.2rem;
            border-radius: 50px;
            font-weight: 600;
            margin-bottom: 2rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        h1 {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.3;
            margin-bottom: 1.5rem;
            max-width: 900px;
        }

        h1 span {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-desc {
            font-size: 1.2rem;
            color: var(--text-secondary);
            margin-bottom: 3rem;
            max-width: 700px;
            line-height: 1.6;
        }

        /* Interactive Subdomain Redirect Tool Card */
        .redirect-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            padding: 2rem;
            border-radius: 24px;
            width: 100%;
            max-width: 550px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(16px);
            margin-bottom: 4rem;
            position: relative;
            overflow: hidden;
        }

        .redirect-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--primary-gradient);
        }

        .redirect-card h3 {
            font-size: 1.4rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .redirect-card p {
            color: var(--text-secondary);
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
        }

        .input-group {
            display: flex;
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid var(--glass-border);
            border-radius: 50px;
            padding: 0.25rem;
            align-items: center;
            margin-bottom: 1rem;
            transition: border-color 0.3s ease;
        }

        .input-group:focus-within {
            border-color: #a855f7;
        }

        .input-group input {
            background: transparent;
            border: none;
            outline: none;
            color: white;
            padding: 0.8rem 1.5rem;
            font-size: 1.1rem;
            flex-grow: 1;
            text-align: left;
            direction: ltr;
        }

        .input-group .domain-suffix {
            background: rgba(255, 255, 255, 0.05);
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 1rem;
            direction: ltr;
        }

        .redirect-btn {
            background: var(--primary-gradient);
            color: white;
            border: none;
            outline: none;
            padding: 0.9rem 2rem;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            transition: transform 0.2s ease;
            box-shadow: 0 4px 15px rgba(168, 85, 247, 0.3);
        }

        .redirect-btn:hover {
            transform: translateY(-2px);
        }

        .quick-links {
            margin-top: 1rem;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .quick-links a {
            color: #818cf8;
            text-decoration: none;
            font-weight: 600;
        }

        /* Features Section */
        .features {
            padding: 6rem 8%;
            text-align: center;
        }

        .section-title {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 4rem;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2.5rem;
        }

        .feature-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            padding: 3rem 2rem;
            border-radius: 20px;
            transition: all 0.3s ease;
            text-align: right;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            background: var(--card-hover);
            border-color: rgba(168, 85, 247, 0.3);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: white;
            margin-bottom: 1.5rem;
        }

        .feature-card.accent .feature-icon {
            background: var(--accent-gradient);
        }

        .feature-card h4 {
            font-size: 1.35rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .feature-card p {
            color: var(--text-secondary);
            line-height: 1.6;
        }

        /* Pricing Section */
        .pricing {
            padding: 6rem 8%;
            text-align: center;
            background: rgba(15, 23, 42, 0.5);
        }

        .pricing-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 2.5rem;
            margin-top: 4rem;
        }

        .pricing-card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 3.5rem 2.5rem;
            width: 100%;
            max-width: 350px;
            text-align: right;
            transition: all 0.3s ease;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .pricing-card:hover {
            transform: scale(1.03);
            border-color: rgba(168, 85, 247, 0.4);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .pricing-card.popular {
            border-color: #a855f7;
            background: rgba(30, 41, 59, 0.9);
        }

        .pricing-card.popular::after {
            content: 'الأكثر طلباً';
            position: absolute;
            top: 20px;
            left: 20px;
            background: var(--primary-gradient);
            padding: 0.3rem 1rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .pricing-card h4 {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .plan-desc {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }

        .price {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 2rem;
            display: flex;
            align-items: baseline;
            gap: 5px;
        }

        .price span {
            font-size: 1rem;
            color: var(--text-secondary);
            font-weight: 400;
        }

        .features-list {
            list-style: none;
            margin-bottom: 3rem;
            flex-grow: 1;
        }

        .features-list li {
            margin-bottom: 1rem;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.95rem;
        }

        .features-list li i {
            color: #a855f7;
        }

        .pricing-card.popular .features-list li i {
            color: #22c55e;
        }

        .plan-btn {
            display: block;
            text-align: center;
            padding: 0.8rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.3s ease;
        }

        .pricing-card.popular .plan-btn {
            background: var(--primary-gradient);
            border: none;
        }

        .plan-btn:hover {
            background: white;
            color: var(--bg-color);
        }

        /* Footer */
        footer {
            padding: 3rem 8%;
            border-top: 1px solid var(--glass-border);
            text-align: center;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            h1 {
                font-size: 2.2rem;
            }
            .nav-links {
                display: none;
            }
            nav {
                padding: 1rem 5%;
            }
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <nav>
        <div class="logo">
            <i class="fa-solid fa-boxes-stacked"></i>
            <span>مخزني SaaS</span>
        </div>
        <ul class="nav-links">
            <li><a href="#hero">الرئيسية</a></li>
            <li><a href="#features">المميزات</a></li>
            <li><a href="#pricing">الباقات والأسعار</a></li>
        </ul>
        <a href="#hero" class="nav-btn">ابدأ الآن</a>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="hero">
        <div class="badge">🚀 أطلق مشروعك السحابي اليوم مع مخزني</div>
        <h1>كل ما تحتاجه لإدارة <span>مخازنك، نقاط بيعك، وتصنيعك</span> في منصة واحدة</h1>
        <p class="hero-desc">
            مخزني هو نظام متكامل مصمم خصيصاً للشركات والمحلات لتتبع مخزون المنتجات وإدارتها، البيع المباشر من خلال الكاشير، وحساب تكاليف التصنيع بدقة فائقة.
        </p>

        <!-- Subdomain Redirect Tool Card -->
        <div class="redirect-card">
            <h3><i class="fa-solid fa-rocket" style="color: #a855f7;"></i> بوابة الدخول التجريبية للعملاء</h3>
            <p>أدخل اسم الشركة الفرعي لتجربة عزل قواعد البيانات وتوجيه النطاقات (مثال: foo)</p>
            <div class="input-group">
                <input type="text" id="tenantInput" placeholder="company-name" value="foo">
                <span class="domain-suffix">.localhost:8000</span>
            </div>
            <button class="redirect-btn" onclick="redirectToTenant()">دخول للوحة التحكم</button>
            <div class="quick-links">
                أو جرب العميل الذي أنشأته للتو: <a href="http://foo.localhost:8000/login" target="_blank">foo.localhost:8000/login</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <h2 class="section-title">المميزات الرئيسية للمنصة</h2>
        <div class="features-grid">
            <!-- Feature 1 -->
            <div class="feature-card">
                <div class="feature-icon"><i class="fa-solid fa-warehouse"></i></div>
                <h4>إدارة المخازن المتعددة</h4>
                <p>تتبع المخزون والكميات وحركاتها، والتحويل بين المستودعات بسهولة تامة مع سجل كامل بالعمليات.</p>
            </div>
            <!-- Feature 2 -->
            <div class="feature-card accent">
                <div class="feature-icon"><i class="fa-solid fa-calculator"></i></div>
                <h4>نظام نقاط البيع POS</h4>
                <p>واجهة كاشير سريعة جداً تدعم قارئ الباركود، لإصدار الفواتير وطباعتها وتحديث المخزون والمالية لحظياً.</p>
            </div>
            <!-- Feature 3 -->
            <div class="feature-card">
                <div class="feature-icon"><i class="fa-solid fa-industry"></i></div>
                <h4>إدارة أوامر التصنيع</h4>
                <p>حساب تكاليف المواد الخام، والمصروفات الإضافية وتكاليف الإنتاج لتحديد السعر الأنسب لمنتجك النهائي.</p>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="pricing" id="pricing">
        <h2 class="section-title">باقات الاشتراك المرنة</h2>
        <div class="pricing-grid">
            @forelse($plans as $plan)
            <div class="pricing-card {{ $plan->slug === 'pos' || $plan->slug === 'retail' || $plan->price >= 30 && $plan->price < 60 ? 'popular' : '' }}">
                <h4>{{ $plan->name }}</h4>
                <p class="plan-desc">{{ $plan->description ?: 'وصف الباقة المميزة' }}</p>
                <div class="price">${{ $plan->price }} <span>/ {{ $plan->billing_period === 'monthly' ? 'شهرياً' : 'سنوياً' }}</span></div>
                <ul class="features-list">
                    <li><i class="fa-solid fa-check"></i> إدارة المخازن والمنتجات</li>
                    <li><i class="fa-solid fa-check"></i> المبيعات وفواتير المشتريات</li>
                    
                    @if($plan->hasFeature('pos'))
                    <li><i class="fa-solid fa-check"></i> نظام نقاط البيع POS السريع</li>
                    @else
                    <li style="color: var(--text-secondary); text-decoration: line-through;"><i class="fa-solid fa-xmark" style="color: #ef4444;"></i> نظام نقاط البيع POS</li>
                    @endif

                    @if($plan->hasFeature('manufacturing'))
                    <li><i class="fa-solid fa-check"></i> موديول التصنيع وحساب التكاليف</li>
                    @else
                    <li style="color: var(--text-secondary); text-decoration: line-through;"><i class="fa-solid fa-xmark" style="color: #ef4444;"></i> موديول التصنيع وحساب التكاليف</li>
                    @endif

                    @if($plan->hasFeature('accounting'))
                    <li><i class="fa-solid fa-check"></i> الحسابات والتقارير المالية المتقدمة</li>
                    @else
                    <li style="color: var(--text-secondary); text-decoration: line-through;"><i class="fa-solid fa-xmark" style="color: #ef4444;"></i> الحسابات والتقارير المالية</li>
                    @endif
                </ul>
                <a href="#hero" class="plan-btn">اشترك الآن</a>
            </div>
            @empty
            <!-- Fallback standard pricing cards if DB is empty -->
            <div class="pricing-card">
                <h4>الباقة الأساسية</h4>
                <p class="plan-desc">مناسبة للمستودعات وشركات التوزيع</p>
                <div class="price">19$ <span>/ شهرياً</span></div>
                <ul class="features-list">
                    <li><i class="fa-solid fa-check"></i> إدارة المخازن والمنتجات</li>
                    <li><i class="fa-solid fa-check"></i> المبيعات وفواتير المشتريات</li>
                    <li><i class="fa-solid fa-check"></i> كشوف حسابات العملاء والموردين</li>
                    <li style="color: var(--text-secondary); text-decoration: line-through;"><i class="fa-solid fa-xmark" style="color: #ef4444;"></i> نظام نقاط البيع POS</li>
                    <li style="color: var(--text-secondary); text-decoration: line-through;"><i class="fa-solid fa-xmark" style="color: #ef4444;"></i> موديول التصنيع وحساب التكاليف</li>
                </ul>
                <a href="#hero" class="plan-btn">اشترك الآن</a>
            </div>
            <div class="pricing-card popular">
                <h4>باقة المحلات والتجزئة</h4>
                <p class="plan-desc">مثالية للماركتات والمحلات ذات الفروع</p>
                <div class="price">39$ <span>/ شهرياً</span></div>
                <ul class="features-list">
                    <li><i class="fa-solid fa-check"></i> جميع مميزات الباقة الأساسية</li>
                    <li><i class="fa-solid fa-check"></i> نظام نقاط البيع POS السريع</li>
                    <li><i class="fa-solid fa-check"></i> دعم فروع ومستودعات متعددة</li>
                    <li><i class="fa-solid fa-check"></i> طباعة الفواتير الحرارية والباركود</li>
                    <li style="color: var(--text-secondary); text-decoration: line-through;"><i class="fa-solid fa-xmark" style="color: #ef4444;"></i> موديول التصنيع وحساب التكاليف</li>
                </ul>
                <a href="#hero" class="plan-btn">اشترك الآن</a>
            </div>
            <div class="pricing-card">
                <h4>الباقة الصناعية الكاملة</h4>
                <p class="plan-desc">للمصانع والورش والشركات الكبرى</p>
                <div class="price">79$ <span>/ شهرياً</span></div>
                <ul class="features-list">
                    <li><i class="fa-solid fa-check"></i> جميع مميزات الباقات السابقة</li>
                    <li><i class="fa-solid fa-check"></i> موديول التصنيع الشامل</li>
                    <li><i class="fa-solid fa-check"></i> حساب تكلفة المواد الخام والهدر</li>
                    <li><i class="fa-solid fa-check"></i> تقارير الإنتاجية والأرباح المتقدمة</li>
                    <li><i class="fa-solid fa-check"></i> دعم فني مخصص 24/7</li>
                </ul>
                <a href="#hero" class="plan-btn">اشترك الآن</a>
            </div>
            @endforelse
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>جميع الحقوق محفوظة &copy; 2026 لمشروع مخزني SaaS</p>
    </footer>

    <script>
        function redirectToTenant() {
            var tenantId = document.getElementById('tenantInput').value.trim();
            if (tenantId) {
                // توجيه العميل إلى الرابط المحلي الفرعي
                var url = 'http://' + tenantId + '.localhost:8000/login';
                window.open(url, '_blank');
            } else {
                alert('الرجاء إدخال اسم الشركة الفرعي!');
            }
        }
    </script>
</body>
</html>
