<!doctype html>
<html lang="ja">
  <body>
    <h2>注文に関する生産者へのお問い合わせ</h2>
    <p><strong>受付番号:</strong> {{ $referenceNumber }}</p>
    <p><strong>注文番号:</strong> {{ $orderNumber }}</p>
    <p><strong>生産者:</strong> {{ $shopName }}</p>
    <p><strong>お問い合わせ分類:</strong> {{ $topic }}</p>
    <p><strong>お問い合わせ内容:</strong></p>
    <p>{!! nl2br(e($inquiryMessage)) !!}</p>
  </body>
</html>
