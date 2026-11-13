# witvortex

witvortex.com için kaynak kod — PHP/Nginx/MySQL tabanlı kurumsal web sitesi.

## Klasör yapısı

```
├── index.php          # Ana sayfa
├── contact.php        # İletişim formu POST endpoint
├── includes/          # Sayfa bölümleri (hero, header, footer, manifesto, what-we-do, contact)
├── app/               # Bootstrap (config, db, helpers), auth, layout
├── admin/             # Admin paneli: giriş, mesaj listesi/detayı
├── assets/            # CSS, JS, görsel, video
├── database/          # schema.sql — veritabanı şeması
├── bin/               # CLI araçları (create-admin.php)
├── deploy/            # Sunucu deploy scripti
├── docker/            # nginx.conf ve php.Dockerfile
└── docker-compose.yml # Yerel geliştirme ortamı (nginx + php)
```

## Yerel geliştirme (Docker)

### 1. Önkoşul: `witvortex-config.php`

Bu dosya versiyon kontrolüne girmez; repo dışında tutulur. Örnek yapı:

```php
<?php
return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'witvortex',
        'user' => 'root',
        'pass' => 'secret',
    ],
    'admin' => [
        'session_name' => 'wv_admin',
    ],
];
```

Dosyayı repo kökünün bir üst dizinine (`../witvortex-config.php`) veya `/var/www/witvortex-config.php` olarak koy; ya da `WITVORTEX_CONFIG` ortam değişkeniyle yolu belirt.

### 2. MySQL

Docker Compose içinde MySQL servisi yoktur. Yerel MySQL, harici bir container veya cloud DB kullanılabilir.

### 3. Şemayı yükle

```bash
mysql -u root -p witvortex < database/schema.sql
```

### 4. Container'ları başlat

```bash
docker-compose up --build
```

### 5. Tarayıcıda aç

`http://localhost:8080`

### 6. İlk admin kullanıcısı

```bash
docker-compose exec php php bin/create-admin.php
```
