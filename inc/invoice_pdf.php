<?php
/* VentStudio — real PDF invoice / credit note via FPDF (no external deps). */
require_once __DIR__ . '/store.php';
require_once __DIR__ . '/lib/fpdf.php';

/** UTF-8 -> cp1252 for FPDF core fonts */
function lt_pdf_c($s) {
    $s = str_replace(['—','–','·','…'], ['-','-','-','...'], (string)$s);
    $out = @iconv('UTF-8', 'windows-1252//TRANSLIT', $s);
    return $out === false ? $s : $out;
}

/**
 * @param array  $o    order
 * @param string $mode 'invoice' | 'credit'
 * @param string $dest 'D' download, 'I' inline, 'S' return string
 */
function lt_invoice_pdf(array $o, string $mode = 'invoice', string $dest = 'D') {
    $C = lt_content_load(); $s = $C['settings'] ?? [];
    $sym   = $s['currencySymbol'] ?? '£';
    $legal = $s['legalName'] ?? 'Example Trading Ltd';
    $addr  = $s['address'] ?? '1 Example Street, Your City, AB1 2CD';
    $email = $s['email'] ?? 'hello@yourvenue.co.uk';
    $phone = $s['phone'] ?? '+44 7000 000000';
    $vatNo = $s['vatNumber'] ?? ''; $vatRate = (float)($s['vatRate'] ?? 20);
    $isCredit = $mode === 'credit';
    $mult = $isCredit ? -1 : 1;
    $money = function ($p) use ($sym, $mult) { return lt_pdf_c($sym) . number_format($mult * $p / 100, 2); };

    $cust = $o['customer'] ?? []; $a = $o['address'] ?? null;
    $total = (int)($o['total'] ?? 0);
    $subtotal = (int)($o['subtotal'] ?? 0);
    $fee = (int)($o['delivery_fee_pence'] ?? 0);
    $vatAmt = $vatRate > 0 ? $total - $total / (1 + $vatRate / 100) : 0;
    $vatLabel = rtrim(rtrim(number_format($vatRate, 2), '0'), '.');

    // colours
    $GRID = [27,21,18]; $HOT = [232,67,31]; $HONEY = [246,168,0]; $BAT = [251,234,209]; $MUT = [110,95,84];

    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->SetAutoPageBreak(true, 18);
    $pdf->AddPage();
    $W = 210; $M = 16;

    // header band
    $pdf->SetFillColor($GRID[0],$GRID[1],$GRID[2]); $pdf->Rect(0,0,$W,34,'F');
    $logo = dirname(__DIR__) . '/assets/img/brand/logo-pdf.png';
    if (is_file($logo)) $pdf->Image($logo, $M, 8, 18, 18);
    $pdf->SetTextColor($BAT[0],$BAT[1],$BAT[2]);
    $pdf->SetFont('Helvetica','B',22); $pdf->SetXY($M+22, 11); $pdf->Cell(60,10, lt_pdf_c('VentStudio'),0,0,'L');
    $pdf->SetFont('Helvetica','B',15); $pdf->SetTextColor($HONEY[0],$HONEY[1],$HONEY[2]);
    $pdf->SetXY($W-90, 9); $pdf->Cell(74,8, $isCredit ? 'CREDIT NOTE' : 'INVOICE',0,2,'R');
    $pdf->SetFont('Helvetica','',10); $pdf->SetTextColor($BAT[0],$BAT[1],$BAT[2]);
    $pdf->Cell(74,6, lt_pdf_c(($isCredit ? ($o['credit_number'] ?? ('CN-'.substr($o['number'],3))) : $o['number'])),0,2,'R');
    $pdf->Cell(74,6, lt_pdf_c(date('j M Y, H:i', strtotime($o['created'] ?? 'now'))),0,2,'R');
    // hot bar
    $pdf->SetFillColor($HOT[0],$HOT[1],$HOT[2]); $pdf->Rect(0,34,$W,2,'F');

    // meta columns
    $pdf->SetTextColor($GRID[0],$GRID[1],$GRID[2]);
    $y = 44; $colW = 58;
    $block = function($x,$title,$lines) use ($pdf,$y,$colW,$MUT,$GRID){
        $pdf->SetXY($x,$y); $pdf->SetFont('Helvetica','B',8); $pdf->SetTextColor($MUT[0],$MUT[1],$MUT[2]);
        $pdf->Cell($colW,5, strtoupper($title),0,2,'L');
        $pdf->SetFont('Helvetica','',9); $pdf->SetTextColor($GRID[0],$GRID[1],$GRID[2]);
        foreach ($lines as $ln){ $pdf->SetX($x); $pdf->MultiCell($colW,4.6, lt_pdf_c($ln),0,'L'); }
    };
    $fromLines = [$legal, 'trading as VentStudio Street Food', $addr, $email.'  '.$phone];
    if ($vatNo) $fromLines[] = 'VAT No. '.$vatNo;
    $billLines = [$cust['name'] ?? ''];
    if ($a) $billLines[] = trim(implode(', ', array_filter([$a['line1']??'',$a['line2']??'',$a['city']??'',strtoupper($a['postcode']??'')])));
    $billLines[] = ($cust['phone'] ?? '').'  '.($cust['email'] ?? '');
    $paidTxt = $isCredit ? 'REFUNDED' : (!empty($o['paid']) ? 'PAID' : (($o['payment']??'')==='on_delivery'?'DUE ON DELIVERY':'PENDING'));
    $payTxt = ($o['payment'] ?? '')==='stripe' ? 'Card (Stripe)' : 'Pay on delivery';
    $statusLines = [$paidTxt, $payTxt, ucfirst($o['fulfilment'] ?? 'delivery').(!empty($cust['time'])?'  '.$cust['time']:'')];
    if ($isCredit) $statusLines[] = 'Refund of '.$o['number'];
    $block($M, 'From', $fromLines);
    $block($M+$colW+4, 'Bill to', $billLines);
    $block($M+2*($colW+4), 'Status', $statusLines);

    // items table
    $pdf->SetXY($M, 92);
    $pdf->SetFont('Helvetica','B',8); $pdf->SetTextColor($MUT[0],$MUT[1],$MUT[2]);
    $iw=90; $qw=20; $uw=30; $aw=$W-2*$M-$iw-$qw-$uw;
    $pdf->Cell($iw,7,'ITEM',0,0,'L'); $pdf->Cell($qw,7,'QTY',0,0,'C'); $pdf->Cell($uw,7,'UNIT',0,0,'R'); $pdf->Cell($aw,7,'AMOUNT',0,1,'R');
    $pdf->SetDrawColor(230,220,205); $pdf->Line($M,$pdf->GetY(),$W-$M,$pdf->GetY());
    $pdf->SetFont('Helvetica','',10); $pdf->SetTextColor($GRID[0],$GRID[1],$GRID[2]);
    foreach ((array)$o['items'] as $it){
        $pdf->Cell($iw,7, lt_pdf_c($it['name']),0,0,'L');
        $pdf->Cell($qw,7, (string)(int)$it['qty'],0,0,'C');
        $pdf->Cell($uw,7, $money((int)round($it['price']*100)),0,0,'R');
        $pdf->Cell($aw,7, $money((int)round($it['price']*100*$it['qty'])),0,1,'R');
        $pdf->SetDrawColor(240,233,220); $pdf->Line($M,$pdf->GetY(),$W-$M,$pdf->GetY());
    }
    // totals (right)
    $ty = $pdf->GetY()+6; $tx = $W-$M-70;
    $row = function($label,$val,$bold=false,$small=false) use (&$ty,$pdf,$tx,$GRID,$MUT){
        $pdf->SetXY($tx,$ty); $pdf->SetFont('Helvetica',$bold?'B':'',$small?8:($bold?12:10));
        $pdf->SetTextColor(($small?$MUT[0]:$GRID[0]),($small?$MUT[1]:$GRID[1]),($small?$MUT[2]:$GRID[2]));
        $pdf->Cell(40,$bold?8:6, lt_pdf_c($label),0,0,'L');
        $pdf->Cell(30,$bold?8:6, lt_pdf_c($val),0,1,'R'); $ty += $bold?8:6;
    };
    $row('Subtotal', $money($subtotal));
    if (($o['fulfilment']??'')==='delivery') $row('Delivery', $fee>0?$money($fee):'FREE');
    $pdf->SetDrawColor($GRID[0],$GRID[1],$GRID[2]); $pdf->Line($tx,$ty+1,$tx+70,$ty+1); $ty+=3;
    $row($isCredit?'Total refunded':'Total', $money($total), true);
    if ($vatAmt>0) $row('Includes VAT @ '.$vatLabel.'%', $money($vatAmt), false, true);

    // note + footer
    $pdf->SetXY($M, max($ty, $pdf->GetY())+10); $pdf->SetFont('Helvetica','',8); $pdf->SetTextColor($MUT[0],$MUT[1],$MUT[2]);
    $note = ($vatNo ? 'All prices include VAT at '.$vatLabel.'%. VAT registration number '.$vatNo.'. ' : '');
    $note .= $isCredit ? 'This credit note cancels invoice '.$o['number'].' and confirms a refund of the amount shown.' : 'Thank you for your order!';
    $pdf->MultiCell(0,4.6, lt_pdf_c($note),0,'L');
    $pdf->SetY(-16); $pdf->SetFont('Helvetica','',8); $pdf->SetTextColor($MUT[0],$MUT[1],$MUT[2]);
    $pdf->Cell(0,6, lt_pdf_c($legal.' t/a VentStudio Street Food  -  '.$addr.'  -  example.com'),0,0,'C');

    $fname = ($isCredit ? 'CreditNote-' : 'Invoice-') . ($o['number'] ?? 'order') . '.pdf';
    return $pdf->Output($dest, $fname, true);
}
