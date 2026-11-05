<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Learn about the NVSU QR Code-Integrated Tree Inventory and Monitoring System.">
    <title>About the Project | NVSU Tree Inventory</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/about.css') }}">
    <link rel="stylesheet" href="{{ asset('css/public-header.css') }}?v={{ filemtime(public_path('css/public-header.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/public-polish.css') }}">
    <link rel="stylesheet" href="{{ asset('css/public-footer.css') }}?v={{ filemtime(public_path('css/public-footer.css')) }}">
</head>
<body>
    <x-public-header active="about" />

    <main>
        <section class="about-hero">
            <div class="hero-image-placeholder" role="img" aria-label="Campus canopy image placeholder"></div><div class="hero-overlay"></div>
            <div class="hero-copy"><span class="eyebrow">NVSU Sustainability Initiative</span><h1>About the Project</h1><p>Protecting the living heritage of Nueva Vizcaya State University through accurate information, responsible monitoring, and accessible technology.</p></div>
        </section>

        <section class="intro-section content-width">
            <div class="intro-visual"><div class="project-image-placeholder" role="img" aria-label="Tree monitoring project image placeholder"><span>♣</span></div><div class="inventory-badge"><strong>{{ number_format($stats['mapped']) }}</strong><span>Mapped records<br>across NVSU</span></div></div>
            <div class="intro-copy"><span class="section-label">Our Project</span><h2>A digital record for every tree on campus</h2><p>The QR Code-Integrated Tree Inventory and Monitoring System is a centralized platform for recording, validating, monitoring, and sharing information about trees within Nueva Vizcaya State University.</p><p>Each registered tree receives a unique record and QR code. Students, personnel, researchers, and visitors can scan the code to access approved information, while authorized staff maintain accurate inventory and condition records.</p><div class="highlight"><span>✓</span><p><strong>Verified and dependable</strong><br>Public records are reviewed and approved by qualified personnel before publication.</p></div></div>
        </section>

        <section class="matters-section">
            <div class="content-width"><div class="center-heading"><span class="section-label">Purpose and Impact</span><h2>Why This System Matters</h2><p>Better information supports better stewardship of the university's natural resources.</p></div>
                <div class="benefit-grid">
                    <article><span class="feature-icon">♣</span><h3>Complete Inventory</h3><p>Maintain organized records for species, location, measurements, photographs, and tree condition.</p></article>
                    <article><span class="feature-icon">⌖</span><h3>Location Mapping</h3><p>See where registered trees are located and understand their distribution across campus areas.</p></article>
                    <article><span class="feature-icon">✓</span><h3>Verified Information</h3><p>Use review and approval workflows to keep public tree information accurate and trustworthy.</p></article>
                    <article><span class="feature-icon">↻</span><h3>Ongoing Monitoring</h3><p>Record inspections and changes over time to support maintenance and conservation decisions.</p></article>
                </div>
            </div>
        </section>

        <section class="workflow-section content-width">
            <div class="center-heading"><span class="section-label">Simple Public Access</span><h2>How the QR System Works</h2><p>From a physical tree to verified information in three simple steps.</p></div>
            <div class="workflow">
                <article><span class="number">01</span><div class="workflow-icon">⌖</div><h3>Find a Registered Tree</h3><p>Look for an inventory marker or QR sign attached near a tree on campus.</p></article><span class="connector">→</span>
                <article><span class="number">02</span><div class="workflow-icon">▦</div><h3>Scan Its QR Code</h3><p>Use a mobile phone camera to scan the unique code assigned to that tree.</p></article><span class="connector">→</span>
                <article><span class="number">03</span><div class="workflow-icon">▤</div><h3>Explore Its Record</h3><p>View approved details about its species, condition, location, and significance.</p></article>
            </div>
        </section>

        <section class="collaboration-section">
            <div class="content-width collaboration-grid"><div><span class="section-label light">Built Through Collaboration</span><h2>Technology serving environmental stewardship</h2><p>This project brings together technology and forestry expertise to create a practical tool for education, research, campus planning, and long-term tree conservation.</p><a href="{{ route('trees.index') }}">Explore the Tree Directory &nbsp; →</a></div>
                <div class="partners"><article><span class="partner-logo"></span><div><strong>College of Information<br>Technology Education</strong><p>System design and development</p></div></article><article><img class="partner-logo forestry-logo" src="{{ asset('images/cferm.png') }}" alt="College of Forestry, Environment and Resources Management logo"><div><strong>College of Forestry</strong><p>Tree validation and stewardship</p></div></article></div>
            </div>
        </section>

        <section class="cta content-width"><div><span>♣</span><div><h2>Start exploring NVSU's trees</h2><p>Discover verified tree records or locate registered trees around campus.</p></div></div><div><a class="primary" href="{{ route('trees.index') }}">Browse Trees</a><a href="{{ route('campus-map') }}">Open Campus Map</a></div></section>
    </main>

    <x-public-footer />
</body>
</html>
