<?php
/**
 * GroqModerationService — Service de modération via Groq (llama-3.3-70b)
 * Utilise un prompt de classification pour détecter le contenu inapproprié.
 * Le modèle retourne un JSON { "safe": bool, "reason": "..." }
 */
class GroqModerationService {
    private $apiKey;
    private $apiUrl;
    private $model;

    private const SYSTEM_PROMPT = <<<'PROMPT'
You are a content moderation classifier for a health/nutrition community forum.
Analyze the user message and determine if it is safe to publish.

REJECT content that contains:
- Hate speech, discrimination, racism, sexism
- Threats of violence, harassment, bullying
- Sexual or pornographic content
- Self-harm or suicide promotion
- Drug abuse promotion
- Spam, scams, phishing links
- Personal information (phone numbers, addresses, emails)
- Dangerous medical advice

ALLOW content that is:
- Normal discussions about health, nutrition, sport, cooking
- Questions, opinions, personal experiences
- Mild language or frustration (not directed at others)

Respond with ONLY a valid JSON object, no other text:
{"safe": true} or {"safe": false, "reason": "brief reason in French"}
PROMPT;

    public function __construct() {
        $this->apiKey = '';
        $this->apiUrl = 'https://api.groq.com/openai/v1/chat/completions';
        $this->model  = 'llama-3.3-70b-versatile';
    }

    /**
     * @param string $text Texte à modérer
     * @return array { success, flagged, categories, reason }
     */
    public function moderateText(string $text): array {
        if (empty(trim($text))) {
            return ['success' => true, 'flagged' => false, 'categories' => [], 'reason' => ''];
        }

        $payload = json_encode([
            'model'       => $this->model,
            'messages'    => [
                ['role' => 'system', 'content' => self::SYSTEM_PROMPT],
                ['role' => 'user',   'content' => $text],
            ],
            'temperature'  => 0,
            'max_tokens'   => 100,
        ]);

        $ch = curl_init($this->apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("GroqModerationService cURL error: $error");
            return ['success' => false, 'flagged' => false, 'categories' => [], 'reason' => 'Service de modération indisponible'];
        }

        if ($httpCode !== 200) {
            error_log("GroqModerationService HTTP $httpCode: $response");
            return ['success' => false, 'flagged' => false, 'categories' => [], 'reason' => 'Erreur service de modération'];
        }

        $data = json_decode($response, true);
        if (!$data || !isset($data['choices'][0]['message']['content'])) {
            error_log("GroqModerationService unexpected response: $response");
            return ['success' => false, 'flagged' => false, 'categories' => [], 'reason' => 'Réponse inattendue'];
        }

        $content = trim($data['choices'][0]['message']['content']);
        // Extraire le JSON de la réponse (le modèle peut ajouter du texte autour)
        if (preg_match('/\{[^}]+\}/', $content, $matches)) {
            $verdict = json_decode($matches[0], true);
        } else {
            $verdict = json_decode($content, true);
        }

        if (!is_array($verdict) || !isset($verdict['safe'])) {
            error_log("GroqModerationService parse error: $content");
            // Fail open
            return ['success' => false, 'flagged' => false, 'categories' => [], 'reason' => 'Erreur de classification'];
        }

        $flagged = !$verdict['safe'];
        $reason  = $flagged ? ($verdict['reason'] ?? 'Contenu inapproprié détecté') : '';

        return [
            'success'    => true,
            'flagged'    => $flagged,
            'categories' => [],
            'reason'     => $reason,
        ];
    }
}
?>
