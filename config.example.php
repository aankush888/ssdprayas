<?php
/**
 * SSD Prayas — configuration TEMPLATE.
 * Copy this file to config.php and fill in the real values.
 * config.php is git-ignored so credentials never reach the repository.
 * Sab site settings yahin se change karo, kisi aur file ko haath lagane ki zarurat nahi.
 */

// ---------- Database ----------
define('DB_HOST', 'localhost');
define('DB_NAME', 'ssdprayas');
define('DB_USER', 'root');
define('DB_PASS', '');            // set for your server

// ---------- Site ----------
define('SITE_NAME',    'SSD Prayas');
define('SITE_LOGO',    'ssdprayaslogo-1.png');   // wordmark used in header/footer
define('SITE_TAGLINE', 'Empowering the Next Generation with AI');
define('SITE_URL',     'http://localhost/ssdprayas');   // TODO: live domain daalna hai
define('SITE_EMAIL',     'info@ssdprayas.com');
define('SITE_EMAIL_ALT', 'jai@ssdprayas.com');

// ---------- Contact ----------
define('CONTACT_PHONE',    '+91 98104 50465');
define('CONTACT_PHONE_RAW','919810450465');   // WhatsApp same number
define('CONTACT_ADDRESS',  'The DM Tower, Danish Kunj, Kolar Road, Bhopal, Madhya Pradesh 462039');

// ---------- Social ----------
define('SOCIAL_FACEBOOK', 'https://www.facebook.com/ssdprayas');
define('SOCIAL_INSTAGRAM','https://www.instagram.com/ssdprayas/');
define('SOCIAL_LINKEDIN', 'https://www.linkedin.com/company/aiforschoolsindia/');
define('SOCIAL_YOUTUBE',  '#');

// ---------- Credits (note ke point 8, 9, 10) ----------
define('CREDIT_COPYRIGHT', 'SS Digimark');
define('CREDIT_COPYRIGHT_URL', '#');

// ---------- Uploads ----------
define('UPLOAD_BLOGS',   __DIR__ . '/assets/uploads/blogs');
define('UPLOAD_RESUMES', __DIR__ . '/assets/uploads/resumes');
define('MAX_UPLOAD_MB',  5);

// ---------- AI (Gemini) ----------
// API key ab code me nahi — environment variable ya niche wali line me daalo.
define('GEMINI_API_KEY', getenv('GEMINI_API_KEY') ?: '');

date_default_timezone_set('Asia/Kolkata');
