import re
import os

with open('assets/css/main.css', 'r', encoding='utf-8') as f:
    css = f.read()

replacements = [
    ('home-hero.png', 'home-hero.webp'),
    ('career-hero.png', 'career-hero.webp'),
    ('programmes-hero.png', 'programmes-hero.webp'),
    ('about-hero.png', 'about-hero.webp'),
    ('govt-hero.png', 'govt-hero.webp'),
    ('contact-hero.png', 'contact-hero.webp')
]

for old, new in replacements:
    count = css.count(old)
    if count > 0:
        css = css.replace(old, new)
        print(f'Replaced {count} occurrences of {old} -> {new}')

with open('assets/css/main.css', 'w', encoding='utf-8') as f:
    f.write(css)

# Minify main.min.css
min_css = re.sub(r'/\*[\s\S]*?\*/', '', css)
min_css = re.sub(r'\s+', ' ', min_css)
min_css = re.sub(r'\s*([\{\}\:\;\,])\s*', r'\1', min_css)
min_css = re.sub(r';\}', '}', min_css).strip()

with open('assets/css/main.min.css', 'w', encoding='utf-8') as f:
    f.write(min_css)

print('Updated main.css and main.min.css successfully!')
