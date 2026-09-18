#!/usr/bin/env python3
"""
scripts/pagespeed_check.py
Technical SEO & PageSpeed Auditor for SSD Prayas
Supports both local URLs (localhost / 127.0.0.1) and live public domains.
"""

import sys
import os
import re
import json
import time
import urllib.request
import urllib.parse
import urllib.error
from html.parser import HTMLParser

class AuditHTMLParser(HTMLParser):
    def __init__(self):
        super().__init__()
        self.title = None
        self.meta_tags = []
        self.canonical = None
        self.headings = {'h1': [], 'h2': [], 'h3': [], 'h4': [], 'h5': [], 'h6': []}
        self.images = []
        self.links = []
        self.scripts = []
        self.stylesheets = []
        self.json_ld = []
        self.current_tag = None
        self.current_data = []
        self.dom_nodes = 0

    def handle_starttag(self, tag, attrs):
        self.dom_nodes += 1
        attrs_dict = dict(attrs)
        self.current_tag = tag

        if tag == 'title':
            self.current_data = []
        elif tag == 'meta':
            self.meta_tags.append(attrs_dict)
        elif tag == 'link':
            rel = attrs_dict.get('rel', '').lower()
            if 'canonical' in rel:
                self.canonical = attrs_dict.get('href')
            elif 'stylesheet' in rel:
                self.stylesheets.append(attrs_dict)
        elif tag in self.headings:
            self.current_data = []
        elif tag == 'img':
            self.images.append({
                'src': attrs_dict.get('src', ''),
                'alt': attrs_dict.get('alt'),
                'loading': attrs_dict.get('loading'),
                'width': attrs_dict.get('width'),
                'height': attrs_dict.get('height')
            })
        elif tag == 'a':
            self.links.append({
                'href': attrs_dict.get('href', ''),
                'text': '',
                'rel': attrs_dict.get('rel', '')
            })
        elif tag == 'script':
            script_type = attrs_dict.get('type', '').lower()
            if script_type == 'application/ld+json':
                self.current_data = []
            else:
                self.scripts.append({
                    'src': attrs_dict.get('src'),
                    'defer': 'defer' in attrs_dict,
                    'async': 'async' in attrs_dict
                })

    def handle_endtag(self, tag):
        content = "".join(self.current_data).strip()
        if tag == 'title' and not self.title:
            self.title = content
        elif tag in self.headings:
            self.headings[tag].append(content)
        elif tag == 'script' and self.current_tag == 'script':
            if content:
                try:
                    parsed = json.loads(content)
                    self.json_ld.append(parsed)
                except Exception:
                    pass
        self.current_tag = None

    def handle_data(self, data):
        if self.current_tag in ['title', 'script'] or self.current_tag in self.headings:
            self.current_data.append(data)


def clean_target_url(raw_url):
    cleaned = raw_url.strip()
    # Fix common duplicate protocol typos like https://http://
    cleaned = re.sub(r'^(https?:\/\/)+(https?:\/\/)+', r'\2', cleaned)
    if not cleaned.startswith(('http://', 'https://')):
        cleaned = 'http://' + cleaned
    return cleaned


def audit_page(target_url):
    parsed = urllib.parse.urlparse(target_url)
    is_localhost = parsed.hostname in ['localhost', '127.0.0.1']

    req = urllib.request.Request(
        target_url,
        headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) SSDPrayas-SEO-Auditor/1.0'}
    )

    t0 = time.time()
    try:
        with urllib.request.urlopen(req, timeout=15) as resp:
            raw_html = resp.read().decode('utf-8', errors='replace')
            status_code = resp.status
            headers = dict(resp.headers)
    except Exception as e:
        return {
            'success': False,
            'url': target_url,
            'error': str(e)
        }
    ttfb_ms = round((time.time() - t0) * 1000, 1)

    parser = AuditHTMLParser()
    try:
        parser.feed(raw_html)
    except Exception:
        pass

    # Meta tags extraction
    description = None
    robots = None
    keywords = None
    viewport = None
    og_tags = {}
    for m in parser.meta_tags:
        name = m.get('name', '').lower()
        prop = m.get('property', '').lower()
        val = m.get('content', '')
        if name == 'description':
            description = val
        elif name == 'robots':
            robots = val
        elif name == 'keywords':
            keywords = val
        elif name == 'viewport':
            viewport = val
        elif prop.startswith('og:'):
            og_tags[prop] = val

    # Schema analysis
    schemas_detected = []
    for s in parser.json_ld:
        if isinstance(s, dict):
            schemas_detected.append(s.get('@type', 'Unknown'))
        elif isinstance(s, list):
            for sub in s:
                if isinstance(sub, dict):
                    schemas_detected.append(sub.get('@type', 'Unknown'))

    # Images audit
    missing_alt = [img for img in parser.images if img['alt'] is None or img['alt'].strip() == '']
    missing_dimensions = [img for img in parser.images if not (img['width'] and img['height'])]
    missing_lazy = [img for img in parser.images if img['loading'] != 'lazy']

    # Performance & CWV indicators
    blocking_scripts = [s for s in parser.scripts if s['src'] and not (s['defer'] or s['async'])]

    # Scoring calculation
    score = 100
    deductions = []

    if not parser.title:
        score -= 20
        deductions.append("Missing <title> tag (-20)")
    elif len(parser.title) < 20 or len(parser.title) > 65:
        score -= 5
        deductions.append(f"Title length ({len(parser.title)} chars) outside optimal 30-60 range (-5)")

    if not description:
        score -= 15
        deductions.append("Missing meta description (-15)")
    elif len(description) < 70 or len(description) > 165:
        score -= 5
        deductions.append(f"Meta description length ({len(description)} chars) outside optimal 120-160 range (-5)")

    if len(parser.headings['h1']) == 0:
        score -= 15
        deductions.append("Missing H1 heading (-15)")
    elif len(parser.headings['h1']) > 1:
        score -= 5
        deductions.append(f"Multiple H1 tags ({len(parser.headings['h1'])}) detected (-5)")

    if not viewport:
        score -= 10
        deductions.append("Missing viewport tag (-10)")

    if not parser.canonical:
        score -= 5
        deductions.append("Missing rel=canonical tag (-5)")

    if len(schemas_detected) == 0:
        score -= 10
        deductions.append("Missing JSON-LD structured data (-10)")

    if len(missing_alt) > 0:
        penalty = min(10, len(missing_alt) * 2)
        score -= penalty
        deductions.append(f"{len(missing_alt)} images missing alt text (-{penalty})")

    if ttfb_ms > 800:
        score -= 10
        deductions.append(f"Slow TTFB ({ttfb_ms}ms > 800ms) (-10)")

    score = max(0, score)

    # Core recommendations
    recommendations = []
    if missing_alt:
        recommendations.append(f"Add descriptive alt attributes to {len(missing_alt)} images.")
    if missing_dimensions:
        recommendations.append(f"Add explicit width/height attributes to {len(missing_dimensions)} images to prevent layout shift (CLS).")
    if blocking_scripts:
        recommendations.append(f"Add 'defer' or 'async' attribute to {len(blocking_scripts)} external JavaScript files.")
    if not parser.canonical:
        recommendations.append("Add <link rel=\"canonical\"> tag pointing to the authoritative page URL.")
    if 'EducationalOrganization' not in schemas_detected and 'Organization' not in schemas_detected:
        recommendations.append("Implement EducationalOrganization schema for Google Knowledge Graph.")

    return {
        'success': True,
        'url': target_url,
        'environment': 'local' if is_localhost else 'live',
        'http_status': status_code,
        'response_time_ms': ttfb_ms,
        'page_size_kb': round(len(raw_html.encode('utf-8')) / 1024, 2),
        'dom_element_count': parser.dom_nodes,
        'overall_seo_score': score,
        'deductions': deductions,
        'technical_seo': {
            'title': parser.title,
            'title_length': len(parser.title) if parser.title else 0,
            'description': description,
            'description_length': len(description) if description else 0,
            'keywords': keywords,
            'robots': robots,
            'canonical': parser.canonical,
            'viewport': viewport,
            'schemas_detected': schemas_detected
        },
        'headings': {
            'h1_count': len(parser.headings['h1']),
            'h1_items': parser.headings['h1'],
            'h2_count': len(parser.headings['h2']),
            'h3_count': len(parser.headings['h3'])
        },
        'assets': {
            'total_images': len(parser.images),
            'missing_alt_count': len(missing_alt),
            'missing_dimensions_count': len(missing_dimensions),
            'missing_lazy_count': len(missing_lazy),
            'total_stylesheets': len(parser.stylesheets),
            'total_scripts': len(parser.scripts),
            'render_blocking_scripts': len(blocking_scripts)
        },
        'recommendations': recommendations
    }


def main():
    args = sys.argv[1:]
    is_json = '--json' in args
    clean_args = [a for a in args if a != '--json']

    if not clean_args:
        raw_url = 'http://localhost/ssdprayas/'
    else:
        raw_url = clean_args[0]

    target_url = clean_target_url(raw_url)
    results = audit_page(target_url)

    if is_json:
        print(json.dumps(results, indent=2))
    else:
        print("=" * 60)
        print("🚀 SSD PRAYAS — TECHNICAL SEO & PAGESPEED AUDIT")
        print("=" * 60)
        print(f"Target URL    : {results.get('url')}")
        print(f"Status Code   : {results.get('http_status')} (Response: {results.get('response_time_ms')}ms)")
        print(f"Overall Score : {results.get('overall_seo_score')}/100")
        print("-" * 60)
        print("Title Tag     :", results.get('technical_seo', {}).get('title'))
        print("Meta Desc     :", results.get('technical_seo', {}).get('description'))
        print("Robots Tag    :", results.get('technical_seo', {}).get('robots'))
        print("Canonical     :", results.get('technical_seo', {}).get('canonical'))
        print("Schemas       :", ", ".join(results.get('technical_seo', {}).get('schemas_detected', [])) or 'None')
        print("-" * 60)
        print("Headings      :", f"H1: {results.get('headings', {}).get('h1_count')}, H2: {results.get('headings', {}).get('h2_count')}, H3: {results.get('headings', {}).get('h3_count')}")
        print("Images        :", f"Total: {results.get('assets', {}).get('total_images')}, Missing Alt: {results.get('assets', {}).get('missing_alt_count')}, Missing Dims: {results.get('assets', {}).get('missing_dimensions_count')}")
        print("-" * 60)
        if results.get('recommendations'):
            print("Actionable Recommendations:")
            for idx, rec in enumerate(results['recommendations'], 1):
                print(f"  {idx}. {rec}")
        print("=" * 60)

if __name__ == '__main__':
    main()
