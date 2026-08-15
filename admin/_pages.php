<?php
function lt_admin_pages() {
    return [
        'home'    => ['label' => 'Home',           'desc' => 'Hero, benefits, UGC, testimonials, logos, how it works, numbers, gallery, newsletter, let\'s talk',
                      'sections' => ['hero','benefits','ugc','testimonials','brands','howItWorks','partners','numbers','gallery','newsletter','carousels','letstalk','followUs']],
        'about'   => ['label' => 'About us',       'desc' => 'Intro, story sections, photos, CTA', 'sections' => ['about']],
        'team'    => ['label' => 'Team',           'desc' => 'Members, roles, photos, e-mails',    'sections' => ['team']],
        'faq'     => ['label' => 'FAQ',            'desc' => 'Questions and answers',              'sections' => ['faq']],
        'contact' => ['label' => 'Contact & Demo', 'desc' => 'Contact form texts + Calendly demo section', 'sections' => ['contact','demo']],
        'legal'   => ['label' => 'Legal',          'desc' => 'Terms (PDF), Privacy hub, Cookie policy',    'sections' => ['legal']],
        'global'  => ['label' => 'Global',         'desc' => 'Navigation, footer, SEO defaults',   'sections' => ['nav','footer','seo']],
    ];
}
