<?php
function summarizeText($text) {
    $api_key = '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';

    $postData = [
        "model" => "meta-llama/llama-4-scout-17b-16e-instruct",
        "messages" => [
            [
                "role" => "user",
                "content" => "Summarize this legal case in 1-2 sentences:\n\n" . $text
            ]
        ]
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.groq.com/openai/v1/chat/completions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer $api_key"
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    if (isset($result['choices'][0]['message']['content'])) {
        $rawSummary = trim($result['choices'][0]['message']['content']);

        // Strip off any leading "Here is a 2-sentence summary of the legal case:" (or similar)
        $cleanedSummary = preg_replace([
            '/^summary\s*:\s*/i',
            '/^here is (?:a\s+)?\d+-sentence summary of the legal case:\s*/i',
            '/^here is (?:a\s+)?summary of the legal case:\s*/i',
        ], '', $rawSummary);

        return $cleanedSummary;
    } elseif (isset($result['error']['message'])) {
        return "AI Error: " . $result['error']['message'];
    } else {
        return "Summary not available.";
    }
}
