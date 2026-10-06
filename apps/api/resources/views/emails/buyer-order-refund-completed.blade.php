<!doctype html>
<html lang="ja">
  <body>
    <h2>返金処理が完了しました</h2>
    <p>{{ $shopName }} の注文 {{ $orderNumber }} について、決済事業者から返金成功が確認されました。</p>
    <p>返金額：{{ number_format($refundAmountYen) }}円</p>
    <p>カード明細への反映時期は、ご利用のカード会社により異なります。</p>
  </body>
</html>
