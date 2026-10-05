<?php

namespace App\Services\Buyer;

class BuyerReceiptPdf extends \TCPDF
{
    // Keep TCPDF's default family on the embedded font so layout metrics match the rendered glyphs.
    public function setFont($_family, $_style = '', $_size = null, $_fontfile = '', $_subset = 'default', $_out = true)
    {
        if (strtolower(trim((string) $_family)) === 'helvetica') {
            $_family = 'notosansjp';
        }

        return parent::setFont($_family, $_style, $_size, $_fontfile, $_subset, $_out);
    }
}
