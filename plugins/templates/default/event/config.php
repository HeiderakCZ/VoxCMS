<?php

return function (): void {
    $config = $this->getConfig();

    $GLOBALS['sunlight_template_universal_config'] = [
        'logo_url' => (string) ($config['logo_url'] ?? ''),
        'logo_alt' => (string) ($config['logo_alt'] ?? ''),
        'header_text' => (string) ($config['header_text'] ?? ''),
    ];
};
