import urllib.request
import json
import re

url = 'http://localhost/ssdprayas/'
html = urllib.request.urlopen(url).read().decode('utf-8')

print('=== 1. META & HEAD DATA ===')
title = re.search(r'<title>(.*?)</title>', html, re.I | re.S)
print('Title:', title.group(1).strip() if title else 'None')
desc = re.search(r'<meta name="description" content="(.*?)"', html, re.I | re.S)
print('Description:', desc.group(1).strip() if desc else 'None')
canonical = re.search(r'<link rel="canonical" href="(.*?)"', html, re.I | re.S)
print('Canonical:', canonical.group(1).strip() if canonical else 'None')
robots = re.search(r'<meta name="robots" content="(.*?)"', html, re.I | re.S)
print('Robots:', robots.group(1).strip() if robots else 'None')

print('\n=== 2. HEADINGS HIERARCHY ===')
for tag in ['h1', 'h2', 'h3']:
    matches = re.findall(rf'<{tag}[^>]*>(.*?)</{tag}>', html, re.DOTALL | re.IGNORECASE)
    print(f'{tag.upper()} ({len(matches)}):')
    for m in matches[:6]:
        clean = re.sub(r'\s+', ' ', re.sub(r'<[^>]+>', '', m)).strip()
        print(f'  - {clean}')

print('\n=== 3. STRUCTURED DATA (JSON-LD) ===')
schemas = re.findall(r'<script type="application/ld\+json">(.*?)</script>', html, re.DOTALL | re.IGNORECASE)
for s in schemas:
    try:
        obj = json.loads(s)
        print('Type:', obj.get('@type'))
        if obj.get('@type') == 'EducationalOrganization':
            print('  Name:', obj.get('name'))
            print('  Address:', obj.get('address'))
        elif obj.get('@type') == 'FAQPage':
            print('  Questions count:', len(obj.get('mainEntity', [])))
    except Exception as e:
        print('Error:', e)

print('\n=== 4. ASSETS & CORE WEB VITALS AUDIT ===')
img_tags = re.findall(r'<img[^>]+>', html)
print(f'Total Images: {len(img_tags)}')
for idx, i in enumerate(img_tags):
    src = re.search(r'src="([^"]+)"', i)
    alt = re.search(r'alt="([^"]*)"', i)
    width = re.search(r'width="([^"]+)"', i)
    height = re.search(r'height="([^"]+)"', i)
    loading = re.search(r'loading="([^"]+)"', i)
    
    src_val = src.group(1).split('/')[-1] if src else 'unknown'
    alt_val = alt.group(1) if alt else 'MISSING'
    dims = f"{width.group(1)}x{height.group(1)}" if width and height else 'MISSING DIMS'
    lazy = loading.group(1) if loading else 'eager/none'
    
    # Check if hero image is lazy loaded (LCP anti-pattern)
    is_hero = idx < 2
    if is_hero and lazy == 'lazy':
        print(f'  ⚠️ LCP WARNING: Above fold image {src_val} has loading=lazy!')
    if not (width and height):
        print(f'  ⚠️ CLS WARNING: {src_val} missing dimensions!')
    if not alt or alt_val == '':
        print(f'  ⚠️ SEO WARNING: {src_val} missing alt text!')

print('\n=== 5. INTERNAL LINKS & ANCHOR TEXT AUDIT ===')
a_tags = re.findall(r'<a\s+[^>]*href="([^"]+)"[^>]*>(.*?)</a>', html, re.DOTALL | re.IGNORECASE)
print(f'Total Links: {len(a_tags)}')
vague_anchors = []
for href, inner in a_tags:
    clean_text = re.sub(r'<[^>]+>', '', inner).strip().lower()
    clean_text = re.sub(r'\s+', ' ', clean_text)
    if clean_text in ['click here', 'read more', 'read', 'more', 'here', 'view']:
        vague_anchors.append((href, clean_text))
print(f'Vague anchor texts found: {len(vague_anchors)}')
for href, text in vague_anchors[:5]:
    print(f'  - "{text}" -> {href}')
