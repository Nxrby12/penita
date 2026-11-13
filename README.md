# Penița

![Teste](https://github.com/Nxrby12/penita/actions/workflows/tests.yml/badge.svg)

Platformă de self-publishing: autorii independenți își publică singuri cărțile (PDF + copertă), iar cititorii le cumpără online prin Stripe. Secțiunea **Blind Date with a Book** vinde cărți „la nevăzute”, doar după trei indicii.

## Funcționalități

- trei roluri: cititor, autor, administrator
- publicarea cărților, cu aprobare de către administrator
- catalog cu filtre pe gen, pagina fiecărei cărți, carusel pe prima pagină
- plăți cu Stripe Checkout și descărcarea cărților cumpărate
- Blind Date with a Book: titlul și coperta apar doar după cumpărare
- jurnal de activitate consultabil din panoul de administrare

## Tehnologii

PHP 8.3 (MVC propriu, fără framework) · MySQL / MariaDB · Apache · Composer · Stripe · PHPUnit · GitHub Actions

## Instalare locală (XAMPP)

1. Copiază proiectul în `C:\xampp\htdocs\biblioteca`.
2. Importă `database/schema.sql` în phpMyAdmin.
3. Copiază `config/config.example.php` ca `config/config.php` și completează-l.
4. Rulează `composer install`, apoi `php database/seed.php` pentru datele demonstrative.
5. Deschide `http://localhost/biblioteca`.

## Teste

```
composer test
```

Testele rulează automat la fiecare `git push`, prin GitHub Actions (tab-ul **Actions**).

## Securitate

Protecție CSRF, XSS și SQL Injection · upload limitat la 12 MB și extensii aprobate · verificare anti-steganografie · rate limiting (100 de cereri pe minut) · CAPTCHA · antete de securitate (CSP, HSTS etc.) · acces restricționat prin `.htaccess`.
