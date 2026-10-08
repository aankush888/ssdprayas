<?php
require_once __DIR__ . '/_auth.php';
require_admin();
require_csrf();

$id  = (int)($_GET['id'] ?? 0);
$row = [
    'id' => 0, 'title' => '', 'slug' => '', 'tag' => '', 'excerpt' => '', 'content' => '',
    'image' => 'assets/img/blog-1.jpg', 'image_alt' => '', 'author' => 'SSD Prayas Team', 'status' => 'published',
    'meta_title' => '', 'meta_description' => '', 'meta_keywords' => '',
    'schema_article' => '', 'schema_faq' => '',
    'og_title' => '', 'og_description' => '', 'og_image' => '',
    'twitter_title' => '', 'twitter_description' => '', 'twitter_image' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $edit_id = (int)($_POST['id'] ?? 0);
    $title   = trim($_POST['title'] ?? '');
    $slug    = slugify($_POST['slug'] ?? '') ?: slugify($title);

    $image = trim($_POST['existing_image'] ?? '') ?: 'assets/img/blog-1.jpg';
    if (!empty($_POST['ai_image_url'])) {
        $candidate = filter_var($_POST['ai_image_url'], FILTER_VALIDATE_URL);
        if ($candidate && str_starts_with($candidate, 'https://')) {
            $image = $candidate;
        }
    }
    $uploaded = handle_upload('image', UPLOAD_BLOGS, ['jpg', 'jpeg', 'png', 'webp', 'gif'], 'blog-');
    if ($uploaded !== null) {
        $image = $uploaded;
    }

    $og_image = trim($_POST['og_image'] ?? '');
    $uploaded_og = handle_upload('og_image_file', UPLOAD_BLOGS, ['jpg', 'jpeg', 'png', 'webp'], 'og-');
    if ($uploaded_og !== null) {
        $og_image = $uploaded_og;
    }

    $twitter_image = trim($_POST['twitter_image'] ?? '');
    $uploaded_tw = handle_upload('twitter_image_file', UPLOAD_BLOGS, ['jpg', 'jpeg', 'png', 'webp'], 'tw-');
    if ($uploaded_tw !== null) {
        $twitter_image = $uploaded_tw;
    }

    $data = [
        'title'               => $title,
        'slug'                => $slug,
        'tag'                 => trim($_POST['tag'] ?? ''),
        'excerpt'             => trim($_POST['excerpt'] ?? ''),
        'content'             => $_POST['content'] ?? '',
        'image'               => $image,
        'image_alt'           => trim($_POST['image_alt'] ?? ''),
        'author'              => trim($_POST['author'] ?? '') ?: 'SSD Prayas Team',
        'status'              => enum_or($_POST['status'] ?? '', ['published', 'draft'], 'published'),
        'meta_title'          => trim($_POST['meta_title'] ?? ''),
        'meta_description'    => trim($_POST['meta_description'] ?? ''),
        'meta_keywords'       => trim($_POST['meta_keywords'] ?? ''),
        'schema_article'      => trim($_POST['schema_article'] ?? ''),
        'schema_faq'          => trim($_POST['schema_faq'] ?? ''),
        'og_title'            => trim($_POST['og_title'] ?? ''),
        'og_description'      => trim($_POST['og_description'] ?? ''),
        'og_image'            => $og_image,
        'twitter_title'       => trim($_POST['twitter_title'] ?? ''),
        'twitter_description' => trim($_POST['twitter_description'] ?? ''),
        'twitter_image'       => $twitter_image,
    ];

    if ($data['title'] === '' || $data['slug'] === '') {
        flash_set('error', 'Title is required.');
        header('Location: blog-form.php' . ($edit_id ? '?id=' . $edit_id : ''));
        exit;
    }

    try {
        if ($edit_id) {
            $sql = "UPDATE blogs SET " . implode(', ', array_map(fn($k) => "$k = ?", array_keys($data))) . " WHERE id = ?";
            $pdo->prepare($sql)->execute([...array_values($data), $edit_id]);
            flash_set('success', 'Post updated successfully.');
        } else {
            $cols  = implode(', ', array_keys($data));
            $marks = implode(', ', array_fill(0, count($data), '?'));
            $pdo->prepare("INSERT INTO blogs ($cols) VALUES ($marks)")->execute(array_values($data));
            flash_set('success', 'Post published successfully.');
        }
        header('Location: blogs.php');
        exit;
    } catch (PDOException $e) {
        error_log('Blog save failed: ' . $e->getMessage());
        $msg = str_contains($e->getMessage(), 'Duplicate')
             ? 'That slug is already used by another post. Change the slug.'
             : 'Could not save the post. Please try again.';
        flash_set('error', $msg);
        header('Location: blog-form.php' . ($edit_id ? '?id=' . $edit_id : ''));
        exit;
    }
}

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM blogs WHERE id = ?");
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if (!$found) { flash_set('error', 'Post not found.'); header('Location: blogs.php'); exit; }
    $row = array_merge($row, $found);
}

$admin_title  = $id ? 'Edit Post' : 'Write Post';
$admin_active = 'blogs';
$admin_sub    = $id ? 'Editing "' . $row['title'] . '"' : 'Write a new article with complete SEO, Schemas & Social Cards.';
include __DIR__ . '/_layout.php';
?>

<style>
  /* AI Floating Trigger */
  .ai-fab {
      position: fixed; right: 26px; bottom: 26px; z-index: 70;
      width: 58px; height: 58px; display: grid; place-items: center; border-radius: 50%;
      background: linear-gradient(135deg, #4285F4, #34A853, #FBBC05, #EA4335);
      color: #fff; font-size: 21px; cursor: pointer; border: none;
      box-shadow: 0 12px 30px rgba(0,0,0,.22); transition: transform .25s ease;
  }
  .ai-fab:hover { transform: scale(1.08) rotate(12deg); }
  .ai-panel {
      position: fixed; top: 0; right: -460px; width: 440px; max-width: 92vw; height: 100vh;
      background: #fff; border-left: 1px solid var(--line); box-shadow: -12px 0 40px rgba(11,18,32,.12);
      z-index: 80; transition: right .35s cubic-bezier(.4,0,.2,1);
      padding: 30px; display: flex; flex-direction: column; overflow-y: auto;
  }
  .ai-panel.is-open { right: 0; }
  .ai-panel h2 { font-size: 19px; margin-bottom: 4px; }
  .ai-panel .close { margin-left: auto; cursor: pointer; font-size: 19px; color: var(--muted); background: none; border: none; }
  .ai-head { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 22px; }
  .ai-out { border: 1px solid var(--line); border-radius: 14px; padding: 18px; margin-top: 18px; display: none; font-size: 14px; }
  .ai-out.is-on { display: block; }
  .spin { width: 16px; height: 16px; border: 2.5px solid rgba(255,255,255,.35); border-top-color: #fff;
          border-radius: 50%; animation: sp .8s linear infinite; display: none; }
  @keyframes sp { to { transform: rotate(360deg); } }

  /* Control Panel Tabs Navigation */
  .blog-tab-nav {
      display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 22px;
      background: var(--bg); padding: 6px; border-radius: 16px; border: 1px solid var(--line);
  }
  .btn-tab {
      display: inline-flex; align-items: center; gap: 8px;
      padding: 10px 18px; border-radius: 12px; font-size: 13.5px; font-weight: 600;
      color: var(--muted); background: transparent; border: none; cursor: pointer;
      transition: all .2s ease;
  }
  .btn-tab:hover { color: var(--ink); background: var(--line-soft); }
  .btn-tab.is-active {
      color: #fff; background: var(--brand);
      box-shadow: 0 4px 12px rgba(26,115,232,0.25);
  }
  .tab-pane { display: none; }
  .tab-pane.is-active { display: block; animation: tabFade .25s ease; }
  @keyframes tabFade { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

  /* SERP Snippet Preview */
  .serp-card {
      background: #ffffff; border: 1px solid var(--line); border-radius: 14px;
      padding: 18px 20px; margin-top: 16px; font-family: Arial, sans-serif;
      box-shadow: 0 4px 14px rgba(0,0,0,0.03);
  }
  .serp-url { color: #202124; font-size: 13px; line-height: 1.3; display: flex; align-items: center; gap: 6px; margin-bottom: 4px; }
  .serp-url cite { font-style: normal; color: #4d5156; }
  .serp-title { color: #1a0dab; font-size: 19px; line-height: 1.3; font-weight: 400; text-decoration: none; cursor: pointer; margin-bottom: 4px; display: inline-block; }
  .serp-title:hover { text-decoration: underline; }
  .serp-desc { color: #4d5156; font-size: 13.5px; line-height: 1.5; margin: 0; word-break: break-word; }

  /* Monospace Code Editor Area for Schema */
  .code-textarea {
      font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
      font-size: 13px; line-height: 1.55; background: #0f172a; color: #e2e8f0;
      border-radius: 12px; padding: 14px; border: 1px solid #334155; min-height: 150px;
      resize: vertical;
  }
  .code-textarea:focus { border-color: var(--brand); outline: none; box-shadow: 0 0 0 3px rgba(26,115,232,0.25); }

  /* Live Social Share Card Preview */
  .social-preview-box {
      border: 1px solid var(--line); border-radius: 14px; overflow: hidden; background: #fff;
      max-width: 480px; margin-top: 14px; box-shadow: 0 6px 18px rgba(0,0,0,0.04);
  }
  .sp-thumb { width: 100%; height: 210px; object-fit: cover; background: #f1f5f9; display: block; }
  .sp-body { padding: 14px 16px; }
  .sp-domain { font-size: 11px; text-transform: uppercase; color: var(--muted); font-weight: 700; letter-spacing: .04em; margin-bottom: 4px; }
  .sp-title { font-size: 15px; font-weight: 700; color: var(--ink); margin-bottom: 6px; line-height: 1.35; }
  .sp-desc { font-size: 13px; color: var(--muted); margin: 0; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

  .counter-badge {
      font-size: 11.5px; font-weight: 700; padding: 2px 7px; border-radius: 6px;
      background: var(--line-soft); color: var(--muted); margin-left: auto;
  }
  .counter-badge.ok { background: #e6f4ea; color: #137333; }
  .counter-badge.warn { background: #fef7e0; color: #b06000; }
  .counter-badge.over { background: #fce8e6; color: #c5221f; }

  /* Normal Editor Styles - 0 External Dependencies */
  .normal-editor-container {
      border: 1px solid var(--line, #cbd5e1);
      border-radius: 12px;
      overflow: hidden;
      background: #ffffff;
      box-shadow: 0 2px 8px rgba(0,0,0,0.03);
      transition: border-color .2s, box-shadow .2s;
  }
  .normal-editor-container:focus-within {
      border-color: var(--brand, #1a73e8);
      box-shadow: 0 0 0 3px rgba(26,115,232,0.18);
  }
  .normal-editor-toolbar {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 8px;
      padding: 10px 14px;
      background: #f8fafc;
      border-bottom: 1px solid #e2e8f0;
  }
  .normal-editor-toolbar .tb-group {
      display: inline-flex;
      align-items: center;
      background: #ffffff;
      border: 1px solid #cbd5e1;
      border-radius: 8px;
      padding: 2px;
      gap: 2px;
  }
  .ed-btn {
      background: transparent;
      border: none;
      border-radius: 6px;
      padding: 6px 10px;
      font-size: 13px;
      font-weight: 600;
      color: #334155;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 5px;
      min-width: 32px;
      height: 32px;
      transition: all .15s ease;
  }
  .ed-btn:hover {
      background: #e2e8f0;
      color: #0f172a;
  }
  .normal-editor-textarea {
      width: 100%;
      min-height: 480px;
      padding: 18px 20px;
      border: none;
      outline: none;
      resize: vertical;
      font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
      font-size: 15px;
      line-height: 1.75;
      color: #1e293b;
      box-sizing: border-box;
      background: #ffffff;
      display: block;
  }
  .normal-editor-preview {
      width: 100%;
      min-height: 480px;
      padding: 22px 24px;
      background: #ffffff;
      box-sizing: border-box;
      font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
      font-size: 15px;
      line-height: 1.75;
      color: #1e293b;
      overflow-y: auto;
  }
  .normal-editor-preview h1 { font-size: 26px; font-weight: 800; color: #0b132b; margin: 20px 0 10px; border-bottom: 2px solid #ebf3fe; padding-bottom: 6px; }
  .normal-editor-preview h2 { font-size: 21px; font-weight: 700; color: #1a73e8; margin: 18px 0 8px; }
  .normal-editor-preview h3 { font-size: 18px; font-weight: 700; color: #1e293b; margin: 16px 0 6px; }
  .normal-editor-preview h4 { font-size: 16px; font-weight: 600; color: #475569; margin: 14px 0 6px; }
  .normal-editor-preview p { margin-bottom: 14px; }
  .normal-editor-preview ul, .normal-editor-preview ol { padding-left: 24px; margin-bottom: 14px; }
  .normal-editor-preview blockquote { border-left: 4px solid #1a73e8; padding-left: 14px; color: #475569; font-style: italic; margin: 14px 0; }
  .normal-editor-footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 8px 16px;
      background: #f8fafc;
      border-top: 1px solid #e2e8f0;
      font-size: 12px;
      color: #64748b;
  }
</style>

<!-- Form Hero Header -->
<div class="mod-hero-strip">
  <div class="mod-hero-left">
    <div class="mod-hero-icon mhi-purple">
      <i class="fas fa-pen-nib"></i>
    </div>
    <div>
      <h2 class="mod-hero-title"><?= e($admin_title) ?></h2>
      <p class="mod-hero-sub">Complete Blog Control Panel: Content, SEO, Schemas, Social Tags, and Media Management.</p>
    </div>
  </div>
  <div class="mod-hero-actions">
    <a href="blogs.php" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Back to Articles</a>
  </div>
</div>

<form method="POST" enctype="multipart/form-data" id="blogForm">
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
  <input type="hidden" name="existing_image" value="<?= e($row['image']) ?>">
  <input type="hidden" name="ai_image_url" id="aiImageUrl">

  <!-- Control Panel Tab Bar -->
  <div class="blog-tab-nav">
    <button type="button" class="btn-tab is-active" data-tab="tab-content">
      <i class="fas fa-file-lines"></i> 1. Content & Editor (H1-H4)
    </button>
    <button type="button" class="btn-tab" data-tab="tab-seo">
      <i class="fas fa-magnifying-glass-chart"></i> 2. SEO & Meta Tags
    </button>
    <button type="button" class="btn-tab" data-tab="tab-schema">
      <i class="fas fa-code"></i> 3. Schema Code (Article & FAQ)
    </button>
    <button type="button" class="btn-tab" data-tab="tab-social">
      <i class="fas fa-share-nodes"></i> 4. Open Graph & Twitter
    </button>
  </div>

  <!-- ==================== TAB 1: Content & Editor ==================== -->
  <div class="tab-pane is-active" id="tab-content">
    <div class="card">
      <div class="card-head">
        <h2><i class="fas fa-feather-pointed text-purple" style="font-size:16px"></i> Article Composer & Headings</h2>
        <span class="spacer"></span>
        <span class="badge b-purple"><i class="fas fa-heading"></i> H1, H2, H3, H4 Supported</span>
      </div>
      <div class="card-body">
        <div class="form-grid">
          
          <div class="fg full">
            <label for="b-title">Post Title <span class="req">*</span></label>
            <input type="text" id="b-title" name="title" required value="<?= e($row['title']) ?>" placeholder="A clear, impactful headline for search & readers">
          </div>

          <div class="fg">
            <label for="b-slug">URL Slug <span class="hint">(e.g. ai-in-schools-india)</span></label>
            <input type="text" id="b-slug" name="slug" value="<?= e($row['slug']) ?>" placeholder="auto-generated from title">
          </div>

          <div class="fg">
            <label for="b-tag">Category Tag</label>
            <input type="text" id="b-tag" name="tag" value="<?= e($row['tag']) ?>" placeholder="e.g. AI, Future Skills, Innovation, Policy">
          </div>

          <div class="fg">
            <label for="b-author">Author Name</label>
            <input type="text" id="b-author" name="author" value="<?= e($row['author']) ?>">
          </div>

          <div class="fg">
            <label for="b-status">Publication Status</label>
            <select id="b-status" name="status">
              <option value="published" <?= $row['status'] === 'published' ? 'selected' : '' ?>>Published (Live on Website)</option>
              <option value="draft"     <?= $row['status'] === 'draft' ? 'selected' : '' ?>>Draft (Saved Privately)</option>
            </select>
          </div>

          <!-- Featured Image & Alt Text -->
          <div class="fg full" style="background:var(--line-soft);padding:18px;border-radius:16px;border:1px solid var(--line)">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;flex-wrap:wrap;gap:8px">
              <label style="margin:0;font-weight:700;font-size:14px"><i class="fas fa-image text-brand"></i> Featured Image & Alt Text</label>
              <span class="badge b-green"><i class="fas fa-universal-access"></i> SEO & Accessibility Ready</span>
            </div>

            <div style="display:flex;align-items:flex-start;gap:20px;flex-wrap:wrap">
              <div>
                <img id="imgPreview" src="<?= e(url($row['image'])) ?>" alt="<?= e($row['image_alt'] ?: $row['title']) ?>"
                     style="width:170px;height:105px;object-fit:cover;border-radius:12px;border:1.5px solid var(--line);background:#fff;display:block">
              </div>

              <div style="flex:1;min-width:260px;display:flex;flex-direction:column;gap:12px">
                <div>
                  <label for="b-image-file" style="font-size:13px;color:var(--ink);margin-bottom:5px">Upload Image (JPG, PNG, WebP &lt; <?= (int)MAX_UPLOAD_MB ?>MB)</label>
                  <input type="file" id="b-image-file" name="image" accept="image/*" style="width:100%">
                </div>

                <div>
                  <label for="b-image-alt" style="font-size:13px;color:var(--ink);margin-bottom:5px">
                    Image Alt Text <span class="hint">(Crucial for Google Image Search & Screen Readers)</span>
                  </label>
                  <input type="text" id="b-image-alt" name="image_alt" value="<?= e($row['image_alt']) ?>" placeholder="Describe what is in the image (e.g. Indian students collaborating with AI tools in computer lab)">
                </div>
              </div>
            </div>
          </div>

          <div class="fg full">
            <label for="b-excerpt">Short Excerpt / Summary <span class="hint">(Shown on blog listing cards)</span></label>
            <textarea id="b-excerpt" name="excerpt" style="min-height:80px" placeholder="One or two compelling sentences summarizing the article"><?= e($row['excerpt']) ?></textarea>
          </div>

          <!-- Article Content: Normal Editor (Zero External Dependencies) -->
          <div class="fg full">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
              <label for="b-content" style="margin:0">Article Content <span class="hint">(Direct Normal Editor &bull; Full HTML & Text Support)</span></label>
              <span class="badge b-blue" style="font-size:11px"><i class="fas fa-feather-pointed"></i> Zero-Lag Editor</span>
            </div>

            <div class="normal-editor-container">
              <!-- Editor Quick Toolbar -->
              <div class="normal-editor-toolbar">
                <div class="tb-group" title="Headings">
                  <button type="button" class="ed-btn" data-tag="h1" title="Heading 1"><b>H1</b></button>
                  <button type="button" class="ed-btn" data-tag="h2" title="Heading 2"><b>H2</b></button>
                  <button type="button" class="ed-btn" data-tag="h3" title="Heading 3"><b>H3</b></button>
                  <button type="button" class="ed-btn" data-tag="h4" title="Heading 4"><b>H4</b></button>
                  <button type="button" class="ed-btn" data-tag="p" title="Paragraph"><i class="fas fa-paragraph"></i></button>
                </div>

                <div class="tb-group" title="Text Styles">
                  <button type="button" class="ed-btn" data-tag="strong" title="Bold"><b>B</b></button>
                  <button type="button" class="ed-btn" data-tag="em" title="Italic"><i>I</i></button>
                  <button type="button" class="ed-btn" data-tag="u" title="Underline"><u>U</u></button>
                  <button type="button" class="ed-btn" data-tag="blockquote" title="Quote"><i class="fas fa-quote-left"></i></button>
                </div>

                <div class="tb-group" title="Lists & Breaks">
                  <button type="button" class="ed-btn" data-action="ul" title="Bullet List"><i class="fas fa-list-ul"></i></button>
                  <button type="button" class="ed-btn" data-action="ol" title="Numbered List"><i class="fas fa-list-ol"></i></button>
                  <button type="button" class="ed-btn" data-action="br" title="Line Break">&lt;br&gt;</button>
                </div>

                <div class="tb-group" title="Links & Media">
                  <button type="button" class="ed-btn" data-action="link" title="Insert Link"><i class="fas fa-link"></i> Link</button>
                  <button type="button" class="ed-btn" data-action="img" title="Insert Image"><i class="fas fa-image"></i> Image</button>
                </div>

                <div style="margin-left:auto;display:flex;gap:6px">
                  <button type="button" class="ed-btn" id="btnTogglePreview" style="background:#e8f0fe;color:#1a73e8;border:1px solid #bfdbfe;font-size:12px">
                    <i class="fas fa-eye"></i> <span id="previewBtnText">Live Preview</span>
                  </button>
                </div>
              </div>

              <!-- Main Content Textarea -->
              <textarea id="b-content" name="content" class="normal-editor-textarea" placeholder="Yahan apna article likhein ya paste karein... HTML tags aur normal text dono supported hain."><?= e($row['content']) ?></textarea>

              <!-- Live Preview Output -->
              <div id="contentPreviewBox" class="normal-editor-preview" style="display:none"></div>

              <!-- Editor Footer -->
              <div class="normal-editor-footer">
                <div style="display:flex;gap:14px;align-items:center">
                  <span id="edWordCount" style="font-weight:700;color:#0f172a">0 words</span>
                  <span id="edCharCount" style="color:#64748b">0 characters</span>
                </div>
                <div>
                  <span class="hint" style="color:#64748b"><i class="fas fa-info-circle"></i> Kisi bhi text ko select karke H1, H2, ya B par click karke format karein</span>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- ==================== TAB 2: SEO & Meta Tags ==================== -->
  <div class="tab-pane" id="tab-seo">
    <div class="card">
      <div class="card-head">
        <h2><i class="fas fa-magnifying-glass-chart text-brand" style="font-size:16px"></i> Search Engine Optimization (SEO)</h2>
        <span class="spacer"></span>
        <span class="badge b-blue"><i class="fab fa-google"></i> SERP Preview</span>
      </div>
      <div class="card-body">
        
        <!-- Live Google Search SERP Simulator -->
        <div class="serp-card">
          <div class="serp-url">
            <i class="fab fa-google" style="color:#4285F4"></i>
            <span>ssdprayas.com</span> &rsaquo; blog &rsaquo; <cite id="serpSlug"><?= e($row['slug'] ?: 'your-article-slug') ?></cite>
          </div>
          <div class="serp-title" id="serpTitle">
            <?= e($row['meta_title'] ?: ($row['title'] ?: 'Article Title | SSD Prayas')) ?>
          </div>
          <p class="serp-desc" id="serpDesc">
            <?= e($row['meta_description'] ?: ($row['excerpt'] ?: 'Add a meta description to control how this article appears in Google Search snippets.')) ?>
          </p>
        </div>

        <div class="form-grid" style="margin-top:24px">
          
          <div class="fg full">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
              <label for="b-mtitle" style="margin:0">Meta Title</label>
              <span class="counter-badge ok" id="mtitleCount">0 / 60 chars</span>
            </div>
            <input type="text" id="b-mtitle" name="meta_title" value="<?= e($row['meta_title']) ?>" placeholder="Optimized title tag (Target: 50–60 characters)">
            <p class="hint">If left blank, the main Post Title will be used.</p>
          </div>

          <div class="fg full">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
              <label for="b-mdesc" style="margin:0">Meta Description</label>
              <span class="counter-badge ok" id="mdescCount">0 / 160 chars</span>
            </div>
            <textarea id="b-mdesc" name="meta_description" style="min-height:90px" maxlength="320" placeholder="A compelling, keyword-rich summary that encourages search clicks (Target: 140–160 characters)"><?= e($row['meta_description']) ?></textarea>
            <p class="hint">Appears in Google search snippets. Keep under 160 characters for optimal display.</p>
          </div>

          <div class="fg full">
            <label for="b-mkeywords">Meta Keywords <span class="hint">(Comma-separated search phrases)</span></label>
            <input type="text" id="b-mkeywords" name="meta_keywords" value="<?= e($row['meta_keywords']) ?>" placeholder="e.g. ai in education, nep 2020 curriculum, artificial intelligence course for schools">
            <p class="hint">Recommended for Bing, Yahoo, and regional search engine indexing.</p>
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- ==================== TAB 3: Schema Code (Article & FAQ) ==================== -->
  <div class="tab-pane" id="tab-schema">
    <div class="card">
      <div class="card-head">
        <h2><i class="fas fa-code text-purple" style="font-size:16px"></i> Structured Data & Schema Code (JSON-LD)</h2>
        <span class="spacer"></span>
        <span class="badge b-purple"><i class="fas fa-check-double"></i> Rich Snippets Ready</span>
      </div>
      <div class="card-body">
        <p style="font-size:13.5px;color:var(--muted);margin-bottom:20px">
          Structured data helps Google understand your content and display enhanced Rich Snippets (Star ratings, FAQ accordions, author credentials, breadcrumbs) in search results.
        </p>

        <!-- 1. Schema Code (Article) -->
        <div style="margin-bottom:26px;padding:20px;background:var(--line-soft);border-radius:16px;border:1px solid var(--line)">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;flex-wrap:wrap;gap:10px">
            <label for="b-schema-art" style="margin:0;font-weight:700;font-size:14px">
              <i class="fas fa-newspaper text-brand"></i> Schema Code (Article / BlogPosting)
            </label>
            <button type="button" class="btn btn-ghost btn-sm" id="btnGenArticleSchema">
              <i class="fas fa-wand-magic-sparkles text-brand"></i> Auto-Generate from Article Details
            </button>
          </div>
          <p class="hint" style="margin-bottom:10px">
            Custom JSON-LD schema for this article. Leave blank to automatically use standard SSD Prayas BlogPosting schema.
          </p>
          <textarea id="b-schema-art" name="schema_article" class="code-textarea" placeholder='{
  "@context": "https://schema.org",
  "@type": "BlogPosting",
  "headline": "Post Title",
  "description": "Article summary..."
}'><?= e($row['schema_article']) ?></textarea>
        </div>

        <!-- 2. Schema Code (FAQ) -->
        <div style="padding:20px;background:var(--line-soft);border-radius:16px;border:1px solid var(--line)">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;flex-wrap:wrap;gap:10px">
            <label for="b-schema-faq" style="margin:0;font-weight:700;font-size:14px">
              <i class="fas fa-circle-question text-green"></i> Schema Code (FAQPage)
            </label>
            <button type="button" class="btn btn-ghost btn-sm" id="btnInsertFaqSchema">
              <i class="fas fa-plus text-green"></i> Insert Sample FAQ Template
            </button>
          </div>
          <p class="hint" style="margin-bottom:10px">
            If your article answers common questions, paste JSON-LD FAQ schema here to display FAQ accordions directly in Google search results.
          </p>
          <textarea id="b-schema-faq" name="schema_faq" class="code-textarea" placeholder='{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "How do students learn AI in school?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Students learn AI through project-based curriculum aligned with NEP 2020."
      }
    }
  ]
}'><?= e($row['schema_faq']) ?></textarea>
        </div>

      </div>
    </div>
  </div>

  <!-- ==================== TAB 4: Open Graph & Twitter Cards ==================== -->
  <div class="tab-pane" id="tab-social">
    <div class="card">
      <div class="card-head">
        <h2><i class="fas fa-share-nodes text-green" style="font-size:16px"></i> Social Media Tags (Open Graph & Twitter Cards)</h2>
        <span class="spacer"></span>
        <span class="badge b-green"><i class="fab fa-whatsapp"></i> WhatsApp, LinkedIn, FB & X</span>
      </div>
      <div class="card-body">
        
        <div style="display:flex;gap:30px;flex-wrap:wrap;align-items:flex-start">
          
          <div style="flex:1;min-width:320px">
            
            <h3 style="font-size:15px;color:var(--ink);margin-bottom:14px;display:flex;align-items:center;gap:8px">
              <i class="fab fa-facebook text-brand"></i> Open Graph Tags (Facebook, LinkedIn, WhatsApp)
            </h3>

            <div class="fg full" style="margin-bottom:16px">
              <label for="b-og-title">OG Title <span class="hint">(Defaults to SEO Title if empty)</span></label>
              <input type="text" id="b-og-title" name="og_title" value="<?= e($row['og_title']) ?>" placeholder="Open Graph headline">
            </div>

            <div class="fg full" style="margin-bottom:16px">
              <label for="b-og-desc">OG Description <span class="hint">(Defaults to SEO Description if empty)</span></label>
              <textarea id="b-og-desc" name="og_description" style="min-height:75px" placeholder="Summary shown when shared on WhatsApp or social networks"><?= e($row['og_description']) ?></textarea>
            </div>

            <div class="fg full" style="margin-bottom:26px">
              <label for="b-og-img">OG Image URL or Upload</label>
              <div style="display:flex;gap:10px;margin-bottom:8px">
                <input type="text" id="b-og-img" name="og_image" value="<?= e($row['og_image']) ?>" placeholder="Image URL or leave empty for featured image" style="flex:1">
              </div>
              <input type="file" name="og_image_file" accept="image/*">
            </div>

            <hr style="border:none;border-top:1px solid var(--line);margin:24px 0">

            <h3 style="font-size:15px;color:var(--ink);margin-bottom:14px;display:flex;align-items:center;gap:8px">
              <i class="fab fa-x-twitter text-ink"></i> Twitter Card Tags (X / Twitter)
            </h3>

            <div class="fg full" style="margin-bottom:16px">
              <label for="b-tw-title">Twitter Title <span class="hint">(Defaults to OG Title if empty)</span></label>
              <input type="text" id="b-tw-title" name="twitter_title" value="<?= e($row['twitter_title']) ?>" placeholder="X / Twitter Card title">
            </div>

            <div class="fg full" style="margin-bottom:16px">
              <label for="b-tw-desc">Twitter Description <span class="hint">(Defaults to OG Description if empty)</span></label>
              <textarea id="b-tw-desc" name="twitter_description" style="min-height:75px" placeholder="Summary shown on Twitter large summary card"><?= e($row['twitter_description']) ?></textarea>
            </div>

            <div class="fg full">
              <label for="b-tw-img">Twitter Image URL or Upload</label>
              <div style="display:flex;gap:10px;margin-bottom:8px">
                <input type="text" id="b-tw-img" name="twitter_image" value="<?= e($row['twitter_image']) ?>" placeholder="Image URL or leave empty for OG/featured image" style="flex:1">
              </div>
              <input type="file" name="twitter_image_file" accept="image/*">
            </div>

          </div>

          <!-- Social Share Preview Box -->
          <div style="width:340px;max-width:100%">
            <label style="font-weight:700;font-size:13.5px;color:var(--ink);display:block;margin-bottom:8px">
              <i class="fas fa-eye text-brand"></i> Social Share Preview
            </label>
            <div class="social-preview-box">
              <img id="spImg" src="<?= e(url($row['og_image'] ?: $row['image'])) ?>" alt="Preview" class="sp-thumb">
              <div class="sp-body">
                <div class="sp-domain">ssdprayas.com</div>
                <div class="sp-title" id="spTitle"><?= e($row['og_title'] ?: ($row['meta_title'] ?: ($row['title'] ?: 'Article Headline'))) ?></div>
                <p class="sp-desc" id="spDesc"><?= e($row['og_description'] ?: ($row['meta_description'] ?: ($row['excerpt'] ?: 'Click to read full article on SSD Prayas official website.'))) ?></p>
              </div>
            </div>
            <p class="hint" style="margin-top:10px">
              Displays preview as formatted when shared in WhatsApp chats, LinkedIn feeds, and Facebook posts.
            </p>
          </div>

        </div>

      </div>
    </div>
  </div>

  <!-- Persistent Actions Bar -->
  <div class="card" style="margin-top:20px;padding:18px 24px;background:#fff;border-radius:18px;border:1px solid var(--line);box-shadow:0 6px 20px rgba(0,0,0,0.03)">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px">
      <div style="display:flex;align-items:center;gap:10px">
        <span class="badge <?= $row['status'] === 'published' ? 'b-green' : 'b-amber' ?>">
          <span class="badge-dot"></span> <?= ucfirst($row['status']) ?>
        </span>
        <span style="font-size:13px;color:var(--muted)">All SEO, Schema and Social features will be updated simultaneously.</span>
      </div>
      <div class="form-actions" style="margin:0;padding:0;border:none;display:flex;gap:10px">
        <button type="submit" class="btn btn-primary" style="padding:12px 24px">
          <i class="fas fa-paper-plane"></i> <?= $id ? 'Save & Update Post' : 'Publish New Post' ?>
        </button>
        <a href="blogs.php" class="btn btn-ghost">Cancel</a>
      </div>
    </div>
  </div>

</form>

<!-- AI Writer Floating Trigger & Panel -->
<button class="ai-fab" id="aiFab" title="AI Writer" type="button"><i class="fas fa-wand-magic-sparkles"></i></button>

<aside class="ai-panel" id="aiPanel">
  <div class="ai-head">
    <div>
      <h2>AI Writer</h2>
      <p style="font-size:13px;color:var(--muted)">Draft an article with full SEO and structured content.</p>
    </div>
    <button class="close" id="aiClose" type="button" aria-label="Close"><i class="fas fa-xmark"></i></button>
  </div>

  <?php if (GEMINI_API_KEY === ''): ?>
    <div class="note note-error" style="display:block">
      <strong>Gemini API key not configured.</strong><br>
      Add your key to <code>config.php</code> to enable AI drafting.
    </div>
  <?php endif; ?>

  <div class="fg">
    <label for="aiPrompt">What should the post be about?</label>
    <textarea id="aiPrompt" style="min-height:96px" placeholder="e.g. Why CBSE introduced AI from Class 3 and what schools need to prepare"></textarea>
  </div>

  <button class="btn btn-primary btn-block" id="genText" type="button" style="margin-bottom:12px">
    <i class="fas fa-pen-fancy"></i> Generate Draft <span class="spin" id="spinText"></span>
  </button>
  <button class="btn btn-ghost btn-block" id="genImg" type="button">
    <i class="fas fa-image"></i> Generate Image <span class="spin" id="spinImg" style="border-color:rgba(0,0,0,.2);border-top-color:var(--brand)"></span>
  </button>

  <div class="ai-out" id="aiOut">
    <div id="aiOutBody"></div>
    <button class="btn btn-primary btn-block" id="applyDraft" type="button" style="margin-top:16px">
      <i class="fas fa-download"></i> Apply to Editor
    </button>
  </div>
</aside>

<script>
(function () {
    var esc = function (s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    };
    var slugify = function (v) {
        return v.toLowerCase().trim().replace(/[^\w\s-]/g, '').replace(/[\s_]+/g, '-').replace(/^-+|-+$/g, '');
    };

    // 1. Tab Switching
    var tabBtns = document.querySelectorAll('.btn-tab');
    var tabPanes = document.querySelectorAll('.tab-pane');
    tabBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var targetId = this.dataset.tab;
            tabBtns.forEach(function (b) { b.classList.remove('is-active'); });
            tabPanes.forEach(function (p) { p.classList.remove('is-active'); });
            this.classList.add('is-active');
            var pane = document.getElementById(targetId);
            if (pane) pane.classList.add('is-active');
        });
    });

    // 2. Normal Editor Logic (0 External Dependencies, 0 CDN, No Domain Restrictions)
    var contentTa     = document.getElementById('b-content');
    var previewBox    = document.getElementById('contentPreviewBox');
    var btnTogglePrev = document.getElementById('btnTogglePreview');
    var prevText      = document.getElementById('previewBtnText');
    var wordCountEl   = document.getElementById('edWordCount');
    var charCountEl   = document.getElementById('edCharCount');

    function updateEditorStats() {
        if (!contentTa) return;
        var val = contentTa.value || '';
        var words = val.trim() ? val.trim().split(/\s+/).length : 0;
        if (wordCountEl) wordCountEl.textContent = words + ' words';
        if (charCountEl) charCountEl.textContent = val.length + ' characters';
    }

    if (contentTa) {
        contentTa.addEventListener('input', updateEditorStats);
        updateEditorStats();
    }

    function insertTag(openTag, closeTag, defaultText) {
        if (!contentTa) return;
        contentTa.focus();
        var start = contentTa.selectionStart;
        var end   = contentTa.selectionEnd;
        var selected = contentTa.value.substring(start, end);
        var text = selected || defaultText || '';
        var replacement = openTag + text + closeTag;
        
        if (typeof contentTa.setRangeText === 'function') {
            contentTa.setRangeText(replacement, start, end, 'end');
        } else {
            contentTa.value = contentTa.value.substring(0, start) + replacement + contentTa.value.substring(end);
        }
        updateEditorStats();
    }

    // Heading & style tag buttons
    document.querySelectorAll('.normal-editor-toolbar .ed-btn[data-tag]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            var tag = this.dataset.tag;
            var placeholders = {
                'h1': 'Heading 1',
                'h2': 'Heading 2',
                'h3': 'Heading 3',
                'h4': 'Heading 4',
                'p': 'Paragraph text...',
                'strong': 'Bold text',
                'em': 'Italic text',
                'u': 'Underlined text',
                'blockquote': 'Quote text...'
            };
            insertTag('<' + tag + '>', '</' + tag + '>', placeholders[tag] || '');
        });
    });

    // Action buttons (Lists, Links, Images, Line break)
    document.querySelectorAll('.normal-editor-toolbar .ed-btn[data-action]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            var action = this.dataset.action;
            if (action === 'br') {
                insertTag('<br>\n', '', '');
            } else if (action === 'ul') {
                insertTag("<ul>\n  <li>", "</li>\n  <li>Item 2</li>\n</ul>\n", "Item 1");
            } else if (action === 'ol') {
                insertTag("<ol>\n  <li>", "</li>\n  <li>Item 2</li>\n</ol>\n", "Item 1");
            } else if (action === 'link') {
                var url = prompt('Link URL enter karein (e.g. https://ssdprayas.com):', 'https://');
                if (url) {
                    insertTag('<a href="' + url.replace(/"/g, '&quot;') + '" target="_blank">', '</a>', 'Link text');
                }
            } else if (action === 'img') {
                var imgUrl = prompt('Image URL enter karein:', 'https://');
                if (imgUrl) {
                    var alt = prompt('Image description (alt text):', 'Blog image') || '';
                    insertTag('<img src="' + imgUrl.replace(/"/g, '&quot;') + '" alt="' + alt.replace(/"/g, '&quot;') + '" style="max-width:100%;border-radius:10px">\n', '', '');
                }
            }
        });
    });

    // Live Preview Toggle
    if (btnTogglePrev && previewBox && contentTa) {
        btnTogglePrev.addEventListener('click', function (e) {
            e.preventDefault();
            var isPreview = (previewBox.style.display !== 'none');
            if (isPreview) {
                previewBox.style.display = 'none';
                contentTa.style.display  = 'block';
                contentTa.focus();
                if (prevText) prevText.textContent = 'Live Preview';
                btnTogglePrev.style.background = '#e8f0fe';
                btnTogglePrev.style.color      = '#1a73e8';
            } else {
                previewBox.innerHTML = contentTa.value.trim() ? contentTa.value : '<p style="color:#94a3b8;font-style:italic">Content khali hai. Textarea me kuch type karein preview dekhne ke liye.</p>';
                previewBox.style.display = 'block';
                contentTa.style.display  = 'none';
                if (prevText) prevText.textContent = 'Edit Content';
                btnTogglePrev.style.background = '#fef3c7';
                btnTogglePrev.style.color      = '#b45309';
            }
        });
    }

    // 3. Title, Slug & SERP Real-time Sync
    var titleInput = document.getElementById('b-title');
    var slugInput  = document.getElementById('b-slug');
    var mtitle     = document.getElementById('b-mtitle');
    var mdesc      = document.getElementById('b-mdesc');
    var excerpt    = document.getElementById('b-excerpt');
    var serpTitle  = document.getElementById('serpTitle');
    var serpDesc   = document.getElementById('serpDesc');
    var serpSlug   = document.getElementById('serpSlug');

    var spTitle = document.getElementById('spTitle');
    var spDesc  = document.getElementById('spDesc');
    var ogTitle = document.getElementById('b-og-title');
    var ogDesc  = document.getElementById('b-og-desc');

    function updateSerp() {
        if (serpSlug) serpSlug.textContent = slugInput.value || 'your-article-slug';
        if (serpTitle) {
            serpTitle.textContent = (mtitle.value.trim() || titleInput.value.trim() || 'Article Title') + ' | SSD Prayas';
        }
        if (serpDesc) {
            serpDesc.textContent = mdesc.value.trim() || excerpt.value.trim() || 'Add a meta description to control how this article appears in Google Search snippets.';
        }
        if (spTitle) {
            spTitle.textContent = ogTitle.value.trim() || mtitle.value.trim() || titleInput.value.trim() || 'Article Headline';
        }
        if (spDesc) {
            spDesc.textContent = ogDesc.value.trim() || mdesc.value.trim() || excerpt.value.trim() || 'Click to read full article on SSD Prayas official website.';
        }
    }

    if (titleInput && slugInput) {
        titleInput.addEventListener('input', function () {
            if (!slugInput.dataset.touched) {
                slugInput.value = slugify(titleInput.value);
            }
            updateSerp();
        });
        slugInput.addEventListener('input', function () {
            slugInput.dataset.touched = '1';
            updateSerp();
        });
    }

    // 4. Character Counters for SEO Title & Description
    var mtitleCount = document.getElementById('mtitleCount');
    var mdescCount  = document.getElementById('mdescCount');

    function updateCounters() {
        var tLen = mtitle.value.length;
        mtitleCount.textContent = tLen + ' / 60 chars';
        mtitleCount.className = 'counter-badge ' + (tLen > 65 ? 'over' : (tLen >= 45 ? 'ok' : 'warn'));

        var dLen = mdesc.value.length;
        mdescCount.textContent = dLen + ' / 160 chars';
        mdescCount.className = 'counter-badge ' + (dLen > 165 ? 'over' : (dLen >= 120 ? 'ok' : 'warn'));

        updateSerp();
    }

    if (mtitle) mtitle.addEventListener('input', updateCounters);
    if (mdesc)  mdesc.addEventListener('input', updateCounters);
    if (excerpt) excerpt.addEventListener('input', updateSerp);
    if (ogTitle) ogTitle.addEventListener('input', updateSerp);
    if (ogDesc)  ogDesc.addEventListener('input', updateSerp);
    updateCounters();

    // 5. Featured Image & Social Image Live Preview
    var imgFile = document.getElementById('b-image-file');
    var imgPrev = document.getElementById('imgPreview');
    var spImg   = document.getElementById('spImg');
    if (imgFile && imgPrev) {
        imgFile.addEventListener('change', function () {
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    imgPrev.src = e.target.result;
                    if (spImg && !document.getElementById('b-og-img').value) {
                        spImg.src = e.target.result;
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // 6. Schema Auto-Generators
    var btnGenArt = document.getElementById('btnGenArticleSchema');
    var txtArt    = document.getElementById('b-schema-art');
    if (btnGenArt && txtArt) {
        btnGenArt.addEventListener('click', function () {
            var artSchema = {
                "@context": "https://schema.org",
                "@type": "BlogPosting",
                "headline": titleInput.value.trim() || "Article Title",
                "image": [ "https://ssdprayas.com/" + (slugInput.value ? "blog/" + slugInput.value : "") ],
                "datePublished": new Date().toISOString(),
                "author": {
                    "@type": "Person",
                    "name": document.getElementById('b-author').value.trim() || "SSD Prayas Team"
                },
                "publisher": {
                    "@type": "EducationalOrganization",
                    "name": "SSD Prayas",
                    "url": "https://ssdprayas.com/"
                },
                "description": mdesc.value.trim() || excerpt.value.trim() || "Article summary"
            };
            txtArt.value = JSON.stringify(artSchema, null, 2);
            alert('Article Schema generated! You can further refine it if needed.');
        });
    }

    var btnFaq = document.getElementById('btnInsertFaqSchema');
    var txtFaq = document.getElementById('b-schema-faq');
    if (btnFaq && txtFaq) {
        btnFaq.addEventListener('click', function () {
            var faqSchema = {
                "@context": "https://schema.org",
                "@type": "FAQPage",
                "mainEntity": [
                    {
                        "@type": "Question",
                        "name": "How does SSD Prayas teach AI in schools?",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "SSD Prayas provides grade-wise curriculum aligned with NEP 2020 and trains existing computer faculty."
                        }
                    },
                    {
                        "@type": "Question",
                        "name": "Is prior coding experience required?",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "No prior coding background is required. Beginner modules start with computational thinking and visual tools."
                        }
                    }
                ]
            };
            txtFaq.value = JSON.stringify(faqSchema, null, 2);
            alert('Sample FAQ Schema inserted! Replace with your article\'s actual FAQs.');
        });
    }

    // 7. AI Assistant Drawer
    var panel = document.getElementById('aiPanel');
    document.getElementById('aiFab').onclick   = function () { panel.classList.add('is-open'); };
    document.getElementById('aiClose').onclick = function () { panel.classList.remove('is-open'); };

    var draft = null;
    var out     = document.getElementById('aiOut');
    var outBody = document.getElementById('aiOutBody');
    var prompt  = document.getElementById('aiPrompt');

    var call = function (action) {
        return fetch('ai_handler.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: action, prompt: prompt.value })
        }).then(function (r) { return r.text(); }).then(function (text) {
            var data;
            try { data = JSON.parse(text); }
            catch (err) { console.error('Non-JSON response:', text); throw new Error('Server sent an invalid response.'); }
            if (data.error) throw new Error(data.error);
            return data;
        });
    };

    document.getElementById('genText').onclick = function () {
        if (!prompt.value.trim()) { alert('Please enter a topic first.'); return; }
        var btn = this, spin = document.getElementById('spinText');
        btn.disabled = true; spin.style.display = 'block';
        call('generate_text').then(function (data) {
            draft = data;
            outBody.innerHTML = '<strong>Title:</strong> ' + esc(data.title) +
                                '<br><br><strong>Tag:</strong> ' + esc(data.tag) +
                                '<br><br><strong>Excerpt:</strong> ' + esc(data.excerpt);
            out.classList.add('is-on');
        }).catch(function (err) {
            alert(err.message);
        }).finally(function () {
            btn.disabled = false; spin.style.display = 'none';
        });
    };

    document.getElementById('genImg').onclick = function () {
        if (!prompt.value.trim()) { alert('Please enter a topic first.'); return; }
        var btn = this, spin = document.getElementById('spinImg');
        btn.disabled = true; spin.style.display = 'block';
        call('generate_image').then(function (data) {
            document.getElementById('imgPreview').src = data.image_url;
            document.getElementById('aiImageUrl').value = data.image_url;
        }).catch(function (err) {
            alert(err.message);
        }).finally(function () {
            btn.disabled = false; spin.style.display = 'none';
        });
    };

    document.getElementById('applyDraft').onclick = function () {
        if (!draft) return;
        document.getElementById('b-title').value   = draft.title   || '';
        document.getElementById('b-tag').value     = draft.tag     || '';
        document.getElementById('b-excerpt').value = draft.excerpt || '';
        document.getElementById('b-slug').value    = slugify(draft.title || '');
        document.getElementById('b-content').value = draft.content || '';
        if (typeof updateEditorStats === 'function') updateEditorStats();
        panel.classList.remove('is-open');
        updateSerp();
    };
})();
</script>

<?php include __DIR__ . '/_layout_end.php'; ?>
