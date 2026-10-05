<!doctype html>
<html lang="ja">
  <body>
    <h2>ご注文をキャンセルしました</h2>
    <p>{{ $shopName }} の注文 {{ $orderNumber }} をキャンセルしました。</p>
    <p>返金額：{{ number_format($refundAmountYen) }}円</p>
    <p>返金処理は現在進行中です。完了後、あらためてメールでお知らせします。</p>
    <p>カード明細への反映時期は、ご利用のカード会社により異なります。</p>
  </body>
</html>
