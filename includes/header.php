<header class="site-header" id="siteHeader">
  <div class="header-inner">
    <button class="lang-toggle" id="langToggle" type="button" aria-label="Change language">
      <span class="lang-active" data-lang="en">EN</span>
      <span class="lang-sep">/</span>
      <span data-lang="tr">TR</span>
    </button>

    <a href="#top" class="logo" aria-label="WitVortex home">
      <img src="<?php echo asset('assets/img/darklogo.png'); ?>" alt="WitVortex" width="386" height="56" class="logo-img logo-img-dark">
      <img src="<?php echo asset('assets/img/whitelogo.png'); ?>" alt="WitVortex" width="386" height="56" class="logo-img logo-img-white">
    </a>

    <button class="menu-toggle" id="menuToggle" type="button" aria-label="Open our services" aria-expanded="false">
      <span></span>
      <span></span>
      <span></span>
    </button>
  </div>
</header>

<div class="nav-overlay" id="navOverlay">
    <span class="section-label" data-tr="Hizmetlerimiz">Our Services</span>

    <div class="nav-services">
      <div class="nav-service-col">
        <h3 data-tr="Ürün">Product</h3>
        <ul>
          <li data-tr="Ürün Stratejisi">Product Strategy</li>
          <li data-tr="Topluluk &amp; Growth">Community &amp; Growth</li>
          <li data-tr="İçerik Otomasyonu">Content Automation</li>
          <li data-tr="MVP'den Ölçeğe">From MVP to Scale</li>
        </ul>
      </div>
      <div class="nav-service-col">
        <h3 data-tr="Deneyim">Experience</h3>
        <ul>
          <li data-tr="UI/UX Tasarım">UI/UX Design</li>
          <li data-tr="Marka Kimliği">Brand Identity</li>
          <li data-tr="Video &amp; Motion">Video &amp; Motion</li>
          <li data-tr="Sosyal Deneyim Tasarımı">Social Experience Design</li>
        </ul>
      </div>
      <div class="nav-service-col">
        <h3 data-tr="Teknoloji">Technology</h3>
        <ul>
          <li data-tr="Mobile (Flutter)">Mobile (Flutter)</li>
          <li data-tr="Backend (Node.js, PHP)">Backend (Node.js, PHP)</li>
          <li data-tr="AI destekli üretim hatları">AI-powered production pipelines</li>
          <li data-tr="REST API &amp; Auth (JWT/OTP)">REST API &amp; Auth (JWT/OTP)</li>
        </ul>
      </div>
    </div>

    <nav class="nav-links">
      <a href="#ne-yapiyoruz" class="nav-link" data-tr="Ne Yapıyoruz">What We Do</a>
      <a href="#yaklasimimiz" class="nav-link" data-tr="Yaklaşımımız">Our Approach</a>
      <a href="#iletisim" class="nav-link" data-tr="İletişim">Contact</a>
    </nav>
  </div>
</div>
