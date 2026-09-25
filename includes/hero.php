<section class="hero" id="top">
  <div class="hero-media">
    <video
      id="heroVideoDesktop"
      class="hero-video hero-video-desktop"
      autoplay
      muted
      loop
      playsinline
      preload="auto"
    >
      <source src="assets/video/hero.mp4" type="video/mp4">
    </video>
    <video
      id="heroVideoMobile"
      class="hero-video hero-video-mobile"
      autoplay
      muted
      loop
      playsinline
      preload="auto"
    >
      <source src="assets/video/hero.mp4" type="video/mp4">
    </video>

    <div class="hero-fallback" id="heroFallback"></div>

    <button class="sound-toggle" id="soundToggle" type="button" aria-label="Sesi aç/kapat" aria-pressed="false">
      <svg class="icon-muted" viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-.77-3.29-2-4.35v8.69c1.23-1.06 2-2.57 2-4.34zM14.5 4.6v1.79c2.89 1 5 3.76 5 6.9 0 3.14-2.11 5.9-5 6.9v1.79c3.89-1.09 6.5-4.5 6.5-8.69s-2.61-7.6-6.5-8.69z"/></svg>
    </button>

    <button class="scroll-hint" id="scrollHint" type="button" aria-label="Aşağı kaydır">
      <span class="scroll-hint-mouse">
        <span class="scroll-hint-dot"></span>
      </span>
    </button>
  </div>
</section>
