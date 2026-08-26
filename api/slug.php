<?php
declare(strict_types=1);

/** Small shared identifier primitive; feature modules must not depend on Writing for slug normalization. */
if(!function_exists('cleanSlug')){
    function cleanSlug(string $slug): string {
        $slug=strtolower(trim($slug));
        $slug=preg_replace('/[^a-z0-9-]+/','-',$slug)??'';
        $slug=preg_replace('/-+/','-',$slug)??'';
        return trim($slug,'-');
    }
}
