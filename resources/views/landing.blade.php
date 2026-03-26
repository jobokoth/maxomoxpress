<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="MasomoPlus — The all-in-one school management system built for Kenyan schools. Mpesa payments, parent portal, attendance, fees and more. Start your free trial today.">
    <title>MasomoPlus | School Management System for Kenyan Schools</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --brand: #4F46E5;
            --brand-dark: #3730a3;
            --brand-light: #EEF2FF;
            --accent: #06B6D4;
            --text-muted: #6B7280;
        }

        body { font-family: 'Inter', sans-serif; color: #111827; }

        /* ── Navbar ─────────────────────────────────────────────── */
        .navbar-brand { font-weight: 800; font-size: 1.35rem; color: var(--brand) !important; }
        .navbar-brand span { color: #06B6D4; }
        .nav-link { font-weight: 500; color: #374151 !important; }
        .nav-link:hover { color: var(--brand) !important; }

        /* ── Hero ───────────────────────────────────────────────── */
        .hero {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #1e40af 100%);
            padding: 100px 0 80px;
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute; inset: 0;
            background: radial-gradient(circle at 70% 50%, rgba(6,182,212,.15) 0%, transparent 60%);
        }
        .hero-badge {
            display: inline-flex; align-items: center; gap: .4rem;
            background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.2);
            color: #e0e7ff; font-size: .82rem; font-weight: 600;
            padding: .3rem .85rem; border-radius: 999px; margin-bottom: 1.5rem;
        }
        .hero h1 {
            font-size: clamp(2.2rem, 5vw, 3.6rem);
            font-weight: 800; line-height: 1.15;
            color: #fff;
        }
        .hero h1 .highlight { color: #67e8f9; }
        .hero p.lead { color: #c7d2fe; font-size: 1.15rem; max-width: 580px; }
        .btn-cta {
            background: var(--accent); border: none; color: #fff;
            font-weight: 700; padding: .75rem 2rem; font-size: 1rem;
            border-radius: 8px; transition: all .2s;
        }
        .btn-cta:hover { background: #0891b2; color: #fff; transform: translateY(-1px); }
        .btn-outline-light { font-weight: 600; padding: .75rem 1.75rem; border-radius: 8px; }
        .hero-stat { color: #e0e7ff; }
        .hero-stat strong { display: block; font-size: 1.6rem; font-weight: 800; color: #fff; }

        /* ── Trust logos strip ──────────────────────────────────── */
        .trust-strip { background: #f9fafb; border-top: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb; }
        .trust-strip p { font-size: .78rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); }

        /* ── Section headings ───────────────────────────────────── */
        .section-label {
            display: inline-block; background: var(--brand-light); color: var(--brand);
            font-size: .78rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .06em; padding: .25rem .75rem; border-radius: 999px; margin-bottom: .75rem;
        }
        .section-title { font-size: clamp(1.6rem, 3vw, 2.4rem); font-weight: 800; line-height: 1.2; }
        .section-sub { color: var(--text-muted); font-size: 1.05rem; max-width: 560px; }

        /* ── Feature cards ──────────────────────────────────────── */
        .feature-card {
            border: 1px solid #e5e7eb; border-radius: 12px;
            padding: 1.75rem; transition: all .25s;
            height: 100%;
        }
        .feature-card:hover { border-color: var(--brand); box-shadow: 0 8px 30px rgba(79,70,229,.1); transform: translateY(-3px); }
        .feature-icon {
            width: 52px; height: 52px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem; margin-bottom: 1rem;
        }
        .feature-card h5 { font-weight: 700; margin-bottom: .4rem; }
        .feature-card p { color: var(--text-muted); font-size: .92rem; margin: 0; }

        /* ── How it works ───────────────────────────────────────── */
        .step-number {
            width: 44px; height: 44px; border-radius: 50%;
            background: var(--brand); color: #fff;
            font-weight: 800; font-size: 1.1rem;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .step-connector {
            width: 2px; background: #e5e7eb; margin: .5rem auto;
            height: 40px;
        }

        /* ── Pricing ────────────────────────────────────────────── */
        .pricing-card {
            border: 2px solid #e5e7eb; border-radius: 16px;
            padding: 2rem; height: 100%; transition: all .25s;
            position: relative;
        }
        .pricing-card.popular {
            border-color: var(--brand);
            box-shadow: 0 0 0 4px rgba(79,70,229,.1);
        }
        .popular-badge {
            position: absolute; top: -14px; left: 50%; transform: translateX(-50%);
            background: var(--brand); color: #fff;
            font-size: .75rem; font-weight: 700; padding: .2rem 1rem;
            border-radius: 999px; white-space: nowrap;
        }
        .pricing-price { font-size: 2.4rem; font-weight: 800; line-height: 1; }
        .pricing-price sup { font-size: 1rem; font-weight: 600; vertical-align: super; }
        .pricing-price span { font-size: .9rem; color: var(--text-muted); font-weight: 400; }
        .pricing-feature { display: flex; align-items: flex-start; gap: .6rem; font-size: .92rem; margin-bottom: .5rem; }
        .pricing-feature i { color: #10b981; margin-top: .15rem; flex-shrink: 0; }

        /* ── Testimonials ───────────────────────────────────────── */
        .testimonial-card {
            background: #f9fafb; border: 1px solid #e5e7eb;
            border-radius: 12px; padding: 1.75rem; height: 100%;
        }
        .stars { color: #f59e0b; font-size: .85rem; }
        .testimonial-card p { color: #374151; font-size: .95rem; font-style: italic; }
        .testimonial-avatar {
            width: 42px; height: 42px; border-radius: 50%;
            background: var(--brand); color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: .95rem; flex-shrink: 0;
        }

        /* ── FAQ ────────────────────────────────────────────────── */
        .accordion-button:not(.collapsed) { background: var(--brand-light); color: var(--brand); box-shadow: none; }
        .accordion-button:focus { box-shadow: none; }
        .accordion-item { border: 1px solid #e5e7eb; border-radius: 8px !important; margin-bottom: .5rem; overflow: hidden; }

        /* ── CTA Banner ─────────────────────────────────────────── */
        .cta-banner {
            background: linear-gradient(135deg, var(--brand) 0%, #1e40af 100%);
            border-radius: 20px;
        }

        /* ── Footer ─────────────────────────────────────────────── */
        footer { background: #111827; color: #9ca3af; }
        footer a { color: #9ca3af; text-decoration: none; transition: color .15s; }
        footer a:hover { color: #fff; }
        footer .footer-brand { font-size: 1.3rem; font-weight: 800; color: #fff; }
        footer .footer-brand span { color: #67e8f9; }
        .footer-link-group h6 { color: #fff; font-weight: 600; margin-bottom: .75rem; }
    </style>
</head>
<body>

{{-- ════════════════════════════════════════ NAVBAR ════════════════════════════════════════ --}}
<nav class="navbar navbar-expand-lg bg-white sticky-top border-bottom shadow-sm" style="z-index:1030;">
    <div class="container">
        <a class="navbar-brand" href="{{ route('landing') }}">
            Masomo<span>Plus</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav mx-auto gap-1">
                <li class="nav-item"><a class="nav-link px-3" href="#features">Features</a></li>
                <li class="nav-item"><a class="nav-link px-3" href="#how-it-works">How it works</a></li>
                <li class="nav-item"><a class="nav-link px-3" href="#pricing">Pricing</a></li>
                <li class="nav-item"><a class="nav-link px-3" href="#faq">FAQ</a></li>
            </ul>
            <div class="d-flex gap-2">
                <a href="{{ route('filament.admin.auth.login') }}" class="btn btn-outline-secondary btn-sm px-3">Sign in</a>
                <a href="{{ route('school.register') }}" class="btn btn-sm px-3" style="background:var(--brand);color:#fff;font-weight:600;">
                    Start Free Trial
                </a>
            </div>
        </div>
    </div>
</nav>

{{-- ════════════════════════════════════════ HERO ════════════════════════════════════════ --}}
<section class="hero">
    <div class="container position-relative">
        <div class="row align-items-center gy-5">
            <div class="col-lg-6">
                <div class="hero-badge">
                    <i class="bi bi-stars"></i> Trusted by schools across Kenya
                </div>
                <h1>
                    The school ERP built for<br>
                    <span class="highlight">Kenyan schools</span>
                </h1>
                <p class="lead mt-3 mb-4">
                    Manage students, fees, attendance, and parent communication — all in one place.
                    Accept payments via Mpesa, bank transfer, or cash with zero friction.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="{{ route('school.register') }}" class="btn btn-cta">
                        <i class="bi bi-rocket-takeoff me-2"></i>Start Free Trial
                    </a>
                    <a href="#how-it-works" class="btn btn-outline-light">
                        See how it works
                    </a>
                </div>
                <div class="d-flex gap-4 mt-5 pt-2">
                    <div class="hero-stat">
                        <strong>1 month</strong>free trial
                    </div>
                    <div class="border-start border-secondary opacity-25"></div>
                    <div class="hero-stat">
                        <strong>No setup</strong>fee
                    </div>
                    <div class="border-start border-secondary opacity-25"></div>
                    <div class="hero-stat">
                        <strong>Cancel</strong>anytime
                    </div>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block">
                <div class="rounded-4 overflow-hidden shadow-lg" style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.15);padding:1.5rem;">
                    {{-- Dashboard preview mockup --}}
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div style="width:10px;height:10px;border-radius:50%;background:#ef4444;"></div>
                        <div style="width:10px;height:10px;border-radius:50%;background:#f59e0b;"></div>
                        <div style="width:10px;height:10px;border-radius:50%;background:#10b981;"></div>
                        <span class="text-white opacity-50 small ms-2">MasomoPlus Dashboard</span>
                    </div>
                    <div class="row g-2 mb-3">
                        @foreach([['bi-people-fill','Students','324','#4F46E5'],['bi-currency-exchange','Fees Collected','KES 1.2M','#10b981'],['bi-calendar-check','Attendance','96.4%','#06B6D4'],['bi-bell-fill','Alerts','3','#f59e0b']] as [$icon, $label, $val, $color])
                        <div class="col-6">
                            <div class="rounded-3 p-3" style="background:rgba(255,255,255,.1);">
                                <i class="bi {{ $icon }} mb-1" style="color:{{ $color }};font-size:1.2rem;"></i>
                                <div class="text-white fw-bold">{{ $val }}</div>
                                <div class="small" style="color:rgba(255,255,255,.6);">{{ $label }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="rounded-3 p-3 mb-2" style="background:rgba(255,255,255,.1);">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-white small fw-semibold">Recent Payments</span>
                            <span class="badge" style="background:#06B6D4;">Mpesa</span>
                        </div>
                        @foreach([['John Mwangi','KES 12,000'],['Amina Odhiambo','KES 8,500'],['Peter Kamau','KES 15,000']] as [$name, $amount])
                        <div class="d-flex justify-content-between align-items-center py-1" style="border-top:1px solid rgba(255,255,255,.08);">
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:28px;height:28px;border-radius:50%;background:rgba(79,70,229,.6);display:flex;align-items:center;justify-content:center;">
                                    <span class="text-white" style="font-size:.7rem;font-weight:700;">{{ substr($name,0,1) }}</span>
                                </div>
                                <span class="text-white small">{{ $name }}</span>
                            </div>
                            <span style="color:#10b981;font-size:.85rem;font-weight:600;">{{ $amount }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ════════════════════════════════════════ TRUST STRIP ════════════════════════════════════════ --}}
<div class="trust-strip py-4">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-center gap-4 gap-md-5">
            <p class="mb-0 me-2">Trusted by schools in:</p>
            @foreach(['Nairobi','Mombasa','Kisumu','Nakuru','Eldoret','Thika','Nyeri'] as $city)
                <span class="fw-semibold text-dark" style="font-size:.92rem;">{{ $city }}</span>
            @endforeach
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════ FEATURES ════════════════════════════════════════ --}}
<section id="features" class="py-6" style="padding:80px 0;">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-label">Everything you need</span>
            <h2 class="section-title">Built for how Kenyan schools actually work</h2>
            <p class="section-sub mx-auto mt-2">
                No generic one-size-fits-all software. MasomoPlus is designed around the workflows,
                payment methods, and reporting needs of schools in Kenya.
            </p>
        </div>

        <div class="row g-4">
            @php
            $features = [
                ['bi-phone-vibrate-fill','bg-success bg-opacity-10 text-success','Mpesa Payments','Accept fees directly via Mpesa Paybill, STK Push, or your bank-issued shortcode. No manual reconciliation.'],
                ['bi-people-fill','bg-primary bg-opacity-10 text-primary','Student Management','Full student lifecycle: admission, promotion, transfer, alumni. Admission numbers, guardian links, medical records.'],
                ['bi-calendar2-check-fill','bg-cyan bg-opacity-10','Attendance Tracking','Daily attendance by class, teacher login, parent SMS alerts for absences. Staff attendance too.'],
                ['bi-cash-coin','bg-warning bg-opacity-10 text-warning','Fee Management','Fee structures per class, installment plans, waivers, automatic balance calculation, receipt generation.'],
                ['bi-house-heart-fill','bg-danger bg-opacity-10 text-danger','Parent & Student Portal','Parents view fees, attendance, results, and announcements. Students see timetables, results, library loans.'],
                ['bi-bar-chart-fill','bg-indigo bg-opacity-10','Exams & Gradebook','Mark entry, automatic grade calculation, term reports, class performance analytics.'],
                ['bi-book-fill','bg-success bg-opacity-10 text-success','Library Management','Book catalogue, member loans, overdue tracking, fine calculation.'],
                ['bi-building-fill','bg-secondary bg-opacity-10 text-secondary','Hostels & Transport','Hostel room allocation, bus routes, student transport lists.'],
                ['bi-graph-up-arrow','bg-primary bg-opacity-10 text-primary','QuickBooks Sync','Fee payments automatically sync to QuickBooks Online as Sales Receipts. Students sync as Customers.'],
                ['bi-megaphone-fill','bg-warning bg-opacity-10 text-warning','Communications','Announcements, events calendar, SMS and email notifications. Parent-teacher messaging.'],
                ['bi-shield-check-fill','bg-danger bg-opacity-10 text-danger','Roles & Permissions','School admin, teacher, finance admin, class teacher, parent, student — each sees only what they need.'],
                ['bi-cloud-arrow-up-fill','bg-cyan bg-opacity-10','Cloud & Secure','Hosted on the cloud. Daily backups. School data is isolated — no cross-school data leaks.'],
            ];
            @endphp

            @foreach ($features as [$icon, $bg, $title, $desc])
            <div class="col-sm-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon {{ $bg }}">
                        <i class="bi {{ $icon }}"></i>
                    </div>
                    <h5>{{ $title }}</h5>
                    <p>{{ $desc }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ════════════════════════════════════════ HOW IT WORKS ════════════════════════════════════════ --}}
<section id="how-it-works" style="background:#f9fafb;padding:80px 0;">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-label">Quick start</span>
            <h2 class="section-title">Up and running in under an hour</h2>
            <p class="section-sub mx-auto mt-2">
                No IT team needed. No installation. No long training sessions.
            </p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-7">
                @php
                $steps = [
                    ['Register your school','Create your account and tell us about your school — name, logo, timezone, and term structure.','bi-building-add','#4F46E5'],
                    ['Set up in minutes','Our onboarding wizard walks you through classes, fee structures, and payment methods. Import students via CSV or add them one by one.','bi-lightning-charge-fill','#06B6D4'],
                    ['Invite staff & go live','Add teachers and finance staff. Parents get portal access automatically when you add their email. You\'re live.','bi-rocket-takeoff-fill','#10b981'],
                ];
                @endphp

                @foreach ($steps as $i => [$title, $desc, $icon, $color])
                <div class="d-flex gap-4 mb-2">
                    <div class="d-flex flex-column align-items-center">
                        <div class="step-number" style="background:{{ $color }};">{{ $i + 1 }}</div>
                        @if (! $loop->last)
                            <div class="step-connector"></div>
                        @endif
                    </div>
                    <div class="pb-4">
                        <h5 class="fw-700 mb-1" style="font-weight:700;">{{ $title }}</h5>
                        <p class="text-muted mb-0" style="font-size:.95rem;">{{ $desc }}</p>
                    </div>
                </div>
                @endforeach

                <div class="mt-3">
                    <a href="{{ route('school.register') }}" class="btn px-4 py-2 fw-bold" style="background:var(--brand);color:#fff;border-radius:8px;">
                        Register your school — it's free <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ════════════════════════════════════════ PRICING ════════════════════════════════════════ --}}
<section id="pricing" style="padding:80px 0;">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-label">Transparent pricing</span>
            <h2 class="section-title">Simple, student-count based pricing</h2>
            <p class="section-sub mx-auto mt-2">
                Pay for what you use. Pricing scales with your school — not arbitrary feature gates.
                Every plan includes a <strong>1-month free trial</strong>.
            </p>
        </div>

        <div class="row g-4 justify-content-center">
            @foreach ($pricing as $plan)
            <div class="col-md-6 col-lg-4">
                <div class="pricing-card {{ $plan['popular'] ? 'popular' : '' }}">
                    @if ($plan['popular'])
                        <span class="popular-badge"><i class="bi bi-star-fill me-1"></i>Most Popular</span>
                    @endif

                    <div class="mb-3">
                        <span class="badge rounded-pill mb-2" style="background:var(--brand-light);color:var(--brand);font-size:.78rem;font-weight:700;">
                            {{ $plan['students'] }}
                        </span>
                        <h4 class="fw-800 mb-0" style="font-weight:800;">{{ $plan['name'] }}</h4>
                    </div>

                    <div class="pricing-price mb-1">
                        <sup>KES </sup>{{ number_format($plan['price']) }}<span>/mo</span>
                    </div>
                    <p class="text-muted small mb-4">Billed monthly &bull; Cancel anytime</p>

                    <a href="{{ route('school.register') }}"
                       class="btn w-100 mb-4 fw-bold py-2"
                       style="background:{{ $plan['popular'] ? 'var(--brand)' : '#f3f4f6' }};
                              color:{{ $plan['popular'] ? '#fff' : '#111827' }};
                              border-radius:8px;border:none;">
                        Start Free Trial
                    </a>

                    <ul class="list-unstyled mb-0">
                        @foreach ($plan['features'] as $feature)
                        <li class="pricing-feature">
                            <i class="bi bi-check-circle-fill"></i>
                            {{ $feature }}
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endforeach
        </div>

        <p class="text-center text-muted mt-4 small">
            <i class="bi bi-info-circle me-1"></i>
            Pricing is automatically adjusted based on your active student count each billing cycle.
            All prices are in Kenyan Shillings (KES) and exclude VAT where applicable.
        </p>
    </div>
</section>

{{-- ════════════════════════════════════════ TESTIMONIALS ════════════════════════════════════════ --}}
<section style="background:#f9fafb;padding:80px 0;">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-label">What schools say</span>
            <h2 class="section-title">Real schools, real results</h2>
        </div>

        <div class="row g-4">
            @php
            $testimonials = [
                ['The Mpesa integration alone saved us 3 days of manual reconciliation every term. Parents can pay anytime and it reflects instantly.','Margaret Wanjiku','Finance Director, Greenwood Academy, Nairobi','MW'],
                ['We moved from WhatsApp groups and Excel sheets. Now teachers take attendance on their phones and parents see it the same day.','James Otieno','Head Teacher, Sunrise Junior School, Kisumu','JO'],
                ['Setup took less than 2 hours. The onboarding wizard is brilliant — it asked exactly the right questions in the right order.','Fatuma Hassan','School Administrator, Al-Noor Academy, Mombasa','FH'],
            ];
            @endphp

            @foreach ($testimonials as [$quote, $name, $role, $initials])
            <div class="col-md-4">
                <div class="testimonial-card">
                    <div class="stars mb-3">
                        <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p class="mb-4">"{{ $quote }}"</p>
                    <div class="d-flex align-items-center gap-3">
                        <div class="testimonial-avatar">{{ $initials }}</div>
                        <div>
                            <div class="fw-600" style="font-weight:600;font-size:.92rem;">{{ $name }}</div>
                            <div class="text-muted" style="font-size:.8rem;">{{ $role }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ════════════════════════════════════════ FAQ ════════════════════════════════════════ --}}
<section id="faq" style="padding:80px 0;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center mb-5">
                    <span class="section-label">FAQ</span>
                    <h2 class="section-title">Frequently asked questions</h2>
                </div>

                <div class="accordion" id="faqAccordion">
                    @php
                    $faqs = [
                        ['Do I need technical knowledge to set up MasomoPlus?','No. The onboarding wizard guides you through every step — school details, classes, fee structures, and payment setup. Most schools are fully configured within 2 hours.'],
                        ['How does the Mpesa integration work?','You can use your school\'s own Paybill (own Daraja account), your bank-issued Mpesa shortcode, or share the MasomoPlus platform account. We auto-register the confirmation webhook with Safaricom so payments reflect in the system instantly.'],
                        ['Can parents access the portal without an app?','Yes. The parent portal is a web application that works on any smartphone browser — no app download needed. Parents log in at your school\'s portal URL.'],
                        ['What happens after the free trial?','After your 1-month trial you\'ll be asked to subscribe. Your data is never deleted — you have 14 days after trial expiry to subscribe before the account is suspended.'],
                        ['Is our school\'s data kept private?','Yes. Each school\'s data is completely isolated — no other school can see your students, fees, or any records. We use encrypted connections and regular backups.'],
                        ['Can we import our existing student data?','Yes. MasomoPlus supports bulk CSV import for students, guardians, and fee records. Our onboarding team can assist with large imports.'],
                        ['Do you offer support in Kiswahili?','Our support team is Kenyan and can assist in both English and Kiswahili. Support is available Monday–Saturday 8am–6pm EAT.'],
                    ];
                    @endphp

                    @foreach ($faqs as $i => [$question, $answer])
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button {{ $i > 0 ? 'collapsed' : '' }} fw-semibold"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#faq{{ $i }}">
                                {{ $question }}
                            </button>
                        </h2>
                        <div id="faq{{ $i }}" class="accordion-collapse collapse {{ $i === 0 ? 'show' : '' }}" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted">{{ $answer }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ════════════════════════════════════════ CTA BANNER ════════════════════════════════════════ --}}
<section style="padding:60px 0 80px;">
    <div class="container">
        <div class="cta-banner text-center text-white p-5">
            <h2 class="fw-800 mb-3" style="font-size:clamp(1.8rem,4vw,2.6rem);font-weight:800;">
                Ready to modernise your school?
            </h2>
            <p class="mb-4 opacity-75" style="font-size:1.1rem;max-width:520px;margin:0 auto 1.5rem;">
                Join hundreds of Kenyan schools managing students, fees, and parents smarter with MasomoPlus.
                Your first month is completely free.
            </p>
            <div class="d-flex flex-wrap gap-3 justify-content-center">
                <a href="{{ route('school.register') }}" class="btn btn-cta px-5 py-3">
                    <i class="bi bi-rocket-takeoff me-2"></i>Start Free Trial — No credit card needed
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ════════════════════════════════════════ FOOTER ════════════════════════════════════════ --}}
<footer class="py-5">
    <div class="container">
        <div class="row g-4 mb-5">
            <div class="col-lg-4">
                <div class="footer-brand mb-2">Masomo<span>Plus</span></div>
                <p style="font-size:.92rem;max-width:280px;">
                    The all-in-one school management system built for Kenyan schools.
                </p>
                <div class="d-flex gap-3 mt-3">
                    <a href="#" title="Facebook"><i class="bi bi-facebook fs-5"></i></a>
                    <a href="#" title="Twitter / X"><i class="bi bi-twitter-x fs-5"></i></a>
                    <a href="#" title="WhatsApp"><i class="bi bi-whatsapp fs-5"></i></a>
                </div>
            </div>

            <div class="col-6 col-lg-2 footer-link-group">
                <h6>Product</h6>
                <ul class="list-unstyled" style="font-size:.9rem;">
                    <li class="mb-1"><a href="#features">Features</a></li>
                    <li class="mb-1"><a href="#pricing">Pricing</a></li>
                    <li class="mb-1"><a href="#how-it-works">How it works</a></li>
                    <li class="mb-1"><a href="#faq">FAQ</a></li>
                </ul>
            </div>

            <div class="col-6 col-lg-2 footer-link-group">
                <h6>School</h6>
                <ul class="list-unstyled" style="font-size:.9rem;">
                    <li class="mb-1"><a href="{{ route('school.register') }}">Register School</a></li>
                    <li class="mb-1"><a href="{{ route('filament.admin.auth.login') }}">School Login</a></li>
                    <li class="mb-1"><a href="{{ route('filament.platform-admin.auth.login') }}">Platform Admin</a></li>
                </ul>
            </div>

            <div class="col-6 col-lg-2 footer-link-group">
                <h6>Legal</h6>
                <ul class="list-unstyled" style="font-size:.9rem;">
                    <li class="mb-1"><a href="#">Privacy Policy</a></li>
                    <li class="mb-1"><a href="#">Terms of Service</a></li>
                    <li class="mb-1"><a href="#">Data Processing</a></li>
                </ul>
            </div>

            <div class="col-6 col-lg-2 footer-link-group">
                <h6>Contact</h6>
                <ul class="list-unstyled" style="font-size:.9rem;">
                    <li class="mb-1"><a href="mailto:hello@masomoplus.co.ke">hello@masomoplus.co.ke</a></li>
                    <li class="mb-1"><a href="tel:+254700000000">+254 700 000 000</a></li>
                    <li class="mb-1 text-muted">Mon–Sat, 8am–6pm EAT</li>
                </ul>
            </div>
        </div>

        <div class="border-top pt-4 d-flex flex-wrap justify-content-between align-items-center gap-2" style="border-color:#1f2937 !important;">
            <p class="mb-0 small">
                &copy; {{ date('Y') }} MasomoPlus. All rights reserved. Built with ❤️ in Kenya.
            </p>
            <div class="d-flex align-items-center gap-2 small">
                <span>Payments secured by</span>
                <span class="fw-semibold text-white">Safaricom Daraja</span>
                <span>&amp;</span>
                <span class="fw-semibold text-white">PayStack</span>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(a => {
        a.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // Navbar shadow on scroll
    const navbar = document.querySelector('.navbar');
    window.addEventListener('scroll', () => {
        navbar.classList.toggle('shadow', window.scrollY > 10);
    });
</script>
</body>
</html>
