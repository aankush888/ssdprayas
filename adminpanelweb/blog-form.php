<?php
require_once __DIR__ . '/_auth.php';
require_admin();
require_csrf();

$id  = (int)($_GET['id'] ?? 0);
$row = [
    'id' => 0, 'title' => '', 'slug' => '', 'tag' => '', 'excerpt' => '', 'content' => '',
    'image' => 'assets/img/blog-1.jpg', 'author' => 'SSD Prayas Team', 'status' => 'published',
    'meta_title' => '', 'meta_description' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $edit_id = (int)($_POST['id'] ?? 0);
    $title   = trim($_POST['title'] ?? '');
    $slug    = slugify($_POST['slug'] ?? '') ?: slugify($title);

    $image = trim($_POST['existing_image'] ?? '') ?: 'assets/img/blog-1.jpg';
    if (!empty($_POST['ai_image_url'])) {
        // Only accept an https image URL from the AI generator.
        $candidate = filter_var($_POST['ai_image_url'], FILTER_VALIDATE_URL);
        if ($candidate && str_starts_with($candidate, 'https://')) {
            $image = $candidate;
        }
    }
    $uploaded = handle_upload('image', UPLOAD_BLOGS, ['jpg', 'jpeg', 'png', 'webp', 'gif'], 'blog-');
    if ($uploaded !== null) {
        $image = $uploaded;
    }

    $data = [
        'title'            => $title,
        'slug'             => $slug,
        'tag'              => trim($_POST['tag'] ?? ''),
        'excerpt'          => trim($_POST['excerpt'] ?? ''),
        'content'          => $_POST['content'] ?? '',
        'image'            => $image,
        'author'           => trim($_POST['author'] ?? '') ?: 'SSD Prayas Team',
        'status'           => enum_or($_POST['status'] ?? '', ['published', 'draft'], 'published'),
        'meta_title'       => trim($_POST['meta_title'] ?? ''),
        'meta_description' => trim($_POST['meta_description'] ?? ''),
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
    $row = $found;
}

$admin_title  = $id ? 'Edit Post' : 'Write Post';
$admin_active = 'blogs';
$admin_sub    = $id ? 'Editing "' . $row['title'] . '"' : 'Write a new article, or let the AI writer draft it.';
include __DIR__ . '/_layout.php';
?>

<style>
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
</style>

<!-- Form Hero Header -->
<div class="mod-hero-strip">
  <div class="mod-hero-left">
    <div class="mod-hero-icon mhi-purple">
      <i class="fas fa-pen-nib"></i>
    </div>
    <div>
      <h2 class="mod-hero-title"><?= e($admin_title) ?></h2>
      <p class="mod-hero-sub">Draft and publish educational articles with AI assistance and rich content formatting.</p>
    </div>
  </div>
  <div class="mod-hero-actions">
    <a href="blogs.php" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Back to Articles</a>
  </div>
</div>

<div class="card">
  <div class="card-head">
    <h2><i class="fas fa-feather-pointed text-purple" style="font-size:16px"></i> Article Composer</h2>
    <span class="spacer"></span>
    <a href="blogs.php" class="btn btn-ghost btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
  </div>
  <div class="card-body">
    <form method="POST" enctype="multipart/form-data" id="blogForm">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
      <input type="hidden" name="existing_image" value="<?= e($row['image']) ?>">
      <input type="hidden" name="ai_image_url" id="aiImageUrl">

      <div class="form-grid">
        <div class="fg full">
          <label for="b-title">Post Title <span class="req">*</span></label>
          <input type="text" id="b-title" name="title" required value="<?= e($row['title']) ?>" placeholder="A clear, specific headline">
        </div>
        <div class="fg">
          <label for="b-tag">Category Tag</label>
          <input type="text" id="b-tag" name="tag" value="<?= e($row['tag']) ?>" placeholder="e.g. AI Education, Policy">
        </div>
        <div class="fg">
          <label for="b-slug">URL Slug</label>
          <input type="text" id="b-slug" name="slug" value="<?= e($row['slug']) ?>" placeholder="auto-generated from title">
        </div>
        <div class="fg">
          <label for="b-author">Author</label>
          <input type="text" id="b-author" name="author" value="<?= e($row['author']) ?>">
        </div>
        <div class="fg">
          <label for="b-status">Status</label>
          <select id="b-status" name="status">
            <option value="published" <?= $row['status'] === 'published' ? 'selected' : '' ?>>Published</option>
            <option value="draft"     <?= $row['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
          </select>
        </div>

        <div class="fg full">
          <label>Featured Image</label>
          <div style="display:flex;align-items:center;gap:18px;flex-wrap:wrap">
            <img id="imgPreview" src="<?= e(url($row['image'])) ?>" alt=""
                 style="width:150px;height:90px;object-fit:cover;border-radius:12px;border:1.5px solid var(--line)">
            <input type="file" name="image" accept="image/*" style="flex:1;min-width:220px">
          </div>
          <p class="hint">Upload a JPG/PNG/WebP under <?= (int)MAX_UPLOAD_MB ?> MB, or generate one with the AI writer.</p>
        </div>

        <div class="fg full">
          <label for="b-excerpt">Short Excerpt</label>
          <textarea id="b-excerpt" name="excerpt" style="min-height:80px" placeholder="One or two lines shown on the blog listing"><?= e($row['excerpt']) ?></textarea>
        </div>

        <div class="fg full">
          <label for="b-content">Full Content</label>
          <textarea id="b-content" name="content"><?= e($row['content']) ?></textarea>
        </div>

        <div class="fg">
          <label for="b-mtitle">SEO Title <span class="hint" style="display:inline">(optional)</span></label>
          <input type="text" id="b-mtitle" name="meta_title" value="<?= e($row['meta_title']) ?>">
        </div>
        <div class="fg">
          <label for="b-mdesc">SEO Description <span class="hint" style="display:inline">(optional)</span></label>
          <input type="text" id="b-mdesc" name="meta_description" value="<?= e($row['meta_description']) ?>" maxlength="300">
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> <?= $id ? 'Update Post' : 'Publish Post' ?></button>
        <a href="blogs.php" class="btn btn-ghost">Cancel</a>
      </div>
    </form>
  </div>
</div>

<button class="ai-fab" id="aiFab" title="AI Writer" type="button"><i class="fas fa-wand-magic-sparkles"></i></button>

<aside class="ai-panel" id="aiPanel">
  <div class="ai-head">
    <div>
      <h2>AI Writer</h2>
      <p style="font-size:13px;color:var(--muted)">Draft a post, then edit it before publishing.</p>
    </div>
    <button class="close" id="aiClose" type="button" aria-label="Close"><i class="fas fa-xmark"></i></button>
  </div>

  <?php if (GEMINI_API_KEY === ''): ?>
    <div class="note note-error" style="display:block">
      <strong>Gemini API key not configured.</strong><br>
      Add your key to <code>config.php</code> (or the <code>GEMINI_API_KEY</code> environment variable)
      to enable AI drafting. Image generation works without it.
    </div>
  <?php endif; ?>

  <div class="fg">
    <label for="aiPrompt">What should the post be about?</label>
    <textarea id="aiPrompt" style="min-height:96px" placeholder="e.g. Why AI training for teachers matters more than AI classes for students"></textarea>
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

<script src="https://cdn.tiny.cloud/1/cfovpxfzfx9fxcuionrvci6065cwk71prl39eibgvt0n1prn/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
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

    tinymce.init({
        selector: '#b-content',
        plugins: 'anchor autolink charmap code codesample image link lists media searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks | bold italic underline | link image media table | bullist numlist | alignleft aligncenter | removeformat code',
        height: 460,
        menubar: false,
        branding: false,
        content_style: 'body{font-family:"Plus Jakarta Sans",system-ui,sans-serif;font-size:15px;line-height:1.7}'
    });

    var title = document.getElementById('b-title');
    var slug  = document.getElementById('b-slug');
    if (title && slug) {
        title.addEventListener('input', function () {
            if (!slug.dataset.touched) slug.value = slugify(title.value);
        });
        slug.addEventListener('input', function () { slug.dataset.touched = '1'; });
    }

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
        if (tinymce.get('b-content')) {
            tinymce.get('b-content').setContent(draft.content || '');
        } else {
            document.getElementById('b-content').value = draft.content || '';
        }
        panel.classList.remove('is-open');
    };
})();
</script>

<?php include __DIR__ . '/_layout_end.php'; ?>
