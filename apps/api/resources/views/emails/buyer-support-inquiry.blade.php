<!doctype html>
<html lang="ja">
  <body>
    <h2>購入者からのお問い合わせ</h2>
    <p><strong>受付番号:</strong> {{ $referenceNumber }}</p>
    <p><strong>件名:</strong> {{ $inquirySubject }}</p>
    <p><strong>購入者:</strong> {{ $buyerName }} ({{ $buyerEmail }})</p>
    <p><strong>お問い合わせ内容:</strong></p>
    <p>{!! nl2br(e($inquiryMessage)) !!}</p>
  </body>
</html>
