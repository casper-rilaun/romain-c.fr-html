# 🌐 CV / Portfolio — Romain Chevrot

## 📚 Sommaire / Summary

- [À propos](#-à-propos)
- [Stack technique](#️-stack-technique)
- [Fonctionnalités](#-fonctionnalités)
- [Structure du projet](#-structure-du-projet)
- [Installation locale](#-installation-locale)
- [Licence](#-licence)

---

- [About (English)](#-about-this-project)
- [Tech stack](#tech-stack)
- [Features](#-features)
- [Local setup](#-local-setup)
- [License](#-license)

Site CV personnel développé pour présenter mon profil de **Consultant intégrateur DevOps**, mon parcours professionnel et mes compétences techniques.

Le site est volontairement **simple, rapide et sans framework**, avec une attention particulière portée à l'UX, à l'accessibilité et à la performance.

👉 En ligne : https://romain-c.fr

---

## 🇫🇷 À propos

Ce site a été conçu comme un **site CV professionnelle**, avec :
- une présentation du profil
- les expériences les plus significatives
- une sélection volontairement non exhaustive de compétences
- un formulaire de contact sécurisé
- un respect strict du RGPD (sans tracking, sans cookies)

Le design, la structure HTML/CSS/JS ainsi qu'une partie du contenu ont été **conçus et itérés avec l'aide de ChatGPT**, utilisé comme **assistant** de conception, de rédaction et de revue technique.

---

## 🛠️ Stack technique

- **HTML5** – structure sémantique
- **CSS3** – design responsive, thèmes clair/sombre via variables CSS
- **JavaScript (vanilla)** – animations, thème, UX
- **PHP** – formulaire de contact
- **PHPMailer** – envoi d'emails via SMTP (sécurisé)
- **Apache / .htaccess** – routing sans extensions (`/contact`, `/rgpd`, etc.)

---

## ✨ Fonctionnalités

- Animation de typing sur le nom
- Thème clair / sombre (auto + persistant)
- Design responsive (desktop / mobile)
- Formulaire de contact avec protections anti-spam
- Pages légales RGPD intégrées au design
- Aucun cookie, aucun tracker

---

## 📁 Structure du projet
```
/
├── index.html
├── contact.html
├── rgpd.html
├── app.js
├── style.css
├── contact.php
├── vendor/        (PHPMailer)
└── .htaccess
```
---

## 🚀 Installation locale

```bash
git clone https://github.com/r-chvrt/romain-c.fr.git
cd romain-c.fr
cp .env.example .env
composer require phpmailer/phpmailer
```

### Configuration de l’environnement

Pour des raisons de sécurité, le fichier `.env` **ne doit pas être accessible publiquement**.

Il est recommandé de placer le fichier `.env` **en dehors du dossier public**, par exemple :

```
/home/USER/.env
```

Puis de mettre à jour le chemin du fichier `.env` dans `contact.php` :

```php
$dotenv = Dotenv\Dotenv::createImmutable('/home/USER');
$dotenv->load();
```

### Variables à configurer

```
# SMTP
SMTP_HOST=smtp.example.fr
SMTP_PORT=587
SMTP_SECURE=tls
SMTP_USER=no-reply@ton-domaine.fr
SMTP_PASS=CHANGE_ME

# Mail
MAIL_TO=toi@ton-domaine.fr
MAIL_FROM=no-reply@ton-domaine.fr
MAIL_FROM_NAME=Ton sujet
MAIL_SUBJECT_PREFIX=[CV]
```

Servir les fichiers via un serveur web avec PHP activé.

⚠️ Le fichier `.env` ne doit jamais être commité dans un dépôt Git.

### URL Rewrite

Ce projet est conçu pour fonctionner avec des URLs propres (sans extension `.html`),
par exemple :
- `/`
- `/contact`
- `/rgpd`

#### Apache (.htaccess)

Créer ou compléter le fichier `.htaccess` à la racine du projet :

```
RewriteEngine On

# Rediriger la racine vers index.html
RewriteRule ^$ index.html [L]

# Supprimer l’extension .html des URLs
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.+)$ $1.html [L]

# Sécurisation du fichier .env
<Files ".env">
  Require all denied
</Files>
```

⚠️ Assurez-vous que le module `mod_rewrite` est activé.

---

#### Nginx

Exemple de configuration de bloc `server` :

```
server {
  listen 80;
  server_name example.com;

  root /var/www/romain-c.fr;
  index index.html;

  location / {
    try_files $uri $uri.html $uri/ =404;
  }

  # Sécurisation du fichier .env
  location ~ /\.env {
    deny all;
  }
}
```
---

## 📝 Licence

Projet personnel — librement consultable à des fins d'inspiration.
Toute réutilisation commerciale du contenu est déconseillée.

---
---

# 🇬🇧 About this project

Personal CV / portfolio website built to showcase my profile as a **DevOps Integration Consultant**, professional experience and technical skills.

The site is intentionally **minimal, fast and framework-free**, with a strong focus on usability, accessibility and performance.

👉 Live: https://romain-c.fr

---

This website was designed as a **professional one-page resume**, featuring:
- a clear profile introduction
- selected professional experiences
- a curated (non-exhaustive) list of technical skills
- a secure contact form
- full GDPR compliance (no tracking, no cookies)

The design, HTML/CSS/JS structure and part of the content were **designed and refined with the assistance of ChatGPT**, used as a technical, UX and writing assistant.

---

## Tech stack

Stack 

- **HTML5**
- **CSS3** 
- **Vanilla JavaScript**
- **PHP**
- **PHPMailer (SMTP)**
- **Apache / .htaccess**

---

## ✨ Features

- Typing animation on name
- Light / dark theme (system-aware & persistent)
- Responsive layout
- Secure contact form with anti-spam protections
- Integrated GDPR page
- No cookies, no analytics

---

## 🚀 Local setup
```bash
git clone https://github.com/your-user/romain-c.fr.git
cd romain-c.fr
cp .env.example .env
composer require phpmailer/phpmailer
```

### Environment configuration

For security reasons, the `.env` file **must not be publicly accessible**.

It is recommended to place the `.env` file **outside of the public web directory**, for example:

```
/home/USER/.env
```

Then update the `.env` path in `contact.php`:

```php
$dotenv = Dotenv\Dotenv::createImmutable('/home/USER');
$dotenv->load();
```

### Required environment variables

```
MAIL_HOST=
MAIL_PORT=
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM=
MAIL_TO=
```

Serve the files using a PHP-enabled web server.

⚠️ The `.env` file must never be committed in a Git repository.

### URL Rewrite 

This project is designed to work with clean URLs (without `.html` extensions),
for example:
- `/`
- `/contact`
- `/rgpd`

---

#### Apache (.htaccess)

Create or update the `.htaccess` file at the project root:

```
RewriteEngine On

# Redirect root to index.html
RewriteRule ^$ index.html [L]

# Remove .html extension from URLs
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.+)$ $1.html [L]

# Protect .env file
<Files ".env">
  Require all denied
</Files>
```

⚠️ Make sure the `mod_rewrite` module is enabled.

---

#### Nginx

Example `server` block configuration:

```
server {
  listen 80;
  server_name example.com;

  root /var/www/romain-c.fr;
  index index.html;

  location / {
    try_files $uri $uri.html $uri/ =404;
  }

  # Protect .env file
  location ~ /\.env {
    deny all;
  }
}
```

---

This configuration allows clean URLs while keeping environment files secure.


---

## 📝 License

Personal project — available for inspiration.
Commercial reuse of content is discouraged.
