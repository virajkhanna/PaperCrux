<?php 
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 300);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

$api = "https://models.github.ai/inference/chat/completions";
$api_key = "INSERT_GITHUB_PERSONAL_ACCESS_TOKEN_HERE";

function check_recaptcha($recaptcha_response) {
    $recaptcha_secret_key = "INSERT_RECAPTCHA_SECRET_KEY_HERE";

    $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . $recaptcha_secret_key . "&response=" . $recaptcha_response);
    
    $result = json_decode($response);
    
    if ($result->success) {
        return true;
    } else {
        error_log("reCAPTCHA verification failed.\n");
        echo "4003";
        die();
    }
}

function summarise($papertext, $wordlimit) {
    global $api, $api_key;

    $data = [
        "messages" => [
            [
                "role" => "system",
                "content" => "DO NOT SAY ANYTHING EXCEPT THE SUMMARY, THIS IS AN INTERGRATION IN A SOFTWARE WITH AN API"
            ],
            [
                "role" => "user",
                "content" => "Summarise this research paper. You are given the text in the format of pages, understand the whole thing, and then write your response in JSON, with three things:- the main summary of the paper, the main claims made, and the main proof provided. In JSON, the key for the main summary should be 'main', the key for the claims should be 'claims', and the key for the proof should be 'proof'. Here's the text: $papertext. Summarize in a maximum of $wordlimit per part of summary (each of the three parts). Don't make seperate part of the JSON for the claims or proofs, just make a text summary for the key value. Use <br> to change lines and keep it clean."
            ],
        ],
        "temperature" => 1.0,
        "top_p" => 1.0,
        "max_tokens" => 1000,
        "model" => "openai/gpt-4.1"
    ];

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $api);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer $api_key"
    ]);

    $response = curl_exec($ch);

    $jsonResponse = json_decode($response, true);

    if (isset($jsonResponse['choices'][0]['message']['content'])) {
        return $jsonResponse['choices'][0]['message']['content'];
        error_log("RESPONSE FROM GPT: " . $jsonResponse['choices'][0]['message']['content']);
    } else {
        return 'No content found in response.';
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $text = $_POST['text'];
    $wordLimit = $_POST['wordLimit'];
    $recaptcha_response = $_POST['recaptchaReply'];

    $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    $wordLimit = htmlspecialchars($wordLimit, ENT_QUOTES, 'UTF-8');

    error_log("TEXT BY USER: $text\n WORD LIMIT: $wordLimit");

    if (check_recaptcha($recaptcha_response)) {
        $summary = summarise($text, $wordLimit);
        print_r($summary);
    }
}
?>
