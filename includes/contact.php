<section class="contact" id="iletisim">
  <div class="contact-inner">
    <div class="contact-content">
      <h2 class="contact-heading" data-reveal data-tr="Hadi<br>Konuşalım.">Let's<br>Talk.</h2>

      <p class="contact-invite" data-reveal data-tr="WitVortex neler yapar/hangi hizmetleri sunar dinlemek, fikirleşmek ya da sadece merhaba demek için burayı kullanabilirsiniz.">You can use this space to hear what WitVortex does and which services it offers, to brainstorm, or just to say hello.</p>

      <form class="contact-form" id="contactForm" action="contact.php" method="POST" novalidate data-reveal>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

        <div class="form-grid">
          <div class="form-row">
            <label for="name" data-tr="Adınız ve Soyadınız">Your Full Name</label>
            <input type="text" id="name" name="name" autocomplete="name" required aria-describedby="name-error">
            <p class="field-error" id="name-error" hidden></p>
          </div>

          <div class="form-row">
            <label for="email" data-tr="E-posta Adresiniz">Your Email Address</label>
            <input type="email" id="email" name="email" autocomplete="email" required aria-describedby="email-error">
            <p class="field-error" id="email-error" hidden></p>
          </div>

          <div class="form-row">
            <label for="phone" data-tr='Telefon Numaranız <span class="label-optional">(isteğe bağlı)</span>'>Your Phone Number <span class="label-optional">(optional)</span></label>
            <input type="tel" id="phone" name="phone" autocomplete="tel">
          </div>

          <div class="form-row">
            <label for="topic" data-tr="Konu">Subject</label>
            <select id="topic" name="topic">
              <option value="Merhaba WitVortex!" data-tr="Merhaba WitVortex!">Hello WitVortex!</option>
              <option value="Ürün Fikri" data-tr="Ürün Fikri">Product Idea</option>
              <option value="İş Birliği" data-tr="İş Birliği">Collaboration</option>
              <option value="Yatırım" data-tr="Yatırım">Investment</option>
              <option value="Kariyer" data-tr="Kariyer">Career</option>
            </select>
          </div>
        </div>

        <div class="form-row">
          <label for="message" data-tr="Mesajınız">Your Message</label>
          <textarea id="message" name="message" rows="4" required aria-describedby="message-error"></textarea>
          <p class="field-error" id="message-error" hidden></p>
        </div>

        <div class="form-actions">
          <button type="submit" class="submit-btn" id="submitBtn">
            <span class="submit-spinner" aria-hidden="true"></span>
            <span class="submit-label">Send</span>
          </button>
          <p class="form-status" id="formStatus" role="status"></p>
        </div>
      </form>

      <div class="form-success" id="formSuccess" tabindex="-1" hidden>
        <h3 class="form-success-title" data-tr="Mesajınız gönderildi.">Message sent.</h3>
        <p class="form-success-text" data-tr="En kısa sürede dönüş yapacağız.">We'll get back to you soon.</p>
        <button type="button" class="form-reset" id="formReset" data-tr="Yeni mesaj gönder">Send another message</button>
      </div>
    </div>

    <div class="contact-image" data-reveal aria-hidden="true">
      <img src="assets/img/contact.png" alt="">
    </div>
  </div>
</section>
