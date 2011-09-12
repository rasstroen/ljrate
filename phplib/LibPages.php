<?php  /* GENERATED AUTOMATICALLY AT 2011-09-12 12:54:25, DO NOT MODIFY */
class LibPages{
public static $pages = 
array (
  'main' => 
  array (
    'title' => 'Главная страница',
    'name' => 'main',
    'params' => 
    array (
      'cache' => true,
      'cache_sec' => 120,
    ),
    'xslt' => 'main.xsl',
    'modules' => 
    array (
    ),
    'modules_deprecated' => 
    array (
    ),
  ),
  'register' => 
  array (
    'title' => 'Регистрация',
    'name' => 'register',
    'params' => 
    array (
      'cache' => true,
      'cache_sec' => 120,
    ),
    'xslt' => 'main.xsl',
    'modules' => 
    array (
    ),
    'modules_deprecated' => 
    array (
    ),
  ),
  'emailconfirm' => 
  array (
    'title' => 'Подтверждение email',
    'name' => 'emailconfirm',
    'params' => 
    array (
      'cache' => true,
      'cache_sec' => 120,
    ),
    'xslt' => 'main.xsl',
    'modules' => 
    array (
    ),
    'modules_deprecated' => 
    array (
    ),
  ),
  'backend' => 
  array (
    'title' => 'Админка',
    'name' => 'backend',
    'params' => 
    array (
      'cache' => false,
      'cache_sec' => 0,
    ),
    'xslt' => 'admin.xsl',
    'modules' => 
    array (
      'BackendModule' => 
      array (
        'block' => 'content',
        'roles' => 
        array (
          20 => '20',
        ),
      ),
    ),
    'modules_deprecated' => 
    array (
    ),
  ),
  'p404' => 
  array (
    'title' => '404',
    'name' => 'p404',
    'params' => 
    array (
      'cache' => true,
      'cache_sec' => 120,
    ),
    'xslt' => 'p404.xsl',
    'modules' => 
    array (
    ),
    'modules_deprecated' => 
    array (
    ),
  ),
  'news' => 
  array (
    'title' => 'Новости',
    'name' => 'news',
    'params' => 
    array (
      'cache' => true,
      'cache_sec' => 120,
    ),
    'xslt' => 'main.xsl',
    'modules' => 
    array (
    ),
    'modules_deprecated' => 
    array (
    ),
  ),
  'user' => 
  array (
    'title' => 'Профиль',
    'name' => 'user',
    'params' => 
    array (
      'cache' => false,
      'cache_sec' => 0,
    ),
    'xslt' => 'main.xsl',
    'modules' => 
    array (
    ),
    'modules_deprecated' => 
    array (
    ),
  ),
  'p502' => 
  array (
    'title' => '502',
    'name' => 'p502',
    'params' => 
    array (
      'cache' => true,
      'cache_sec' => 120,
    ),
    'xslt' => 'p502.xsl',
    'modules' => 
    array (
    ),
    'modules_deprecated' => 
    array (
    ),
  ),
);
}