<?php

class View
{
    public static function render(string $template, array $data = []): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        include APP_ROOT . '/app/Views/' . $template . '.php';
        return ob_get_clean();
    }

    public static function page(string $template, array $data = []): string
    {
        $content = self::render($template, $data);
        $data['content'] = $content;
        $data['user'] = Auth::user();
        return self::render('layout', $data);
    }
}
