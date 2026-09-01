<?php
/**
 * Service: Sort
 */

class Sort {
    static function formatSortTitle($title, $sortName) {

        $site_title = Option::get('site_title');
        $blogname = Option::get('blogname');
        $title = trim((string)$title);
        $placeholderTexts = [
            '标题（用于分类页的 title）',
            '标题（用于分类页的 title）支持变量: {{site_title}}, {{site_name}}, {{sort_name}}',
        ];

        if (empty($site_title)) {
            $site_title = $blogname;
        }
        if (empty($title) || in_array($title, $placeholderTexts, true)) {
            return $sortName;
        }
        return strtr($title, [
            '{{site_title}}' => $site_title,
            '{{site_name}}'  => $blogname,
            '{{sort_name}}'  => $sortName
        ]);
    }
}
