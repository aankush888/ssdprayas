# SSD Prayas — Website

AI skilling website for students (Class 3–12), educators and professionals,
plus an admin panel for the partner / educator / student / batch databases.

## Stack
Plain PHP 8 + PDO (MySQL), no framework, no build step. Runs on XAMPP.

## Layout
```
config.php              All site settings — DB, contact, social, Gemini key
includes/
  db.php                PDO connection
  functions.php         Helpers (escaping, uploads, CSRF, stats)
  header.php            Public site header
  footer.php            Public site footer
index.php               Homepage (single page, all sections)
about.php               About + leadership
careers.php             Educator hiring + resume upload
blogs.php               Blog listing
blog-detail.php         Single post (routed as /blog/{slug})
sitemap.php             Auto-generated sitemap
assets/css/main.css     Front-end design system
assets/js/main.js       Front-end behaviour
assets/uploads/         Blog images and resumes (resumes are not web-readable)
adminpanelweb/          Admin panel
_backup_old_site/       Previous "AI for Schools" site, kept for reference
```

## Database
`ssdprayas` — tables: `states`, `partners`, `educators`, `students`, `batches`,
`job_applications`, `enquiries`, `blogs`, `admin_users`.

## Admin panel
`/adminpanelweb/` — username `admin`.
The default password must be changed from Settings after first login.

## Configuration
Everything site-wide lives in `config.php`: phone, WhatsApp, address, email,
social links, and `GEMINI_API_KEY` for the AI blog writer.

# ssdprayas
