<!doctype html>
<html lang="ja"><head><meta charset="utf-8"></head><body>
<p>みのりくん 生産者ポータル</p>
<p>パスワードの再設定を受け付けました。以下のリンクから新しいパスワードを設定してください。</p>
<p><a href="{{ $resetUrl }}">パスワードを再設定する</a></p>
<p>このリンクは{{ config('producer-password-reset.expires_minutes') }}分間有効で、一度だけ使用できます。</p>
<p>お心当たりがない場合は、このメールを破棄してください。</p>
</body></html>
