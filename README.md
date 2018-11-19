# smartblog-urls-in-sitemappro

Prestashop 1.6.9.х Integration SitemapPro with SmartBlog

Prestashop's SitemapPro doesn't work with SmartBlog so the post urls don't go into the sitemap file. The code below solves this problem. 

Installation. You need to open /modules/sitemappro/classes/sitemap/SitemapBuilder.php and add it below this line: $sitemap->createSitemapIndex(ToolsSMP::getShopDomainWithBase());<br>
Then go to admin page and regenerate the sitemap.
The code is:<br>
$p_urls = Db::getInstance()->ExecuteS('SELECT post.`id_smart_blog_post`, CONCAT(post.`id_smart_blog_post`,"_",postlang.link_rewrite,".html") as link '.'FROM `'._DB_PREFIX_.'smart_blog_post` post, '._DB_PREFIX_.'smart_blog_post_lang postlang '.'WHERE post.active=1 and post.id_smart_blog_post=postlang.id_smart_blog_post '.'and postlang.id_lang='.(int)$l['id_lang'].' order by id_smart_blog_post asc');
		
		foreach ($p_urls as $p_url)
		    $sitemap->addItem('/SmartBlog/' . $p_url['link'] . '', '0.7', 'weekly', 'Today', array(), ''); <br>
Where '/SmartBlog/' is the url of your blog<br>
Or just replace the file or add a new file into /overrides/...

Модуль SitemapPro от Prestashop не поддерживает модуль SmartBlog, поэтому в карте сайта отсутствуют ссылки на записи блога. Код ниже решает эту проблему.

Установка. Вам нужно открыть /modules/sitemappro/classes/sitemap/SitemapBuilder.php и вставить код перед:
$sitemap->createSitemapIndex(ToolsSMP::getShopDomainWithBase());<br>
Дальше зайти в админку, настройки модуля и заново сгенерировать карту сайта.
Сам код:<br>
$p_urls = Db::getInstance()->ExecuteS('SELECT post.`id_smart_blog_post`, CONCAT(post.`id_smart_blog_post`,"_",postlang.link_rewrite,".html") as link '.'FROM `'._DB_PREFIX_.'smart_blog_post` post, '._DB_PREFIX_.'smart_blog_post_lang postlang '.'WHERE post.active=1 and post.id_smart_blog_post=postlang.id_smart_blog_post '.'and postlang.id_lang='.(int)$l['id_lang'].' order by id_smart_blog_post asc');
		
		foreach ($p_urls as $p_url)
		    $sitemap->addItem('/SmartBlog/' . $p_url['link'] . '', '0.7', 'weekly', 'Today', array(), ''); <br>
Где '/SmartBlog/' это адрес Вашего блога<br>
Или просто замените файл, или добавьте этот файл в /overrides/...
