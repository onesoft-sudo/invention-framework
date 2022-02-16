<?php


namespace OSN\Framework\PowerParser;


trait ParseData
{
    protected function replacements(): array
    {
        return [
            "!{{" => '<?php ',
            "}}!" => '; ?>',
            "{{" => '<?= htmlspecialchars(',
            "}}" => ' ?? ""); ?>',
        ];
    }
}
