<header class="site-header" id="siteHeader">
  <div class="header-inner">
    <div class="lang-switch" role="group" aria-label="Language" data-tr-aria-label="Dil">
      <button type="button" class="lang-option" data-lang="en" lang="en" aria-pressed="true">EN</button>
      <span aria-hidden="true">/</span>
      <button type="button" class="lang-option" data-lang="tr" lang="tr" aria-pressed="false">TR</button>
    </div>

    <a href="#top" class="logo" aria-label="WitVortex">
      <img src="<?php echo asset('assets/img/darklogo.png'); ?>" alt="" width="386" height="56" class="logo-img logo-img-dark">
      <img src="<?php echo asset('assets/img/whitelogo.png'); ?>" alt="" width="386" height="56" class="logo-img logo-img-white">
    </a>

    <button class="menu-toggle" id="menuToggle" type="button" aria-label="Menu" data-tr-aria-label="Menü" aria-expanded="false" aria-controls="navOverlay">
      <span></span>
      <span></span>
      <span></span>
    </button>
  </div>
</header>

<div class="nav-overlay" id="navOverlay">
  <div class="nav-overlay-inner">
    <nav class="nav-links">
      <a href="#yaklasimimiz" class="nav-link" data-tr="Yaklaşımımız">Our Approach</a>
      <a href="#ne-yapiyoruz" class="nav-link" data-tr="Ne Yapıyoruz">What We Do</a>
      <a href="#iletisim" class="nav-link" data-tr="İletişim">Contact</a>
    </nav>

    <div class="nav-services-block">
      <span class="section-label" data-tr="Hizmetlerimiz">Our Services</span>

      <div class="nav-services">
        <div class="nav-service-col">
          <p class="nav-service-title" data-tr="Ürün">Product</p>
          <ul>
            <li data-tr="Ürün Stratejisi">Product Strategy</li>
            <li data-tr="Topluluk &amp; Growth">Community &amp; Growth</li>
            <li data-tr="İçerik Otomasyonu">Content Automation</li>
            <li data-tr="MVP'den Ölçeğe">From MVP to Scale</li>
          </ul>
        </div>
        <div class="nav-service-col">
          <p class="nav-service-title" data-tr="Deneyim">Experience</p>
          <ul>
            <li data-tr="UI/UX Tasarım">UI/UX Design</li>
            <li data-tr="Marka Kimliği">Brand Identity</li>
            <li data-tr="Video &amp; Motion">Video &amp; Motion</li>
            <li data-tr="Sosyal Deneyim Tasarımı">Social Experience Design</li>
          </ul>
        </div>
        <div class="nav-service-col">
          <p class="nav-service-title" data-tr="Teknoloji">Technology</p>
          <ul>
            <li data-tr="Mobile (Flutter)">Mobile (Flutter)</li>
            <li data-tr="Backend (Node.js, PHP)">Backend (Node.js, PHP)</li>
            <li data-tr="AI destekli üretim hatları">AI-powered production pipelines</li>
            <li data-tr="REST API &amp; Auth (JWT/OTP)">REST API &amp; Auth (JWT/OTP)</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
