<?php
/**
 * AI drafting endpoint for the blog editor.
 * Admin-only. Returns JSON in every path.
 */
require_once __DIR__ . '/_auth.php';
require_admin();

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

$input  = json_decode(file_get_contents('php://input'), true) ?: [];
$action = $input['action'] ?? '';
$prompt = trim($input['prompt'] ?? '');

if ($prompt === '') {
    echo json_encode(['error' => 'Please enter a topic first.']);
    exit;
}

/* ---------- Text draft via Gemini ---------- */
if ($action === 'generate_text') {

    if (GEMINI_API_KEY === '') {
        echo json_encode(['error' => 'Gemini API key is not configured. Add it in config.php.']);
        exit;
    }

    $instruction = "You are a professional blog writer for 'SSD Prayas', an Indian AI skilling "
                 . "organisation working with school students (Class 3-12), school educators and "
                 . "working professionals, including large government projects across multiple states. "
                 . "Write a detailed, accurate, educational blog post for the given topic. "
                 . "Return ONLY a JSON object with these keys: title, excerpt, content, tag. "
                 . "'content' must be HTML using <h2>, <h3>, <p>, <ul> and <li> tags. "
                 . "'excerpt' is one or two plain-text sentences. 'tag' is a short category name.";

    $payload = [
        'contents' => [[
            'parts' => [['text' => $instruction . "\n\nTopic: " . $prompt]],
        ]],
        'generationConfig' => ['response_mime_type' => 'application/json'],
    ];

    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key='
         . urlencode(GEMINI_API_KEY);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 60,
    ]);
    $response = curl_exec($ch);
    $http     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_err = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        error_log('Gemini request failed: ' . $curl_err);
        echo json_encode(['error' => 'Could not reach the Gemini API. Check your internet connection.']);
        exit;
    }

    if ($http !== 200) {
        error_log("Gemini API error (HTTP $http): " . $response);
        echo json_encode(['error' => "Gemini API returned an error (HTTP $http). See the server log for details."]);
        exit;
    }

    $result = json_decode($response, true);
    $text   = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

    if ($text === '') {
        error_log('Gemini returned empty content: ' . $response);
        echo json_encode(['error' => 'Gemini returned an empty draft. Try rephrasing the topic.']);
        exit;
    }

    // Gemini is asked for JSON — make sure it really is before passing it on.
    $draft = json_decode($text, true);
    if (!is_array($draft) || empty($draft['title'])) {
        error_log('Gemini returned unparseable draft: ' . $text);
        echo json_encode(['error' => 'The AI response could not be read. Try again.']);
        exit;
    }

    echo json_encode([
        'title'   => (string)($draft['title'] ?? ''),
        'excerpt' => (string)($draft['excerpt'] ?? ''),
        'content' => (string)($draft['content'] ?? ''),
        'tag'     => (string)($draft['tag'] ?? 'AI Education'),
    ]);
    exit;
}

/* ---------- Image via Pollinations ---------- */
if ($action === 'generate_image') {
    $clean = preg_replace('/[^a-zA-Z0-9\s]/', '', $prompt);
    $clean = trim(preg_replace('/\s+/', ' ', $clean));

    if ($clean === '') {
        echo json_encode(['error' => 'Please use letters and numbers in the image prompt.']);
        exit;
    }

    $image_url = 'https://image.pollinations.ai/prompt/' . rawurlencode($clean)
               . '?width=1024&height=640&seed=' . random_int(1000, 9999) . '&nologo=true';

    echo json_encode(['image_url' => $image_url]);
    exit;
}

echo json_encode(['error' => 'Unknown action.']);
