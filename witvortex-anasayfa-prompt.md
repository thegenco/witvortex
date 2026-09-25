# WitVortex Ana Sayfa — Tasarım & Geliştirme Prompt'u

> Bu dosyayı olduğu gibi Claude Code / Antigravity'e (veya başka bir agentic IDE'ye) yapıştırıp "bu spesifikasyona göre ana sayfayı oluştur" diyebilirsin. Referans alınan site: lorn.com.tr (Lörn Creative Agency) — minimal, bold, hikaye anlatan, video ağırlıklı bir ajans sitesi. Aşağıda o dilin WitVortex'e uyarlanmış hali var.

---

## 1. Proje Bağlamı

WitVortex, bağımsız olarak birden fazla dijital ürün geliştiren bir yapı/stüdyo (kapalı davetli sosyal ağ uygulaması + otomatik Türkçe eğitici YouTube çocuk içeriği üretim hattı gibi projeler yürütülüyor). Ana sayfa, WitVortex'i klasik bir "kurumsal şirket sitesi" gibi değil, **Lörn'ün ajans sitesindeki gibi** kişilikli, iddialı, hikaye anlatan bir "dijital ürün stüdyosu" markası olarak sunmalı.

**Ton:** Minimal ama iddialı, biraz kendine güvenen/oyuncu, "biz farklı fikirler üretiyoruz" havası. Kurumsal klişelerden kaçın.

**Dil:** Türkçe (opsiyonel EN toggle, Lörn'deki gibi sağ üstte).

---

## 2. Teknik Yığın

- **PHP** (saf PHP, framework şart değil — istersen sayfayı `index.php` + `includes/` klasöründe bölümlere ayır: `header.php`, `hero.php`, `manifesto.php` vb., hepsini `index.php` içinde `include` ile birleştir)
- **HTML5 + CSS3** (Tailwind CDN kullanılabilir ama şart değil; istersen sade custom CSS de olur — Docker/PHP-MySQL geçmişine uygun şekilde tercih senin)
- **Vanilla JavaScript** — scroll animasyonları için `IntersectionObserver` (kütüphanesiz, hafif)
- Video için native `<video>` etiketi + lazy load (`preload="none"`, scroll'a girince JS ile play)
- İletişim formu: `contact.php` → basit `mail()` fonksiyonu veya mevcut MySQL altyapına küçük bir `messages` tablosu (zaten PHP/MySQL/Docker deneyimin var, admin panelindeki approval workflow mantığına benzer şekilde okunabilir)
- Fontlar: biri bold/display (başlıklar için), biri nötr sans (gövde metni için) — Google Fonts üzerinden self-host (CDN'e bağımlı kalmamak için `.woff2` dosyalarını lokale indirip kullan)
- Deploy: Docker ile bildiğin şekilde (nginx/apache + php-fpm), free-tier bir VPS veya hosting'e taşınabilir olsun

---

## 3. Sayfa Yapısı (Bölüm Bölüm)

### 3.1 Header / Navigasyon
- Sabit (sticky), transparan başlar, scroll'da arka plan alır
- Sol: WitVortex logosu (wordmark, iki varyant: açık/koyu zemin için)
- Sağ: dil toggle (TR/EN), hamburger menü (Lörn'de olduğu gibi minimal, sadece 3-4 link: Ne Yapıyoruz / Yaklaşımımız / İletişim)

### 3.2 Hero — Tam Ekran Video/Görsel
- Fullscreen background video (loop, muted by default) — masaüstü ve mobil için ayrı video/görsel kaynağı (Lörn'de `masa_ustu.mp4` / `mobil.mp4` ayrımı var, aynısını uygula)
- Üzerine büyük, bold başlık: **"WitVortex Neler Kurar:"** gibi bir açılış
- Sağ altta ses aç/kapa ikonu (video sessiz başlar, kullanıcı isterse açar)
- Video altında/üstünde 3 sütun halinde kısa yetkinlik listesi (Lörn'deki Strategy / Design / Technology üçlüsünün WitVortex versiyonu):

```
Ürün                          Deneyim                        Teknoloji
- Ürün Stratejisi              - UI/UX Tasarım                - Mobile (Flutter)
- Topluluk & Growth            - Marka Kimliği                - Backend (Node.js, PHP)
- İçerik Otomasyonu            - Video & Motion                - AI destekli üretim hatları
- MVP'den Ölçeğe                - Sosyal Deneyim Tasarımı       - REST API & Auth (JWT/OTP)
```

### 3.3 Manifesto Bölümü ("Hayır, ... değiliz" tarzı)
Lörn'ün "Hayır. IKEA'da bir kanepe değiliz" bölümünün WitVortex karşılığı — kısa, çarpıcı, bold tipografiyle satır satır açılan bir manifesto. Örnek taslak (istersen değiştir):

> Hayır. Bir yazılım fabrikası değiliz.
>
> Sırayla ihale bekleyen, şablon üstüne şablon basan bir ekip değiliz.
>
> Aklımıza takılan fikri, kimse istemeden önce inşa etmeyi severiz.
>
> Küçük başlayıp büyüyen ürünlere inanırız — büyük bütçeli, küçük etkili işlere değil.
>
> Bir uygulamayı sadece "çalışsın" diye değil, insanların gerçekten sevsin diye kurarız.
>
> Bugün bir prompt, yarın bir prototip, haftaya bir ürün.
>
> Geç, hiçten iyidir — ama biz zaten erkenciyiz.

Bu bölüm scroll ile satır satır fade-in/blur-in animasyonlu olsun (Framer Motion `whileInView`).

### 3.4 "WitVortex ne yapar?" — Anlatı Bölümü
Lörn'ün "ne yapar" bölümüne benzer, ama WitVortex'in çalışma biçimini anlatan 2-3 paragraflık, biraz felsefi/samimi bir metin blok. Yanında (Lörn'deki `hakkimizdaMob.png` gibi) tek büyük bir görsel/illustrasyon veya ürün ekran görüntüsü kolajı.

Örnek ton (kendi cümlelerinle uyarlanabilir):
- WitVortex fikri dinler, hızlıca prototipe çevirir, gerçek kullanıcıyla test eder.
- Karmaşık problemi basit arayüze indirger, "en anlaşılır hali en doğrusudur" der.
- Aynı anda birden fazla ürünü küçük ama disiplinli adımlarla ilerletir.

### 3.5 Ürünler / Projeler Vitrini
Lörn'deki marka logoları şeridinin (brand grid) WitVortex karşılığı: burada müşteri logosu yerine **kendi ürünlerini** kartlar halinde göster (henüz yayında olmasa da "yapım aşamasında" etiketiyle).

Her kart: ürün adı, tek cümlelik açıklama, durum etiketi (Geliştiriliyor / Kapalı Beta / Yayında), ufak bir ikon/mockup.

```
[Ürün 1: Sosyal Ağ Uygulaması]      Durum: Kapalı Beta
Arkadaşlık, profesyonel ağ ve içerik akışını tek çatı altında birleştiren
davetiye bazlı sosyal platform.

[Ürün 2: Otomatik Eğitim İçerik Hattı]   Durum: Geliştiriliyor
Türkçe çocuk eğitim videolarını uçtan uca otomatik üreten yapay zekâ
destekli prodüksiyon hattı.
```

Grid layout, hover'da hafif scale + glow animasyonu (Lörn'deki logo grid hover efektine benzer).

### 3.6 "Hadi Konuşalım" — İletişim Bölümü
Lörn'ün form yapısına birebir yakın:
- Sol: kısa davet metni ("Bir fikrin mi var, birlikte mi çalışalım, yoksa sadece merhaba mı demek istiyorsun?")
- Sağ: form
  - **Konu** dropdown/chip seçimi: Merhaba WitVortex! / Ürün Fikri / İş Birliği / Yatırım / Kariyer
  - Ad Soyad, E-posta, Mesaj alanları
  - Gönder butonu (submit sonrası "Gönderildi ✓" state'i)
- Altında iletişim bilgileri: e-posta, (varsa) telefon, şehir/konum
- Sosyal medya ikonları (Instagram, LinkedIn, X, YouTube — hangileri aktifse)

### 3.7 Footer
- Copyright © 2026 WitVortex. Tüm hakları saklıdır.
- Gizlilik/KVKK linki (varsa)
- Yukarı çık butonu

---

## 4. Görsel Kimlik Yönergeleri

- **Renk paleti:** Koyu zemin ağırlıklı (siyah/çok koyu lacivert), tek bir vurgu rengi (elektrik moru, neon yeşili veya turuncu gibi — "vortex" temasına uygun bir spiral/gradient vurgusu düşünülebilir). Lörn'de olduğu gibi az renk, çok kontrast.
- **Tipografi:** Büyük, bold, geniş letter-spacing başlıklar; gövde metni ince ve okunaklı. Başlıklarda satır satır scroll-reveal animasyonu.
- **Hareket:** Sayfa "sessiz sinema" gibi akmalı — video sessiz başlar, scroll'da yumuşak parallax, buton hover'larında mikro-etkileşimler.
- **Boşluk kullanımı:** Lörn'deki gibi cömert whitespace/negative space; sıkışık değil, nefes alan bir düzen.
- **WitVortex logosu/ismi:** Eğer "vortex" (girdap/spiral) temasını görsel olarak da yansıtmak istersen, hero'da veya loading state'inde ince bir spiral SVG animasyonu kullanılabilir.

---

## 5. Antigravity / Claude Code için Uygulama Notları (PHP)

1. Proje iskeleti:
   ```
   witvortex/
     index.php
     includes/
       header.php
       hero.php
       manifesto.php
       what-we-do.php
       products.php
       contact.php
       footer.php
     assets/
       css/style.css
       js/main.js
       video/ (masaustu.mp4, mobil.mp4)
       img/
     contact.php   (form POST hedefi)
     docker-compose.yml
   ```
2. `index.php` içinde her bölümü `<?php include 'includes/hero.php'; ?>` şeklinde birleştir — bakımı kolay, tek dosyada boğulmazsın.
3. CSS'i tek `style.css` içinde CSS custom properties (`--accent-color`, `--bg-dark` vb.) ile yönet, generic Bootstrap görünümünden kaçın.
4. `main.js` içinde `IntersectionObserver` ile scroll-reveal (manifesto satırlarının teker teker fade-in olması), video autoplay/sound-toggle mantığı.
5. Video yoksa placeholder olarak koyu bir CSS gradient + basit bir spiral SVG animasyonu kullan, gerçek video geldiğinde `<video>` etiketiyle değiştirilebilir bırak.
6. Mobilde ağır video yerine statik görsel/gradient kullan (performans için) — Lörn de bunu ayrı `masa_ustu.mp4` / `mobil.mp4` dosyalarıyla çözmüş, aynı yaklaşımı uygula.
7. `contact.php`: `$_POST` ile formu al, `mail()` ile e-posta gönder veya MySQL'e `messages` tablosuna yaz (zaten bildiğin PHP/MySQL/Docker düzenine uygun). Basit bir CSRF token da ekle.
8. Docker: mevcut Esse Kuaför projesinde kullandığın PHP/MySQL/Docker şablonunu referans alıp aynı `docker-compose.yml` yapısını burada da kullanabilirsin (php-fpm + nginx + mysql servisleri).
9. Performans: video `preload="none"`, resimler için `loading="lazy"`, fontlar `font-display: swap`, CSS/JS minify.

---

## 6. Kısa Özet (Tek Cümlede Brief)

> "lorn.com.tr'nin minimal, bold, hikaye anlatan ajans sitesi diline sahip; ama içeriği ve ürünleri WitVortex'in kendi dijital ürün stüdyosu kimliğine uyarlanmış, saf PHP + HTML/CSS + vanilla JavaScript ile yapılmış, Docker'da çalışan, tek sayfalık (single-page) bir ana sayfa istiyorum. Framework yok, hafif ve elimde tam kontrol olsun."
