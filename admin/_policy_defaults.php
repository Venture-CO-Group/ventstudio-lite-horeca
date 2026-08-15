<?php
/* Default VentStudio policy document set (cloned from example.com/en/policies).
   VentStudio's own docs point to locally-hosted PDFs in /assets/doc/;
   partner/campaign docs link out to their official pages.
   Each entry: group (partner), label{en,hu,es}, pdf (file or URL), logo (relative to /assets/img/). */
function lt_policy_defaults() {
    $L = function ($en) { return ['en' => $en, 'hu' => '', 'es' => '']; };
    return [
        ['group'=>'VentStudio App',        'label'=>$L('Privacy Policy'),                         'pdf'=>'/assets/doc/ventstudio-app-privacy-policy.pdf',                    'logo'=>'brand/logo-blue.webp'],
        ['group'=>'VentStudio App',        'label'=>$L('General Terms and Conditions'),           'pdf'=>'/assets/doc/ventstudio-app-general-terms-and-conditions.pdf',      'logo'=>'brand/logo-blue.webp'],
        ['group'=>'VentStudio App',        'label'=>$L('SimplePay Data Transmission Statement'),  'pdf'=>'/en/simplepay-data-transmission-statement',                 'logo'=>'brand/logo-blue.webp'],
        ['group'=>'FanXP (MLSZ)',    'label'=>$L('Privacy Policy'),                         'pdf'=>'/assets/doc/v1-fanxp-privacy-policy.pdf',                    'logo'=>'logos/mlsz-logo.webp'],
        ['group'=>'FanXP (MLSZ)',    'label'=>$L('General Terms and Conditions'),           'pdf'=>'/assets/doc/v1-fanxp-general-terms-and-conditions.pdf',      'logo'=>'logos/mlsz-logo.webp'],
        ['group'=>'MOL Selfie Cam',  'label'=>$L('Privacy Policy'),                         'pdf'=>'/assets/doc/ventstudio-mol-selfie-cam-privacy-policy.pdf',         'logo'=>'logos/mol-logo.webp'],
        ['group'=>'MOL Selfie Cam',  'label'=>$L('General Terms and Conditions'),           'pdf'=>'/assets/doc/ventstudio-mol-selfie-cam-general-terms-and-conditions.pdf','logo'=>'logos/mol-logo.webp'],
        ['group'=>'Stock Pro Series','label'=>$L('Política de Privacidade'),                'pdf'=>'https://www.stockproseries.com.br/stockcar/proseries/politica-privacidade', 'logo'=>''],
        ['group'=>'Stock Pro Series','label'=>$L('Termos de uso'),                          'pdf'=>'https://www.stockproseries.com.br/stockcar/proseries/termos-de-uso',       'logo'=>''],
        ['group'=>'Sirens Netball',  'label'=>$L('Privacy Policy'),                         'pdf'=>'https://sirensnetball.com/privacy-policy/',                  'logo'=>''],
        ['group'=>'Hibernian FC',    'label'=>$L('Privacy Policy'),                         'pdf'=>'https://www.hibernianfc.co.uk/our-club/policies',            'logo'=>''],
        ['group'=>'AS Roma',         'label'=>$L('Privacy Policy'),                         'pdf'=>'https://www.asroma.com/en/privacy-policy',                   'logo'=>'logos/as-roma-logo-2017-svg.webp'],
        ['group'=>'AS Roma',         'label'=>$L('Terms and Conditions'),                  'pdf'=>'https://www.asroma.com/en/terms-and-conditions',             'logo'=>'logos/as-roma-logo-2017-svg.webp'],
        ['group'=>'Sevilla FC',      'label'=>$L('Privacy Policy'),                         'pdf'=>'https://sevillafc.es/en/privacy-policy',                     'logo'=>''],
        ['group'=>'Sevilla FC',      'label'=>$L('Condiciones legales Fan Zone'),           'pdf'=>'https://sevillafc.es/condiciones-legales-fan-zone',          'logo'=>''],
        ['group'=>'UNIQA',           'label'=>$L('Adatkezelés'),                            'pdf'=>'https://www.uniqa.hu/adatkezeles',                          'logo'=>'logos/uniqa-insurance-group-logo.webp'],
        ['group'=>'Fan campaign',    'label'=>$L('Privacy Policy'),                         'pdf'=>'https://www.iubenda.com/privacy-policy/30710801',            'logo'=>''],
        ['group'=>'Fan campaign',    'label'=>$L('Terms and Conditions'),                  'pdf'=>'https://www.iubenda.com/terms-and-conditions/30710801',      'logo'=>''],
        ['group'=>'Red Bull Brazil', 'label'=>$L('Política de Privacidade'),                'pdf'=>'https://policies.redbull.com/policies/RedBull.com_Brazil/202006080742/pt/privacy.html', 'logo'=>''],
        ['group'=>'Red Bull Brazil', 'label'=>$L('Termos & Condições'),                     'pdf'=>'https://policies.redbull.com/policies/RedBull.com_Brazil/202006080742/pt/terms.html',   'logo'=>''],
        ['group'=>'Atomized Studios','label'=>$L('Privacy Policy'),                         'pdf'=>'https://www.atomizedstudios.tv/privacy-policy',              'logo'=>''],
        ['group'=>'Atomized Studios','label'=>$L('Terms of use'),                           'pdf'=>'https://www.atomizedstudios.tv/terms-of-use',               'logo'=>''],
        ['group'=>'WaterAid',        'label'=>$L('Privacy Policy'),                         'pdf'=>'https://www.wateraid.org/uk/privacy-notice',                 'logo'=>''],
        ['group'=>'WaterAid',        'label'=>$L('Terms of use'),                           'pdf'=>'https://www.wateraid.org/uk/terms-and-conditions',           'logo'=>''],
        ['group'=>'Villarreal CF',   'label'=>$L('Privacy Policy'),                         'pdf'=>'https://villarrealcf.es/en/politica-de-privacidad/',         'logo'=>'logos/logo-villarreal.webp'],
    ];
}
