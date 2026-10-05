# Receipt Font

`regular/notosansjp.json`, `regular/notosansjp.ctg.z`, and `regular/notosansjp.z` are generated TCPDF resources for embedded Noto Sans JP at weight 400. `NotoSansJP.ttf` is the static weight-400 instance of the official variable font. The upstream font and license are available from [Google Fonts](https://github.com/google/fonts/tree/main/ofl/notosansjp); see `NotoSansJP-OFL.txt` for the font license.

To regenerate the TCPDF resources, download `NotoSansJP[wght].ttf` from that directory and run from `apps/api`:

```sh
php vendor/tecnickcom/tc-lib-pdf-font/util/convert.php \
  --outpath=resources/fonts/regular \
  --type=TrueTypeUnicode \
  --flags=32 \
  --encoding_id=10 \
  --fonts=resources/fonts/NotoSansJP.ttf
```
