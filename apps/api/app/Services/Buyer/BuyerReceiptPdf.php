<?php

namespace App\Services\Buyer;

class BuyerReceiptPdf extends \TCPDF
{
    // TCPDF initializes Helvetica in its constructor, so use the bundled Japanese font instead.
    public function setFont($_family, $_style = '', $_size = null, $_fontfile = '', $_subset = 'default', $_out = true)
    {
        if (strtolower(trim((string) $_family)) === 'helvetica') {
            $_family = 'cid0jp';
        }

        return parent::setFont($_family, $_style, $_size, $_fontfile, $_subset, $_out);
    }
}
