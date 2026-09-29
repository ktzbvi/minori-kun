<?php

$localSample = in_array(env('APP_ENV', 'production'), ['local', 'testing'], true);
$termsVersion = env('PRODUCER_TERMS_VERSION') ?: null;
$termsTitle = env('PRODUCER_TERMS_TITLE') ?: null;
$termsContent = env('PRODUCER_TERMS_CONTENT') ?: null;
$sampleTerms = $localSample && (! $termsVersion || ! $termsTitle || ! $termsContent);

return [
    'cookie_name' => env('PRODUCER_REGISTRATION_COOKIE', 'producer_registration_session'),
    'pending_ttl_minutes' => 1440,
    'verified_ttl_minutes' => 1440,
    'otp_ttl_seconds' => 180,
    'resend_cooldown_seconds' => 180,
    'photo_ttl_minutes' => 1440,
    'photo_max_bytes' => 5 * 1024 * 1024,
    'photo_max_dimension' => 4096,
    'photo_output_dimension' => 1024,
    'terms' => [
        'version' => $termsVersion ?: ($localSample ? 'local-sample-1' : null),
        'title' => $termsTitle ?: ($localSample ? '生産者利用規約（開発用サンプル）' : null),
        'content' => $termsContent ?: ($localSample ? "これはローカル・テスト環境専用の未承認サンプルです。\n本番の規約として使用しないでください。\n\n開発用の説明として、当社は販売額の10%を販売手数料として受領します。" : null),
        'is_sample' => $sampleTerms,
    ],
];
